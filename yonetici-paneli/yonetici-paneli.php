<?php
/**
 * ==========================================================================
 * DOSYA: yonetici-paneli.php (Ana Eklenti Dosyası - Hata Düzeltilmiş)
 * ==========================================================================
 *
 * Plugin Name:       Yönetici Paneli - Çiçek Mezatı
 * Description:       Çiçek Mezatı sitesi için özel yönetici paneli.
 * Version:           2.1 (Hata Düzeltilmiş)
 * Author:            Sizin Adınız
 * License:           GPL v2 or later
 * Text Domain:       yonetici-paneli
 */

// WordPress'in dışında doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Eklenti için temel sabitleri tanımla
define( 'YP_VERSION', '2.1' );
define( 'YP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'YP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// ==========================================================================
// HATA DÜZELTMESİ:
// Tüm sınıf dosyalarını, ana eklenti sınıfı tanımlanmadan ÖNCE yüklüyoruz.
// Bu, PHP'nin "CicekMezati_Yonetici_Paneli" sınıfını okumaya başladığında,
// ihtiyaç duyacağı diğer tüm sınıfları (YP_Admin_Menus, YP_Ajax_Handlers vb.)
// zaten tanıyor olmasını sağlar. "Class not found" hatasının temel çözümü budur.
// ==========================================================================
require_once YP_PLUGIN_DIR . 'includes/class-yp-db-helper.php';
require_once YP_PLUGIN_DIR . 'includes/class-yp-admin-menus.php';
require_once YP_PLUGIN_DIR . 'includes/class-yp-admin-columns.php';
require_once YP_PLUGIN_DIR . 'includes/class-yp-acf-enhancements.php';
require_once YP_PLUGIN_DIR . 'includes/class-yp-ajax-handlers.php';

/**
 * Ana eklenti sınıfı.
 */
class CicekMezati_Yonetici_Paneli {

    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // init_components fonksiyonu artık doğrudan __construct içinde çağrılıyor.
        // Bu, eklenti yüklendiği anda tüm bileşenlerin başlamasını sağlar.
        new YP_Admin_Menus();
        new YP_Admin_Columns();
        new YP_ACF_Enhancements();
        new YP_Ajax_Handlers();
    }

    public function activate() {
        add_role('yonetici_yardimcisi', __('Yönetici Yardımcısı'), get_role('editor')->capabilities);
        YP_DB_Helper::create_tables();
    }

    public function deactivate() {
        remove_role('yonetici_yardimcisi');
    }
}

// Eklentiyi başlat
new CicekMezati_Yonetici_Paneli();
// --- Bitiş: yonetici-paneli.php ---
