<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/inc/
 * DOSYA: ajax-handlers.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu dosya, tema genelinde kullanılan tüm AJAX isteklerini yönetir.
 * Kullanıcıların sayfa yenilemeden sunucu ile iletişim kurmasını sağlayan
 * fonksiyonları içerir. Bu yapı, `yonetici-paneli` eklentisindeki AJAX
 * işlemlerinden ayrıdır ve sadece tema ön yüzüne hizmet eder.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Sınıfın daha önce tanımlanıp tanımlanmadığını kontrol et.
if ( ! class_exists( 'CicekMezat_Ajax_Handler' ) ) :

    /**
     * Temanın ön yüzünden (front-end) gelen tüm AJAX isteklerini yöneten sınıf.
     */
    class CicekMezat_Ajax_Handler {

        /**
         * Sınıf yapıcı metodu. Gerekli AJAX kancalarını (hooks) WordPress'e ekler.
         */
        public function __construct() {
            // 'wp_ajax_' ile başlayan kanca sadece giriş yapmış kullanıcılar için çalışır.
            // JavaScript'ten 'action: "get_user_credit"' parametresiyle yapılan istekler bu fonksiyona yönlendirilir.
            add_action('wp_ajax_get_user_credit', array($this, 'get_user_credit'));

            // 'wp_ajax_nopriv_' ile başlayan kanca giriş yapmamış kullanıcılar içindir.
            // Madde 22: Müşteri kayıt formunda kullanıcı adının uygunluğunu kontrol etmek için.
            add_action('wp_ajax_nopriv_check_username_availability', array($this, 'check_username_availability'));
        }

        /**
         * Güvenlik kontrolü yapan özel bir yardımcı fonksiyon.
         *
         * @param string $nonce_action      Güvenlik anahtarının (nonce) beklenen adı.
         * @param bool   $check_capability  Yetki kontrolü yapılıp yapılmayacağı.
         * @param string $capability        Kullanıcının sahip olması gereken minimum yetki.
         */
        private function security_check( $nonce_action, $check_capability = true, $capability = 'read' ) {
            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), $nonce_action ) ) {
                wp_send_json_error( array('message' => 'Güvenlik doğrulaması başarısız oldu.'), 403 );
            }
            if ( $check_capability && ! current_user_can( $capability ) ) {
                wp_send_json_error( array('message' => 'Bu işlemi yapma yetkiniz bulunmamaktadır.'), 403 );
            }
        }

        /**
         * AJAX İsteği: Giriş yapmış kullanıcının TeraWallet cüzdan bakiyesini döndürür.
         */
        public function get_user_credit() {
            $this->security_check('cicekmezat-nonce'); // Sadece giriş yapmış ve nonce'u geçerli kullanıcılar

            if ( ! is_user_logged_in() ) {
                wp_send_json_error(['message' => 'Bu işlem için giriş yapmalısınız.']);
            }

            if ( function_exists('woo_wallet') ) {
                $user_id = get_current_user_id();
                $balance = woo_wallet()->wallet->get_wallet_balance($user_id);
                $formatted_balance = wc_price($balance);

                wp_send_json_success([
                    'balance' => $balance,
                    'formatted_balance' => $formatted_balance
                ]);
            } else {
                wp_send_json_error(['message' => 'Kredi cüzdan sistemi aktif değil.']);
            }
            wp_die();
        }

        /**
         * AJAX İsteği: Kayıt formunda girilen kullanıcı adının uygun olup olmadığını kontrol eder.
         */
        public function check_username_availability() {
            // Bu herkese açık bir işlem olduğu için yetki kontrolü yapmıyoruz, sadece nonce'u kontrol ediyoruz.
            $this->security_check('cicekmezat-nonce', false);

            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';

            if (empty($username)) {
                wp_send_json_error(['message' => 'Kullanıcı adı boş olamaz.']);
            }

            if (username_exists($username)) {
                // Kullanıcı adı zaten varsa
                wp_send_json_error(['message' => 'Bu kullanıcı adı zaten alınmış.']);
            } else if (!validate_username($username)) {
                // Kullanıcı adı geçersiz karakterler içeriyorsa
                wp_send_json_error(['message' => 'Geçersiz kullanıcı adı. Sadece harf ve rakam kullanın.']);
            } else {
                // Kullanıcı adı uygunsa
                wp_send_json_success(['message' => 'Kullanıcı adı uygun.']);
            }
            wp_die();
        }
    }

endif; // class_exists kontrolü biter.

// Sınıfı başlatarak AJAX kancalarını aktif et.
new CicekMezat_Ajax_Handler();

// --- Bitiş: inc/ajax-handlers.php ---
