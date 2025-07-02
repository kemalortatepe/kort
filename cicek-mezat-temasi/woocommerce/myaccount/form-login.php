<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: form-login.php (WooCommerce 9.9.x Sürümüne Göre Güncellendi)
 * AÇIKLAMA: Müşteri Giriş Formu Şablonu.
 * Bu dosya, WooCommerce'in varsayılan giriş formunu geçersiz kılar (override)
 * ve temanızla tam uyumlu, özel bir tasarım sunar.
 * ==========================================================================
 *
 * @package CicekMezat
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 9.9.0
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// WooCommerce'den gelen bildirimleri (örn: "Hatalı şifre", "Böyle bir kullanıcı yok")
// göstermek için kancayı (hook) tetikle.
do_action( 'woocommerce_before_customer_login_form' ); ?>

<div id="customer_login" class="u-columns col2-set">

	<div class="u-column1 col-1">
        <?php // Giriş formunun kendisi ?>
		<form class="woocommerce-form woocommerce-form-login login space-y-6" method="post">

			<?php // Formun başlangıcında çalışması gereken kancalar için. ?>
			<?php do_action( 'woocommerce_login_form_start' ); ?>

            <?php // Kullanıcı adı veya E-posta alanı ?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username" class="block text-sm font-medium text-gray-400 mb-1"><?php esc_html_e( 'Kullanıcı adı veya e-posta adresi', 'woocommerce' ); ?>&nbsp;<span class="required text-red-500">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text w-full p-3 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-amber-500" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
			</p>
            
            <?php // Şifre alanı ?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password" class="block text-sm font-medium text-gray-400 mb-1"><?php esc_html_e( 'Şifre', 'woocommerce' ); ?>&nbsp;<span class="required text-red-500">*</span></label>
				<input class="woocommerce-Input woocommerce-Input--password input-text w-full p-3 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-amber-500" type="password" name="password" id="password" autocomplete="current-password" />
			</p>

			<?php // Diğer eklentilerin giriş formuna alan eklemesi için kanca. ?>
			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="form-row flex items-center justify-between">
                <?php // "Beni Hatırla" onay kutusu ?>
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme inline-flex items-center text-sm text-gray-300">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox rounded bg-gray-700 border-gray-600 text-amber-500 focus:ring-amber-500 mr-2" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Beni hatırla', 'woocommerce' ); ?></span>
				</label>

                <?php // Güvenlik anahtarı (nonce), formun bizim sitemizden gönderildiğini doğrular. ?>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				
                <?php // Giriş Yap butonu ?>
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md transition-colors" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Giriş Yap', 'woocommerce' ); ?></button>
			</div>

			<p class="woocommerce-LostPassword lost_password text-center text-sm pt-4">
				<a class="text-gray-400 hover:text-amber-400" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Şifrenizi mi unuttunuz?', 'woocommerce' ); ?></a>
			</p>

			<?php // Formun sonunda çalışması gereken kancalar için. ?>
			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>
	</div>

</div>

<?php // Giriş formundan sonra çalışması gereken kancalar için. ?>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
<?php // --- Bitiş: woocommerce/myaccount/form-login.php --- ?>
