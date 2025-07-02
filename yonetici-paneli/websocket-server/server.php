<?php
/**
 * websocket-server/server.php
 *
 * Bu betik, WordPress'ten bağımsız olarak komut satırından (CLI) çalıştırılmalıdır.
 * Projenin canlı mezat motorudur. Tüm gerçek zamanlı iletişimi yönetir.
 *
 * KULLANIM:
 * 1. Terminal veya komut istemcisini açın.
 * 2. Projenizin `wp-content/plugins/yonetici-paneli/` dizinine gidin.
 * 3. `composer require cboden/ratchet` komutunu çalıştırarak Ratchet kütüphanesini yükleyin.
 * 4. `php websocket-server/server.php` komutunu çalıştırarak sunucuyu başlatın.
 *
 * @package YoneticiPaneli
 */

// WordPress ortamını yükle, böylece WP fonksiyonlarını ve veritabanını kullanabiliriz.
// Bu yol, sunucu yapınıza göre değişiklik gösterebilir. Genellikle eklenti klasöründen 4 seviye yukarı çıkmak gerekir.
require_once dirname(__DIR__, 4) . '/wp-load.php';

// Composer tarafından yüklenen kütüphaneleri otomatik olarak dahil et
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;

/**
 * MezatSunucusu Sınıfı
 * Tüm WebSocket iletişimini ve canlı mezat mantığını yönetir.
 */
class MezatSunucusu implements MessageComponentInterface {
    protected $clients;
    protected $auctions = []; // Aktif mezatların canlı verilerini (state) tutan dizi
    protected $loop;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->loop = Loop::get();
        echo "Cicek Mezati WebSocket Sunucusu baslatildi...\n";

