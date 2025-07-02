<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/inc/
 * DOSYA: template-functions.php (Yeni Dosya)
 * AÇIKLAMA: Bu dosya, temanın şablon dosyaları içinde tekrar eden veya
 * karmaşık mantıklar içeren kod bloklarını, yeniden kullanılabilir
 * fonksiyonlar haline getirir. Bu, kod tekrarını önler (DRY - Don't Repeat Yourself)
 * ve temanın bakımını kolaylaştırır.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! function_exists( 'cicekmezat_get_mezat_durum_etiketi' ) ) :
    /**
     * Veritabanından gelen mezat durumuna göre renkli bir HTML etiketi oluşturur.
     *
     * @param string $durum Mezatın durumu ('yayinda', 'canli', 'bitti', 'iptal').
     * @return string Oluşturulan HTML etiketi.
     */
    function cicekmezat_get_mezat_durum_etiketi( $durum ) {
        $etiket_class = 'bg-gray-500'; // Varsayılan renk
        $etiket_metin = ucfirst($durum); // İlk harfi büyüt

        switch ($durum) {
            case 'canli':
                $etiket_class = 'bg-green-600';
                $etiket_metin = 'Canlı';
                break;
            case 'yayinda':
                $etiket_class = 'bg-blue-600';
                $etiket_metin = 'Yayında';
                break;
            case 'iptal':
                $etiket_class = 'bg-red-600';
                $etiket_metin = 'İptal Edildi';
                break;
            case 'bitti':
                $etiket_class = 'bg-gray-700';
                $etiket_metin = 'Bitti';
                break;
            case 'taslak':
                $etiket_class = 'bg-yellow-500 text-black';
                $etiket_metin = 'Taslak';
                break;
        }

        return '<span class="px-2 py-1 text-xs font-semibold rounded-full text-white ' . esc_attr($etiket_class) . '">' . esc_html($etiket_metin) . '</span>';
    }
endif;


if ( ! function_exists( 'cicekmezat_get_user_status_etiketi' ) ) :
    /**
     * Kullanıcının meta verilerine göre durum etiketini oluşturur.
     *
     * @param int $user_id Kullanıcı ID'si.
     * @return string Oluşturulan HTML etiketi.
     */
    function cicekmezat_get_user_status_etiketi( $user_id ) {
        $erisim_durumu = get_user_meta($user_id, 'erisim_durumu', true);
        $ceza_durumu = get_user_meta($user_id, 'ceza_durumu', true);

        if ( $erisim_durumu === 'engelli' ) {
            return '<span class="font-bold text-red-600">Engelli</span>';
        }
        if ( $ceza_durumu === 'cezali' ) {
            return '<span class="font-bold text-yellow-500">Cezalı</span>';
        }

        return '<span class="font-bold text-green-600">Aktif</span>';
    }
endif;


if ( ! function_exists( 'cicekmezat_render_admin_pagination' ) ) :
    /**
     * Yönetici paneli sayfaları için standart sayfalama navigasyonu oluşturur.
     *
     * @param int $total_items Toplam öğe sayısı.
     * @param int $per_page Sayfa başına gösterilecek öğe sayısı.
     */
    function cicekmezat_render_admin_pagination( $total_items, $per_page ) {
        if ($total_items <= $per_page) {
            return; // Sayfalamaya gerek yoksa hiçbir şey yapma.
        }

        $total_pages = ceil($total_items / $per_page);
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;

        echo '<div class="tablenav bottom"><div class="tablenav-pages"><span class="displaying-num">' . esc_html($total_items) . ' öğe</span>';
        echo '<span class="pagination-links">' . paginate_links( array(
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total'     => $total_pages,
            'current'   => $current_page,
        ) ) . '</span></div></div>';
    }
endif;


// Gelecekte eklenecek diğer yardımcı fonksiyonlar buraya yazılabilir.
// Örneğin, bir çiçeğin tüm özelliklerini tek bir fonksiyonla çeken
// veya bir mezat için kalan süreyi biçimlendiren fonksiyonlar.

// --- Bitiş: inc/template-functions.php ---
