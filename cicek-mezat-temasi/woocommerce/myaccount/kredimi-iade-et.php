<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: kredimi-iade-et.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Müşterinin "Hesabım > Kredimi İade Et" sekmesinin içeriğini oluşturur.
 * Bu şablon, `inc/woocommerce-hooks.php` dosyasında tanımlanan endpoint sayesinde
 * "Hesabım" sayfası içinde doğru sekmede görüntülenir.
 *
 * Bu dosya, TeraWallet eklentisinin para çekme (withdrawal) formunu
 * görüntülemek için kendi kısa kodunu (shortcode) kullanır.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// WooCommerce'in veya diğer eklentilerin bu şablonun başına bir şey eklemesi için kanca.
do_action( 'woocommerce_account_kredimi-iade-et_endpoint_start' ); 
?>

<?php // Genel bir kapsayıcı ve başlık. Tasarım, müşteri hesap yönetimi HTML örneklerine uygundur. ?>
<div class="content-card bg-[#1F2937] p-6 md:p-8 rounded-lg border border-gray-700">
    <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-600">Kredi İade Talebi</h2>
    
    <?php
    // TeraWallet eklentisinin aktif olup olmadığını kontrol et.
    if ( function_exists('woo_wallet') ) :
    ?>
        <p class="mb-6 text-gray-400">
            Kullanmadığınız kredi bakiyesini banka hesabınıza geri talep edebilirsiniz. 
            İade talepleri yönetici onayı sonrası işleme alınacaktır. Lütfen aşağıdaki formu 
            doldurarak iade talebinizi oluşturun.
        </p>

        <?php
        /**
         * TeraWallet eklentisinin para çekme formunu çağıran kısa kod.
         * Bu kısa kod, eklentinin ayarlarında yapılandırdığınız (örn: Banka Transferi)
         * para çekme yöntemlerini ve gerekli form alanlarını otomatik olarak oluşturur.
         * Formun gönderilmesi ve işlenmesi (AJAX dahil) TeraWallet tarafından yönetilir.
         *
         * * ÖNEMLİ: Bu formun çalışması için WordPress Admin Panelinden
         * TeraWallet > Ayarlar > Para Çekme (Withdrawal) sekmesine giderek
         * en az bir para çekme yöntemini (örn: Banka Transferi) etkinleştirmeniz gerekir.
         */
        echo do_shortcode('[woo_wallet_withdrawal]'); 
        ?>

    <?php else: ?>
        <?php // Eğer TeraWallet eklentisi aktif değilse, kullanıcıya bir bilgilendirme mesajı göster. ?>
        <div class="woocommerce-error p-4 bg-red-500/10 text-red-400 border border-red-500/30 rounded-md">
            <?php esc_html_e( 'Kredi iade sistemi şu anda aktif değil. Lütfen site yöneticisi ile iletişime geçin.', 'cicekmezat' ); ?>
        </div>
    <?php endif; ?>

</div>

<?php 
// Şablonun sonunda çalışması gereken kancalar için.
do_action( 'woocommerce_account_kredimi-iade-et_endpoint_end' ); 
?>
<?php // --- Bitiş: woocommerce/myaccount/kredimi-iade-et.php --- ?>
