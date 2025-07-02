<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: form-edit-address.php (WooCommerce 9.9.x Sürümüne Göre Güncellendi)
 * AÇIKLAMA: Müşterinin "Adres Bilgilerim" sekmesinin şablonu.
 * Bu dosya, WooCommerce'in varsayılan şablonunu geçersiz kılar (override).
 * Kullanıcının fatura ve teslimat adreslerini yönetmesini sağlar.
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

// WooCommerce'in adres düzenleme işleminden önce çalışması gereken kancası.
// Örneğin, bildirimleri bu kanca ile gösterebilir.
$page_title = ( 'billing' === $load_address ) ? __( 'Fatura Adresi', 'woocommerce' ) : __( 'Teslimat Adresi', 'woocommerce' );

// Giriş yapmış olan kullanıcıyı al
$current_user = wp_get_current_user();
?>

<?php // WooCommerce'den gelen bildirimleri (örn: "Adres başarıyla güncellendi.") göstermek için. ?>
<?php wc_print_notices(); ?>

<?php
// Eğer URL'de düzenlenecek bir adres belirtilmemişse (yani /hesabim/edit-address/ URL'ine gidilmemişse),
// kullanıcının hem fatura hem de teslimat adresini gördüğü ana ekranı göster.
// Bu ana ekranın şablonu 'my-address.php' dosyasıdır.
if ( ! $load_address ) :
	wc_get_template( 'myaccount/my-address.php' );
else :
    // Eğer bir adres düzenleniyorsa (örn: /hesabim/edit-address/billing/ URL'ine gidilmişse),
    // adres düzenleme formunu göster.
?>

	<form method="post" class="bg-[#1F2937] p-8 rounded-lg border border-gray-700">

		<h3 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-700"><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3>

		<div class="woocommerce-address-fields space-y-4">
			<?php 
            // Form alanlarından önce çalışması gereken kancalar için.
            do_action( "woocommerce_before_edit_address_form_{$load_address}" ); 
            ?>

			<div class="space-y-4">
                <?php
                // İlgili adres (fatura veya teslimat) için tüm form alanlarını döngü ile oluşturur.
                // $address değişkeni WooCommerce tarafından bu şablona otomatik olarak sağlanır.
                foreach ( $address as $key => $field ) {
                    // Alanların görünümünü tema dosyalarındaki CSS ile uyumlu hale getirmek için
                    // varsayılan sınıflarına kendi stil sınıflarımızı ekliyoruz.
                    if (isset($field['class']) && is_array($field['class'])) {
                        $field['class'][] = 'w-full p-3 bg-gray-800 border border-gray-600 rounded-md';
                    } else {
                        $field['class'] = array('w-full', 'p-3', 'bg-gray-800', 'border', 'border-gray-600', 'rounded-md');
                    }
                    $field['label_class'] = array('block', 'text-sm', 'font-medium', 'text-gray-400', 'mb-1');
                    
                    // WooCommerce'in standart fonksiyonu ile form alanını ekrana yazdır.
                    woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
                }
                ?>
            </div>

			<?php 
            // Form alanlarından sonra çalışması gereken kancalar için.
            do_action( "woocommerce_after_edit_address_form_{$load_address}" ); 
            ?>
		</div>

		<p class="mt-8">
			<button type="submit" class="button bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-md w-full" name="save_address" value="<?php esc_attr_e( 'Adresi kaydet', 'woocommerce' ); ?>"><?php esc_html_e( 'Adresi Kaydet', 'woocommerce' ); ?></button>
            <?php // Güvenlik anahtarı (nonce), formun bizim sitemizden gönderildiğini doğrular. ?>
			<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
            <?php // WooCommerce'in hangi formun gönderildiğini anlaması için gizli bir alan. ?>
			<input type="hidden" name="action" value="edit_address" />
		</p>

	</form>

<?php endif; ?>
<?php // --- Bitiş: woocommerce/myaccount/form-edit-address.php --- ?>
