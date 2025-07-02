<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: form-edit-account.php (WooCommerce 9.9.x Sürümüne Göre Güncellendi)
 * AÇIKLAMA: Müşterinin "Hesap Bilgilerim" sekmesinin şablonu.
 * Bu dosya, WooCommerce'in varsayılan şablonunu geçersiz kılar (override).
 * Kullanıcının kişisel bilgilerini ve şifresini ayrı formlarda güncellemesini sağlar.
 * ==========================================================================
 *
 * @package CicekMezat
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 9.7.0
 * @woocommerce-version 9.9.5
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Form gönderildiğinde WooCommerce tarafından gösterilecek bildirimler için kanca (hook).
// Örn: "Hesap bilgileri başarıyla değiştirildi."
do_action( 'woocommerce_before_edit_account_form' ); ?>

<?php // İki formu birbirinden ayırmak için bir kapsayıcı div ?>
<div class="space-y-12">
    
    <form class="woocommerce-EditAccountForm edit-account bg-[#1F2937] p-8 rounded-lg border border-gray-700" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
        
        <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-700">Hesap Bilgilerim</h2>

        <?php // Formun başlangıcında çalışması gereken kancalar için (örn: eklentilerin alan eklemesi). ?>
        <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php // Adı Alanı ?>
                <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                    <label for="account_first_name" class="form-label"><?php esc_html_e( 'Adınız', 'woocommerce' ); ?>&nbsp;<span class="required text-red-500">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
                </p>
                <?php // Soyadı Alanı ?>
                <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                    <label for="account_last_name" class="form-label"><?php esc_html_e( 'Soyadınız', 'woocommerce' ); ?>&nbsp;<span class="required text-red-500">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
                </p>
            </div>

            <?php // Görünen Ad (Kullanıcı Adı) Alanı - Değiştirilemez ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="account_display_name" class="form-label"><?php esc_html_e( 'Görünen ad', 'woocommerce' ); ?>&nbsp;<span class="required text-red-500">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text form-input" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" disabled />
                <span class="text-xs text-gray-400 mt-1 block"><?php esc_html_e( 'Bu, herkese açık profilinizde ve yorumlarda nasıl görüneceğinizdir. Kullanıcı adı değiştirilemez.', 'woocommerce' ); ?></span>
            </p>

            <?php // E-posta Adresi Alanı ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="account_email" class="form-label"><?php esc_html_e( 'E-posta adresi', 'woocommerce' ); ?>&nbsp;<span class="required text-red-500">*</span></label>
                <input type="email" class="woocommerce-Input woocommerce-Input--email input-text form-input" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
            </p>
        </div>

        <?php // Diğer eklentilerin bu forma alan eklemesi için WordPress kancası. ?>
		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<p class="mt-8">
            <?php // Güvenlik anahtarı (nonce), formun bizim sitemizden gönderildiğini doğrular. ?>
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<button type="submit" class="woocommerce-Button button bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-md w-full" name="save_account_details" value="<?php esc_attr_e( 'Değişiklikleri kaydet', 'woocommerce' ); ?>"><?php esc_html_e( 'Kişisel Bilgileri Güncelle', 'woocommerce' ); ?></button>
            <?php // WooCommerce'in hangi formun gönderildiğini anlaması için gizli bir alan. ?>
			<input type="hidden" name="action" value="save_account_details" />
		</p>
    </form>

    <form class="woocommerce-EditAccountForm edit-account bg-[#1F2937] p-8 rounded-lg border border-gray-700" action="" method="post">
        <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-700">Şifre Değiştir</h2>
        
        <fieldset class="space-y-6">
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password_current" class="form-label"><?php esc_html_e( 'Mevcut şifre (yeni şifre belirlemek için zorunlu)', 'woocommerce' ); ?></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--password input-text form-input" name="password_current" id="password_current" autocomplete="off" />
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password_1" class="form-label"><?php esc_html_e( 'Yeni şifre (boş bırakırsanız değişmez)', 'woocommerce' ); ?></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--password input-text form-input" name="password_1" id="password_1" autocomplete="off" />
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password_2" class="form-label"><?php esc_html_e( 'Yeni şifreyi onayla', 'woocommerce' ); ?></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--password input-text form-input" name="password_2" id="password_2" autocomplete="off" />
            </p>
            <div class="text-xs text-gray-400">
                Şifre en az 8 karakterli olmalı; büyük harf, küçük harf, sayı ve özel karakter içermelidir.
            </div>
        </fieldset>
        
        <p class="mt-8">
            <?php // Güvenlik anahtarı ve action bilgisi üstteki form ile aynı olduğu için tekrar eklenir. ?>
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<button type="submit" class="woocommerce-Button button bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md w-full" name="save_account_details" value="<?php esc_attr_e( 'Değişiklikleri kaydet', 'woocommerce' ); ?>"><?php esc_html_e( 'Şifreyi Güncelle', 'woocommerce' ); ?></button>
			<input type="hidden" name="action" value="save_account_details" />
		</p>
    </form>
</div>

<?php // Formdan sonra çalışması gereken kancalar için. ?>
<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
<?php // --- Bitiş: woocommerce/myaccount/form-edit-account.php --- ?>
