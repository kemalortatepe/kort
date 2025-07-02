<?php
/**
 * ==========================================================================
 * KLASÖR: yonetici-paneli/includes/
 * DOSYA: class-yp-admin-columns.php (Nihai Düzeltilmiş Sürüm)
 * AÇIKLAMA: Bu sınıf, WordPress'in standart "Çiçekler" ve "Mezatlar" listeleme
 * ekranlarına, projemize özel sütunlar ve dinamik aksiyon linkleri/butonları ekler.
 *
 * GÜNCELLEME NOTLARI:
 * - Ölümcül hataya neden olan YP_DB_Helper bağımlılığı tamamen kaldırıldı.
 * - Tüm veriler, artık doğrudan ve doğru yöntem olan get_field() ile çekilmektedir.
 * - Hem Çiçekler hem de Mezatlar listesine, istenen tüm aksiyonları içeren
 * özel bir "Aksiyonlar" sütunu eklendi.
 * - Mezat listesindeki boş satır hatası, 'title' sütunu doğru yönetilerek giderildi.
 * ==========================================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class YP_Admin_Columns {

    public function __construct() {
        // Çiçekler gönderi türü için filtre ve aksiyonlar
        add_filter('manage_cicek_posts_columns', array($this, 'set_cicek_columns'));
        add_action('manage_cicek_posts_custom_column', array($this, 'render_cicek_columns'), 10, 2);

        // Mezatlar gönderi türü için filtre ve aksiyonlar
        add_filter('manage_mezat_posts_columns', array($this, 'set_mezat_columns'));
        add_action('manage_mezat_posts_custom_column', array($this, 'render_mezat_columns'), 10, 2);
    }

    // ========================= ÇİÇEKLER ==============================

    public function set_cicek_columns($columns) {
        unset($columns['date'], $columns['author']);
        $new_columns = [];
        $new_columns['cb']         = $columns['cb'];
        $new_columns['image']      = __('Resim');
        $new_columns['cicek_id']   = __('ID');
        $new_columns['title']      = __('Çiçek Adı');
        $new_columns['kalite']     = __('Kalite');
        $new_columns['stok']       = __('Toplam Stok');
        $new_columns['actions']    = __('Aksiyonlar');
        return $new_columns;
    }

    public function render_cicek_columns($column, $post_id) {
        switch($column) {
            case 'image':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(60,60), array('style'=>'border-radius:4px;object-fit:cover;'));
                } else {
                    $gallery = get_field('cicek_galerisi', $post_id);
                    if (!empty($gallery) && isset($gallery[0]['sizes']['thumbnail'])) {
                        echo '<img src="' . esc_url($gallery[0]['sizes']['thumbnail']) . '" width="60" height="60" alt="Galeri Resmi" style="border-radius:4px;object-fit:cover;" />';
                    } else {
                        echo '<span style="color:#888; display:inline-block; width:60px; text-align:center;">Resim Yok</span>';
                    }
                }
                break;
            case 'cicek_id':
                echo '<strong>' . esc_html(get_field('cicek_id', $post_id)) . '</strong>';
                break;
            case 'kalite':
                echo esc_html(get_field('kalite_sinifi', $post_id));
                break;
            case 'stok':
                $stok = get_field('toplam_stok_tane', $post_id);
                $birim = get_field('stok_birimi', $post_id);
                echo $stok ? '<strong>' . number_format($stok) . '</strong> ' . esc_html($birim['label']) : '-';
                break;
            case 'actions':
                printf('<a class="button" href="%s">Düzenle</a>', get_edit_post_link($post_id));
                printf(' <a class="button button-link-delete" href="%s" onclick="return confirm(\'Bu çiçeği kalıcı olarak silmek istediğinizden emin misiniz?\')">Sil</a>', get_delete_post_link($post_id));
                break;
        }
    }

    // ========================= MEZATLAR ==============================

    public function set_mezat_columns($columns) {
        unset($columns['date'], $columns['author']);
        $new_columns = [];
        $new_columns['cb']            = $columns['cb'];
        $new_columns['title']         = __('Mezat Başlığı');
        $new_columns['mezat_cicek']   = __('Atanan Çiçek');
        $new_columns['mezat_tarihi']  = __('Mezat Zamanı');
        $new_columns['mezat_stok']    = __('Stok');
        $new_columns['durum']         = __('Durum');
        $new_columns['actions']       = __('Aksiyonlar');
        return $new_columns;
    }

    public function render_mezat_columns($column, $post_id) {
        switch ($column) {
            case 'mezat_cicek':
                $cicek_post_object = get_field('mezattaki_cicek', $post_id);
                if ($cicek_post_object) {
                    echo '<a href="' . esc_url(get_edit_post_link($cicek_post_object->ID)) . '"><strong>' . esc_html(get_the_title($cicek_post_object->ID)) . '</strong></a>';
                } else {
                    echo '<span style="color:#888;">Çiçek Atanmamış</span>';
                }
                break;
            case 'mezat_tarihi':
                $tarih = get_field('mezat_tarihi', $post_id, false); // Ham veriyi al: Y-m-d H:i:s
                if ($tarih) {
                    echo '<strong>' . date_i18n('d M Y', strtotime($tarih)) . '</strong><br>' . date_i18n('H:i', strtotime($tarih));
                }
                break;
            case 'mezat_stok':
                $stok = get_field('mezat_stok', $post_id);
                echo $stok ? '<strong>' . esc_html($stok) . '</strong> adet' : '-';
                break;
            case 'durum':
                $durum = get_field('mezat_durumu', $post_id, false);
                echo $durum ? cicekmezat_get_mezat_durum_etiketi($durum) : '<span style="color:#888;">Belirtilmemiş</span>';
                break;
            case 'actions':
                echo '<div style="display:flex; flex-wrap:wrap; gap:5px;">';
                $status = get_field('mezat_durumu', $post_id, false);
                $mezat_tarihi_str = get_field('mezat_tarihi', $post_id, false);
                $is_live_time = ($mezat_tarihi_str && strtotime($mezat_tarihi_str) <= time());

                // Canlı Git Butonu
                if (in_array($status, ['yayinda', 'canli', 'duraklatildi']) && $is_live_time) {
                    $url = get_permalink(get_page_by_path('yonetici-canli-mezat')) . '?post_id=' . $post_id;
                    echo '<a href="' . esc_url($url) . '" class="button button-small button-primary">Canlı Git</a>';
                }
                // Düzenle Butonu
                echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '" class="button button-small">Düzenle</a>';

                // Diğer durum butonları...
                if ($status === 'yayinda') {
                    echo '<a href="#" class="button button-small mezat-status-action" data-action="taslak_yap" data-post-id="'.$post_id.'">Taslak Yap</a>';
                } elseif ($status === 'taslak') {
                    echo '<a href="#" class="button button-small mezat-status-action" data-action="yayinla" data-post-id="'.$post_id.'">Yayınla</a>';
                }
                if (in_array($status, ['yayinda','taslak','canli','duraklatildi'])) {
                    echo '<a href="#" class="button-link-delete mezat-status-action" data-action="iptal" data-post-id="'.$post_id.'">İptal Et</a>';
                }
                
                // Sil Butonu (WordPress standart linki)
                 echo '<a href="' . get_delete_post_link($post_id) . '" class="button-link-delete" onclick="return confirm(\'Bu mezatı kalıcı olarak silmek istediğinizden emin misiniz?\')">Sil</a>';
                
                echo '</div>';
                break;
        }
    }
}
