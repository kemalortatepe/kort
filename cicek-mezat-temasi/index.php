<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: index.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Temanızın ana şablon dosyasıdır. WordPress, gösterilecek içerik için
 * daha spesifik bir şablon bulamazsa (örn: archive.php, single.php, front-page.php),
 * varsayılan olarak bu dosyayı kullanır. Genellikle blog gönderi listesi için kullanılır.
 * Bu dosyada doğrudan AJAX kodu bulunmaz, çünkü görevi sunucudan gelen içeriği
 * statik olarak görüntülemektir. AJAX işlemleri, `get_header()` ve `get_footer()`
 * fonksiyonları aracılığıyla yüklenen JavaScript dosyaları üzerinden yönetilir.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// Sitenin header.php dosyasını dahil et. Bu, sayfanın <head> bölümünü,
// ana menüyü ve sitenin başlığını içerir. Ayrıca, wp_head() kancası ile
// tüm CSS ve JS dosyalarımızı (AJAX kodlarımızın bulunduğu) yükler.
get_header();
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

    <header class="page-header mb-8">
        <?php
        // Eğer bir arşiv sayfası görüntüleniyorsa (kategori, etiket vb.), arşiv başlığını göster.
        if ( is_archive() ) {
            the_archive_title( '<h1 class="text-3xl font-bold text-amber-400">', '</h1>' );
            the_archive_description( '<div class="text-gray-400 mt-2">', '</div>' );
        } 
        // Eğer arama sonuçları sayfası ise, arama başlığını göster.
        elseif ( is_search() ) {
            ?>
            <h1 class="text-3xl font-bold text-amber-400">
                <?php
                /* translators: %s: arama sorgusu. */
                printf( esc_html__( 'Arama Sonuçları: %s', 'cicekmezat' ), '<span>' . get_search_query() . '</span>' );
                ?>
            </h1>
            <?php
        } 
        // Diğer durumlarda (örneğin blog ana sayfası ise) genel bir başlık gösterilebilir.
        else {
            // echo '<h1 class="text-3xl font-bold text-amber-400">Blog</h1>';
        }
        ?>
    </header>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php
        // WordPress'in ana döngüsü (The Loop) başlar.
        // Bu döngü, veritabanında gösterilecek gönderi olup olmadığını kontrol eder.
		if ( have_posts() ) :

			// Gönderiler varsa, her biri için döngüyü çalıştır.
			while ( have_posts() ) :
				the_post(); // Mevcut gönderi verilerini kurar (başlık, içerik, tarih vb.).

				/*
				 * Gönderi içeriğini göstermek için bir şablon parçası dahil et.
				 * Bu, kodun tekrarını önler ve daha organize bir yapı sağlar.
				 * WordPress, get_post_type() fonksiyonu ile mevcut gönderinin türünü
				 * (post, page, cicek vb.) alarak `content-post.php`, `content-page.php`
				 * gibi dosyaları arar. Bulamazsa, `content.php`'yi kullanır.
                 * DİKKAT: Bu dosyanın hatasız çalışması için temanızın içinde
                 * `template-parts/content.php` dosyasının oluşturulmuş olması gerekmektedir.
				 */
				get_template_part( 'template-parts/content', get_post_type() );

			endwhile; // Döngü biter.

			// Gönderi listesinin altına sayfalama linklerini (Önceki Sayfa / Sonraki Sayfa) ekler.
			the_posts_navigation();

		else : // Eğer gösterilecek hiç gönderi bulunamazsa

			// `template-parts/content-none.php` şablonunu dahil et.
            // DİKKAT: Bu dosyanın hatasız çalışması için temanızın içinde
            // `template-parts/content-none.php` dosyasının oluşturulmuş olması gerekmektedir.
			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

		</main></div></div><?php
// Sitenin footer.php dosyasını dahil et. Bu, sayfanın alt bilgi bölümünü
// ve en önemlisi, wp_footer() kancasını içerir. AJAX kodlarımızın çalışması için
// gerekli olan script dosyaları bu kanca ile yüklenir.
get_footer();
// --- Bitiş: index.php ---
