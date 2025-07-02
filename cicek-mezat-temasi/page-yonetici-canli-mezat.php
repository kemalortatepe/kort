<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: page-yonetici-canli-mezat.php (Yeni Mimariye Göre Güncellendi)
 * AÇIKLAMA: Bu, WordPress panelinden oluşturulacak bir sayfaya atanacak özel şablondur.
 * Yönetici, "Mezat Listesi"nden bir mezatın "Canlı Git" linkine tıkladığında
 * bu sayfaya yönlendirilir (örn: site.com/yonetici-canli-mezat/?post_id=123).
 * Sayfanın içeriği, URL'deki 'post_id' parametresine göre dinamik olarak değişir.
 * ==========================================================================
 *
 * Template Name: Yönetici Canlı Mezat Paneli
 *
 * @package CicekMezat
 */

// --- GÜVENLİK VE YETKİ KONTROLÜ ---
// Kullanıcı giriş yapmamışsa, özel yönetici giriş sayfasına yönlendir.
if ( ! is_user_logged_in() ) {
    wp_redirect( get_permalink( get_page_by_path( 'yonetim-girisi' ) ) );
    exit;
}

// Kullanıcının rolü 'administrator' veya 'yonetici_yardimcisi' değilse, ana sayfaya yönlendir.
$user = wp_get_current_user();
if ( ! in_array( 'administrator', (array) $user->roles ) && ! in_array( 'yonetici_yardimcisi', (array) $user->roles ) ) {
    wp_redirect( home_url() );
    exit;
}

// --- VERİ ÇEKME İŞLEMLERİ ---
global $wpdb;
// URL'den kontrol edilecek mezatın post ID'sini al ve tamsayıya çevirerek temizle.
$mezat_post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;

// Mezat ve ilgili çiçek verilerini özel tablolardan çek.
$mezat = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cm_mezatlar WHERE post_id = %d", $mezat_post_id ) );
$cicek = null;
if ( $mezat ) {
    $cicek_post_id_from_acf = get_field('mezattaki_cicek', $mezat->post_id);
    $cicek = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cm_cicekler WHERE post_id = %d", $cicek_post_id_from_acf ) );
}

// Eğer mezat veya çiçek verisi bulunamazsa, hata göster ve işlemi sonlandır.
if ( ! $mezat || ! $cicek ) {
    get_header();
    echo '<div class="container mx-auto p-8 text-center text-red-500">Geçersiz veya bulunamayan mezat ID\'si. Lütfen mezat listesine geri dönün.</div>';
    get_footer();
    exit;
}

// Sitenin header.php dosyasını dahil et.
get_header();
?>

