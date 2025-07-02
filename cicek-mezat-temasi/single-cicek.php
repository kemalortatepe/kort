<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: single-cicek.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: "Çiçek" özel gönderi türüne (Custom Post Type) ait tekil sayfaları
 * görüntülemek için kullanılan şablondur. Bir ziyaretçi bir çiçeğin
 * detayını görmek istediğinde WordPress otomatik olarak bu şablonu kullanır.
 *
 * YENİ YAPI GÜNCELLEMESİ:
 * - Bu şablon artık ACF PRO'nun "Galeri" alanını kullanarak birden fazla resmi
 * interaktif bir şekilde gösterecek şekilde güncellenmiştir.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// Sitenin header.php dosyasını dahil et.
get_header();

global $wpdb; // WordPress veritabanı sınıfına erişim sağlar.
$post_id = get_the_ID(); // Görüntülenen mevcut çiçeğin WordPress post ID'sini al.

// --- VERİ ÇEKME İŞLEMLERİ ---

// Özel tablomuz olan `wp_cm_cicekler`'den, mevcut post ID'ye ait çiçek verilerini çek.
$cicek_data = $wpdb->get_row( $wpdb->prepare( 
    "SELECT * FROM {$wpdb->prefix}cm_cicekler WHERE post_id = %d", 
    $post_id 
) );

// ACF PRO'nun "Galeri" alanından resimleri al.
// Alan adının 'cicek_galerisi' olduğu varsayılmıştır.
$gallery_images = get_field('cicek_galerisi', $post_id);

// Ana resim olarak öne çıkan görseli kullan. Eğer yoksa, galerideki ilk resmi kullan.
$main_image_url = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'large') : ($gallery_images ? esc_url($gallery_images[0]['sizes']['large']) : 'https://placehold.co/800x600/1F2937/FFFFFF?text=Resim+Yok');

?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php
			// WordPress'in ana döngüsü (The Loop) başlar.
			while ( have_posts() ) :
				the_post(); // Mevcut gönderi (çiçek) verilerini kurar.
			?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <?php // Geri Dön butonu, kullanıcıyı geldiği sayfaya (genellikle mezat takvimi) yönlendirir. ?>
                    <header class="mb-8">
                        <a href="<?php echo esc_url( get_post_type_archive_link('mezat') ?: home_url() ); ?>" class="text-blue-400 hover:text-blue-500 transition-colors">
                            &larr; Mezat Takvimine Geri Dön
                        </a>
                    </header>
        
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12">
                        
                        <div class="space-y-4">
                            <?php // Ana Resim Alanı ?>
                            <div class="main-image-wrapper">
                                <img id="detailMainImage" src="<?php echo esc_url($main_image_url); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-auto max-h-[500px] object-cover rounded-lg shadow-lg">
                            </div>
                            
                            <?php // Galeri Thumbnail'ları Alanı ?>
                            <?php if ( ! empty( $gallery_images ) ) : ?>
                                <div id="detailGalleryContainer" class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-4">
                                    <?php // Öne çıkan görseli de galerinin ilk elemanı olarak ekleyelim (varsa) ?>
                                    <?php if(has_post_thumbnail()): ?>
                                         <img src="<?php echo get_the_post_thumbnail_url($post_id, 'thumbnail'); ?>" data-large-src="<?php echo get_the_post_thumbnail_url($post_id, 'large'); ?>" alt="<?php the_title_attribute(); ?>" class="gallery-thumbnail w-full h-24 object-cover rounded-md cursor-pointer border-2 border-amber-400 transition-colors">
                                    <?php endif; ?>
                                    
                                    <?php foreach ( $gallery_images as $image ) : ?>
                                        <img src="<?php echo esc_url($image['sizes']['thumbnail']); ?>" data-large-src="<?php echo esc_url($image['sizes']['large']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="gallery-thumbnail w-full h-24 object-cover rounded-md cursor-pointer border-2 border-transparent hover:border-amber-400 transition-colors">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
        
                        <div class="space-y-6">
                            <div>
                                <?php if ($cicek_data) : ?>
                                    <span class="text-sm text-gray-400">Çiçek ID: <?php echo esc_html($cicek_data->cicek_id); ?></span>
                                <?php endif; ?>
                                <?php the_title( '<h1 class="text-4xl font-bold text-white mt-1">', '</h1>' ); ?>
                            </div>
                            
                            <div class="text-lg leading-relaxed prose prose-invert max-w-none text-gray-300">
                                <h2 class="text-2xl font-semibold text-amber-400 mb-2">Ürün Açıklaması</h2>
                                <?php the_content(); // WordPress editörüne girilen içeriği gösterir. ?>
                            </div>
        
                            <?php if ($cicek_data) : // Sadece özel tablo verileri varsa bu bölümü göster. ?>
                            <div class="spec-table bg-[#1F2937] border border-gray-700 rounded-lg p-6">
                                <h3 class="text-xl font-semibold text-white mb-4 pb-2 border-b border-gray-600">Teknik Özellikler</h3>
                                <div class="space-y-3">
                                    <div class="spec-table-row flex justify-between"><span class="spec-label text-gray-400">Kalite Sınıfı</span><span class="spec-value font-semibold text-white"><?php echo esc_html(get_field('kalite_sinifi', $post_id)); ?></span></div>
                                    <div class="spec-table-row flex justify-between"><span class="spec-label text-gray-400">Sap Uzunluğu</span><span class="spec-value font-semibold text-white"><?php echo esc_html(get_field('sap_uzunlugu', $post_id)); ?> cm</span></div>
                                    <div class="spec-table-row flex justify-between"><span class="spec-label text-gray-400">Menşei</span><span class="spec-value font-semibold text-white"><?php echo esc_html(get_field('mensei', $post_id)); ?></span></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

			<?php
			endwhile; // Döngü biter.
			?>

		</main></div></div><script>
// Bu script, sayfadaki resim galerisine tıklama işlevselliği ekler.
// Bu tür basit DOM manipülasyonları için AJAX gerekli değildir.
jQuery(document).ready(function($) {
    'use strict';
    
    const galleryContainer = $('#detailGalleryContainer');

    // Eğer sayfada galeri varsa...
    if (galleryContainer.length) {
        const mainImage = $('#detailMainImage');

        // Galerideki herhangi bir resme tıklandığında...
        galleryContainer.on('click', '.gallery-thumbnail', function() {
            const clickedImage = $(this);
            const largeImageUrl = clickedImage.data('large-src'); // data-large-src attribute'ündeki büyük resim URL'sini al.
            
            // Ana resmin src'sini, tıklanan thumbnail'in büyük versiyonu ile değiştir.
            mainImage.attr('src', largeImageUrl);

            // Görsel geri bildirim için stil güncellemesi
            // Önce tüm resimlerden 'active' stilini kaldır.
            galleryContainer.find('.gallery-thumbnail').removeClass('border-amber-400').addClass('border-transparent');
            // Sonra sadece tıklanan resme 'active' stilini ekle.
            clickedImage.addClass('border-amber-400').removeClass('border-transparent');
        });
    }
});
</script>

<?php
// Sitenin footer.php dosyasını dahil et.
get_footer();
// --- Bitiş: single-cicek.php ---
