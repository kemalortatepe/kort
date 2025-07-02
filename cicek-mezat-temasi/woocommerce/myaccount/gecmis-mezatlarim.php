<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: gecmis-mezatlarim.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Müşterinin "Hesabım > Geçmiş Mezatlarım" sekmesinin içeriğini oluşturur.
 * Bu şablon, `inc/woocommerce-hooks.php` dosyasında tanımlanan endpoint sayesinde
 * "Hesabım" sayfası içinde doğru sekmede görüntülenir.
 *
 * Bu dosya, özel veritabanı tablomuz olan `wp_cm_alimlar`'dan veri çekerek,
 * müşterinin daha önce mezatlardan yaptığı tüm alımları listeler.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb; // WordPress'in veritabanı sınıfına erişim sağlar.
$user_id = get_current_user_id(); // Mevcut giriş yapmış kullanıcının ID'sini al.

// --- VERİTABANI SORGUSU ---
// Müşterinin alım yaptığı tüm mezatları, alım zamanına göre en yeniden en eskiye doğru sıralayarak çek.
// Bu sorgu, 3 özel tablomuzu birleştirir (JOIN):
// 1. `wp_cm_alimlar` (a): Alımın kendisi (miktar, fiyat).
// 2. `wp_cm_mezatlar` (m): Alımın yapıldığı mezatın bilgileri (post_id).
// 3. `wp_cm_cicekler` (c): Mezattaki çiçeğin adı.
$alimlar = $wpdb->get_results($wpdb->prepare("
    SELECT a.alim_id, a.alim_miktari, a.birim_fiyat, a.toplam_tutar, a.alim_zamani, 
           m.post_id as mezat_post_id, 
           c.cicek_adi
    FROM {$wpdb->prefix}cm_alimlar a
    JOIN {$wpdb->prefix}cm_mezatlar m ON a.mezat_id = m.mezat_id
    JOIN {$wpdb->prefix}cm_cicekler c ON m.cicek_id = c.cicek_id
    WHERE a.user_id = %d
    ORDER BY a.alim_zamani DESC
", $user_id));

$toplam_harcama = 0; // Tüm alımların toplam tutarını hesaplamak için bir değişken oluştur.
?>

<?php // Genel sayfa kapsayıcısı ve başlığı ?>
<div class="content-card bg-[#1F2937] p-6 md:p-8 rounded-lg border border-gray-700">
    <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-600">Geçmiş Mezat Alımlarım</h2>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3">Mezat ID</th>
                    <th scope="col" class="px-6 py-3">Çiçek Adı</th>
                    <th scope="col" class="px-6 py-3">Alım Tarihi</th>
                    <th scope="col" class="px-6 py-3 text-right">Alım Miktarı</th>
                    <th scope="col" class="px-6 py-3 text-right">Birim Fiyat</th>
                    <th scope="col" class="px-6 py-3 text-right">Toplam Ödeme</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($alimlar)) : ?>
                    <?php 
                    // Veritabanından gelen her bir alım için bir tablo satırı oluştur.
                    foreach($alimlar as $alim) : 
                        // Genel toplam harcamayı güncelle.
                        $toplam_harcama += $alim->toplam_tutar;
                    ?>
                        <tr class="border-b border-gray-700 hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-medium text-amber-400">#<?php echo esc_html($alim->mezat_post_id); ?></td>
                            <td class="px-6 py-4 text-white"><?php echo esc_html($alim->cicek_adi); ?></td>
                            <td class="px-6 py-4"><?php echo date_i18n('d F Y, H:i', strtotime($alim->alim_zamani)); ?></td>
                            <td class="px-6 py-4 text-right"><?php echo esc_html($alim->alim_miktari); ?> adet</td>
                            <td class="px-6 py-4 text-right"><?php echo wc_price($alim->birim_fiyat); ?></td>
                            <td class="px-6 py-4 text-right font-semibold text-green-400"><?php echo wc_price($alim->toplam_tutar); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php // Eğer hiç alım yapılmamışsa, bilgilendirme mesajı göster. ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Daha önce herhangi bir mezattan alım yapmadınız.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($alimlar)) : ?>
                <?php // Eğer en az bir alım varsa, tablonun en altına genel toplamı ekle. ?>
                <tfoot class="text-white font-bold">
                    <tr class="border-t-2 border-gray-600">
                        <td colspan="5" class="text-right px-6 py-4 text-lg">TÜM MEZATLARDAKİ TOPLAM HARCAMA:</td>
                        <td class="text-right px-6 py-4 text-lg text-amber-400"><?php echo wc_price($toplam_harcama); ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php // --- Bitiş: woocommerce/myaccount/gecmis-mezatlarim.php --- ?>