<?php // Bu ana kapsayıcı, JavaScript dosyamızın gerekli ID'leri almasını sağlar. ?>
<div class="wrap" id="yonetici-canli-mezat-paneli" 
     data-mezat-id="<?php echo esc_attr($mezat_post_id); ?>"
     data-admin-id="<?php echo esc_attr($user->ID); ?>"
     data-admin-name="<?php echo esc_attr($user->display_name); ?>">

    <main class="py-8 px-4">
        <div class="container mx-auto max-w-screen-2xl mb-4">
            <div class="flex flex-wrap justify-between items-center bg-gray-800 p-3 rounded-lg gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white">
                        <span class="text-gray-400">Canlı Kontrol Paneli:</span>
                        <span class="text-amber-400"><?php echo esc_html($cicek->cicek_adi); ?> (#<?php echo esc_html($mezat_post_id); ?>)</span>
                    </h1>
                </div>
                <div class="flex items-center gap-4">
                    <div id="mezat-durum-gostergesi" class="px-4 py-1 rounded-full font-semibold text-sm text-white bg-gray-600 transition-colors">
                        Bağlanılıyor...
                    </div>
                    <a href="<?php echo esc_url(get_admin_url(null, 'edit.php?post_type=mezat')); ?>" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">
                        Mezat Listesine Dön
                    </a>
                </div>
            </div>
        </div>

        <div class="container mx-auto max-w-screen-2xl grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-3 bg-[#1F2937] p-6 rounded-xl border border-gray-700 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-amber-400 mb-4">Mezat Kontrolleri</h2>
                    <div class="space-y-3">
                        <button id="btn-mezat-baslat" class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">MEZATI BAŞLAT</button>
                        <button id="btn-mezat-durdur" class="w-full py-3 px-4 bg-yellow-500 hover:bg-yellow-600 text-black font-bold rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">MEZATI DURAKLAT</button>
                        <button id="btn-mezat-bitir" class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">MEZATI SONLANDIR</button>
                    </div>
                </div>
                
                <div class="border-t border-gray-600 pt-6">
                    <h2 class="text-xl font-bold text-amber-400 mb-4">Canlı Ayarlar</h2>
                    <?php // Bu formdaki değişiklikler AJAX ile sunucuya gönderilir. ?>
                    <form id="mezat-ayarlari-formu" class="space-y-4 text-sm">
                        <?php wp_nonce_field('yp_canli_ayar_guncelle', 'yp_ayar_nonce'); ?>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($mezat_post_id); ?>">
                        <div>
                            <label for="setting-start-price" class="form-label">Başlangıç Fiyatı (₺)</label>
                            <input type="number" step="0.01" id="setting-start-price" name="baslangic_fiyati" value="<?php echo esc_attr($mezat->baslangic_fiyati); ?>" class="form-input">
                        </div>
                        <div>
                            <label for="setting-min-price" class="form-label">Minimum Fiyat (₺)</label>
                            <input type="number" step="0.01" id="setting-min-price" name="minimum_fiyat" value="<?php echo esc_attr($mezat->minimum_fiyat); ?>" class="form-input">
                        </div>
                        <div>
                            <label for="setting-price-interval" class="form-label">Fiyat Değişim Sıklığı (sn)</label>
                            <input type="number" id="setting-price-interval" name="fiyat_degisim_sikligi_sn" value="<?php echo esc_attr($mezat->fiyat_degisim_sikligi_sn); ?>" class="form-input">
                        </div>
                         <div>
                            <label for="setting-duration" class="form-label">Mezat Süresi (Dakika)</label>
                            <input type="number" id="setting-duration" name="mezat_suresi_dk" value="<?php echo esc_attr($mezat->mezat_suresi_dk); ?>" class="form-input">
                        </div>
                        <button type="submit" class="w-full mt-2 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md">Ayarları Canlı Güncelle</button>
                    </form>
                </div>
            </div>
            
            <div class="lg:col-span-5 bg-[#1F2937] p-6 rounded-xl border border-gray-700 flex flex-col items-center justify-center text-center space-y-6">
                <div class="w-full">
                    <h2 class="text-lg font-semibold text-gray-300">Kalan Süre</h2>
                    <div id="izleme-kalan-sure" class="text-5xl font-bold text-white my-2">--:--:--</div>
                </div>
                <div class="bg-white text-gray-900 p-6 rounded-lg shadow-inner w-full">
                    <div class="text-lg font-semibold">ANLIK BİRİM FİYAT</div>
                    <div id="izleme-anlik-fiyat" class="text-6xl font-extrabold text-blue-700">₺--.--</div>
                </div>
                <div class="w-full grid grid-cols-2 gap-4 text-white">
                    <div class="bg-gray-800/50 p-4 rounded-lg">
                        <div class="text-gray-400 text-sm">Katılımcı Sayısı</div>
                        <div id="izleme-katilimci-sayisi" class="text-2xl font-bold">0</div>
                    </div>
                    <div class="bg-gray-800/50 p-4 rounded-lg">
                        <div class="text-gray-400 text-sm">Kalan Miktar</div>
                        <div id="izleme-kalan-miktar" class="text-2xl font-bold">--</div>
                    </div>
                </div>
                 <div class="w-full border-t border-gray-600 pt-4">
                    <label for="setting-min-purchase" class="form-label text-sm">Minimum Alış Miktarını Güncelle</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="setting-min-purchase" name="minimum_alim_miktari" value="<?php echo esc_attr($mezat->minimum_alim_miktari); ?>" class="form-input">
                        <button id="btn-update-min-purchase" class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md">Güncelle</button>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-4 bg-[#1F2937] p-6 rounded-xl border border-gray-700 flex flex-col">
                <h2 class="text-xl font-bold text-amber-400 mb-4 border-b border-gray-600 pb-2">Canlı Satış Akışı</h2>
                <ul id="canli-satis-akisi" class="space-y-3 flex-grow h-96 overflow-y-auto pr-2">
                    <li class="text-gray-500 text-center py-4">Henüz satış yapılmadı...</li>
                </ul>
            </div>
        </div>
    </main>
</div>
<?php
// Sitenin footer.php dosyasını dahil et.
get_footer();
// --- Bitiş: page-yonetici-canli-mezat.php ---
