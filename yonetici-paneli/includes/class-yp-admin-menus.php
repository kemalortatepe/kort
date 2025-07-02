<?php
/**
 * ==========================================================================
 * KLASÖR: yonetici-paneli/includes/
 * DOSYA: class-yp-admin-menus.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu sınıf, "Yönetici Paneli" eklentisinin WordPress yönetici
 * panelindeki özel menülerini oluşturur.
 *
 * YENİ YAPI GÜNCELLEMESİ:
 * - "Çiçek Listesi", "Çiçek Oluştur", "Mezat Listesi" ve "Mezat Oluştur" menüleri
 * kaldırılmıştır. Bu işlevler artık doğrudan WordPress'in ana menüsünden
 * (ACF PRO ile güçlendirilmiş "Çiçekler" ve "Mezatlar" sekmeleri) yönetilecektir.
 * - Bu dosya, artık sadece ACF'nin yönetmediği özel sayfaları menüye ekler.
 * ==========================================================================
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YP_Admin_Menus {

    /**
     * Sınıf yapıcı metodu. Gerekli kancaları ekler.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'register_admin_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Yönetici menülerini ve alt menüleri WordPress'e kaydeder.
     */
    public function register_admin_pages() {
        // Ana menü öğesini oluştur: 'Çiçek Mezatı'
        add_menu_page(
            __('Çiçek Mezatı', 'yonetici-paneli'),      // Sayfa başlığı (<title> etiketi)
            __('Çiçek Mezatı', 'yonetici-paneli'),      // Menüde görünecek isim
            'edit_posts',                               // Bu menüyü görebilmek için gereken minimum yetki
            'yp-dashboard',                             // Ana sayfanın benzersiz adı (slug)
            array($this, 'render_page'),                // Sayfa içeriğini oluşturacak fonksiyon
            'dashicons-palmtree',                       // Menü ikonu
            20                                          // Menünün WordPress menüsündeki sırası
        );

        // **DÜZELTME:** Alt menüler sadeleştirildi.
        // Artık sadece özel eklenti sayfalarımız burada tanımlanıyor.
        $submenus = [
            'yp-dashboard'             => __('Ana Panel', 'yonetici-paneli'),
            'yp-musteri-yonetimi'      => __('Müşteri Yönetimi', 'yonetici-paneli'),
            'yp-satis-raporlari'       => __('Satış Raporları', 'yonetici-paneli'),
            'yp-stok-raporlari'        => __('Stok Raporları', 'yonetici-paneli'),
            'yp-odeme-raporlari'       => __('Ödeme Raporları', 'yonetici-paneli'),
            'yp-odeme-ayarlari'        => __('Ödeme Ayarları', 'yonetici-paneli'),
            'yp-hesap-ayarlari'        => __('Hesap Ayarları', 'yonetici-paneli'),
        ];
        
        // Döngü ile tüm alt menüleri otomatik olarak oluştur
        foreach ($submenus as $slug => $title) {
            // Ana menü öğesi ('yp-dashboard') zaten add_menu_page ile oluşturulduğu için,
            // submenu olarak tekrar eklemeye gerek yok. Ancak menüdeki ismini değiştirmek
            // için bu özel yöntemi kullanabiliriz.
            $hook = add_submenu_page(
                'yp-dashboard', // Ana menünün slug'ı
                $title,         // Sayfa başlığı (<title> etiketi)
                $title,         // Menüde görünecek isim
                'edit_posts',   // Gereken yetki
                $slug,          // Sayfanın benzersiz adı (slug)
                array($this, 'render_page')
            );
        }
        
        // Ana menünün kendisi de bir alt menü olarak eklenir, bu yüzden ilk elemanı kaldırarak
        // menüde "Çiçek Mezatı" isminin tekrar etmesini önlüyoruz.
        remove_submenu_page('yp-dashboard', 'yp-dashboard');
    }

    /**
     * URL'deki 'page' parametresine göre ilgili PHP şablon dosyasını yükler.
     */
    public function render_page() {
        $page_slug = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'yp-dashboard';
        $path = YP_PLUGIN_DIR . "includes/admin-pages/page-{$page_slug}.php";
        
        echo '<div class="wrap yp-wrap">';
        if (file_exists($path)) {
            include $path;
        } else {
            echo '<h2>' . esc_html($page_slug) . ' sayfası için şablon dosyası bulunamadı.</h2>';
            echo '<p>Beklenen dosya yolu: <code>' . esc_html($path) . '</code></p>';
        }
        echo '</div>';
    }
    
    /**
     * Yönetici paneli için gerekli CSS ve JS dosyalarını yükler.
     */
    public function enqueue_scripts($hook) {
        // Scriptleri sadece bizim eklenti sayfalarımızda ve ACF'nin Çiçek/Mezat ekranlarında yükle.
        $screen = get_current_screen();
        if (strpos($hook, 'yp-') === false && (!is_object($screen) || !in_array($screen->post_type, ['cicek', 'mezat']))) {
            return;
        }

        wp_enqueue_style('yp-admin-style', YP_PLUGIN_URL . 'assets/css/admin-style.css', array(), YP_VERSION);
        wp_enqueue_media();
        wp_enqueue_script('yp-admin-script', YP_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), YP_VERSION, true);
        
        wp_localize_script('yp-admin-script', 'yp_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('yp_ajax_nonce'),
        ]);
    }
}
// --- Bitiş: includes/class-yp-admin-menus.php ---
