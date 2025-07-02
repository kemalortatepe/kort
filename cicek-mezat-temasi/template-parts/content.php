<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/template-parts/
 * DOSYA: content.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu dosya, `index.php` ve `archive.php` gibi listeleme sayfalarında
 * her bir gönderinin (post) özetini göstermek için kullanılan şablon parçasıdır.
 * get_template_part() fonksiyonu ile çağrılır. Bu modüler yapı, kodun
 * tekrarını önler ve yönetimi kolaylaştırır.
 * ==========================================================================
 *
 * @package CicekMezat
 */
?>

<?php // Her bir gönderi için ana kapsayıcı. post_class() fonksiyonu, gönderiye özel CSS sınıfları (post, hentry, type-post vb.) ekler. ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'mb-12 bg-gray-800 rounded-lg overflow-hidden shadow-lg border border-gray-700 transition-all hover:border-amber-500/50 hover:shadow-amber-500/5' ); ?>>
    
    <?php // Gönderinin öne çıkan görseli var mı diye kontrol et. ?>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="post-thumbnail">
            <?php // Öne çıkan görsele, gönderinin kendi sayfasına giden bir link ekle. ?>
            <a href="<?php the_permalink(); ?>">
                <?php 
                // Öne çıkan görseli 'large' boyutunda ve belirtilen CSS sınıflarıyla ekrana yazdır.
                the_post_thumbnail( 'large', array( 'class' => 'w-full h-64 object-cover' ) ); 
                ?>
            </a>
        </div>
    <?php endif; ?>

	<div class="entry-content p-6">
        <header class="entry-header mb-4">
            <?php
            // Gönderi başlığını, gönderinin kendi sayfasına link vererek (rel="bookmark") ve bir h2 etiketi içinde gösterir.
            the_title( sprintf( '<h2 class="entry-title text-2xl font-bold text-amber-400 hover:text-amber-500 transition-colors"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
            ?>
             <div class="entry-meta text-xs text-gray-400 mt-2">
                <?php // Gönderinin yayımlanma tarihini, WordPress ayarlarındaki formata göre gösterir. ?>
                <span>Yayımlanma Tarihi: <?php echo get_the_date(); ?></span>
            </div></header><div class="prose prose-invert max-w-none text-gray-300">
			<?php
            /**
             * Gönderinin özetini gösterir. Eğer gönderi içinde etiketi varsa,
             * o etikete kadar olan kısmı alır. Yoksa, gönderinin ilk 55 kelimesini otomatik
             * olarak alır ve sonuna "[...]" ekler.
             */
			the_excerpt();
			?>
		</div><footer class="entry-footer mt-6">
            <?php // Gönderinin tam halini okumak için kendi sayfasına yönlendiren bir buton. ?>
            <a href="<?php the_permalink(); ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">
                <?php esc_html_e( 'Devamını Oku', 'cicekmezat' ); ?>
            </a>
        </footer></div></article><?php // --- Bitiş: template-parts/content.php --- ?>
