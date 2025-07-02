<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: odeme-yap.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Müşterinin "Hesabım > Ödeme Yap" sekmesinin içeriğini oluşturur.
 * Bu şablon, `inc/woocommerce-hooks.php` dosyasında tanımlanan endpoint sayesinde
 * "Hesabım" sayfası içinde doğru sekmede görüntülenir.
 *
 * Bu sayfa, özellikle kredisi yetersizken alım yapmasına izin verilen (yönetici ayarına bağlı)
 * ve bu nedenle borcu oluşan müşterilerin, eksik ödemelerini listeler.
 * NOT: Bu özellik, ciddi finansal riskler taşıdığı için canlı bir sistemde
 * kullanılması tavsiye edilmez. Bunun yerine "Ön Provizyon" mekanizması önerilir.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb; // WordPress'in veritabanı işlemlerini yöneten global nesnesi.
$user_id = get_current_user_id(); // Mevcut giriş yapmış kullanıcının ID'sini al.

// --- VERİTABANI SORGUSU ---
// `wp_cm_alimlar` tablosundan mevcut kullanıcının ödenmemiş borçlarını çek.
// Bu sorgunun çalışması için `wp_cm_alimlar` tablosuna `odeme_durumu` (ENUM 'odenmis', 'odenmedi')
// ve `odenen_tutar` (DECIMAL) adında iki yeni sütun eklenmesi gerekir.
$borclar = $wpdb->get_results($wpdb->prepare("
    SELECT 
        a.alim_id,
        a.toplam_tutar,
        a.odenen_tutar,
        a.alim_zamani,
        m.post_id as mezat_post_id, 
        c.cicek_adi, 
        (a.toplam_tutar - IFNULL(a.odenen_tutar, 0)) as kalan_borc
    FROM {$wpdb->prefix}cm_alimlar a
    JOIN {$wpdb->prefix}cm_mezatlar m ON a.mezat_id = m.mezat_id
    JOIN {$wpdb->prefix}cm_cicekler c ON m.cicek_id = c.cicek_id
    WHERE a.user_id = %d AND a.odeme_durumu = 'odenmedi' AND (a.toplam_tutar - IFNULL(a.odenen_tutar, 0)) > 0
    ORDER BY a.alim_zamani ASC
", $user_id));

?>

<?php // Genel sayfa kapsayıcısı ve başlığı ?>
<div class="content-card bg-[#1F2937] p-6 md:p-8 rounded-lg border border-gray-700">
    <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-600">Eksik Ödemelerim</h2>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3">Mezat ID</th>
                    <th scope="col" class="px-6 py-3">Çiçek Adı</th>
                    <th scope="col" class="px-6 py-3">Alım Tarihi</th>
                    <th scope="col" class="px-6 py-3 text-right">Toplam Tutar</th>
                    <th scope="col" class="px-6 py-3 text-right">Ödenen</th>
                    <th scope="col" class="px-6 py-3 text-right">Ödenmesi Gereken</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksiyon</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($borclar)) : ?>
                    <?php 
                    // Veritabanından gelen her bir borç için bir tablo satırı oluştur.
                    foreach($borclar as $borc) : 
                    ?>
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-medium text-amber-400">#<?php echo esc_html($borc->mezat_post_id); ?></td>
                            <td class="px-6 py-4 text-white"><?php echo esc_html($borc->cicek_adi); ?></td>
                            <td class="px-6 py-4"><?php echo date_i18n('d F Y, H:i', strtotime($borc->alim_zamani)); ?></td>
                            <td class="px-6 py-4 text-right"><?php echo wc_price($borc->toplam_tutar); ?></td>
                            <td class="px-6 py-4 text-right"><?php echo wc_price($borc->odenen_tutar); ?></td>
                            <td class="px-6 py-4 text-right font-semibold text-red-400"><?php echo wc_price($borc->kalan_borc); ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                /**
                                 * Ödeme sayfasına yönlendiren bir link oluşturulur.
                                 * Bu link, WooCommerce sepetine borç tutarı kadar bir "Ödeme" ürünü ekleyip
                                 * direkt ödeme sayfasına yönlendirir. Bu, WooCommerce'in güvenli ödeme
                                 * altyapısını kullanmanın en standart yoludur.
                                 * * ÖNEMLİ: Bu özelliğin çalışması için WooCommerce > Ürünler bölümünde
                                 * fiyatı 1 TL olan, "Sanal" ve "Ayrı ayrı satılır" olarak işaretlenmiş,
                                 * "Borç Ödeme" adında bir ürün oluşturup ID'sini aşağıdaki değişkene atamanız gerekir.
                                 */
                                $borc_odeme_urunu_id = 101; // Kendi "Borç Ödeme" ürününüzün ID'sini buraya girin.

                                // Sepeti temizleyip sadece borç ödeme ürününü ekleyen özel bir URL oluşturuyoruz.
                                $odeme_url = add_query_arg([
                                    'add-to-cart' => $borc_odeme_urunu_id,
                                    'quantity'    => $borc->kalan_borc, // Miktar, borç tutarına eşit olacak
                                    'alim_id'     => $borc->alim_id, // Hangi alımın ödendiğini takip etmek için
                                ], wc_get_checkout_url());
                                ?>
                                <a href="<?php echo esc_url($odeme_url); ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">
                                    Şimdi Öde
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php // Eğer hiç borç yoksa, bilgilendirme mesajı göster. ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Ödenmemiş bir borcunuz bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php // --- Bitiş: woocommerce/myaccount/odeme-yap.php --- ?>
