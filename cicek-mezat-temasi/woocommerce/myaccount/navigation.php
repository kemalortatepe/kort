<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: navigation.php (WooCommerce 9.9.x Sürümüne Göre Güncellendi)
 * AÇIKLAMA: "Hesabım" sayfasındaki sol navigasyon menüsünün şablonu.
 * Bu dosya, WooCommerce'in varsayılan şablonunu geçersiz kılar (override).
 * Menüdeki sekmeler, `inc/woocommerce-hooks.php` dosyasında tanımlanan
 * sıraya ve isimlendirmeye göre otomatik olarak oluşturulur.
 * ==========================================================================
 *
 * @package CicekMezat
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 9.3.0
 * @woocommerce-version 9.9.5
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WooCommerce'in navigasyon öncesi için ayırdığı kanca (hook).
// Diğer eklentilerin bu alana içerik eklemesine olanak tanır.
do_action( 'woocommerce_before_account_navigation' );
?>

<?php // Navigasyonun ana kapsayıcısı. HTML örneklerindeki stil sınıfları kullanılmıştır. ?>
<nav class="woocommerce-MyAccount-navigation bg-[#1F2937] border-r border-gray-700 flex-shrink-0 w-full lg:w-72">
    
    <?php // Menünün üst kısmında kullanıcı bilgilerini gösteren bölüm. ?>
    <div class="p-6 text-center border-b border-gray-700">
        <?php 
        // Mevcut giriş yapmış kullanıcıyı al
        $current_user = wp_get_current_user(); 
        ?>
        <p class="text-gray-400 text-sm">Hoş geldiniz,</p>
        <p class="text-xl font-semibold text-white"><?php echo esc_html($current_user->display_name); ?></p>
        
        <?php
        // Kullanıcının ceza ve erişim durumunu `user_meta` tablosundan kontrol et.
        // Bu meta verileri, yönetici paneli eklentisi tarafından ayarlanır.
        $erisim_durumu = get_user_meta($current_user->ID, 'erisim_durumu', true);
        $ceza_durumu = get_user_meta($current_user->ID, 'ceza_durumu', true);

        // Eğer kullanıcı engelliyse veya cezalıysa bir uyarı göster.
        if ($erisim_durumu === 'engelli' || $ceza_durumu === 'cezali') {
            echo '<div class="mt-2 text-xs font-semibold p-2 bg-red-500/20 text-red-400 rounded-md">Hesap Kısıtlı</div>';
        }
        ?>
    </div>

	<ul class="mt-4">
		<?php 
        // `wc_get_account_menu_items()` fonksiyonu, `woocommerce-hooks.php` dosyasında
        // bizim özelleştirdiğimiz menü öğelerini bir dizi olarak döndürür.
        // Bu döngü, o dizideki her bir öğe için bir `<li>` elemanı oluşturur.
        foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); // Aktif sekme için 'is-active' sınıfını otomatik ekler. ?>">
				
                <?php // Her bir menü öğesi için link oluşturur. ?>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" class="sidebar-link block py-3 px-6 text-gray-400 hover:bg-gray-700 hover:text-white hover:border-l-amber-400 border-l-4 border-transparent transition-all duration-200">
                    <?php echo esc_html( $label ); // Menü başlığını ekrana yazdırır. ?>
                </a>

			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php
// WooCommerce'in navigasyon sonrası için ayırdığı kanca (hook).
do_action( 'woocommerce_after_account_navigation' ); 
?>

<?php // --- Bitiş: woocommerce/myaccount/navigation.php --- ?>
