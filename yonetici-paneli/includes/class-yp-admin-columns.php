<?php
/**
 * ==========================================================================
 * KLASÖR: yonetici-paneli/includes/
 * DOSYA: class-yp-admin-columns.php (Kullanıcı Çözümü ile Nihai Sürüm)
 * AÇIKLAMA: Bu sınıf, WordPress'in standart "Çiçekler" ve "Mezatlar" listeleme
 * ekranlarına, projenin gerektirdiği tüm özel sütunları ve duruma göre
 * değişen aksiyon butonlarını ekler. Bu kod, kullanıcı tarafından sağlanan
 * ve sorunları başarıyla çözen kod temel alınarak hazırlanmıştır.
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
        unset($columns['date'], $columns['author']); // Gereksiz sütunları kaldır

        $new_columns = array();
        $new_columns['cb']         = $columns['cb']; // Checkbox sütununu koru
        $new_columns['image']      = __('Resim', 'yonetici-paneli');
        $new_columns['cicek_id']   = __('ID', 'yonetici-paneli');
        $new_columns['title']      = __('Çiçek Adı', 'yonetici-paneli'); // Ana başlık sütununu koru
        $new_columns['kalite']     = __('Kalite', 'yonetici-paneli');
        $new_columns['stok']       = __('Toplam Stok', 'yonetici-paneli');
        $new_columns['actions']    = __('Aksiyonlar', 'yonetici-paneli'); // Özel aksiyon sütunu
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
                        echo '<span style="color:#888">Resim Yok</span>';
                    }
                }
                break;
            case 'cicek_id':
                $cicek_id = get_field('cicek_id', $post_id);
                echo $cicek_id ? '<strong>' . esc_html($cicek_id) . '</strong>' : 'N/A';
                break;
            case 'kalite':
                $kalite = get_field('kalite_sinifi', $post_id);
                echo $kalite ? esc_html($kalite) : '-';
                break;
            case 'stok':
                $stok = get_field('toplam_stok', $post_id);
                $birim = get_field('stok_birimi', $post_id);
                echo $stok ? '<strong>' . number_format($stok) . '</strong> ' . esc_html($birim) : '-';
                break;
            case 'actions':
                $edit_link = get_edit_post_link($post_id);
                $delete_link = get_delete_post_link($post_id);
                echo '<a href="' . esc_url($edit_link) . '" class="button button-small">Düzenle</a> ';
                echo '<a href="' . esc_url($delete_link) . '" class="button button-small" style="color:red;" onclick="return confirm(\'Bu çiçeği kalıcı olarak silmek istediğinizden emin misiniz?\')">Sil</a>';
                break;
        }
    }

    // ========================= MEZATLAR ==============================

    public function set_mezat_columns($columns) {
        unset($columns['date'], $columns['author']);
        $new_columns = array();
        $new_columns['cb']            = $columns['cb'];
        $new_columns['title']         = __('Mezat Başlığı', 'yonetici-paneli');
        $new_columns['mezat_cicek']   = __('Atanan Çiçek', 'yonetici-paneli');
        $new_columns['mezat_tarihi']  = __('Mezat Zamanı', 'yonetici-paneli');
        $new_columns['mezat_stok']    = __('Stok', 'yonetici-paneli');
        $new_columns['durum']         = __('Durum', 'yonetici-paneli');
        $new_columns['actions']       = __('Aksiyonlar', 'yonetici-paneli');
        return $new_columns;
    }

    public function render_mezat_columns($column, $post_id) {
        switch ($column) {
            case 'mezat_cicek':
                $cicek_post_object = get_field('mezattaki_cicek', $post_id);
                if ($cicek_post_object) {
                    echo '<a href="' . esc_url(get_edit_post_link($cicek_post_object->ID)) . '"><strong>' . esc_html(get_the_title($cicek_post_object->ID)) . '</strong></a>';
                } else {
                    echo '<span style="color:#888">Çiçek Yok</span>';
                }
                break;
            case 'mezat_tarihi':
                $tarih = get_field('mezat_tarihi', $post_id);
                if ($tarih) {
                    echo '<strong>' . date_i18n('d M Y', strtotime($tarih)) . '</strong><br>';
                    echo date_i18n('H:i', strtotime($tarih));
                }
                break;
            case 'mezat_stok':
                $stok = get_field('mezat_stok', $post_id);
                echo $stok ? '<strong>' . esc_html($stok) . '</strong> adet' : '-';
                break;
            case 'durum':
                $durum = get_field('mezat_durumu', $post_id, false); // Ham değeri al
                echo $durum ? cicekmezat_get_mezat_durum_etiketi($durum) : '-';
                break;
            case 'actions':
                $status = get_field('mezat_durumu', $post_id, false);
                $is_live_time = (strtotime(get_field('mezat_tarihi', $post_id)) <= time());

                echo '<div class="flex gap-2 flex-wrap">';
                echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '" class="button button-small">Düzenle</a> ';
                
                if (in_array($status, ['yayinda', 'canli', 'duraklatildi']) && $is_live_time) {
                    $url = get_permalink(get_page_by_path('yonetici-canli-mezat')) . '?post_id=' . $post_id;
                    echo '<a href="' . esc_url($url) . '" class="button button-small button-primary">Canlı Git</a> ';
                }
                if ($status === 'yayinda') {
                    echo '<a href="#" class="button button-small mezat-status-action" data-action="taslak" data-post="' . esc_attr($post_id) . '">Taslak Yap</a> ';
                } elseif ($status === 'taslak') {
                    echo '<a href="#" class="button button-small mezat-status-action" data-action="yayinla" data-post="' . esc_attr($post_id) . '">Yayınla</a> ';
                }
                if (in_array($status, ['yayinda','taslak','canli','duraklatildi'])) {
                    echo '<a href="#" class="button button-small mezat-status-action" data-action="iptal" data-post="' . esc_attr($post_id) . '" style="color:#b32d2e;">İptal Et</a> ';
                }
                
                echo '<a href="' . get_delete_post_link($post_id) . '" class="button button-small" style="color:red;" onclick="return confirm(\'Bu mezatı kalıcı olarak silmek istediğinizden emin misiniz?\')">Sil</a>';
                echo '</div>';
                break;
        }
    }
}

// Sınıfı başlatmak için bir fonksiyon
function initialize_yp_admin_columns() {
    new YP_Admin_Columns();
}
// Sadece yönetici panelinde ve doğru zamanda çalışmasını sağla
if (is_admin()) {
    add_action('load-edit.php', 'initialize_yp_admin_columns');
}
