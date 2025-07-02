<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: my-account.php (WooCommerce 9.9.x Sürümüne Göre Güncellendi)
 * AÇIKLAMA: "Hesabım" sayfasının ana kapsayıcı şablonu.
 * Bu dosya, sol tarafta navigasyon menüsünü (navigation.php), sağ tarafta ise
 * seçili olan sekmenin içeriğini gösteren genel düzeni oluşturur.
 * ==========================================================================
 *
 * @package CicekMezat
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 3.5.0
 * @woocommerce-version 9.9.5
 * // NOT: Bu dosyanın temel yapısı WooCommerce'in sonraki sürümlerinde (3.5.0'dan beri)
 * // büyük bir değişiklik göstermemiştir. Bu nedenle versiyon numarasını
 * // 3.5.0 olarak tutmak, uyumluluğu en geniş yelpazede sağlar.
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php // Müşteriye gösterilecek genel bildirimler (örn: "Hesap bilgileri güncellendi.") bu alanda görünür. ?>
<?php wc_print_notices(); ?>

<?php
/**
 * 'Hesabım' sayfasının ana kapsayıcı div'i.
 * Flexbox kullanarak sol menü (nav) ve sağ içerik (content) alanını yan yana getirir.
 * Küçük ekranlarda (lg breakpoint'inden küçük) menü ve içerik alt alta sıralanır.
 */
?>
<div class="woocommerce-account flex flex-col lg:flex-row">

	<?php
	/**
	 * Hesabım Navigasyon Menüsü.
	 * * Bu kanca, WooCommerce'e `woocommerce/myaccount/navigation.php` dosyasını
	 * bu noktada yüklemesini söyler. Biz bu dosyayı kendi temamızda özelleştirdiğimiz
	 * için, bizim tasarladığımız sol menü burada görünecektir.
	 *
	 * @hooked woocommerce_account_navigation - 10
	 */
	do_action( 'woocommerce_account_navigation' ); 
	?>

	<div class="woocommerce-MyAccount-content flex-grow p-4 md:p-8">
		<?php
		/**
		 * Hesabım İçerik Alanı.
		 * * Bu kanca, WooCommerce'in o an seçili olan sekmeye (endpoint) ait içeriği
		 * yüklemesini sağlar. Örneğin, kullanıcı "Mezat Takvimi" sekmesine tıkladığında,
		 * WooCommerce bu alana `mezat-takvimi.php` şablonunun içeriğini basar.
		 * "Adres Bilgilerim"e tıkladığında ise `form-edit-address.php` şablonunu yükler.
		 *
		 * @hooked woocommerce_account_content - 10
		 */
		do_action( 'woocommerce_account_content' );
		?>
	</div>
</div>

<?php // --- Bitiş: woocommerce/myaccount/my-account.php --- ?>
