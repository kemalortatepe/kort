<?php
/**
 * ==========================================================================
 * KLASÖR: yonetici-paneli/includes/
 * DOSYA: class-yp-acf-enhancements.php (Yeni Dosya)
 * AÇIKLAMA: Bu sınıf, ACF PRO formlarına zeka ve iş mantığı ekler.
 * Form alanları arasında dinamik hesaplamalar yapar ve veri girişi sırasında
 * doğrulama kuralları uygular.
 * ==========================================================================
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YP_ACF_Enhancements {

    /**
     * Sınıf yapıcı metodu. Gerekli ACF kancalarını (hooks) ekler.
     */
    public function __construct() {
        // 1. ACF'nin yönetici paneli altbilgisine özel JavaScript eklemek için kanca.
        // Bu, stok hesaplama gibi dinamik form etkileşimleri için kullanılır.
        add_action('acf/input/admin_footer', array($this, 'inject_stock_calculation_js'));

        // 2. Bir ACF alanı kaydedilmeden önce değeri doğrulamak için filtre.
        // Bu, mezattaki çiçek miktarının ana stoktan fazla olmamasını kontrol etmek için kullanılır.
        // `name=mezat_stok` kısmı, bu filtrenin sadece Alan Adı 'mezat_stok' olan alanda çalışmasını sağlar.
        add_filter('acf/validate_value/name=mezat_stok', array($this, 'validate_auction_stock'), 10, 4);
    }

    /**
     * "Çiçek Ekle/Düzenle" sayfasına dinamik stok hesaplama JavaScript'ini ekler.
     */
    public function inject_stock_calculation_js() {
        // Sadece 'cicek' gönderi türünün düzenleme ekranında olduğumuzdan emin ol.
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'cicek' ) {
            return;
        }
        
        // ACF alanlarının anahtar (key) değerlerini alıyoruz. Bu, en güvenilir yöntemdir.
        // Bu anahtarları, ACF arayüzünde alan adının yanında görebilirsiniz.
        // ÖNEMLİ: Kendi ACF kurulumunuzdaki alan anahtarları farklıysa, bunları güncellemeniz gerekir.
        $stok_miktari_key = 'field_667f1b7f0c1b5'; // Örnek anahtar
        $stok_birimi_key = 'field_667f1b950c1b6';   // Örnek anahtar
        $ambalaj_adet_key = 'field_667f1ba90c1b7';  // Örnek anahtar
        $konteyner_adet_key = 'field_667f1bc00c1b8'; // Örnek anahtar
        $toplam_stok_key = 'field_667f1bd40c1b9';    // Örnek anahtar (Bu bir metin alanı olmalı)
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            // İlgili ACF alanlarını seç
            const stokMiktariInput = acf.getField('<?php echo $stok_miktari_key; ?>').$el.find('input');
            const stokBirimiSelect = acf.getField('<?php echo $stok_birimi_key; ?>').$el.find('select');
            const ambalajInput = acf.getField('<?php echo $ambalaj_adet_key; ?>').$el.find('input');
            const konteynerInput = acf.getField('<?php echo $konteyner_adet_key; ?>').$el.find('input');
            const toplamStokField = acf.getField('<?php echo $toplam_stok_key; ?>');

            function hesaplaVeGuncelle() {
                let miktar = parseInt(stokMiktariInput.val()) || 0;
                let birim = stokBirimiSelect.val();
                let ambalajAdet = parseInt(ambalajInput.val()) || 1;
                let konteynerAdet = parseInt(konteynerInput.val()) || 1;
                let toplam = 0;

                if (birim === 'tane') {
                    toplam = miktar;
                } else if (birim === 'ambalaj') {
                    toplam = miktar * ambalajAdet;
                } else if (birim === 'konteyner') {
                    toplam = miktar * konteynerAdet * ambalajAdet;
                }

                // Hesaplanan toplamı "Tane Olarak Toplam Stok" alanına yazdır.
                // Bu alanın "Salt Okunur" (Read Only) olarak ayarlanması tavsiye edilir.
                toplamStokField.val(toplam.toLocaleString('tr-TR') + ' adet');
            }

            // İlgili alanlardan herhangi biri değiştiğinde hesaplamayı yeniden yap.
            stokMiktariInput.on('keyup change', hesaplaVeGuncelle);
            stokBirimiSelect.on('change', hesaplaVeGuncelle);
            ambalajInput.on('keyup change', hesaplaVeGuncelle);
            konteynerInput.on('keyup change', hesaplaVeGuncelle);
            
            // Sayfa yüklendiğinde ilk hesaplamayı yap.
            hesaplaVeGuncelle();
        });
        </script>
        <?php
    }

    /**
     * "Mezat Oluştur/Düzenle" sayfasında, mezata konulan stok miktarını doğrular.
     *
     * @param bool   $valid      Alan değerinin geçerli olup olmadığı (true/false).
     * @param mixed  $value      Alanın mevcut değeri.
     * @param array  $field      Alan ayarları dizisi.
     * @param string $input_name Formdaki alanın 'name' niteliği.
     * @return bool|string       Geçerliyse true, geçersizse hata mesajı döndürür.
     */
    public function validate_auction_stock($valid, $value, $field, $input_name) {
        // Eğer değer zaten geçersizse (örn: boş bırakılmışsa), dokunma.
        if (!$valid) {
            return $valid;
        }

        // Formdan seçilen çiçeğin post ID'sini al.
        // ACF, verileri `$_POST['acf']` altında bir dizi olarak gönderir.
        $cicek_post_id = isset($_POST['acf']['field_60b8b1a2c3d5e']) ? intval($_POST['acf']['field_60b8b1a2c3d5e']) : 0; // 'mezattaki_cicek' alanının anahtarı
        
        if ( ! $cicek_post_id ) {
            return $valid; // Çiçek seçilmemişse doğrulama yapma.
        }
        
        // Seçilen çiçeğin ana stok miktarını veritabanından çek.
        global $wpdb;
        $ana_stok = $wpdb->get_var($wpdb->prepare(
            "SELECT toplam_stok_tane FROM {$wpdb->prefix}cm_cicekler WHERE post_id = %d",
            $cicek_post_id
        ));

        // Mezat stoğu, ana stoktan büyükse hata mesajı döndür.
        if ( (int)$value > (int)$ana_stok ) {
            $valid = 'Mezata konulan miktar (' . $value . ' adet), çiçeğin ana stoğundan (' . $ana_stok . ' adet) fazla olamaz.';
        }

        return $valid;
    }
}
// --- Bitiş: includes/class-yp-acf-enhancements.php ---
