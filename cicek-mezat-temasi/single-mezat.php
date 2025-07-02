<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi
 * DOSYA: single-mezat.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: "Mezat" özel gönderi türüne ait tekil sayfaları görüntülemek için
 * kullanılan şablondur. Bir müşteri, Mezat Takvimi'nden bir mezatın linkine
 * tıkladığında WordPress otomatik olarak bu şablonu kullanır.
 *
 * Bu dosya, sayfanın statik iskeletini oluşturur ve veritabanından gerekli
 * başlangıç bilgilerini çeker. Sayfanın tüm dinamik işlevselliği
 * (anlık fiyat, zaman sayacı, alım yapma) WebSocket bağlantısı kuran
 * `assets/js/live-auction-customer.js` dosyası tarafından yönetilir.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// Sitenin header.php dosyasını dahil et.
get_header();

// --- GÜVENLİK VE VERİ KONTROLÜ ---

// Sadece giriş yapmış kullanıcılar bu sayfayı görebilir.
if ( ! is_user_logged_in() ) {
    // Giriş yapmamışsa, ana sayfaya yönlendir ve işlemi sonlandır.
    wp_redirect( home_url() );
    exit;
}

global $wpdb; // WordPress veritabanı sınıfına erişim sağlar.
$post_id = get_the_ID(); // Görüntülenen mevcut mezatın WordPress post ID'sini al.
$current_user = wp_get_current_user(); // Mevcut giriş yapmış kullanıcıyı al.

// Kullanıcının yönetici tarafından engellenip engellenmediğini kontrol et.
$erisim_durumu = get_user_meta($current_user->ID, 'erisim_durumu', true);
if ($erisim_durumu === 'engelli') {
    echo '<div class="container mx-auto p-8 text-center text-red-500">Hesabınız kısıtlandığı için mezatlara katılamazsınız.</div>';
    get_footer();
    exit;
}

// Özel tablomuz olan `wp_cm_mezatlar`'dan, mevcut post ID'ye ait mezat verilerini çek.
$mezat = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cm_mezatlar WHERE post_id = %d", $post_id ) );

// Eğer mezat verisi bulunamazsa, kullanıcıya bir hata göster ve işlemi sonlandır.
if ( ! $mezat ) {
    echo '<div class="container mx-auto p-8 text-center text-red-500">Mezat bilgileri bulunamadı.</div>';
    get_footer();
    return;
}

// Mezattaki çiçeğin verilerini `wp_cm_cicekler` tablosundan çek.
$cicek = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cm_cicekler WHERE cicek_id = %d", $mezat->cicek_id ) );
// Çiçeğin WordPress gönderi bilgilerini (açıklama ve öne çıkan görsel için) çek.
$cicek_post = get_post($cicek->post_id);

// Eğer çiçek bilgileri de bulunamazsa, hata göster.
if ( ! $cicek || ! $cicek_post ) {
    echo '<div class="container mx-auto p-8 text-center text-red-500">Mezata ait çiçek bilgileri bulunamadı.</div>';
    get_footer();
    return;
}

?>

