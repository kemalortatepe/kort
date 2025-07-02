<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/page-templates/
 * DOSYA: template-kayit.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu, WordPress panelinden "Sayfalar > Yeni Ekle" menüsüyle oluşturulacak bir
 * "Kayıt Ol" sayfası için özel şablondur. Sayfa oluşturulduktan sonra, sağ taraftaki
 * "Sayfa Özellikleri" bölümünden bu şablonun seçilmesi gerekir.
 *
 * Bu şablon, WooCommerce'in kayıt formunu kullanarak, kullanıcının hem standart
 * WordPress kullanıcısı hem de WooCommerce müşterisi olarak kaydedilmesini sağlar.
 * YENİ YAPI GÜNCELLEMESİ: AJAX ile anlık kullanıcı adı kontrolü ve uluslararası
 * telefon numarası girişi özellikleri eklenmiştir.
 * ==========================================================================
 *
 * Template Name: Müşteri Kayıt Sayfası
 *
 * @package CicekMezat
 */

// Eğer kullanıcı zaten giriş yapmışsa, onu "Hesabım" sayfasına yönlendir.
if (is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}

// Bu şablona özel script ve stilleri yükle.
// Normalde bu kodlar inc/enqueue.php içinde yönetilir, ancak bu kütüphane sadece bu sayfada
// gerektiği için burada yüklemek daha verimlidir.
add_action('wp_head', function() {
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <?php
});

get_header(); // Sitenin header.php dosyasını dahil et.
?>

<div class="kayit-sayfasi-container py-12 md:py-20 bg-gray-900">
    <div class="w-full max-w-screen-md mx-auto px-4">
        
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white">Müşteri Kayıt Sayfası</h1>
            <p class="text-gray-400 mt-1">Mezat sistemine katılmak için bilgilerinizi girin.</p>
        </header>

        <div class="form-container bg-[#1F2937] p-6 md:p-8 rounded-xl border border-gray-700 shadow-2xl">
            <?php wc_print_notices(); ?>

            <form id="musteri-kayit-formu" class="woocommerce-form woocommerce-form-register register space-y-6" method="post">
                
                <?php do_action( 'woocommerce_register_form_start' ); ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="reg_billing_first_name" class="form-label"><?php esc_html_e( 'Adınız', 'woocommerce' ); ?> <span class="required text-red-500">*</span></label>
                        <input type="text" name="billing_first_name" id="reg_billing_first_name" class="form-input" value="<?php echo ( ! empty( $_POST['billing_first_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_first_name'] ) ) : ''; ?>" required>
                    </div>
                    <div>
                        <label for="reg_billing_last_name" class="form-label"><?php esc_html_e( 'Soyadınız', 'woocommerce' ); ?> <span class="required text-red-500">*</span></label>
                        <input type="text" name="billing_last_name" id="reg_billing_last_name" class="form-input" value="<?php echo ( ! empty( $_POST['billing_last_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_last_name'] ) ) : ''; ?>" required>
                    </div>
                </div>

                <div>
                    <label for="reg_username" class="form-label"><?php esc_html_e( 'Kullanıcı adı', 'woocommerce' ); ?> <span class="required text-red-500">*</span></label>
                    <input type="text" name="username" id="reg_username" autocomplete="username" class="form-input" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required>
                    <div id="username-feedback" class="text-xs mt-1"></div>
                </div>

                <div>
                    <label for="reg_email" class="form-label"><?php esc_html_e( 'E-posta Adresiniz', 'woocommerce' ); ?> <span class="required text-red-500">*</span></label>
                    <input type="email" name="email" id="reg_email" autocomplete="email" class="form-input" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required>
                </div>

                <div>
                    <label for="reg_billing_phone" class="form-label"><?php esc_html_e( 'Cep Telefonu', 'woocommerce' ); ?> <span class="required text-red-500">*</span></label>
                    <input type="tel" name="billing_phone" id="reg_billing_phone" class="form-input w-full" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="reg_password" class="form-label"><?php esc_html_e( 'Şifre', 'woocommerce' ); ?> <span class="required text-red-500">*</span></label>
                        <input type="password" name="password" id="reg_password" autocomplete="new-password" class="form-input" required>
                    </div>
                    <div>
                        <label for="reg_password2" class="form-label">Şifre Tekrar <span class="required text-red-500">*</span></label>
                        <input type="password" name="password2" id="reg_password2" autocomplete="new-password" class="form-input" required>
                    </div>
                </div>

                <?php do_action( 'woocommerce_register_form' ); ?>

                <div class="space-y-4 pt-4 border-t border-gray-700">
                    <label class="form-checkbox-label flex items-center text-sm">
                        <input type="checkbox" id="terms" name="terms" class="form-checkbox h-4 w-4" required>
                        <span><a href="/kayit-sartlari" class="text-blue-400 hover:underline" target="_blank">Kayıt Şartları</a>'nı okudum ve kabul ediyorum.</span>
                    </label>
                    
                    <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                    
                    <button type="submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>" class="register-btn w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-md transition-colors disabled:bg-gray-500 disabled:cursor-not-allowed">
                        <?php esc_html_e( 'Kayıt Ol', 'woocommerce' ); ?>
                    </button>
                </div>

                <?php do_action( 'woocommerce_register_form_end' ); ?>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // --- ULUSLARARASI TELEFON GİRİŞİ ---
    const phoneInput = document.querySelector("#reg_billing_phone");
    if (phoneInput) {
        window.intlTelInput(phoneInput, {
            initialCountry: "tr", // Varsayılan ülke Türkiye
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
    }

    // --- KULLANICI ADI UYGUNLUK KONTROLÜ (AJAX) ---
    let usernameTimer;
    $('#reg_username').on('keyup', function() {
        clearTimeout(usernameTimer);
        const username = $(this).val();
        const feedbackEl = $('#username-feedback');

        if (username.length < 4) {
            feedbackEl.text('Kullanıcı adı en az 4 karakter olmalıdır.').removeClass('text-green-500').addClass('text-red-500');
            return;
        }

        feedbackEl.text('Kontrol ediliyor...').removeClass('text-green-500 text-red-500').addClass('text-gray-400');

        // Kullanıcı yazmayı bıraktıktan 500ms sonra AJAX isteğini gönder
        usernameTimer = setTimeout(function() {
            $.ajax({
                url: cm_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'check_username_availability',
                    nonce: cm_ajax_object.nonce,
                    username: username
                },
                success: function(response) {
                    if (response.success) {
                        feedbackEl.text(response.data.message).removeClass('text-red-500').addClass('text-green-500');
                    } else {
                        feedbackEl.text(response.data.message).removeClass('text-green-500').addClass('text-red-500');
                    }
                }
            });
        }, 500);
    });

    // --- DİĞER FORM DOĞRULAMALARI (CLIENT-SIDE) ---
    // (Şifre eşleşmesi ve buton aktif/pasif etme mantığı önceki yanıttaki gibi buraya eklenebilir)
});
</script>

<?php
get_footer(); // Sitenin footer.php dosyasını dahil et.
?>
<?php // --- Bitiş: page-templates/template-kayit.php --- ?>
