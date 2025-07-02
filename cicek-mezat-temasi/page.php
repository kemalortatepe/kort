<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: page.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Tüm standart WordPress sayfaları için varsayılan şablondur.
 * WordPress, bir sayfa görüntülenirken daha spesifik bir şablon
 * (örneğin, page-hakkimizda.php veya page-templates/ altındaki bir şablon)
 * bulamazsa bu dosyayı kullanır.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// Sitenin header.php dosyasını dahil et.
get_header();
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php
			// WordPress'in ana döngüsü (The Loop) başlar.
			while ( have_posts() ) :
				the_post(); // Mevcut sayfa verilerini kurar (başlık, içerik vb.).
			?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-[#1F2937] p-6 md:p-8 rounded-lg border border-gray-700' ); ?>>
					<header class="entry-header mb-6 pb-4 border-b border-gray-600">
						<?php 
                        // Sayfanın başlığını bir h1 etiketi içinde gösterir.
                        the_title( '<h1 class="entry-title text-3xl font-bold text-amber-400">', '</h1>' ); 
                        ?>
					</header><div class="entry-content prose prose-invert max-w-none text-gray-300">
						<?php
                        // Sayfanın WordPress editöründe girilen ana içeriğini gösterir.
						the_content();

                        // Çok sayfalı gönderiler için sayfa linklerini gösterir.
						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Sayfalar:', 'cicekmezat' ),
								'after'  => '</div>',
							)
						);
						?>
					</div><?php if ( get_edit_post_link() ) : // Sadece giriş yapmış ve yetkisi olan kullanıcılar için "Düzenle" linki göster. ?>
						<footer class="entry-footer mt-6 pt-6 border-t border-gray-600">
							<?php
							edit_post_link(
								sprintf(
									wp_kses(
										/* translators: %s: Name of current post. Only visible to screen readers */
										__( 'Düzenle <span class="sr-only">%s</span>', 'cicekmezat' ),
										array(
											'span' => array(
												'class' => array(),
											),
										)
									),
									get_the_title()
								),
								'<span class="edit-link text-sm text-blue-400 hover:underline">',
								'</span>'
							);
							?>
						</footer><?php endif; ?>
				</article><?php
				// Eğer sayfa için yorumlar açıksa, yorum formunu ve listesini göster.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // Döngü biter.
			?>

		</main></div></div><?php
// Sitenin footer.php dosyasını dahil et.
get_footer();
// --- Bitiş: page.php ---