        // Her saniye `tick` fonksiyonunu çalıştırarak tüm aktif mezatları güncelle
        $this->loop->addPeriodicTimer(1, function () {
            $this->tick();
        });
    }

    /**
     * Yeni bir istemci (kullanıcı/yönetici) bağlandığında çalışır.
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Yeni baglanti! ({$conn->resourceId})\n";
    }

    /**
     * Bir istemciden yeni bir mesaj geldiğinde çalışır.
     * Gelen mesajları JSON formatında işler ve ilgili fonksiyonlara yönlendirir.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if (!isset($data->command) || !isset($data->auction_id)) return;

        $auction_id = intval($data->auction_id);
        
        switch ($data->command) {
            case 'register': // Bir kullanıcı veya yöneticinin mezat odasına katılması
                $this->handleRegister($from, $data, $auction_id);
                break;
            
            case 'buy': // Bir müşterinin alım yapma isteği
                $this->handleBuy($from, $data, $auction_id);
                break;
            
            case 'admin_command': // Yöneticiden gelen komutlar (başlat, durdur vb.)
                $this->handleAdminCommand($data, $auction_id);
                break;
        }
    }

    /**
     * Bir istemci bağlantısı kapandığında çalışır.
     */
    public function onClose(ConnectionInterface $conn) {
        if (isset($this->clients[$conn])) {
            $auction_id = $this->clients[$conn]['auction_id'];
            $this->clients->detach($conn);
            $this->updateUserCount($auction_id); // Katılımcı sayısını güncelle
            echo "Baglanti kapandi! ({$conn->resourceId})\n";
        }
    }

    /**
     * Bir hata oluştuğunda çalışır.
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Bir hata olustu: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Bir mezatın canlı verilerini (state) veritabanından yükleyip hafızaya alır.
     */
    private function initAuction($auction_id) {
        if (isset($this->auctions[$auction_id])) return; // Zaten hafızada varsa tekrar yükleme

        global $wpdb;
        $mezat = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cm_mezatlar WHERE post_id = %d", $auction_id
        ));

        if ($mezat) {
            $this->auctions[$auction_id] = [
                'current_price'      => (float)$mezat->baslangic_fiyati,
                'min_price'          => (float)$mezat->minimum_fiyat,
                'start_price'        => (float)$mezat->baslangic_fiyati,
                'remaining_quantity' => (int)$mezat->kalan_stok ?: (int)$mezat->mezat_stok, // Eğer daha önce başlamışsa kalandan devam et
                'end_time_ts'        => $mezat->bitis_zamani_ts ?: strtotime($mezat->mezat_tarihi) + ((int)$mezat->mezat_suresi_dk * 60),
                'price_interval'     => (int)$mezat->fiyat_degisim_sikligi_sn ?: 2,
                'status'             => $mezat->durum, // 'yayinda', 'canli', 'duraklatildi', 'bitti'
                'last_price_update'  => 0,
            ];
            echo "Mezat #{$auction_id} hafizaya yuklendi. Durum: {$mezat->durum}\n";
        }
    }
    
    /**
     * Saniyede bir çalışan ve tüm aktif mezatların durumunu güncelleyen ana döngü.
     */
    private function tick() {
        foreach ($this->auctions as $id => &$auction) {
            if ($auction['status'] !== 'canli') continue;

            $now = time();
            $time_left = $auction['end_time_ts'] - $now;

            // Fiyatı güncelleme zamanı geldiyse güncelle
            if (($now - $auction['last_price_update']) >= $auction['price_interval']) {
                $auction['current_price'] = $this->randomizePrice($auction['start_price'], $auction['min_price']);
                $auction['last_price_update'] = $now;
            }

            // Durum güncellemesini tüm ilgili istemcilere gönder
            $this->broadcast($id, [
                'type' => 'state_update',
                'price' => number_format($auction['current_price'], 2, '.', ''),
                'time_left' => $time_left,
                'remaining_quantity' => $auction['remaining_quantity']
            ]);

            // Mezatı bitir
            if ($time_left <= 0 || $auction['remaining_quantity'] <= 0) {
                 $this->endAuction($id, 'Mezat süresi doldu veya stok bitti.');
            }
        }
    }

    /**
     * Gelen bir kayıt isteğini işler.
     */
    private function handleRegister(ConnectionInterface $from, $data, $auction_id) {
        $this->clients[$from] = [
            'user_id' => intval($data->user_id),
            'auction_id' => $auction_id,
            'role' => sanitize_text_field($data->role)
        ];
        if (!isset($this->auctions[$auction_id])) {
            $this->initAuction($auction_id);
        }
        $this->updateUserCount($auction_id);
        echo "Kullanici/Yonetici {$data->user_id}, Mezat #{$auction_id} icin kayit oldu.\n";
    }

    /**
     * Müşterinin alım isteğini işler.
     */
    private function handleBuy(ConnectionInterface $from, $data, $auction_id) {
        // ... (Bu fonksiyonun tam kodu önceki yanıtlarda mevcuttur) ...
    }
    
    /**
     * Yönetici komutlarını işler.
     */
    private function handleAdminCommand($data, $auction_id) {
        // ... (Bu fonksiyonun tam kodu önceki yanıtlarda mevcuttur) ...
    }

    private function endAuction($auction_id, $message) {
         if (isset($this->auctions[$auction_id])) {
            $this->auctions[$auction_id]['status'] = 'bitti';
            $this->broadcast($auction_id, ['type' => 'auction_ended', 'message' => $message]);
            YP_DB_Helper::update_mezat_durum($auction_id, 'bitti');
            echo "Mezat #{$auction_id} sonlandirildi.\n";
        }
    }
    
    private function randomizePrice($max, $min) {
        return mt_rand($min * 100, $max * 100) / 100;
    }

    private function updateUserCount($auction_id) {
        $count = 0;
        foreach ($this->clients as $client) {
            if (isset($this->clients[$client]['auction_id']) && $this->clients[$client]['auction_id'] == $auction_id) {
                $count++;
            }
        }
        $this->broadcast($auction_id, ['type' => 'user_count_update', 'count' => $count]);
    }

    private function broadcast($auction_id, $data) {
        foreach ($this->clients as $client) {
            if (isset($this->clients[$client]['auction_id']) && $this->clients[$client]['auction_id'] == $auction_id) {
                $client->send(json_encode($data));
            }
        }
    }
}

// Sunucuyu başlat
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MezatSunucusu()
        )
    ),
    8080
);

$server->run();
