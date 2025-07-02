<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: functions.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Temanızın ana fonksiyon ve yapılandırma dosyasıdır.
 * Bu dosya, temanın beyni gibi çalışır; tüm diğer PHP bileşenlerini,
 * scriptleri ve stilleri organize eder ve sisteme dahil eder.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- TEMA SABİTLERİ ---
// Temanın versiyonunu ve dizin yollarını sabit olarak tanımlıyoruz.
// Bu, dosya yollarını yönetmeyi ve sürüm kontrolünü kolaylaştırır.
define( 'CICEKMEZAT_VERSION', '2.2' ); // ACF PRO uyumlu ve güncel sürüm
define( 'CICEKMEZAT_THEME_DIR', get_template_directory() );
define( 'CICEKMEZAT_THEME_URI', get_template_directory_uri() );


// --- ÇEKİRDEK TEMA DOSYALARINI YÜKLEME ---
// Projenin ana işlevlerini organize ettiğimiz `inc/` klasörü altındaki
// dosyaları `require_once` ile buraya dahil ediyoruz.
$theme_includes = array(
    '/inc/setup.php',             // Tema temel ayarları (add_theme_support vb.)
    '/inc/enqueue.php',           // CSS ve JavaScript dosyalarını yükler.
    '/inc/post-types.php',        // 'cicek' ve 'mezat' türlerini kaydeder.
    '/inc/woocommerce-hooks.php', // WooCommerce "Hesabım" sayfasını özelleştirir.
    '/inc/ajax-handlers.php',     // Tema ön yüzü için AJAX isteklerini yönetir.
    '/inc/template-functions.php',// Temaya özel yardımcı fonksiyonlar.
);

foreach ( $theme_includes as $file ) {
    // Dosyanın var olup olmadığını kontrol ederek hata oluşmasını engelle
    if ( file_exists( CICEKMEZAT_THEME_DIR . $file ) ) {
        require_once CICEKMEZAT_THEME_DIR . $file;
    }
}


// ==========================================================================
// WORDPRESS ARAYÜZ ÖZELLEŞTİRMELERİ (Madde 16, 17, 18)
// ==========================================================================

/**
 * Yönetici Giriş Sayfası Başlığını Değiştirir.
 * Bu fonksiyon, wp-login.php sayfasındaki varsayılan "WordPress" yazısını değiştirir.
 *
 * @param string $login_header_text Orijinal başlık metni.
 * @return string Yeni başlık metni.
 */
function cicekmezat_custom_login_title( $login_header_text ) {
    return 'Çiçek Mezat Yönetim Paneli'; // Buraya istediğiniz başlığı yazabilirsiniz.
}
add_filter( 'login_headertext', 'cicekmezat_custom_login_title' );

/**
 * Yönetici Giriş Sayfası Logo Linkini Değiştirir.
 * Varsayılan olarak wordpress.org'a giden logo linkini, sitenizin ana sayfasına yönlendirir.
 *
 * @param string $login_header_url Orijinal URL.
 * @return string Yeni URL.
 */
function cicekmezat_custom_login_url( $login_header_url ) {
    return home_url('/');
}
add_filter( 'login_headerurl', 'cicekmezat_custom_login_url' );

/**
 * Yönetici Giriş Sayfasına Özel Logo ve Stil Ekler.
 * Logonuzu temanızın `assets/images/admin-logo.png` yoluna kaydedin.
 */
function cicekmezat_custom_login_logo_style() { 
    // Bu fonksiyonun içeriği `inc/enqueue.php` dosyasına taşınmıştır.
    // Tüm stil ve script yüklemelerini tek bir yerden yönetmek daha doğru bir pratiktir.
}
// Bu kanca artık `inc/enqueue.php` içinde yönetilmektedir.


/**
 * Yönetici Paneli'nin sol üst köşesindeki WordPress logosunu kaldırır.
 */
function cicekmezat_remove_admin_bar_logo() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('wp-logo');
}
add_action('admin_bar_menu', 'cicekmezat_remove_admin_bar_logo', 999);
