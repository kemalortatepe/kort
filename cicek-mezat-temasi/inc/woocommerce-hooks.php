<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/inc/
 * DOSYA: woocommerce-hooks.php (Nihai Düzeltilmiş Sürüm)
 * AÇIKLAMA: Bu dosya, WooCommerce "Hesabım" sayfasını tamamen özelleştirir.
 * Yönlendirme döngüsü hatası giderilmiş ve tüm özel endpoint'lerin
 * içeriklerinin doğru şablonlardan yüklenmesi sağlandı.
 * ==========================================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1. "Hesabım" menü öğelerini yeniden düzenler ve özel sekmeler ekler.
 */
function cicekmezat_my_account_menu_items( $items ) {
    unset($items['orders'], $items['downloads'], $items['dashboard']);
    return [
        'mezat-takvimi'      => __( 'Mezat Takvimi', 'cicekmezat' ),
        'edit-account'       => __( 'Hesap Bilgilerim', 'cicekmezat' ),
        'edit-address'       => __( 'Adres Bilgilerim', 'cicekmezat' ),
        'kredi-durumu'       => __( 'Kredi Durumu', 'cicekmezat' ),
        'kredi-al'           => __( 'Kredi Al', 'cicekmezat' ),
        'kredimi-iade-et'    => __( 'Kredimi İade Et', 'cicekmezat' ),
        'gecmis-mezatlarim'  => __( 'Geçmiş Mezatlarım', 'cicekmezat' ),
        'odeme-yap'          => __( 'Ödeme Yap', 'cicekmezat' ),
        'customer-logout'    => __( 'Çıkış Yap', 'cicekmezat' ),
    ];
}
add_filter( 'woocommerce_account_menu_items', 'cicekmezat_my_account_menu_items', 99 );

/**
 * 2. "Hesabım" sayfası için yeni URL endpoint'leri kaydeder.
 */
function cicekmezat_add_my_account_endpoints() {
    $endpoints = ['mezat-takvimi', 'kredi-durumu', 'kredi-al', 'kredimi-iade-et', 'gecmis-mezatlarim', 'odeme-yap'];
    foreach ($endpoints as $endpoint) {
        add_rewrite_endpoint( $endpoint, EP_PAGES );
    }
}
add_action( 'init', 'cicekmezat_add_my_account_endpoints' );

/**
 * 3. YENİ ve KRİTİK FONKSİYON: Yeni oluşturulan her bir endpoint için, hangi şablon
 * dosyasının yükleneceğini belirler. Boş sayfa veya yanlış içerik sorununun ana çözümü budur.
 */
function cicekmezat_endpoint_content() {
    $endpoints = ['mezat-takvimi', 'kredi-durumu', 'kredi-al', 'kredimi-iade-et', 'gecmis-mezatlarim', 'odeme-yap'];
    foreach ($endpoints as $endpoint) {
        add_action('woocommerce_account_' . $endpoint . '_endpoint', function() use ($endpoint) {
            wc_get_template("myaccount/{$endpoint}.php");
        });
    }
}
add_action('init', 'cicekmezat_endpoint_content');

/**
 * 4. Yeni endpoint'ler için sayfa başlıklarını belirler.
 */
function cicekmezat_custom_endpoint_titles( $title, $endpoint ) {
    $titles = [
        'mezat-takvimi'      => __( 'Mezat Takvimi', 'cicekmezat' ),
        'edit-account'       => __( 'Hesap Bilgilerim', 'cicekmezat' ),
        'edit-address'       => __( 'Adres Bilgilerim', 'cicekmezat' ),
        'kredi-durumu'       => __( 'Kredi Durumu', 'cicekmezat' ),
        'kredi-al'           => __( 'Kredi Al', 'cicekmezat' ),
        'kredimi-iade-et'    => __( 'Kredimi İade Et', 'cicekmezat' ),
        'gecmis-mezatlarim'  => __( 'Geçmiş Mezatlarım', 'cicekmezat' ),
        'odeme-yap'          => __( 'Ödeme Yap', 'cicekmezat' ),
    ];
    return isset($titles[$endpoint]) ? $titles[$endpoint] : $title;
}
add_filter( 'woocommerce_endpoint_title', 'cicekmezat_custom_endpoint_titles', 10, 2 );

/**
 * 5. GÜNCELLENMİŞ FONKSİYON: Kullanıcıyı varsayılan "Pano" yerine doğrudan "Mezat Takvimi"ne yönlendirir.
 * Bu mantık, sizin sağladığınız daha güvenli kod ile güncellenmiştir.
 */
function cicekmezat_redirect_dashboard_to_auctions() {
    if (
        is_account_page() &&
        is_user_logged_in() &&
        WC()->query->get_current_endpoint() === '' &&
        !isset($_GET['customer-logout'])
    ) {
        $target_url = wc_get_account_endpoint_url('mezat-takvimi');
        // Mevcut URL zaten hedefse tekrar yönlendirme yapma (Sizin sağladığınız güvenli kontrol)
        if ( untrailingslashit($_SERVER['REQUEST_URI']) !== untrailingslashit(parse_url($target_url, PHP_URL_PATH)) ) {
            wp_safe_redirect( $target_url );
            exit;
        }
    }
}
add_action( 'template_redirect', 'cicekmezat_redirect_dashboard_to_auctions' );
