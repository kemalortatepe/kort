<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: header.php (Logo ve Karşılama Mesajı Güncellenmiş)
 * AÇIKLAMA: Sitenizin tüm sayfalarının üst kısmını oluşturan şablon dosyasıdır.
 * Bu dosya, HTML belgesinin başlangıcını, <head> bölümünü ve sitenin
 * görsel başlığını (logo, menü, kullanıcı butonları) içerir.
 *
 * GÜNCELLEME NOTLARI:
 * - Site logosu ve site adı, logonun en solda olacağı şekilde yan yana hizalanmıştır.
 * - Giriş yapan kullanıcılar için "Hoş geldiniz, [Kullanıcı Adı]" mesajı eklenmiştir.
 * ==========================================================================
 *
 * @package CicekMezat
 */
?>
<!DOCTYPE html>
<?php // Sitenin dil ayarlarını HTML etiketine ekler (örn: lang="tr-TR") ?>
<html <?php language_attributes(); ?>>
<head>
    <?php // Sitenin karakter setini (örn: UTF-8) ekler ?>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php // Mobil cihazlarda sitenin düzgün görüntülenmesi için responsive meta etiketi ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    /**
     * wp_head() WordPress'in en önemli kancalarından biridir.
     * Eklentilerin ve temanın <head> bölümüne CSS, JS ve diğer meta etiketlerini
     * eklemesini sağlar. Bu satır olmadan siteniz düzgün çalışmaz.
     */
    wp_head(); 
    ?>
</head>
<?php // body_class() fonksiyonu, o anki sayfaya özel CSS sınıfları ekler. (örn: logged-in, home, single-post) ?>
<body <?php body_class('bg-gray-900 text-gray-300 font-sans antialiased'); ?>>

<?php // Sayfanın en dış sarmalayıcısı ?>
<div id="page" class="min-h-screen flex flex-col">
    
    <header id="masthead" class="site-header bg-[#1F2937] shadow-lg sticky top-0 z-50 border-b border-gray-700">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-3">
            
            <?php // Site Logosu / Başlığı (Güncellenmiş Yapı) ?>
            <div class="site-branding flex items-center gap-x-3">
                <?php
                // "Görünüm > Özelleştir > Site Kimliği" bölümünden yüklenen logoyu gösterir.
                if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                    // a etiketinin içindeki standard dışı p etiketini kaldırmak için str_replace kullanıyoruz
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '" rel="home"><img src="' . esc_url( $logo[0] ) . '" alt="' . get_bloginfo( 'name' ) . '" class="h-10 w-auto"></a>';
                }
                ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="text-2xl font-bold text-white">
                    Çiçek<span class="text-amber-400">Mezat</span>
                </a>
            </div>

            <?php // Ana Navigasyon Menüsü ?>
            <nav id="site-navigation" class="main-navigation hidden md:block">
                <?php
                if ( has_nav_menu( 'primary' ) ) {
                    wp_nav_menu( ['theme_location' => 'primary', 'menu_class' => 'flex items-center gap-x-6 text-sm font-medium', 'container' => false] );
                }
                ?>
            </nav>

            <?php // Sağ Taraftaki Kullanıcı Butonları (Güncellenmiş Yapı) ?>
            <div class="header-actions flex items-center gap-4">
                <?php if ( is_user_logged_in() ) : // Kullanıcı giriş yapmışsa
                    $current_user = wp_get_current_user();
                ?>
                    <span class="text-sm text-gray-300 hidden sm:block">
                        Hoş geldiniz, <strong class="font-semibold text-amber-400"><?php echo esc_html($current_user->display_name); ?></strong>
                    </span>
                    
                    <?php
                    // Kullanıcı rolüne göre farklı butonlar göster
                    if ( in_array( 'administrator', (array) $current_user->roles ) || in_array( 'yonetici_yardimcisi', (array) $current_user->roles ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=yp-dashboard' ) ); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">Yönetici Paneli</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">Hesabım</a>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="text-gray-400 hover:text-white text-sm transition-colors">Çıkış Yap</a>

                <?php else : // Kullanıcı giriş yapmamışsa ?>
                    
                    <button data-modal-target="login-modal" data-modal-toggle="login-modal" class="text-white hover:text-amber-400 text-sm font-medium transition-colors">Giriş Yap</button>
                    <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'kayit' ) ) ); ?>" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">Kayıt Ol</a>
                
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php // Giriş Modal Penceresi ?>
    <?php if ( ! is_user_logged_in() ) : ?>
    <div id="login-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-gray-800 rounded-lg shadow border border-gray-700">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-600 hover:text-white rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="login-modal">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
                <div class="p-6">
                    <h3 class="mb-4 text-xl font-medium text-white">Müşteri Girişi</h3>
                    <?php if (function_exists('wc_get_template')) { wc_get_template( 'myaccount/form-login.php' ); } ?>
                </div>
            </div>
        </div>
    </div> 
    <?php endif; ?>

    <?php // Ana içerik alanı başlar. Bu div footer.php'de kapatılır. ?>
    <div id="content" class="site-content flex-grow">
<?php // --- Bitiş: header.php --- ?>
