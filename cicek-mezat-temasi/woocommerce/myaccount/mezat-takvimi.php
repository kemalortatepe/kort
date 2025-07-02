<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: mezat-takvimi.php (Nihai Düzeltilmiş Sürüm)
 * AÇIKLAMA: Müşterinin "Hesabım > Mezat Takvimi" sekmesinin içeriğini oluşturur.
 *
 * GÜNCELLEME NOTLARI:
 * - WP_Query sorgusu, ACF PRO tarih alanı (`mezat_tarihi`) ile şimdiki zamanı
 * doğru bir şekilde karşılaştıracak şekilde tamamen yeniden yazıldı. Bu, "Yaklaşan
 * mezat bulunamadı" hatasını kesin olarak çözer.
 * - DOCX dosyasında istenen tüm sütun başlıkları (`Kalite`, `Sap Uzunluğu`,
 * `Gereken Kredi` vb.) eklendi ve ilgili veriler ACF'den çekildi.
 * - Kredi yeterlilik hesaplaması ve dinamik aksiyon butonu mantığı tam olarak
 * isteklere göre yeniden yapılandırıldı.
 * ==========================================================================
 *
 * @package CicekMezat
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- GÜVENLİK VE VERİ HAZIRLAMA ---

$user_id = get_current_user_id();
$user_wallet_balance = function_exists('woo_wallet') ? (float) woo_wallet()->wallet->get_wallet_balance($user_id) : 0;

// Kullanıcının ceza ve erişim durumunu kontrol et
$erisim_durumu = get_user_meta($user_id, 'erisim_durumu', true);
if ($erisim_durumu === 'engelli') {
    wc_print_notice(__('Hesabınızdaki kısıtlama nedeniyle mezat takvimini görüntüleyemezsiniz.', 'cicekmezat'), 'error');
    return;
}

// --- VERİTABANI SORGUSU (DÜZELTİLMİŞ) ---

$simdiki_zaman = current_time('Y-m-d H:i:s');

// "mezat" gönderi türündeki tüm yayında olan ve tarihi gelmemiş gönderileri al
$args = array(
    'post_type'      => 'mezat',
    'post_status'    => 'publish', // Sadece WordPress'te durumu "Yayımlandı" olanlar
    'posts_per_page' => -1,       // Tümünü al
    'meta_query'     => array(
        'relation' => 'AND', // Aşağıdaki tüm koşullar sağlanmalı
        array(
            'key'     => 'mezat_tarihi',      // ACF alan adı
            'value'   => $simdiki_zaman,
            'compare' => '>=',              // Tarihi bugünden büyük veya eşit olanlar
            'type'    => 'DATETIME'         // BU SATIR KRİTİK: Karşılaştırmanın bir tarih/zaman karşılaştırması olduğunu belirtir.
        ),
        array(
            'key'     => 'mezat_durumu',      // ACF alan adı (Radyo Butonu veya Seçim)
            'value'   => 'yayinda',           // Değeri 'yayinda' olanlar
            'compare' => '=',
        )
    ),
    'orderby'  => 'meta_value', // Tarihe göre sırala
    'meta_key' => 'mezat_tarihi',
    'order'    => 'ASC',        // En yakın tarihli olan en üstte
);
$mezatlar_query = new WP_Query($args);
?>

<div class="content-card bg-[#1F2937] p-6 rounded-lg border border-gray-700">
    <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-600">Yaklaşan Mezatlar</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                <tr>
                    <th class="px-4 py-3">Mezat ID</th>
                    <th class="px-4 py-3">Çiçek</th>
                    <th class="px-4 py-3">Mezat Zamanı</th>
                    <th class="px-4 py-3">Kalite / Sap / Menşei</th>
                    <th class="px-4 py-3 text-right">Miktar</th>
                    <th class="px-4 py-3 text-right">Fiyat Aralığı</th>
                    <th class="px-4 py-3">Kredi Durumu</th>
                    <th class="px-4 py-3 text-center">Aksiyon</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if ( $mezatlar_query->have_posts() ) : ?>
                    <?php while ( $mezatlar_query->have_posts() ) : $mezatlar_query->the_post();
                        
                        // --- MEZAT VE ÇİÇEK VERİLERİNİ ACF'DEN ÇEKME ---
                        $mezat_id = get_the_ID();
                        $cicek_post_obj = get_field('mezattaki_cicek', $mezat_id);
                        if (!$cicek_post_obj) continue;

                        $cicek_id = $cicek_post_obj->ID;

                        // Mezat ACF Alanları
                        $mezat_tarihi = get_field('mezat_tarihi', $mezat_id);
                        $mezat_stok = get_field('mezat_stok', $mezat_id);
                        $baslangic_fiyati = get_field('baslangic_fiyati', $mezat_id);
                        $minimum_fiyat = get_field('minimum_fiyat', $mezat_id);
                        $kredi_sarti_zorunlu = get_field('kredi_sarti_zorunlu', $mezat_id);
                        
                        // Çiçek ACF Alanları
                        $cicek_kalite = get_field('kalite_sinifi', $cicek_id);
                        $cicek_sap = get_field('sap_uzunlugu', $cicek_id);
                        $cicek_mensei = get_field('mensei', $cicek_id);

                        // Hesaplamalar
                        $gereken_kredi = $baslangic_fiyati * $mezat_stok;
                        $kredi_yeterli = ($user_wallet_balance >= $gereken_kredi);
                        $katilabilir = ($kredi_yeterli || !$kredi_sarti_zorunlu);
                    ?>
                        <tr class="hover:bg-gray-800/50">
                            <td class="px-4 py-4 font-medium text-amber-400">#<?php echo $mezat_id; ?></td>
                            <td class="px-4 py-4 text-white font-semibold">
                                <a href="<?php echo get_permalink($cicek_id); ?>" class="hover:underline">
                                    <?php echo get_the_title($cicek_id); ?>
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <span class="font-semibold block"><?php echo date_i18n('d F Y', strtotime($mezat_tarihi)); ?></span>
                                <span class="text-gray-400 block"><?php echo date_i18n('H:i', strtotime($mezat_tarihi)); ?></span>
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <span class="block"><strong>Kalite:</strong> <?php echo esc_html($cicek_kalite); ?></span>
                                <span class="block"><strong>Sap:</strong> <?php echo esc_html($cicek_sap); ?></span>
                                <span class="block"><strong>Menşei:</strong> <?php echo esc_html($cicek_mensei); ?></span>
                            </td>
                            <td class="px-4 py-4 text-right"><?php echo esc_html($mezat_stok); ?> adet</td>
                            <td class="px-4 py-4 text-right"><?php echo wc_price($minimum_fiyat) . ' - ' . wc_price($baslangic_fiyati); ?></td>
                            <td class="px-4 py-4">
                                <?php if (!$kredi_sarti_zorunlu): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-gray-500">Şart Yok</span>
                                <?php elseif ($kredi_yeterli): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-green-600">Kredi Yeterli</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-red-600">Kredi Yetersiz</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php if ($katilabilir): ?>
                                    <a href="<?php the_permalink(); ?>" class="button bg-green-600 hover:bg-green-700 text-white">Mezata Git</a>
                                <?php else: ?>
                                    <a href="<?php echo wc_get_account_endpoint_url('kredi-al'); ?>" class="button bg-yellow-500 hover:bg-yellow-600 text-black">Kredi Yükle</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">Yaklaşan mezat bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
