<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/inc/
 * DOSYA: enqueue.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu dosya, tema için gerekli olan tüm CSS (stil) ve JavaScript (script)
 * dosyalarını WordPress'e doğru şekilde yükler (enqueue). Ayrıca, PHP'den JavaScript'e
 * veri aktarmak için wp_localize_script fonksiyonunu kullanırız. Bu,
 * AJAX ve WebSocket bağlantıları için kritik öneme sahiptir.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tema için CSS ve JavaScript dosyalarını dahil eder.
 * Bu fonksiyon, sitenin ön yüzü her yüklendiğinde 'wp_enqueue_scripts' kancası ile çalışır.
 */
function cicekmezat_enqueue_scripts() {
    
    // --- DIŞ KAYNAKLAR ---

    // TailwindCSS CDN: Tasarımın temelini oluşturan CSS çatısını CDN üzerinden yüklüyor.
    // Lokal geliştirme için pratik bir çözümdür. Canlıya geçerken bu dosyaları
    // sunucunuza indirip yerel olarak yüklemek performansı artırabilir.
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.1', false);

    // Flowbite JS: Modal (açılır pencere) gibi interaktif Tailwind bileşenleri için gerekli script.
    wp_enqueue_script('flowbite', 'https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js', array(), '2.3.0', true);

	// Google Fonts: Tasarımda kullanılan 'Inter' yazı tipini yükler.
	wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null );

    // --- TEMA İÇİ DOSYALAR ---

    // Temanın ana stil dosyası (style.css). WordPress'in temayı tanıması için zorunludur.
	wp_enqueue_style( 'cicekmezat-style', get_stylesheet_uri(), array(), CICEKMEZAT_VERSION );
    
    // Özel stillerimizin bulunduğu dosya (assets/css/main.css).
    wp_enqueue_style( 'cicekmezat-main-css', CICEKMEZAT_THEME_URI . '/assets/css/main.css', array(), CICEKMEZAT_VERSION );

    // Temanın genel JavaScript dosyası (assets/js/main.js). jQuery'ye bağımlı olarak yüklenir.
    wp_enqueue_script( 'cicekmezat-main-js', CICEKMEZAT_THEME_URI . '/assets/js/main.js', array('jquery'), CICEKMEZAT_VERSION, true );
    
    // --- KOŞULLU YÜKLEMELER ---

    // Sadece Müşteri Canlı Mezat sayfasında (yani 'mezat' gönderi türünün tekil sayfasında)
    // live-auction-customer.js dosyasını yükle. Bu, gereksiz script yüklemeyi önler.
    if ( is_singular('mezat') ) {
        wp_enqueue_script( 'cicekmezat-auction-customer-js', CICEKMEZAT_THEME_URI . '/assets/js/live-auction-customer.js', array('jquery', 'cicekmezat-main-js'), CICEKMEZAT_VERSION, true );
    }

    // Sadece Yönetici Canlı Mezat sayfasında (özel şablonu kontrol ederek)
    // live-auction-admin.js dosyasını yükle.
    if ( is_page_template('page-yonetici-canli-mezat.php') ) {
         wp_enqueue_script( 'cicekmezat-auction-admin-js', CICEKMEZAT_THEME_URI . '/assets/js/live-auction-admin.js', array('jquery', 'cicekmezat-main-js'), CICEKMEZAT_VERSION, true );
    }

    // --- AJAX KODLARINI AKTİF ETME VE VERİ AKTARIMI ---

    // Bu fonksiyon, PHP tarafındaki verileri bir JavaScript objesine ('cm_ajax_object') dönüştürerek
    // script dosyamızın içinde kullanılabilir hale getirir. AJAX ve WebSocket bağlantıları için hayatidir.
    $user_id = get_current_user_id();
    $user_data = get_userdata($user_id);
    $user_role = ($user_data && !empty($user_data->roles)) ? $user_data->roles[0] : 'guest';

    wp_localize_script( 'cicekmezat-main-js', 'cm_ajax_object', array(
        'ajax_url'  => admin_url( 'admin-ajax.php' ), // WordPress AJAX işleyicisinin standart adresi.
        'nonce'     => wp_create_nonce('cicekmezat-nonce'), // Güvenlik doğrulaması için her oturuma özel anahtar.
        'ws_url'    => 'ws://localhost:8080', // Lokal WebSocket Sunucu adresi. Canlıda değiştirilecek.
        'user_id'   => $user_id, // Mevcut giriş yapmış kullanıcının ID'si.
        'user_role' => $user_role, // Kullanıcının rolü (örn: 'customer', 'administrator').
    ));
}
// cicekmezat_enqueue_scripts fonksiyonunu, WordPress'in ön yüz scriptlerini yüklediği kancaya ekle.
add_action( 'wp_enqueue_scripts', 'cicekmezat_enqueue_scripts' );

/**
 * Yönetici giriş sayfasının (wp-login.php) stilini özelleştirir.
 * Bu, sitenizin marka kimliğini giriş ekranına da yansıtmanızı sağlar.
 */
function cicekmezat_custom_login_page_style() { ?>
    <style type="text/css">
        body.login { background-color: #111827 !important; }
        #login h1 a, .login h1 a {
            background-image: url(<?php echo CICEKMEZAT_THEME_URI; ?>/assets/images/admin-logo.png); /* Logonuzu buraya koyun */
            height: 80px;
            width: 320px;
            background-size: contain;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
        #login form {
            background-color: #1F2937;
            border: 1px solid #374151;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }
        .login label { color: #D1D5DB; }
        .login input[type="text"], .login input[type="password"] {
            background-color: #374151; 
            border-color: #4B5563; 
            color: #E5E7EB; 
            border-radius: 0.375rem;
        }
        .login input[type="text"]:focus, .login input[type="password"]:focus {
            border-color: #FBBF24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.5);
        }
        .login #nav a, .login #backtoblog a { color: #9CA3AF; }
        .login #nav a:hover, .login #backtoblog a:hover { color: #FBBF24; }
        .wp-core-ui .button-primary {
            background: #FBBF24 !important; 
            border-color: #D97706 !important; 
            color: #111827 !important; 
            text-shadow: none !important; 
            border-radius: 0.375rem;
            font-weight: 600;
        }
    </style>
<?php }
// cicekmezat_custom_login_page_style fonksiyonunu, WordPress'in giriş sayfası scriptlerini yüklediği kancaya ekle.
add_action( 'login_enqueue_scripts', 'cicekmezat_custom_login_page_style' );
// --- Bitiş: inc/enqueue.php ---
