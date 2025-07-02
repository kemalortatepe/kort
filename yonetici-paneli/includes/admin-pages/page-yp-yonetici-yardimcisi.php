<?php
/**
 * includes/admin-pages/page-yp-yonetici-yardimcisi.php
 *
 * Yönetici Paneli'ndeki "Yardımcı Yönetici" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-yonetici-yardimcisi' olduğunda yüklenir.
 * Yöneticinin, 'yonetici_yardimcisi' rolüne sahip kullanıcıları listelemesini,
 * yeni yardımcılar atamasını ve mevcut yardımcıları yönetmesini sağlar.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Sadece 'administrator' rolündeki asıl yönetici bu sayfayı görebilir ve işlem yapabilir.
if ( ! current_user_can( 'administrator' ) ) {
    wp_die( __( 'Bu sayfayı görüntüleme yetkiniz yok.' ) );
}

// --- VERİ HAZIRLAMA ---

// 'yonetici_yardimcisi' rolüne sahip tüm kullanıcıları listelemek için WP_User_Query kullan.
$yardimci_sorgusu = new WP_User_Query(array(
    'role'    => 'yonetici_yardimcisi',
    'orderby' => 'registered', // Kayıt tarihine göre sırala
    'order'   => 'DESC',       // En yeni eklenen en üstte olacak
));
$yardimcilar = $yardimci_sorgusu->get_results();

?>

<div class="wrap">
    <div class="flex justify-between items-center mb-6">
        <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Yardımcı Yönetici Yönetimi</h1>
        <button id="showNewAssistantFormBtn" class="page-title-action">Yeni Yardımcı Ata</button>
    </div>

    <div id="newAssistantFormWrapper" class="hidden p-6 bg-gray-50 rounded-lg border mb-6 transition-all duration-500">
        <h3 class="text-lg font-semibold text-gray-600 mb-4">Yeni Yardımcı Yönetici Formu</h3>
        
        <?php // Formu 'yp-ajax-form' class'ı ile işaretleyerek admin-script.js'in bu formu yakalamasını sağlıyoruz. ?>
        <form id="yeni-yardimci-formu" class="yp-ajax-form space-y-4" method="post" data-action="yp_yardimci_kaydet">
            <?php // Güvenlik için Nonce Alanı. AJAX isteğinde bu değer doğrulanır. ?>
            <?php wp_nonce_field('yp_yeni_yardimci_nonce', 'nonce'); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="user_login" placeholder="Kullanıcı Adı (zorunlu)" required class="w-full">
                <input type="email" name="email" placeholder="E-posta Adresi (zorunlu)" required class="w-full">
            </div>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="first_name" placeholder="Adı" class="w-full">
                <input type="text" name="last_name" placeholder="Soyadı" class="w-full">
            </div>
            <div>
                <input type="password" name="password" placeholder="Geçici Şifre (zorunlu)" required class="w-full">
                <p class="text-xs text-gray-500 mt-1">Şifre kuralına uygun bir geçici şifre belirleyin. Yardımcı yönetici giriş yaptıktan sonra kendi şifresini değiştirebilir.</p>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="button button-primary">Kaydet ve Ata</button>
                <button type="button" id="cancelNewAssistantBtn" class="button button-secondary">İptal</button>
            </div>
        </form>
    </div>
    
    <table class="wp-list-table widefat fixed striped table-view-list users">
        <thead>
            <tr>
                <th>Adı Soyadı</th>
                <th>Kullanıcı Adı / E-posta</th>
                <th class="text-center">Durum</th>
                <th class="text-center column-Aksiyon">Aksiyon</th>
            </tr>
        </thead>
        <tbody id="the-list">
            <?php if (!empty($yardimcilar)): ?>
                <?php foreach ($yardimcilar as $yardimci): 
                    // Yöneticinin bu yardımcıyı pasif duruma alıp almadığını kontrol et.
                    $is_passive = get_user_meta($yardimci->ID, 'is_passive', true);
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($yardimci->display_name); ?></strong></td>
                        <td>
                            <?php echo esc_html($yardimci->user_login); ?>
                            <br>
                            <span class="text-gray-500"><?php echo esc_html($yardimci->user_email); ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($is_passive): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-red-600">Pasif</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-green-600">Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="Aksiyon text-center">
                            <div class="flex gap-2 justify-center">
                                <?php if ($is_passive): ?>
                                    <button class="button button-small btn-yardimci-aksiyon" data-aksiyon="aktif_yap" data-user-id="<?php echo esc_attr($yardimci->ID); ?>">Aktif Yap</button>
                                <?php else: ?>
                                    <button class="button button-small btn-yardimci-aksiyon" data-aksiyon="pasif_yap" data-user-id="<?php echo esc_attr($yardimci->ID); ?>">Pasif Yap</button>
                                <?php endif; ?>
                                
                                <?php // WordPress'in standart kullanıcı düzenleme sayfasına link. ?>
                                <a href="<?php echo esc_url(get_edit_user_link($yardimci->ID)); ?>" class="button button-small">Güncelle</a>
                                
                                <button class="button button-danger button-small btn-yardimci-aksiyon" data-aksiyon="sil" data-user-id="<?php echo esc_attr($yardimci->ID); ?>">Sil</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center py-4">Henüz yardımcı yönetici atanmamış.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php // --- Bitiş: includes/admin-pages/page-yp-yonetici-yardimcisi.php --- ?>
