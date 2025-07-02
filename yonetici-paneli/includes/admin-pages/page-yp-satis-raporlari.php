<?php
/**
 * includes/admin-pages/page-yp-satis-raporlari.php
 *
 * Yönetici Paneli'ndeki "Satış Raporları" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-satis-raporlari' olduğunda yüklenir.
 * Tamamlanmış mezatlardaki tüm satış işlemlerini listeler, filtrelenmesini sağlar
 * ve PDF/Yazdırma gibi dışa aktarma seçenekleri sunar.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb; // WordPress'in veritabanı işlemlerini yöneten global nesnesi.

// --- Sayfa İlk Yüklendiğinde Varsayılan Verileri Çekme ---
// Sayfa ilk açıldığında, herhangi bir filtre uygulanmamış halde tüm satışları listeleriz.
$alimlar = $wpdb->get_results("
    SELECT a.*, m.post_id as mezat_post_id, c.cicek_adi, u.display_name, u.user_login
    FROM {$wpdb->prefix}cm_alimlar a
    JOIN {$wpdb->prefix}cm_mezatlar m ON a.mezat_id = m.mezat_id
    JOIN {$wpdb->prefix}cm_cicekler c ON m.cicek_id = c.cicek_id
    JOIN {$wpdb->prefix}users u ON a.user_id = u.ID
    ORDER BY a.alim_zamani DESC
");

?>

<div class="wrap">
    <div class="flex justify-between items-center mb-6">
        <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Satış Raporları</h1>
        <div>
            <?php // no-print sınıfı, yazdırma sırasında bu butonların gizlenmesini sağlar. ?>
            <button id="print-report-btn" class="button button-secondary no-print">Yazdır</button>
            <button id="pdf-report-btn" class="button button-secondary no-print">PDF Olarak Kaydet</button>
        </div>
    </div>

    <form id="satis-rapor-filtre-formu" class="my-4 p-4 bg-gray-100 rounded-lg flex flex-wrap items-center gap-4 no-print">
        <?php // Güvenlik için Nonce Alanı. AJAX isteğinde bu değer doğrulanır. ?>
        <?php wp_nonce_field('yp_satis_raporu_nonce', 'nonce'); ?>
        
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
        <button type="submit" class="button button-primary">Raporu Getir</button>
    </form>

    <div id="rapor-sonuc-alani" class="print-area">
        <table class="wp-list-table widefat fixed striped table-view-list posts mt-6">
            <thead>
                <tr>
                    <th scope="col">Mezat ID</th>
                    <th scope="col">Çiçek Adı</th>
                    <th scope="col">Müşteri</th>
                    <th scope="col">Alım Zamanı</th>
                    <th scope="col" class="text-right">Miktar</th>
                    <th scope="col" class="text-right">Birim Fiyat</th>
                    <th scope="col" class="text-right">Toplam Tutar</th>
                </tr>
            </thead>
            <tbody id="rapor-tablo-govdesi">
                <?php if ( ! empty($alimlar) ) :
                    $toplam_kazanc = 0;
                    foreach ( $alimlar as $alim ) : 
                        $toplam_kazanc += $alim->toplam_tutar;
                ?>
                        <tr>
                            <td><strong>#<?php echo esc_html($alim->mezat_post_id); ?></strong></td>
                            <td><?php echo esc_html($alim->cicek_adi); ?></td>
                            <td><?php echo esc_html($alim->display_name . ' (' . $alim->user_login . ')'); ?></td>
                            <td><?php echo date_i18n('d F Y, H:i', strtotime($alim->alim_zamani)); ?></td>
                            <td class="text-right"><?php echo esc_html($alim->alim_miktari); ?> adet</td>
                            <td class="text-right"><?php echo wc_price($alim->birim_fiyat); ?></td>
                            <td class="text-right font-semibold text-green-700"><?php echo wc_price($alim->toplam_tutar); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7" class="text-center py-6">Gösterilecek satış kaydı bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if ( ! empty($alimlar) ) : ?>
                <tfoot class="bg-gray-200 font-bold">
                    <tr>
                        <td colspan="6" class="text-right px-6 py-3 text-lg">TOPLAM KAZANÇ:</td>
                        <td id="toplam-kazanc-gostergesi" class="text-right px-6 py-3 text-lg text-green-800"><?php echo wc_price($toplam_kazanc); ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php // Sayfa içi JavaScript kodları (AJAX) ?>
<script>
jQuery(document).ready(function($) {
    'use strict';

    const filtreFormu = $('#satis-rapor-filtre-formu');
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
        // Formun normal şekilde (sayfa yenilenerek) gönderilmesini engelle.
        e.preventDefault();

        const form = $(this);
        const buton = form.find('button[type="submit"]');
        const eskiButonMetni = buton.text();

        $.ajax({
            url: yp_ajax_object.ajax_url, // WordPress AJAX işleyicisi
            type: 'POST',
            data: form.serialize() + '&action=yp_satis_raporu_filtrele', // Gönderilecek veri ve AJAX aksiyonu
            dataType: 'json', // Sunucudan JSON formatında yanıt bekle
            beforeSend: function() {
                // İstek gönderilmeden önce butonu pasif hale getir ve metnini değiştir.
                buton.text('Rapor Getiriliyor...').prop('disabled', true);
                $('#rapor-tablo-govdesi').css('opacity', 0.5); // Tabloyu soluklaştır
            },
            success: function(response) {
                // İstek başarılı olduğunda çalışır.
                if (response.success) {
                    // Sunucudan gelen HTML ve toplam kazanç ile tabloyu güncelle.
                    $('#rapor-tablo-govdesi').html(response.data.tablo_html);
                    $('#toplam-kazanc-gostergesi').html(response.data.toplam_kazanc_html);
                } else {
                    // Sunucudan hata mesajı geldiyse, tabloyu temizle ve hata mesajını göster.
                    $('#rapor-tablo-govdesi').html('<tr><td colspan="7" class="text-center py-6 text-red-600">' + response.data.message + '</td></tr>');
                    $('#toplam-kazanc-gostergesi').html('₺0,00');
                }
            },
            error: function() {
                // Sunucu hatası veya ağ sorunu gibi durumlarda çalışır.
                alert('Sunucu hatası oluştu. Lütfen tekrar deneyin.');
            },
            complete: function() {
                // İstek başarılı veya başarısız olsun, her durumda çalışır.
                buton.text(eskiButonMetni).prop('disabled', false);
                $('#rapor-tablo-govdesi').css('opacity', 1);
            }
        });
    });

    // Yazdır ve PDF butonları
    $('#print-report-btn, #pdf-report-btn').on('click', function() {
        // Basit bir yazdırma işlemi. Tarayıcının yazdırma diyaloğu açılır.
        // Kullanıcı buradan "PDF olarak kaydet" seçeneğini de seçebilir.
        window.print();
    });
});
</script>

<?php // --- Bitiş: includes/admin-pages/page-yp-satis-raporlari.php --- ?>
