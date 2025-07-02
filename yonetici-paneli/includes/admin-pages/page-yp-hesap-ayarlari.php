<?php
/**
 * includes/admin-pages/page-yp-hesap-ayarlari.php
 *
 * Yönetici Paneli'ndeki "Hesap Ayarları" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-hesap-ayarlari' olduğunda yüklenir.
 * Yöneticinin kendi bilgilerini ve şifresini güncellemesini, ayrıca
 * yardımcı yönetici atamasını ve yönetmesini sağlar.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- VERİ HAZIRLAMA ---

// Mevcut giriş yapmış olan yöneticinin bilgilerini al.
$current_user = wp_get_current_user();

// Yardımcı yöneticileri listelemek için WP_User_Query kullan.
// Sadece 'yonetici_yardimcisi' rolüne sahip kullanıcıları getirir.
$yardimci_sorgusu = new WP_User_Query(array(
    'role'    => 'yonetici_yardimcisi',
    'orderby' => 'registered', // Kayıt tarihine göre sırala
    'order'   => 'DESC',       // En yeni eklenen en üstte olacak
));
$yardimcilar = $yardimci_sorgusu->get_results();

?>

<div class="wrap">
    <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Hesap Ayarları</h1>
    <p class="text-gray-600 mt-1">Kendi hesap bilgilerinizi güncelleyebilir ve yardımcı yönetici atamalarını yönetebilirsiniz.</p>

    <hr class="wp-header-end">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
        
        <div class="space-y-8">
            <form id="yonetici-bilgi-formu" class="yp-ajax-form bg-white p-6 rounded-lg shadow-md space-y-6" method="post" data-action="yp_yonetici_bilgi_guncelle">
                <h2 class="text-xl font-semibold text-gray-700">Kişisel Bilgiler</h2>
                <?php wp_nonce_field('yp_yonetici_bilgi_nonce', 'nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="user_login">Kullanıcı Adı</label></th>
                            <td><input type="text" id="user_login" value="<?php echo esc_attr($current_user->user_login); ?>" disabled class="regular-text bg-gray-100">
                            <p class="description">Kullanıcı adları güvenlik nedeniyle değiştirilemez.</p></td>
                        </tr>
                         <tr>
                            <th scope="row"><label for="first_name">Adınız</label></th>
                            <td><input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>" required class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="last_name">Soyadınız</label></th>
                            <td><input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>" required class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="email">E-posta Adresi</label></th>
                            <td><input type="email" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required class="regular-text"></td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Bilgileri Güncelle</button>
                </p>
            </form>

            <form id="yonetici-sifre-formu" class="yp-ajax-form bg-white p-6 rounded-lg shadow-md space-y-6" method="post" data-action="yp_yonetici_sifre_guncelle">
                <h2 class="text-xl font-semibold text-gray-700">Şifre Değiştir</h2>
                <?php wp_nonce_field('yp_yonetici_sifre_nonce', 'nonce'); ?>
                <table class="form-table" role="presentation">
                     <tbody>
                        <tr>
                            <th scope="row"><label for="current_password">Mevcut Şifreniz</label></th>
                            <td><input type="password" id="current_password" name="current_password" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_password1">Yeni Şifre</label></th>
                            <td><input type="password" id="new_password1" name="new_password1" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_password2">Yeni Şifre (Tekrar)</label></th>
                            <td><input type="password" id="new_password2" name="new_password2" class="regular-text" required></td>
                        </tr>
                    </tbody>
                </table>
                <p class="description ml-4">Şifre en az 8 karakterli olmalı, büyük/küçük harf, rakam ve özel karakter içermelidir.</p>
                <p class="submit">
                    <button type="submit" class="button button-primary">Şifreyi Güncelle</button>
                </p>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-700">Yardımcı Yöneticiler</h2>
                <button id="showNewAssistantFormBtn" class="button button-secondary">Yeni Yardımcı Ata</button>
            </div>

            <div id="newAssistantFormWrapper" class="hidden p-6 bg-gray-50 rounded-lg border mb-6">
                <h3 class="text-lg font-semibold text-gray-600 mb-4">Yeni Yardımcı Yönetici Formu</h3>
                <form id="yeni-yardimci-formu" class="yp-ajax-form space-y-4" method="post" data-action="yp_yardimci_kaydet">
                    <?php wp_nonce_field('yp_yeni_yardimci_nonce', 'nonce'); ?>
                    <input type="text" name="user_login" placeholder="Kullanıcı Adı (zorunlu)" required class="w-full">
                    <input type="email" name="email" placeholder="E-posta Adresi (zorunlu)" required class="w-full">
                    <input type="password" name="password" placeholder="Geçici Şifre (zorunlu)" required class="w-full">
                    <p class="text-xs text-gray-500 mt-1">Şifre kuralına uygun bir geçici şifre belirleyin. Yardımcı yönetici giriş yaptıktan sonra kendi şifresini değiştirebilir.</p>
                    <div class="flex gap-4">
                        <button type="submit" class="button button-primary">Kaydet ve Ata</button>
                        <button type="button" id="cancelNewAssistantBtn" class="button button-secondary">İptal</button>
                    </div>
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Adı Soyadı</th>
                        <th>Kullanıcı Adı</th>
                        <th class="text-center">Durum</th>
                        <th class="text-center">Aksiyon</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($yardimcilar)): ?>
                        <?php foreach ($yardimcilar as $yardimci): 
                            $is_passive = get_user_meta($yardimci->ID, 'is_passive', true);
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($yardimci->display_name); ?></strong></td>
                                <td><?php echo esc_html($yardimci->user_login); ?></td>
                                <td class="text-center">
                                    <?php if ($is_passive): ?>
                                        <span class="font-semibold text-red-600">Pasif</span>
                                    <?php else: ?>
                                        <span class="font-semibold text-green-600">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="flex gap-2 justify-center">
                                        <?php if ($is_passive): ?>
                                            <button class="button button-small btn-yardimci-aksiyon" data-aksiyon="aktif_yap" data-user-id="<?php echo esc_attr($yardimci->ID); ?>">Aktif Yap</button>
                                        <?php else: ?>
                                            <button class="button button-small btn-yardimci-aksiyon" data-aksiyon="pasif_yap" data-user-id="<?php echo esc_attr($yardimci->ID); ?>">Pasif Yap</button>
                                        <?php endif; ?>
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
    </div>
</div>
<?php // --- Bitiş: includes/admin-pages/page-yp-hesap-ayarlari.php --- ?>
