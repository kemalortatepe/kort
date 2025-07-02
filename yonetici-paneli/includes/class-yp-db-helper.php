<?php
/**
 * ==========================================================================
 * KLASÖR: yonetici-paneli/includes/
 * DOSYA: class-yp-db-helper.php (Hata Düzeltilmiş ve Tam Sürüm)
 * AÇIKLAMA: Bu sınıf, veritabanı ile ilgili yardımcı fonksiyonları içerir.
 * Temel görevi, eklenti aktive edildiğinde projemizin ihtiyaç duyduğu
 * özel veritabanı tablolarını oluşturmaktır. Ayrıca, sık kullanılan
 * veritabanı işlemlerini merkezi hale getiren fonksiyonlar da buraya eklenmiştir.
 * GÜNCELLEME: `get_cicek_by_post_id` ve `get_mezat_by_post_id` fonksiyonları
 * "Fatal error" hatasını çözmek için eklendi.
 * ==========================================================================
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YP_DB_Helper {

    /**
     * Eklenti için gerekli olan özel veritabanı tablolarını oluşturur.
     * Bu fonksiyon, `yonetici-paneli.php` dosyasındaki `register_activation_hook`
     * ile eklenti etkinleştirildiğinde sadece bir kez çalıştırılır.
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // --- 1. Çiçekler Tablosu (wp_cm_cicekler) ---
        $table_name_cicekler = $wpdb->prefix . 'cm_cicekler';
        $sql_cicekler = "CREATE TABLE $table_name_cicekler (
            cicek_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            cicek_adi VARCHAR(255) NOT NULL,
            toplam_stok_tane INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (cicek_id),
            UNIQUE KEY post_id (post_id)
        ) $charset_collate;";
        dbDelta( $sql_cicekler );

        // --- 2. Mezatlar Tablosu (wp_cm_mezatlar) ---
        $table_name_mezatlar = $wpdb->prefix . 'cm_mezatlar';
        $sql_mezatlar = "CREATE TABLE $table_name_mezatlar (
            mezat_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            cicek_id BIGINT(20) UNSIGNED NOT NULL,
            mezat_tarihi DATETIME NOT NULL,
            durum VARCHAR(50) NOT NULL DEFAULT 'taslak',
            PRIMARY KEY (mezat_id),
            UNIQUE KEY post_id (post_id)
        ) $charset_collate;";
        dbDelta( $sql_mezatlar );

        // --- 3. Alımlar Tablosu (wp_cm_alimlar) ---
        $table_name_alimlar = $wpdb->prefix . 'cm_alimlar';
        $sql_alimlar = "CREATE TABLE $table_name_alimlar (
            alim_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            mezat_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            alim_miktari INT(11) NOT NULL,
            birim_fiyat DECIMAL(10,2) NOT NULL,
            toplam_tutar DECIMAL(10,2) NOT NULL,
            alim_zamani DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            odeme_durumu VARCHAR(20) NOT NULL DEFAULT 'odenmis',
            PRIMARY KEY (alim_id),
            KEY mezat_id (mezat_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta( $sql_alimlar );
    }

    /**
     * YENİ FONKSİYON (Hata Düzeltmesi):
     * Bir WordPress post ID'sine göre özel çiçek verilerini getirir.
     * @param int $post_id Çiçeğin WordPress post ID'si.
     * @return object|null Sonuç bulunursa veritabanı satırı nesnesi, bulunamazsa null.
     */
    public static function get_cicek_by_post_id($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cm_cicekler';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id));
    }

    /**
     * YENİ FONKSİYON (Hata Düzeltmesi):
     * Bir WordPress post ID'sine göre özel mezat verilerini getirir.
     * @param int $post_id Mezatın WordPress post ID'si.
     * @return object|null Sonuç bulunursa veritabanı satırı nesnesi, bulunamazsa null.
     */
    public static function get_mezat_by_post_id($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cm_mezatlar';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id));
    }

    /**
     * Bir mezatın durumunu veritabanında günceller.
     * @param int $post_id Güncellenecek mezatın post ID'si.
     * @param string $yeni_durum Mezatın yeni durumu ('canli', 'bitti', 'iptal' vb.).
     * @return bool|int Başarılı ise güncellenen satır sayısı, başarısızsa false.
     */
    public static function update_mezat_durum($post_id, $yeni_durum) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cm_mezatlar';
        
        return $wpdb->update(
            $table_name,
            array('durum' => $yeni_durum),
            array('post_id' => $post_id),
            array('%s'), // $yeni_durum için format
            array('%d')  // $post_id için format
        );
    }

    /**
     * Yapılan bir alım işlemini `wp_cm_alimlar` tablosuna kaydeder.
     * @param int $mezat_id Alımın yapıldığı mezatın ID'si.
     * @param int $user_id Alımı yapan kullanıcının ID'si.
     * @param int $miktar Alınan miktar.
     * @param float $birim_fiyat Alım anındaki birim fiyat.
     * @param float $toplam_tutar Toplam ödenen tutar.
     * @return bool|int Başarılı ise 1, başarısızsa false.
     */
    public static function kaydet_alim($mezat_id, $user_id, $miktar, $birim_fiyat, $toplam_tutar) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cm_alimlar';
        
        return $wpdb->insert($table_name, array(
            'mezat_id'      => $mezat_id,
            'user_id'       => $user_id,
            'alim_miktari'  => $miktar,
            'birim_fiyat'   => $birim_fiyat,
            'toplam_tutar'  => $toplam_tutar,
            'alim_zamani'   => current_time('mysql'),
            'odeme_durumu'  => 'odenmis', // Varsayılan olarak ödendi kabul ediyoruz (kredi sistemine göre)
        ));
    }
}
// --- Bitiş: includes/class-yp-db-helper.php ---
