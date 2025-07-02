<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/template-parts/
 * DOSYA: content-none.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu şablon, WordPress bir sorgu çalıştırdığında (örneğin bir arama
 * yaptığında, bir kategori arşivini listelediğinde vb.) hiçbir gönderi veya
 * sayfa bulunamazsa gösterilir. Kullanıcıya aradığının mevcut olmadığını
 * bildirir ve alternatif eylemler sunar.
 * ==========================================================================
 *
 * @package CicekMezat
 */
?>

<section class="no-results not-found bg-[#1F2937] p-8 rounded-lg border border-gray-700 text-center">
	<header class="page-header">
		<h1 class="page-title text-3xl font-bold text-amber-400 mb-4">
            <?php esc_html_e( 'Hiçbir Şey Bulunamadı', 'cicekmezat' ); ?>
        </h1>
	</header><div class="page-content prose prose-invert max-w-none text-gray-300">
		<?php
        // Eğer kullanıcı ana sayfadaysa ve hiç gönderi yoksa, yeni bir gönderi
        // oluşturması için bir link göster (sadece yönetici yetkisine sahipse).
		if ( is_home() && current_user_can( 'publish_posts' ) ) :

			printf(
				'<p>' . wp_kses(
					/* translators: 1: link to WP admin new post page. */
					__( 'İlk gönderinizi yayınlamaya hazır mısınız? <a href="%1$s">Buradan başlayın</a>.', 'cicekmezat' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				) . '</p>',
				esc_url( admin_url( 'post-new.php' ) )
			);

        // Eğer bir arama sonucunda içerik bulunamadıysa, kullanıcıya farklı
        // anahtar kelimelerle tekrar denemesini öner.
		elseif ( is_search() ) :
			?>

			<p><?php esc_html_e( 'Üzgünüz, aramanızla eşleşen bir sonuç bulunamadı. Lütfen farklı anahtar kelimelerle tekrar deneyin.', 'cicekmezat' ); ?></p>
			<?php
            // WordPress'in standart arama formunu gösterir.
			get_search_form();

        // Diğer tüm durumlarda (boş kategori, etiket vb.), genel bir mesaj
        // ve arama formu göster.
		else :
			?>

			<p><?php esc_html_e( 'Görünüşe göre aradığınızı bulamadık. Belki bir arama yardımcı olabilir.', 'cicekmezat' ); ?></p>
			<?php
			get_search_form();

		endif;
		?>
	</div></section><?php // --- Bitiş: template-parts/content-none.php --- ?>
