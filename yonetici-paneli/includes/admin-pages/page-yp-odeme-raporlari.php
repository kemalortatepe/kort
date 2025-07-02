<?php
/**
 * includes/admin-pages/page-yp-odeme-raporlari.php
 *
 * Yönetici Paneli'ndeki "Ödeme Raporları" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-odeme-raporlari' olduğunda yüklenir.
 * Müşterilerin tüm alımlarını, ödeme durumlarını listeler ve yöneticinin
 * ödemeler üzerinde işlem yapmasını sağlar.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb; // WordPress'in veritabanı işlemlerini yöneten global nesnesi.

// --- Sayfa İlk Yüklendiğinde Varsayılan Verileri Çekme ---
// Bu sorgu, tüm alım kayıtlarını, ilgili müşteri ve çiçek bilgileriyle birleştirerek çeker.
// AJAX filtrelemesi için bu sorgunun bir kopyası `class-yp-ajax-handlers.php` içinde de kullanılacaktır.
$alimlar = $wpdb->get_results("
    SELECT 
        a.alim_id, 
        a.user_id,
        a.toplam_tutar, 
        a.odenen_tutar,
        a.odeme_durumu,
        a.alim_zamani,
        m.post_id as mezat_post_id, 
        c.cicek_adi, 
        u.display_name, 
        u.user_login
    FROM {$wpdb->prefix}cm_alimlar a
    JOIN {$wpdb->prefix}cm_mezatlar m ON a.mezat_id = m.mezat_id
    JOIN {$wpdb->prefix}cm_cicekler c ON m.cicek_id = c.cicek_id
    JOIN {$wpdb->prefix}users u ON a.user_id = u.ID
    ORDER BY a.alim_zamani DESC
");

?>

<div class="wrap">
    <div class="flex justify-between items-center mb-6">
        <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Ödeme Raporları</h1>
        <div>
            <?php // no-print sınıfı, yazdırma sırasında bu butonların gizlenmesini sağlar. ?>
            <button id="print-report-btn" class="button button-secondary no-print">Yazdır</button>
        </div>
    </div>

    <form id="odeme-rapor-filtre-formu" class="my-4 p-4 bg-gray-100 rounded-lg flex flex-wrap items-center gap-4 no-print">
        <?php // Güvenlik için Nonce Alanı. AJAX isteğinde bu değer doğrulanır. ?>
        <?php wp_nonce_field('yp_odeme_raporu_nonce', 'nonce'); ?>
        
        <div>
            <label for="tarih_araligi" class="mr-2">Tarih Aralığı:</label>
            <select name="tarih_araligi" id="tarih_araligi">
                <option value="all">Tüm Zamanlar</option>
                <option value="today">Bugün</option>
                <option value="this_week">Bu Hafta</option>
                <option value="this_month">Bu Ay</option>
                <option value="custom">Özel Aralık</option>
            </select>
        </div>
        <div id="ozel-aralik-alanlari" class="hidden flex items-center gap-2">
            <input type="date" name="baslangic_tarihi" id="baslangic_tarihi">
            <span>-</span>
            <input type="date" name="bitis_tarihi" id="bitis_tarihi">
        </div>
        <div>
            <label for="odeme_durumu" class="mr-2">Ödeme Durumu:</label>
            <select name="odeme_durumu" id="odeme_durumu">
                <option value="all">Tümü</option>
                <option value="odenmis">Ödenmiş</option>
                <option value="odenmedi">Ödenmemiş</option>
            </select>
        </div>
        <button type="submit" class="button button-primary">Raporu Getir</button>
    </form>

    <div id="rapor-sonuc-alani" class="print-area">
        <table class="wp-list-table widefat fixed striped table-view-list posts mt-6">
            <thead>
                <tr>
                    <th scope="col">Müşteri</th>
                    <th scope="col">Mezat ID</th>
                    <th scope="col">Alım Zamanı</th>
                    <th scope="col" class="text-right">Toplam Tutar</th>
                    <th scope="col" class="text-right">Ödenen Tutar</th>
                    <th scope="col" class="text-right">Kalan Borç</th>
                    <th scope="col" class="text-center">Ödeme Durumu</th>
                    <th scope="col" class="text-center column-Aksiyon">Aksiyon</th>
                </tr>
            </thead>
            <tbody id="rapor-tablo-govdesi">
                <?php if ( ! empty($alimlar) ) :
                    foreach ( $alimlar as $alim ) : 
                        $kalan_borc = $alim->toplam_tutar - $alim->odenen_tutar;
                ?>
                        <tr>
                            <td><?php echo esc_html($alim->display_name . ' (' . $alim->user_login . ')'); ?></td>
                            <td><strong>#<?php echo esc_html($alim->mezat_post_id); ?></strong></td>
                            <td><?php echo date_i18n('d F Y, H:i', strtotime($alim->alim_zamani)); ?></td>
                            <td class="text-right"><?php echo wc_price($alim->toplam_tutar); ?></td>
                            <td class="text-right"><?php echo wc_price($alim->odenen_tutar); ?></td>
                            <td class="text-right font-semibold <?php echo ($kalan_borc > 0) ? 'text-red-600' : 'text-gray-500'; ?>">
                                <?php echo wc_price($kalan_borc); ?>
                            </td>
                            <td class="text-center">
                                <?php if ($alim->odeme_durumu === 'odenmis'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-green-600">Ödeme Tamamlandı</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-yellow-500">Ödeme Bekleniyor</span>
                                <?php endif; ?>
                            </td>
                            <td class="Aksiyon text-center">
                                <?php if ($alim->odeme_durumu === 'odenmedi' && $kalan_borc > 0): ?>
                                    <div class="flex gap-2 justify-center">
                                        <button class="button button-secondary btn-odeme-aksiyon" data-aksiyon="kredi_kullan" data-alim-id="<?php echo esc_attr($alim->alim_id); ?>">Kredisini Kullan</button>
                                        <button class="button button-primary btn-odeme-aksiyon" data-aksiyon="odendi_isaretle" data-alim-id="<?php echo esc_attr($alim->alim_id); ?>">Ödendi İşaretle</button>
                                    </div>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="8" class="text-center py-6">Gösterilecek ödeme kaydı bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php // Sayfa içi JavaScript kodları (AJAX) ?>
<script>
jQuery(document).ready(function($) {
    'use strict';

    const filtreFormu = $('#odeme-rapor-filtre-formu');
    const ozelAralikAlanlari = $('#ozel-aralik-alanlari');
    const tarihAraligiSelect = $('#tarih_araligi');

    // "Özel Aralık" seçildiğinde tarih inputlarını göster/gizle
    tarihAraligiSelect.on('change', function() {
        if ($(this).val() === 'custom') {
            ozelAralikAlanlari.removeClass('hidden');
        } else {
            ozelAralikAlanlari.addClass('hidden');
        }
    });

    // Filtreleme formu gönderildiğinde AJAX isteği yap
    filtreFormu.on('submit', function(e) {
        e.preventDefault(); // Formun normal gönderimini engelle

        const form = $(this);
        const buton = form.find('button[type="submit"]');
        const eskiButonMetni = buton.text();

        $.ajax({
            url: yp_ajax_object.ajax_url, // WordPress AJAX işleyicisi
            type: 'POST',
            data: form.serialize() + '&action=yp_odeme_raporu_filtrele', // Gönderilecek veri ve AJAX aksiyonu
            dataType: 'json',
            beforeSend: function() {
                buton.text('Rapor Getiriliyor...').prop('disabled', true);
                $('#rapor-tablo-govdesi').css('opacity', 0.5);
            },
            success: function(response) {
                if (response.success) {
                    $('#rapor-tablo-govdesi').html(response.data.tablo_html);
                } else {
                    $('#rapor-tablo-govdesi').html('<tr><td colspan="8" class="text-center py-6 text-red-600">' + response.data.message + '</td></tr>');
                }
            },
            error: function() {
                alert('Sunucu hatası oluştu.');
            },
            complete: function() {
                buton.text(eskiButonMetni).prop('disabled', false);
                $('#rapor-tablo-govdesi').css('opacity', 1);
            }
        });
    });

    // Aksiyon butonlarına tıklandığında AJAX isteği yap
    $(document).on('click', '.btn-odeme-aksiyon', function() {
        const buton = $(this);
        const aksiyon = buton.data('aksiyon');
        const alimId = buton.data('alim-id');

        if (!confirm('Bu işlemi yapmak istediğinizden emin misiniz?')) {
            return;
        }

        $.ajax({
            url: yp_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'yp_odeme_aksiyonu',
                nonce: yp_ajax_object.nonce,
                alim_id: alimId,
                odeme_aksiyonu: aksiyon
            },
            dataType: 'json',
            beforeSend: function() {
                buton.closest('div').html('<i>İşleniyor...</i>');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload(); // En güncel listeyi görmek için sayfayı yenile
                } else {
                    alert('Hata: ' + response.data.message);
                    location.reload();
                }
            }
        });
    });

     // Yazdır butonu
    $('#print-report-btn').on('click', function() {
        window.print();
    });
});
</script>

<?php // --- Bitiş: includes/admin-pages/page-yp-odeme-raporlari.php --- ?>
