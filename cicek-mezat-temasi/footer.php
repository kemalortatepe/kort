<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: footer.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Sitenizin tüm sayfalarının alt kısmını (footer) oluşturan şablondur.
 * Bu dosya, header.php'de açılan ana HTML kapsayıcılarını kapatır ve sitenin
 * alt bilgi bölümünü oluşturur.
 * ==========================================================================
 *
 * @package CicekMezat
 */

?>

    </div><footer id="colophon" class="site-footer bg-[#111827] border-t border-gray-700 mt-16">
        <div class="container mx-auto text-center py-8 px-4 text-gray-400">

            <?php // Footer Menüsü: Görünüm > Menüler'den 'Alt Bilgi Menüsü' olarak atanmış menüyü gösterir. ?>
            <nav class="footer-navigation mb-4">
                <?php
                if ( has_nav_menu( 'footer' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'footer',
                        'menu_class'     => 'flex justify-center gap-x-6 gap-y-2 flex-wrap text-sm',
                        'container'      => false,
                        'depth'          => 1, // Sadece ana menü öğelerini göster, alt menüleri gösterme.
                    ) );
                }
                ?>
            </nav>

            <?php // Şirket Adı ve İletişim Bilgileri ?>
            <div class="mb-4">
                 <h3 class="text-lg font-bold text-white mb-2">Çiçek Mezat Dünyası</h3>
                 <p class="text-sm">İletişim: info@cicekmezat.com | Tel: 0212 123 45 67</p>
            </div>

            <?php // Telif Hakkı Uyarısı ?>
            <div class="site-info text-xs text-gray-500">
                <?php 
                // Yılı ve site adını dinamik olarak alarak telif hakkı metnini oluşturur.
                printf( 
                    esc_html__( '© %1$s %2$s. Tüm Hakları Saklıdır.', 'cicekmezat' ), 
                    date_i18n( 'Y' ), // WordPress saat ayarına göre mevcut yılı alır.
                    get_bloginfo( 'name' ) // Sitenin Ayarlar > Genel'deki adını alır.
                ); 
                ?>
            </div>

        </div></footer></div><?php 
/**
 * wp_footer()
 * WordPress'in en önemli kancalarından biridir ve </body> etiketinden hemen önce
 * yer almalıdır. Eklentilerin ve temanın JavaScript dosyalarını, analiz kodlarını
 * ve diğer alt bilgi scriptlerini sayfaya eklemesini sağlar.
 * AJAX KODLARININ AKTİF OLMASI, bu fonksiyonun çağrılmasına bağlıdır, çünkü
 * `main.js` gibi script dosyalarımız bu kanca aracılığıyla yüklenir.
 */
wp_footer(); 
?>

</body>
</html>
<?php // --- Bitiş: footer.php --- ?>
