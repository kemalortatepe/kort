<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: kredi-durumu.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Müşterinin "Hesabım > Kredi Durumu" sekmesinin içeriğini oluşturur.
 * Bu şablon, `inc/woocommerce-hooks.php` dosyasında tanımlanan endpoint sayesinde
 * "Hesabım" sayfası içinde doğru sekmede görüntülenir.
 *
 * Bu dosya, TeraWallet eklentisinin fonksiyonlarını ve kısa kodlarını kullanarak
 * müşterinin mevcut kredi bakiyesini ve son kredi hareketlerini gösterir.
 * ==========================================================================
 *
 * @package CicekMezat
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// WooCommerce'in veya diğer eklentilerin bu şablonun başına bir şey eklemesi için kanca.
do_action( 'woocommerce_account_kredi-durumu_endpoint_start' ); 
?>

<?php // Genel bir kapsayıcı ve başlık. Tasarım, müşteri hesap yönetimi HTML örneklerine uygundur. ?>
<div class="content-card bg-[#1F2937] p-6 md:p-8 rounded-lg border border-gray-700">
    <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-600">Kredi Durumu</h2>
    
    <?php
    // TeraWallet eklentisinin aktif olup olmadığını kontrol et. Bu, eklenti devre dışı bırakıldığında
    // sitenin çökmesini (fatal error) önler.
    if ( function_exists('woo_wallet') ) :
        // Mevcut giriş yapmış kullanıcının ID'sini al.
        $user_id = get_current_user_id();
        
        // TeraWallet fonksiyonunu kullanarak kullanıcının cüzdan bakiyesini al.
        // 'display' parametresi, bakiyenin WooCommerce para birimi formatında (örn: ₺1.500,00) gelmesini sağlar.
        $balance = woo_wallet()->wallet->get_wallet_balance($user_id, 'display');
    ?>
        
        <?php // Mevcut kredi bakiyesini gösteren büyük kutu. ?>
        <div class="info-box bg-gray-800 p-6 rounded-lg text-center mb-8 border border-gray-700">
            <span class="text-lg text-gray-300">Mevcut Krediniz</span>
            <p class="text-5xl font-bold text-green-400 tracking-tight mt-2"><?php echo $balance; ?></p>
        </div>

        <?php // Son kredi hareketleri bölümü başlığı. ?>
        <h3 class="text-lg font-semibold text-gray-200 mb-4">Son Kredi Hareketleri</h3>
        
        <?php
        /**
         * Son işlemleri listeleyen tabloyu göstermenin en kolay ve güvenilir yolu,
         * TeraWallet eklentisinin kendi sağladığı kısa kodu (shortcode) kullanmaktır.
         * Bu kısa kod, eklenti güncellendiğinde bile uyumlu kalacak bir işlem geçmişi tablosu oluşturur.
         * Tablonun stili, temanızın genel CSS'i tarafından (table, th, td etiketleri) etkilenir.
         */
        ?>
        <div class="woocommerce-wallet-transactions overflow-x-auto">
            <?php echo do_shortcode('[woo_wallet_transactions]'); ?>
        </div>

    <?php else: ?>
        <?php // Eğer TeraWallet eklentisi aktif değilse, kullanıcıya bir bilgilendirme mesajı göster. ?>
        <div class="woocommerce-error p-4 bg-red-500/10 text-red-400 border border-red-500/30 rounded-md">
            <?php esc_html_e( 'Kredi cüzdanı sistemi şu anda aktif değil. Lütfen site yöneticisi ile iletişime geçin.', 'cicekmezat' ); ?>
        </div>
    <?php endif; ?>

</div>

<?php 
// Şablonun sonunda çalışması gereken kancalar için.
do_action( 'woocommerce_account_kredi-durumu_endpoint_end' ); 
?>
<?php // --- Bitiş: woocommerce/myaccount/kredi-durumu.php --- ?>
