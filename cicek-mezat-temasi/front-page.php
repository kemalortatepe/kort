<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: front-page.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Sitenin ana sayfasını oluşturan özel şablondur.
 * WordPress Ayarlar > Okuma menüsünden "Ana sayfa görüntülemesi" olarak
 * statik bir sayfa seçildiğinde ve o sayfa "Ana Sayfa" olarak atandığında bu şablon kullanılır.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// Sitenin header.php dosyasını dahil et. Bu, sayfanın <head> bölümünü,
// ana menüyü ve sitenin başlığını içerir.
get_header();
?>

<main id="main" class="site-main">

    <section class="hero-section relative text-white text-center py-20 md:py-32 flex items-center justify-center" style="background: linear-gradient(rgba(17, 24, 39, 0.8), rgba(17, 24, 39, 0.8)), url('<?php echo CICEKMEZAT_THEME_URI; ?>/assets/images/hero-bg.jpg') no-repeat center center; background-size: cover;">
        <div class="relative z-10 max-w-4xl mx-auto px-4">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4">
                Tazeliğe Açılan Kapı
            </h1>
            <p class="text-lg md:text-xl text-gray-300 mb-8">
                Üreticiden alıcıya, en taze çiçeklere en adil fiyatlarla ulaşın. Otantik mezat deneyimiyle tanışın.
            </p>
            <?php // Mezat Takvimi sayfasına yönlendiren ana eylem çağrısı (Call to Action) butonu ?>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'mezat-takvimi' ) ); ?>" class="bg-amber-400 hover:bg-amber-500 text-gray-900 font-bold py-3 px-8 rounded-full text-lg transition-transform transform hover:scale-105 inline-block">
                Mezatları Keşfet
            </a>
        </div>
    </section>

    <section class="py-16 md:py-24 bg-gray-900">
        <div class="container mx-auto px-6 space-y-16">
            
            <div class="info-card grid grid-cols-1 md:grid-cols-2 gap-12 items-center bg-gray-800 p-8 rounded-xl border border-gray-700">
                <div>
                    <h3 class="text-3xl font-bold text-amber-400 mb-4">Mezat Sistemimizin Amacı</h3>
                    <div class="prose prose-invert max-w-none text-gray-300 space-y-4">
                        <p>Amacımız, çiçekçilik sektöründe geleneksel mezat heyecanını modern teknolojiyle birleştirerek şeffaf, adil ve dinamik bir pazar yeri oluşturmaktır.</p>
                        <p>Gerçek zamanlı fiyat değişimleri ve anlık alım fırsatları ile hem satıcılar hem de alıcılar için kazançlı bir ekosistem sunuyoruz.</p>
                    </div>
                </div>
                <div>
                    <img src="<?php echo CICEKMEZAT_THEME_URI; ?>/assets/images/info-image.jpg" alt="Şeffaf Pazar Yeri" class="rounded-lg shadow-lg">
                </div>
            </div>

            <div class="info-card bg-gray-800 p-8 rounded-xl border border-gray-700">
                <h3 class="text-3xl font-bold text-amber-400 mb-12 text-center">Mezat Nasıl Çalışır?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="step-icon bg-gray-700 text-amber-400 w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold mb-4">1</div>
                        <h4 class="text-xl font-semibold mb-2 text-white">Takvimi İncele</h4>
                        <p class="text-gray-400">Yaklaşan mezatları ve satışa çıkacak çiçekleri mezat takviminden takip edin.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="step-icon bg-gray-700 text-amber-400 w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold mb-4">2</div>
                        <h4 class="text-xl font-semibold mb-2 text-white">Fiyatı İzle</h4>
                        <p class="text-gray-400">Canlı mezat sayfasında anlık değişen fiyatları izleyin ve doğru anı bekleyin.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="step-icon bg-gray-700 text-amber-400 w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold mb-4">3</div>
                        <h4 class="text-xl font-semibold mb-2 text-white">Satın Al</h4>
                        <p class="text-gray-400">Beğendiğiniz fiyattan istediğiniz miktarda çiçeği "Hemen Satın Al" butonuyla anında alın.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
// Sitenin footer.php dosyasını dahil et.
get_footer();
// --- Bitiş: front-page.php ---
