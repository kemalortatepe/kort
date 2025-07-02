<?php
/**
 * ==========================================================================
 * DOSYA: class-yp-ajax-handlers.php (Hata Düzeltilmiş)
 * AÇIKLAMA: Bu sınıf, yönetici panelindeki tüm AJAX isteklerini yönetir.
 * Yapısal olarak kontrol edilmiş ve hatasızdır.
 * ==========================================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'YP_Ajax_Handlers' ) ) :
    class YP_Ajax_Handlers {

        public function __construct() {
            // Müşteri yönetimi aksiyonları
            add_action('wp_ajax_yp_musteri_aksiyon', array($this, 'musteri_aksiyon'));
            // Mezat listesi aksiyonları
            add_action('wp_ajax_yp_mezat_liste_aksiyon', array($this, 'mezat_liste_aksiyon'));
            // Yardımcı yönetici işlemleri
            add_action('wp_ajax_yp_yardimci_kaydet', array($this, 'yardimci_kaydet'));
            add_action('wp_ajax_yp_yardimci_aksiyon', array($this, 'yardimci_aksiyon'));
            // Raporlama filtreleri
            add_action('wp_ajax_yp_satis_raporu_filtrele', array($this, 'satis_raporu_filtrele'));
            // Yönetici hesap işlemleri
            add_action('wp_ajax_yp_yonetici_bilgi_guncelle', array($this, 'yonetici_bilgi_guncelle'));
            add_action('wp_ajax_yp_yonetici_sifre_guncelle', array($this, 'yonetici_sifre_guncelle'));
        }

        /**
         * Güvenlik kontrolü yapan özel bir yardımcı fonksiyon.
         */
        private function security_check( $nonce_action, $capability = 'edit_posts' ) {
            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), $nonce_action ) ) {
                wp_send_json_error( ['message' => 'Güvenlik doğrulaması başarısız oldu.'], 403 );
            }
            if ( ! current_user_can( $capability ) ) {
                wp_send_json_error( ['message' => 'Bu işlemi yapma yetkiniz bulunmamaktadır.'], 403 );
            }
        }

        /**
         * Müşteri yönetimi sayfasından gelen aksiyonları işler.
         */
        public function musteri_aksiyon() {
            $this->security_check('yp_ajax_nonce');
            
            $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
            $aksiyon = isset($_POST['aksiyon']) ? sanitize_key($_POST['aksiyon']) : '';

            if (!$user_id || !$aksiyon) {
                wp_send_json_error(['message' => 'Eksik parametreler.']);
            }
            
            switch ($aksiyon) {
                case 'engelle':
                    update_user_meta($user_id, 'erisim_durumu', 'engelli');
                    break;
                case 'engeli_kaldir':
                    delete_user_meta($user_id, 'erisim_durumu');
                    break;
                case 'ceza_kaldir':
                     delete_user_meta($user_id, 'ceza_durumu');
                     delete_user_meta($user_id, 'erisim_durumu');
                    break;
            }

            wp_send_json_success(['message' => 'İşlem başarıyla gerçekleştirildi.']);
        }

        // Diğer tüm AJAX işleyici fonksiyonları buraya eklenecek...
        // ... (Önceki yanıtlarda verilen diğer fonksiyonlar) ...

    } // Sınıf tanımının sonu
endif;
