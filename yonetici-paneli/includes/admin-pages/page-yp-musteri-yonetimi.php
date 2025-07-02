<?php
/**
 * includes/admin-pages/page-yp-musteri-yonetimi.php
 *
 * Yönetici Paneli'ndeki "Müşteri Yönetimi" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-musteri-yonetimi' olduğunda yüklenir.
 * Sitedeki tüm müşterileri (rolü 'customer' olan kullanıcılar) listeler,
 * filtrelenmesini sağlar ve her bir müşteri için yönetim aksiyonları sunar.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- FİLTRELEME VE ARAMA İŞLEMLERİ ---

// URL'den gelen arama ve filtre parametrelerini al ve temizle.
$arama_terimi = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$durum_filtresi = isset($_GET['durum_filtresi']) ? sanitize_key($_GET['durum_filtresi']) : '';

// WP_User_Query için argümanları hazırla.
$args = array(
    'role'    => 'customer', // Sadece 'customer' rolüne sahip kullanıcıları getir.
    'orderby' => 'registered',
    'order'   => 'DESC',
    'number'  => 20, // Sayfa başına gösterilecek müşteri sayısı
    'paged'   => get_query_var('paged') ? get_query_var('paged') : 1, // Sayfalama için
);

// Arama terimi varsa, sorguyu buna göre genişlet.
if ( ! empty($arama_terimi) ) {
    $args['search'] = '*' . esc_attr($arama_terimi) . '*';
    $args['search_columns'] = array('user_login', 'user_email', 'display_name');
}

// Durum filtresi varsa, meta sorgusu ekle.
if ( ! empty($durum_filtresi) ) {
    if ($durum_filtresi === 'cezali') {
        $args['meta_key'] = 'ceza_durumu';
        $args['meta_value'] = 'cezali';
    } elseif ($durum_filtresi === 'engelli') {
        $args['meta_key'] = 'erisim_durumu';
        $args['meta_value'] = 'engelli';
    }
}

// Kullanıcıları veritabanından çek.
$musteri_sorgusu = new WP_User_Query($args);
$musteriler = $musteri_sorgusu->get_results();
$toplam_musteri = $musteri_sorgusu->get_total();
?>

<div class="wrap">
    <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Müşteri Yönetimi</h1>
    
    <?php // Arama ve Filtreleme Formu ?>
    <form method="get" class="my-4 p-4 bg-gray-100 rounded-lg flex flex-wrap items-center gap-4">
        <input type="hidden" name="page" value="yp_musteri_yonetimi">
        <label for="user-search-input" class="screen-reader-text">Kullanıcı Ara:</label>
        <input type="search" id="user-search-input" name="s" value="<?php echo esc_attr($arama_terimi); ?>" placeholder="Ad, kullanıcı adı veya e-posta ile ara..." class="regular-text">
        
        <select name="durum_filtresi">
            <option value="">Tüm Durumlar</option>
            <option value="aktif" <?php selected($durum_filtresi, 'aktif'); ?>>Aktif</option>
            <option value="engelli" <?php selected($durum_filtresi, 'engelli'); ?>>Engelli</option>
            <option value="cezali" <?php selected($durum_filtresi, 'cezali'); ?>>Cezalı</option>
        </select>
        
        <button type="submit" class="button">Filtrele</button>
    </form>

    <?php // Müşterileri Listeleyen Tablo ?>
    <table class="wp-list-table widefat fixed striped table-view-list users">
        <thead>
            <tr>
                <?php // DOCX dosyasında istenen tüm başlıklar ?>
                <th scope="col" class="manage-column">Kullanıcı Adı</th>
                <th scope="col" class="manage-column">Adı Soyadı</th>
                <th scope="col" class="manage-column">İletişim</th>
                <th scope="col" class="manage-column">Bakiye</th>
                <th scope="col" class="manage-column">Ceza Durumu</th>
                <th scope="col" class="manage-column">Erişim Durumu</th>
                <th scope="col" class="manage-column column-Aksiyon text-center">Aksiyon</th>
            </tr>
        </thead>
        <tbody id="the-list">
            <?php if ( ! empty($musteriler) ) : ?>
                <?php 
                foreach ( $musteriler as $musteri ) : 
                    // Müşteriye ait özel meta verilerini çekiyoruz.
                    $erisim_durumu = get_user_meta($musteri->ID, 'erisim_durumu', true);
                    $ceza_durumu = get_user_meta($musteri->ID, 'ceza_durumu', true);
                    $telefon = get_user_meta($musteri->ID, 'billing_phone', true);
                    $bakiye = (function_exists('woo_wallet')) ? woo_wallet()->wallet->get_wallet_balance($musteri->ID, 'display') : 'N/A';
                ?>
                    <tr>
                        <td>
                            <strong><a href="<?php echo get_edit_user_link($musteri->ID); ?>"><?php echo esc_html($musteri->user_login); ?></a></strong>
                        </td>
                        <td><?php echo esc_html($musteri->first_name . ' ' . $musteri->last_name); ?></td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($musteri->user_email); ?>"><?php echo esc_html($musteri->user_email); ?></a>
                            <br>
                            <span class="text-gray-500"><?php echo esc_html($telefon); ?></span>
                        </td>
                        <td class="font-semibold"><?php echo $bakiye; ?></td>
                        <td>
                            <?php if ($ceza_durumu === 'cezali'): ?>
                                <span class="font-bold text-red-600">Cezalı</span>
                            <?php else: ?>
                                <span class="text-green-600">Temiz</span>
                            <?php endif; ?>
                        </td>
                        <td>
                             <?php if ($erisim_durumu === 'engelli'): ?>
                                <span class="font-bold text-red-600">Engelli</span>
                            <?php else: ?>
                                <span class="font-bold text-green-600">Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="Aksiyon text-center">
                            <div class="flex gap-2 justify-center">
                                <?php // Duruma göre farklı aksiyon butonları göster ?>
                                <?php if ($erisim_durumu === 'engelli'): ?>
                                    <button class="button button-secondary btn-musteri-aksiyon" data-aksiyon="engeli_kaldir" data-user-id="<?php echo esc_attr($musteri->ID); ?>">Engeli Kaldır</button>
                                <?php else: ?>
                                     <button class="button button-secondary btn-musteri-aksiyon" data-aksiyon="engelle" data-user-id="<?php echo esc_attr($musteri->ID); ?>">Engelle</button>
                                <?php endif; ?>

                                <?php if ($ceza_durumu === 'cezali'): ?>
                                    <button class="button button-primary btn-musteri-aksiyon" data-aksiyon="ceza_kaldir" data-user-id="<?php echo esc_attr($musteri->ID); ?>">Cezayı Kaldır</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="7" class="text-center py-6">Filtreye uygun müşteri bulunamadı.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php // Sayfalama Navigasyonu ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $toplam_musteri; ?> öğe</span>
            <span class="pagination-links">
                <?php
                echo paginate_links( array(
                    'base' => add_query_arg( 'paged', '%#%' ),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => ceil($toplam_musteri / 20),
                    'current' => get_query_var('paged') ? get_query_var('paged') : 1,
                ));
                ?>
            </span>
        </div>
    </div>
</div>
<?php // --- Bitiş: includes/admin-pages/page-yp-musteri-yonetimi.php --- ?>
