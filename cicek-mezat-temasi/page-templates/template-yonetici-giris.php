<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/page-templates/
 * DOSYA: template-yonetici-giris.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu, WordPress panelinden "Sayfalar > Yeni Ekle" menüsüyle oluşturulacak bir
 * "Yönetim Girişi" sayfası için özel şablondur. Bu sayfanın URL'si
 * sadece yönetici ve yönetici yardımcısı rolleri tarafından bilinmelidir,
 * bu sayede standart wp-login.php sayfasından farklı, özel bir giriş noktası oluşturulur.
 * ==========================================================================
 *
 * Template Name: Yönetici Giriş Sayfası
 *
 * @package CicekMezat
 */

// --- GÜVENLİK VE YÖNLENDİRME ---

// Eğer kullanıcı zaten giriş yapmışsa, rolüne göre doğru yere yönlendir.
if (is_user_logged_in()) {
    $user = wp_get_current_user();
    // Eğer kullanıcı 'administrator' veya 'yonetici_yardimcisi' rolüne sahipse, admin paneline yönlendir.
    if (in_array('administrator', (array)$user->roles) || in_array('yonetici_yardimcisi', (array)$user->roles)) {
        wp_redirect(admin_url());
    } else {
        // Eğer başka bir roldeyse (örneğin müşteri), ana sayfaya yönlendir.
        wp_redirect(home_url());
    }
    exit;
}

// Sitenin header.php dosyasını dahil et.
get_header(); 
?>

<div class="yonetici-giris-container flex items-center justify-center min-h-[80vh] bg-gray-900 px-4 py-12">
    <div class="w-full max-w-md">
        
        <div class="bg-[#1F2937] p-8 rounded-xl border border-gray-700 shadow-2xl">
            <h1 class="text-2xl font-bold text-center text-amber-400 mb-8">Yönetim Paneli Girişi</h1>
            
            <?php
            // WordPress'in standart ve güvenli giriş formunu, kendi özel ayarlarımızla çağırıyoruz.
            // Bu fonksiyon, nonce alanları, hata mesajları ve yönlendirme gibi tüm işlemleri
            // otomatik olarak yönetir.
            wp_login_form(array(
                'redirect'       => admin_url(), // Başarılı girişten sonra admin paneline (/wp-admin/) yönlendir.
                'label_username' => __('Kullanıcı Adı veya E-posta', 'cicekmezat'),
                'label_password' => __('Şifre', 'cicekmezat'),
                'label_remember' => __('Beni Hatırla', 'cicekmezat'),
                'label_log_in'   => __('Giriş Yap', 'cicekmezat'),
                'remember'       => true, // "Beni Hatırla" seçeneğini göster.
                'form_id'        => 'yonetici-login-form',
            ));
            ?>
        </div>
        
        <div class="text-center mt-6">
             <a class="text-sm text-gray-400 hover:text-amber-400" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
                <?php esc_html_e( 'Şifrenizi mi unuttunuz?', 'cicekmezat' ); ?>
            </a>
        </div>

    </div>
</div>

<style>
    /* wp_login_form() fonksiyonunun çıktısını Tailwind CSS ile doğrudan stilize etmek zor olduğu için,
    bu sayfaya özel birkaç CSS kuralı ekliyoruz. Bu, en temiz ve en güvenilir yöntemdir.
    */
    #yonetici-login-form p label {
        color: #9CA3AF;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    #yonetici-login-form input[type="text"],
    #yonetici-login-form input[type="password"] {
        width: 100%;
        background-color: #374151;
        color: #E5E7EB;
        border: 1px solid #4B5563;
        border-radius: 0.375rem;
        padding: 0.75rem;
    }
    #yonetici-login-form .login-remember label {
        display: inline-flex;
        align-items: center;
        color: #D1D5DB;
        font-weight: normal;
    }
    #yonetici-login-form #wp-submit {
        width: 100%;
        background-color: #FBBF24;
        color: #111827;
        font-weight: 700;
        border: none;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-top: 1rem;
    }
</style>

<?php
// Sitenin footer.php dosyasını dahil et.
get_footer();
// --- Bitiş: page-templates/template-yonetici-giris.php ---
