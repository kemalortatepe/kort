<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/inc/
 * DOSYA: post-types.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Projemize özel "Çiçekler" (cicek) ve "Mezatlar" (mezat)
 * özel gönderi türlerini (Custom Post Types - CPT) WordPress'e tanıtır.
 *
 * YENİ YAPI GÜNCELLEMESİ:
 * 'show_in_menu' argümanı 'true' olarak değiştirildi. Bu sayede WordPress,
 * bu gönderi türleri için yönetici panelinin sol tarafına otomatik olarak
 * "Çiçekler" ve "Mezatlar" adında ana menü öğeleri ekler. Yönetim artık
 * bu standart WordPress arayüzlerinden ACF PRO ile yapılacaktır.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Projeye özel tüm gönderi türlerini kaydeder.
 * Bu fonksiyon, WordPress'in başlangıç aşamasında (init kancası) çalıştırılır.
 */
function cicekmezat_register_post_types() {

    /**
     * Post Type: Çiçekler.
     * Mezatta satılacak her bir çiçek türü için bir gönderi oluşturulur.
     */
    $cicek_labels = array(
        'name'                  => _x( 'Çiçekler', 'Post Type General Name', 'cicekmezat' ),
        'singular_name'         => _x( 'Çiçek', 'Post Type Singular Name', 'cicekmezat' ),
        'menu_name'             => __( 'Çiçekler', 'cicekmezat' ),
        'name_admin_bar'        => __( 'Çiçek', 'cicekmezat' ),
        'archives'              => __( 'Çiçek Arşivleri', 'cicekmezat' ),
        'attributes'            => __( 'Çiçek Özellikleri', 'cicekmezat' ),
        'parent_item_colon'     => __( 'Üst Çiçek:', 'cicekmezat' ),
        'all_items'             => __( 'Tüm Çiçekler', 'cicekmezat' ),
        'add_new_item'          => __( 'Yeni Çiçek Ekle', 'cicekmezat' ),
        'add_new'               => __( 'Yeni Ekle', 'cicekmezat' ),
        'new_item'              => __( 'Yeni Çiçek', 'cicekmezat' ),
        'edit_item'             => __( 'Çiçeği Düzenle', 'cicekmezat' ),
        'update_item'           => __( 'Çiçeği Güncelle', 'cicekmezat' ),
        'view_item'             => __( 'Çiçeği Görüntüle', 'cicekmezat' ),
        'view_items'            => __( 'Çiçekleri Görüntüle', 'cicekmezat' ),
        'search_items'          => __( 'Çiçek Ara', 'cicekmezat' ),
    );
    $cicek_args = array(
        'label'                 => __( 'Çiçek', 'cicekmezat' ),
        'description'           => __( 'Mezatta satılacak çiçekler.', 'cicekmezat' ),
        'labels'                => $cicek_labels,
        'supports'              => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true, // GÜNCELLENDİ: Artık WordPress ana menüsünde gösterilecek.
        'menu_position'         => 21, // "Yorumlar" menüsünün altına yerleştir
        'menu_icon'             => 'dashicons-palmtree', // Menü ikonu
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => array( 'slug' => 'cicek', 'with_front' => false ),
    );
    register_post_type( 'cicek', $cicek_args );

    /**
     * Post Type: Mezatlar.
     * Her bir canlı mezat etkinliği için bir gönderi oluşturulur.
     */
    $mezat_labels = array(
        'name'                  => _x( 'Mezatlar', 'Post Type General Name', 'cicekmezat' ),
        'singular_name'         => _x( 'Mezat', 'Post Type Singular Name', 'cicekmezat' ),
        'menu_name'             => __( 'Mezatlar', 'cicekmezat' ),
        'name_admin_bar'        => __( 'Mezat', 'cicekmezat' ),
        'archives'              => __( 'Mezat Arşivleri', 'cicekmezat' ),
        'attributes'            => __( 'Mezat Özellikleri', 'cicekmezat' ),
        'parent_item_colon'     => __( 'Üst Mezat:', 'cicekmezat' ),
        'all_items'             => __( 'Tüm Mezatlar', 'cicekmezat' ),
        'add_new_item'          => __( 'Yeni Mezat Ekle', 'cicekmezat' ),
        'add_new'               => __( 'Yeni Ekle', 'cicekmezat' ),
        'new_item'              => __( 'Yeni Mezat', 'cicekmezat' ),
        'edit_item'             => __( 'Mezatı Düzenle', 'cicekmezat' ),
        'update_item'           => __( 'Mezatı Güncelle', 'cicekmezat' ),
        'view_item'             => __( 'Mezatı Görüntüle', 'cicekmezat' ),
        'view_items'            => __( 'Mezatları Görüntüle', 'cicekmezat' ),
        'search_items'          => __( 'Mezat Ara', 'cicekmezat' ),
    );
    $mezat_args = array(
        'label'                 => __( 'Mezat', 'cicekmezat' ),
        'description'           => __( 'Canlı mezat etkinlikleri.', 'cicekmezat' ),
        'labels'                => $mezat_labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true, // GÜNCELLENDİ: Artık WordPress ana menüsünde gösterilecek.
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-megaphone',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => array( 'slug' => 'mezat', 'with_front' => false ),
    );
    register_post_type( 'mezat', $mezat_args );

}
// cicekmezat_register_post_types fonksiyonunu WordPress'in doğru zamanında (init) çalıştır.
add_action( 'init', 'cicekmezat_register_post_types', 0 );
// --- Bitiş: inc/post-types.php ---