<?php // Ana içerik alanı. JS script'lerinin çalışması için data-* nitelikleri ile gerekli ID'leri taşıyoruz. ?>
<main id="main" class="site-main py-8 px-4" 
    data-auction-id="<?php echo esc_attr($post_id); ?>" 
    data-user-id="<?php echo esc_attr($current_user->ID); ?>"
    data-user-name="<?php echo esc_attr($current_user->display_name); ?>"
    data-role="user">

    <!-- Sayfa Üst Bilgisi (Mezat Başlığı ve Durumu) -->
    <div class="container mx-auto max-w-screen-2xl mb-4">
        <div class="flex flex-wrap justify-between items-center bg-gray-800 p-3 rounded-lg gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-white">
                    <span class="text-gray-400">Mezat #<?php echo esc_html($post_id); ?>:</span>
                    <span class="text-amber-400"><?php echo esc_html($cicek->cicek_adi); ?></span>
                </h1>
            </div>
            <div class="flex items-center gap-4">
                <div id="mezat-durum-gostergesi" class="px-4 py-1 rounded-full font-semibold text-sm text-white bg-gray-600 transition-colors">
                    Bağlanılıyor...
                </div>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('mezat-takvimi')); ?>" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">
                    Mezattan Çık
                </a>
            </div>
        </div>
    </div>

    <!-- Ana Mezat Alanı (3 Sütunlu Yapı) -->
    <div id="live-auction-container" class="container mx-auto max-w-screen-2xl grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Sol Sütun: Çiçek Bilgileri -->
        <div class="lg:col-span-4 bg-[#1F2937] p-6 rounded-xl border border-gray-700 space-y-4">
            <?php if (has_post_thumbnail($cicek->post_id)): ?>
                <img src="<?php echo get_the_post_thumbnail_url($cicek->post_id, 'large'); ?>" alt="<?php echo esc_attr($cicek->cicek_adi); ?>" class="w-full h-72 object-cover rounded-lg shadow-lg"/>
            <?php endif; ?>
            <div>
                <h2 class="text-2xl font-bold text-white"><?php echo esc_html($cicek->cicek_adi); ?></h2>
                <div class="prose prose-invert text-gray-300 mt-2">
                    <?php echo apply_filters('the_content', $cicek_post->post_content); ?>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-400">Kalite Sınıfı:</span> <strong class="text-white"><?php echo esc_html($cicek->kalite); ?></strong></div>
                <div class="flex justify-between"><span class="text-gray-400">Sap Uzunluğu:</span> <strong class="text-white"><?php echo esc_html($cicek->sap_uzunlugu); ?> cm</strong></div>
                <div class="flex justify-between"><span class="text-gray-400">Menşei:</span> <strong class="text-white"><?php echo esc_html($cicek->mensei); ?></strong></div>
            </div>
        </div>

        <!-- Orta Sütun: Mezat Paneli -->
        <div class="lg:col-span-5 bg-[#1F2937] rounded-xl p-6 border border-gray-700 flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                 <div>
                    <h3 class="text-sm font-semibold uppercase text-center text-gray-400 mb-2">Mezatta Kalan Süre</h3>
                     <div id="timer-container" class="grid grid-cols-3 gap-3 text-center">
                         <div class="bg-gray-800 p-3 rounded-lg"><span id="timer-hours" class="text-3xl font-bold text-amber-400">--</span><span class="block text-xs text-gray-400">Saat</span></div>
                         <div class="bg-gray-800 p-3 rounded-lg"><span id="timer-minutes" class="text-3xl font-bold text-amber-400">--</span><span class="block text-xs text-gray-400">Dakika</span></div>
                         <div class="bg-gray-800 p-3 rounded-lg"><span id="timer-seconds" class="text-3xl font-bold text-amber-400">--</span><span class="block text-xs text-gray-400">Saniye</span></div>
                     </div>
                 </div>
                 <div class="bg-gray-800 p-4 rounded-lg grid grid-cols-3 gap-4 text-center">
                    <div><div class="text-sm text-gray-400">Toplam Miktar</div><div class="text-xl font-bold"><?php echo esc_html($mezat->mezat_stok); ?></div></div>
                    <div><div class="text-sm text-gray-400">Kalan Miktar</div><div id="remaining-quantity" class="text-xl font-bold text-green-400">--</div></div>
                    <div><div class="text-sm text-gray-400">Min. Alım</div><div class="text-xl font-bold"><?php echo esc_html($mezat->minimum_alim_miktari); ?></div></div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="bg-white text-gray-900 p-4 rounded-lg text-center shadow-lg">
                    <span class="font-semibold text-md text-gray-600">ANLIK FİYAT (Birim)</span>
                    <span id="current-price" class="block text-5xl font-bold text-blue-600">₺--.--</span>
                </div>
                 <div class="flex items-center gap-4">
                     <input type="number" id="quantityToBuy" min="<?php echo esc_attr($mezat->minimum_alim_miktari); ?>" value="<?php echo esc_attr($mezat->minimum_alim_miktari); ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 text-center text-lg font-bold" disabled>
                     <button id="buyButton" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors disabled:bg-gray-600 disabled:cursor-not-allowed" disabled>
                         Hemen Satın Al
                     </button>
                 </div>
                 <div id="message-box" class="p-3 rounded-md text-center text-sm w-full hidden transition-opacity"></div>
            </div>
        </div>

        <!-- Sağ Sütun: Alım Geçmişim -->
        <div class="lg:col-span-3 bg-[#1F2937] rounded-xl p-6 border border-gray-700 flex flex-col">
             <h2 class="text-xl font-bold text-amber-400 border-b border-gray-700 pb-2 mb-4">Bu Mezattaki Alımlarım</h2>
             <ul id="purchase-history-list" class="flex-grow space-y-3 overflow-y-auto max-h-80 pr-2">
                 <li id="no-purchase-yet" class="text-gray-500 text-center mt-8">Henüz alım yapmadınız.</li>
             </ul>
            <div class="border-t border-gray-700 mt-4 pt-4 space-y-2 text-sm">
                 <div class="flex justify-between"><span class="text-gray-300">Toplam Alınan:</span><span id="total-items-purchased" class="font-bold text-white">0</span></div>
                 <div class="flex justify-between text-base"><span class="font-semibold text-amber-400">Toplam Harcama:</span><span id="total-amount-spent" class="font-bold text-amber-400">₺0.00</span></div>
            </div>
        </div>
    </div>
</main>

<?php
// Sitenin footer.php dosyasını dahil et.
get_footer();
// --- Bitiş: single-mezat.php ---
