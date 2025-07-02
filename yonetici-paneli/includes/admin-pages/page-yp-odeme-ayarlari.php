<?php
/**
 * includes/admin-pages/page-yp-odeme-ayarlari.php
 *
 * Yönetici Paneli'ndeki "Ödeme Ayarları" sayfasının şablonu.
 * Bu şablon, `class-yp-admin-menus.php` dosyasında tanımlanan menü sistemi
 * tarafından, 'page' parametresi 'yp-odeme-ayarlari' olduğunda yüklenir.
 * Site genelindeki kredi, ödeme ve sanal POS ayarları bu form üzerinden yönetilir.
 * Form, AJAX ile gönderilerek sayfa yenilenmeden kaydedilir.
 *
 * @package YoneticiPaneli
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Kaydedilmiş ayarları veritabanından çek. get_option, WordPress'in ayarları saklamak için standart yoludur.
// Eğer daha önce ayar kaydedilmemişse, ikinci parametredeki varsayılan değerler kullanılır.
$odeme_ayarlari = get_option('cicek_mezat_odeme_ayarlari', array(
    'kredisiz_alim_izni'    => 'engelle',
    'borc_odeme_suresi'     => 24,
    'paytr_merchant_id'     => '',
    'paytr_merchant_key'    => '',
    'paytr_merchant_salt'   => '',
));

?>

<div class="wrap">
    <h1 class="wp-heading-inline text-2xl font-bold text-gray-800">Ödeme ve Kredi Ayarları</h1>
    <p class="text-gray-600 mt-1">Sitenizin finansal kurallarını ve ödeme altyapısı ayarlarını buradan yönetin.</p>
    
    <hr class="wp-header-end">

    <?php // Formu 'yp-ajax-form' class'ı ile işaretleyerek admin-script.js'in bu formu yakalamasını sağlıyoruz. ?>
    <?php // 'data-action' niteliği, hangi AJAX aksiyonunun çağrılacağını belirtir. ?>
    <form id="odeme-ayarlari-formu" class="yp-ajax-form" method="post" data-action="yp_odeme_ayarlarini_kaydet">
        
        <?php // Güvenlik için Nonce Alanı. AJAX isteğinde bu değer doğrulanır. ?>
        <?php wp_nonce_field('yp_odeme_ayarlari_nonce', 'nonce'); ?>

        <h2 class="text-xl font-semibold text-gray-700 mb-4">Genel Kurallar</h2>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label>Kredisiz Alım İzni</label>
                    </th>
                    <td>
                        <fieldset>
                            <label class="mr-6">
                                <input type="radio" name="kredisiz_alim_izni" value="engelle" <?php checked($odeme_ayarlari['kredisiz_alim_izni'], 'engelle'); ?>>
                                <span>Katılımları Engelle (Önerilen ve Güvenli Yöntem)</span>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="kredisiz_alim_izni" value="izin_ver" <?php checked($odeme_ayarlari['kredisiz_alim_izni'], 'izin_ver'); ?>>
                                <span>Katılımlarına İzin Ver (Riskli, Müşteri Borçlanabilir)</span>
                            </label>
                            <p class="description">"İzin Ver" seçeneği, müşterinin borçlanmasına olanak tanır ve finansal takip gerektirir.</p>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="borc_odeme_suresi">Borç Ödeme Süresi</label>
                    </th>
                    <td>
                        <input name="borc_odeme_suresi" type="number" id="borc_odeme_suresi" value="<?php echo esc_attr($odeme_ayarlari['borc_odeme_suresi']); ?>" class="regular-text" min="1">
                        <span>saat</span>
                        <p class="description">Kredisiz alıma izin verilirse, borçlanan müşterinin ödeme yapması için tanınacak maksimum süre. Süre sonunda müşteri "Cezalı" duruma düşürülür.</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <h2 class="text-xl font-semibold text-gray-700 mt-8 mb-4">Ödeme Ağ Geçidi Ayarları</h2>
        
        <table class="form-table" role="presentation">
             <tbody>
                <tr>
                    <th scope="row">
                        <label>Müşteri Ödeme Yöntemleri</label>
                    </th>
                    <td>
                        <p class="description">
                            Müşterilerin kredi yüklemek veya iade almak için kullanabileceği ödeme yöntemleri, doğrudan WooCommerce ayarlarından yönetilir. Bu, en güvenli ve standart yöntemdir.
                        </p>
                        <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout'); ?>" class="button button-secondary mt-2">WooCommerce Ödeme Ayarlarına Git</a>
                    </td>
                </tr>
                 <tr>
                    <th scope="row" colspan="2"><h3 class="text-lg">PayTR Sanal POS API Bilgileri</h3></th>
                </tr>
                 <tr>
                    <th scope="row">
                        <label for="paytr_merchant_id">Mağaza ID (Merchant ID)</label>
                    </th>
                    <td>
                        <input name="paytr_merchant_id" type="text" id="paytr_merchant_id" value="<?php echo esc_attr($odeme_ayarlari['paytr_merchant_id']); ?>" class="regular-text">
                    </td>
                </tr>
                 <tr>
                    <th scope="row">
                        <label for="paytr_merchant_key">Mağaza Anahtarı (Merchant Key)</label>
                    </th>
                    <td>
                        <input name="paytr_merchant_key" type="password" id="paytr_merchant_key" value="<?php echo esc_attr($odeme_ayarlari['paytr_merchant_key']); ?>" class="regular-text">
                    </td>
                </tr>
                 <tr>
                    <th scope="row">
                        <label for="paytr_merchant_salt">Mağaza Gizli Anahtarı (Merchant Salt)</label>
                    </th>
                    <td>
                        <input name="paytr_merchant_salt" type="password" id="paytr_merchant_salt" value="<?php echo esc_attr($odeme_ayarlari['paytr_merchant_salt']); ?>" class="regular-text">
                    </td>
                </tr>
            </tbody>
        </table>

        <?php // Kaydet Butonu ?>
        <p class="submit">
            <button type="submit" class="button button-primary">
                Ayarları Kaydet
            </button>
        </p>
    </form>
</div>
<?php // --- Bitiş: includes/admin-pages/page-yp-odeme-ayarlari.php --- ?>
