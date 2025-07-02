<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/inc/
 * DOSYA: setup.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu dosya, tema ilk aktive edildiğinde çalışan temel kurulum ayarlarını içerir.
 * Menü konumları, HTML5 desteği, öne çıkan görsel desteği gibi WordPress
 * özelliklerini tema için etkinleştirir. Bu dosya, ana functions.php
 * tarafından çağrılarak temanın çekirdek yapılandırmasını oluşturur.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fonksiyonun daha önce tanımlanıp tanımlanmadığını kontrol et. Bu, alt tema (child theme)
// kullanımında veya başka eklentilerle çakışmaları önler.
if ( ! function_exists( 'cicekmezat_setup' ) ) :
    /**
     * Temanın temel kurulumunu ve ayarlarını yapar.
     * Bu fonksiyon 'after_setup_theme' kancası ile çalıştırılır. Bu kanca, WordPress
     * tema dosyalarını yükledikten hemen sonra tetiklenir.
     */
    function cicekmezat_setup() {
        
        // Temanın çeviri dosyalarını yükler. Bu, temanızın çok dilli sitelerde
        // doğru şekilde çalışmasını sağlar. 'cicekmezat' text domain'i,
        // çeviri fonksiyonlarında kullanılacak olan anahtardır.
        load_theme_textdomain( 'cicekmezat', CICEKMEZAT_THEME_DIR . '/languages' );

        // <head> bölümüne RSS beslemeleri için otomatik linkler ekler.
        add_theme_support( 'automatic-feed-links' );

        // WordPress'in sayfa başlığını (<title> etiketi) yönetmesine izin verir.
        // Bu, SEO eklentileriyle daha iyi uyumluluk sağlar.
        add_theme_support( 'title-tag' );

        // Yazılar ve sayfalar için "Öne Çıkan Görsel" kutusunu etkinleştirir.
        // Çiçekler ve mezatlar için görselleri bu özellik üzerinden yöneteceğiz.
        add_theme_support( 'post-thumbnails' );

        // Temanın WooCommerce ile tam uyumlu olduğunu ve özelliklerini
        // desteklediğini WordPress'e bildirir. Bu, e-ticaret fonksiyonları için zorunludur.
        add_theme_support( 'woocommerce' );

        // WordPress paneli > Görünüm > Menüler altında kullanılabilir menü konumları oluşturur.
        register_nav_menus(
            array(
                // Header'da kullanılacak ana menü
                'primary' => esc_html__( 'Ana Menü', 'cicekmezat' ),
                // Footer'da kullanılacak alt bilgi menüsü
                'footer'  => esc_html__( 'Alt Bilgi Menüsü', 'cicekmezat' ),
            )
        );

        // Arama formu, yorum formu, galeri gibi alanlarda modern HTML5
        // yapısının kullanılmasını sağlar. Bu, daha temiz ve standartlara uygun kod çıktısı üretir.
        add_theme_support(
            'html5',
            array( 
                'search-form', 
                'comment-form', 
                'comment-list', 
                'gallery', 
                'caption', 
                'style', 
                'script' 
            )
        );

        // Madde 16: Temanın özel bir logo yüklemesini desteklediğini belirtir.
        // Bu sayede "Görünüm > Özelleştir" menüsünden logo yüklenebilir hale gelir.
        add_theme_support( 'custom-logo', array(
            'height'      => 100, // Önerilen maksimum yükseklik
            'width'       => 400, // Önerilen maksimum genişlik
            'flex-height' => true,
            'flex-width'  => true,
        ) );
    }
endif;

// cicekmezat_setup fonksiyonunu WordPress'in doğru zamanında çalıştır.
add_action( 'after_setup_theme', 'cicekmezat_setup' );
// --- Bitiş: inc/setup.php ---
