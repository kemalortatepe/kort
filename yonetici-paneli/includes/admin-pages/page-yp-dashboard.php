<?php
/**
 * ==========================================================================
 * KLASÖR: yonetici-paneli/includes/admin-pages/
 * DOSYA: page-yp-dashboard.php (WordPress Arayüzüne Uyumlu)
 * ==========================================================================
 *
 * Yönetici Paneli'nin ana karşılama sayfası.
 * Madde 20'deki isteğiniz üzerine, sayfa artık WordPress'in standart arayüzünü
 * temel alır. Madde 21'deki isteğiniz doğrultusunda, özet bilgi kartları
 * HTML örneklerindeki gibi büyük ve renkli sayılarla stilize edilmiştir.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb; // WordPress'in veritabanı işlemlerini yöneten global nesnesi.

// --- VERİ ÇEKME İŞLEMLERİ ---
// 1. Aktif Mezat Sayısı
$aktif_mezat_sayisi = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cm_mezatlar WHERE durum = 'canli'");
// 2. Toplam Müşteri Sayısı
$musteri_rolleri = ['customer'];
$toplam_musteri_sayisi = count_users(['role__in' => $musteri_rolleri])['total_users'];
// 3. Bugünkü Kazanç
$bugun = current_time('Y-m-d');
$gunluk_kazanc = $wpdb->get_var($wpdb->prepare("SELECT SUM(toplam_tutar) FROM {$wpdb->prefix}cm_alimlar WHERE DATE(alim_zamani) = %s", $bugun));
$gunluk_kazanc = $gunluk_kazanc ?? 0;
// 4. Cezalı Müşteri Sayısı
$cezali_kullanici_sorgusu = new WP_User_Query(['meta_key' => 'ceza_durumu', 'meta_value' => 'cezali']);
$cezali_musteri_sayisi = $cezali_kullanici_sorgusu->get_total();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Ana Panel</h1>
    <p class="text-gray-600">Sitenizin genel durumuna hızlı bir bakış.</p>
    <hr class="wp-header-end">

    <?php // Özet bilgi kartlarını içeren grid yapısı ?>
    <div id="dashboard-widgets-wrap" class="mt-6">
        <div id="dashboard-widgets" class="metabox-holder">
            <?php // WordPress'in kendi grid yapısını kullanıyoruz, böylece mobil uyumlu olur. ?>
            <div class="postbox-container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <div class="yp-dashboard-card">
                        <h3 class="card-title">Aktif Mezat Sayısı</h3>
                        <p class="card-number text-blue-600">
                            <?php echo esc_html($aktif_mezat_sayisi); ?>
                        </p>
                    </div>
                    
                    <div class="yp-dashboard-card">
                        <h3 class="card-title">Toplam Müşteri Sayısı</h3>
                        <p class="card-number text-gray-700">
                            <?php echo esc_html($toplam_musteri_sayisi); ?>
                        </p>
                    </div>
                    
                    <div class="yp-dashboard-card">
                        <h3 class="card-title">Bugünkü Kazanç</h3>
                        <p class="card-number text-green-600">
                            <?php echo function_exists('wc_price') ? wc_price($gunluk_kazanc) : esc_html($gunluk_kazanc) . ' TL'; ?>
                        </p>
                    </div>
                    
                    <div class="yp-dashboard-card">
                        <h3 class="card-title">Cezalı Müşteri Sayısı</h3>
                        <p class="card-number text-red-600">
                            <?php echo esc_html($cezali_musteri_sayisi); ?>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
