<?php
/**
 * includes/admin-pages/page-yp-stok-raporlari.php
 *
 * Yönetici Paneli'ndeki "Stok Raporları" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-stok-raporlari' olduğunda yüklenir.
 * Tüm çiçeklerin başlangıç stoklarını, satılan miktarları ve kalan stokları
 * listeler ve filtrelenmesini sağlar.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb; // WordPress'in veritabanı işlemlerini yöneten global nesnesi.

// --- Sayfa İlk Yüklendiğinde Varsayılan Verileri Çekme ---
// Bu sorgu, her bir çiçeği alır ve o çiçeğe ait tüm alımları (satışları)
// `wp_cm_alimlar` tablosundan toplayarak (SUM) satılan toplam miktarı hesaplar.
$stok_raporu = $wpdb->get_results("
    SELECT 
        c.cicek_id,
        c.cicek_adi,
        c.toplam_stok_tane AS baslangic_stogu,
        IFNULL(SUM(a.alim_miktari), 0) AS toplam_satilan,
        (c.toplam_stok_tane - IFNULL(SUM(a.alim_miktari), 0)) AS kalan_stok
    FROM 
        {$wpdb->prefix}cm_cicekler c
    LEFT JOIN 
        {$wpdb->prefix}cm_alimlar a ON c.cicek_id = (SELECT cicek_id FROM {$wpdb->prefix}cm_mezatlar WHERE mezat_id = a.mezat_id)
    GROUP BY 
        c.cicek_id
    ORDER BY 
        c.cicek_adi ASC
");

?>

<div class="wrap">
    <div class="flex justify-between items-center mb-6">
        <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Stok Raporları</h1>
        <div>
            <?php // no-print sınıfı, yazdırma sırasında bu butonların gizlenmesini sağlar. ?>
            <button id="print-report-btn" class="button button-secondary no-print">Yazdır</button>
            <button id="pdf-report-btn" class="button button-secondary no-print">PDF Olarak Kaydet</button>
        </div>
    </div>

    <form id="stok-rapor-filtre-formu" class="my-4 p-4 bg-gray-100 rounded-lg flex flex-wrap items-center gap-4 no-print">
        <p class="text-sm text-gray-600">Gelişmiş filtreleme seçenekleri yakında eklenecektir.</p>
    </form>

    <div id="rapor-sonuc-alani" class="print-area">
        <table class="wp-list-table widefat fixed striped table-view-list posts mt-6">
            <thead>
                <tr>
                    <th scope="col">Çiçek ID</th>
                    <th scope="col">Çiçek Adı</th>
                    <th scope="col" class="text-right">Başlangıç Stoğu (Tane)</th>
                    <th scope="col" class="text-right">Toplam Satılan (Tane)</th>
                    <th scope="col" class="text-right">Kalan Stok (Tane)</th>
                    <th scope="col" class="text-center">Stok Durumu</th>
                </tr>
            </thead>
            <tbody id="rapor-tablo-govdesi">
                <?php if ( ! empty($stok_raporu) ) : ?>
                    <?php foreach ( $stok_raporu as $rapor_satiri ) : ?>
                        <tr>
                            <td><strong>#<?php echo esc_html($rapor_satiri->cicek_id); ?></strong></td>
                            <td><?php echo esc_html($rapor_satiri->cicek_adi); ?></td>
                            <td class="text-right"><?php echo number_format(esc_html($rapor_satiri->baslangic_stogu), 0, ',', '.'); ?></td>
                            <td class="text-right"><?php echo number_format(esc_html($rapor_satiri->toplam_satilan), 0, ',', '.'); ?></td>
                            <td class="text-right font-semibold"><?php echo number_format(esc_html($rapor_satiri->kalan_stok), 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <?php
                                // Kalan stok yüzdesine göre durum etiketi göster.
                                $stok_yuzdesi = ($rapor_satiri->baslangic_stogu > 0) ? ($rapor_satiri->kalan_stok / $rapor_satiri->baslangic_stogu) * 100 : 0;
                                if ($stok_yuzdesi <= 0) {
                                    echo '<span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-red-600">Tükendi</span>';
                                } elseif ($stok_yuzdesi <= 20) {
                                    echo '<span class="px-2 py-1 text-xs font-semibold rounded-full text-black bg-yellow-400">Kritik Seviye</span>';
                                } else {
                                    echo '<span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-green-600">Yeterli</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="6" class="text-center py-6">Gösterilecek stok kaydı bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php // Sayfa içi JavaScript kodları ?>
<script>
jQuery(document).ready(function($) {
    'use strict';

    // Bu sayfanın AJAX işlevselliği, gelecekte eklenecek olan
    // tarihsel stok filtreleri için bir altyapı olarak tasarlanmıştır.
    // Mevcut yapıda, sayfa yüklendiğinde tüm veriler PHP ile çekildiği için
    // anlık bir AJAX işlemi gerekmemektedir.

    // Yazdır ve PDF butonları için olay dinleyicileri
    $('#print-report-btn, #pdf-report-btn').on('click', function() {
        // Basit bir yazdırma işlemi. Tarayıcının yazdırma diyaloğu açılır.
        // Kullanıcı buradan "PDF olarak kaydet" seçeneğini de seçebilir.
        // Daha gelişmiş PDF'ler için jsPDF gibi bir kütüphane entegre edilebilir.
        
        // Sadece rapor tablosunu yazdırmak için geçici stiller ekle
        $('body').addClass('print-view');
        window.print();
        $('body').removeClass('print-view');
    });

    // Yazdırma için özel stiller
    const printStyles = `
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    `;
    $('head').append('<style>' + printStyles + '</style>');
});
</script>

<?php // --- Bitiş: includes/admin-pages/page-yp-stok-raporlari.php --- ?>
