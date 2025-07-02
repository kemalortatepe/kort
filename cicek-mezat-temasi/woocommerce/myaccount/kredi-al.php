<?php
/**
 * ==========================================================================
 * KLASÖR: cicek-mezat-temasi/woocommerce/myaccount/
 * DOSYA: kredi-al.php (Yeni Mimariye ve Güncel WooCommerce'e Göre)
 * AÇIKLAMA: Müşterinin "Hesabım > Kredi Al" sekmesinin içeriğini oluşturur.
 * Bu şablon, `inc/woocommerce-hooks.php` dosyasında tanımlanan endpoint sayesinde
 * "Hesabım" sayfası içinde doğru sekmede görüntülenir.
 *
 * Müşterinin hesabına TeraWallet cüzdanı için kredi yüklemesini sağlar.
 * Bu işlem, WooCommerce altyapısını kullanarak, "Kredi" adında özel bir ürün
 * üzerinden gerçekleştirilir.
 * ==========================================================================
 *
 * @package CicekMezat
 * @version 9.9.5
 */

// WordPress'in dışında doğrudan erişimi engellemek için güvenlik kontrolü.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// WooCommerce'in veya diğer eklentilerin bu şablonun başına bir şey eklemesi için kanca.
do_action( 'woocommerce_account_kredi-al_endpoint_start' ); 

// **ÖNEMLİ NOT:** Bu şablonun düzgün çalışması için, WordPress Admin Panelinizden
// "Ürünler > Yeni Ekle" menüsünü kullanarak aşağıdaki özelliklerde bir ürün oluşturmanız gerekir:
// 1. Ürün Adı: Kredi Paketi (veya benzer bir isim)
// 2. Ürün Tipi: Basit Ürün
// 3. Seçenek: "Sanal" kutucuğunu işaretleyin.
// 4. Genel Sekmesi > Normal Fiyat: 1 (Bu, 1 kredinin 1 TL olduğu anlamına gelir)
// 5. Envanter Sekmesi > "Stok yönetimini ürün düzeyinde etkinleştir" seçeneğinin işaretini KALDIRIN.
// 6. Gelişmiş Sekmesi > "Bu ürün için yorumları etkinleştir" seçeneğinin işaretini KALDIRIN.
// 7. Ürünü yayımlayın ve ürün ID'sini not alın. Aşağıdaki koddaki '99' yerine bu ID'yi yazın.
$kredi_urunu_id = 99; // DİKKAT: Kendi kredi ürününüzün ID'sini buraya girin.
$kredi_urunu = wc_get_product($kredi_urunu_id);

?>

<div class="content-card bg-[#1F2937] p-6 md:p-8 rounded-lg border border-gray-700">
    <h2 class="text-2xl font-bold text-amber-400 mb-6 pb-4 border-b border-gray-600">Kredi Yükle</h2>
    
    <?php if ( $kredi_urunu && $kredi_urunu->is_purchasable() ) : // Kredi ürünü bulunabildiyse ve satın alınabilir durumdaysa formu göster ?>

        <p class="mb-6 text-gray-400">Hesabınıza güvenli bir şekilde kredi yüklemek için aşağıdaki formu kullanabilirsiniz. Girdiğiniz tutar kadar kredi, ödeme sonrası cüzdanınıza eklenecektir.</p>
        
        <?php 
        // Form, WooCommerce'in sepetine ürün ekleme mantığını kullanır.
        // Form gönderildiğinde, kullanıcıyı doğrudan ödeme sayfasına yönlendirir.
        ?>
        <form class="cart" action="<?php echo esc_url( wc_get_checkout_url() ); // Kullanıcıyı direkt ödeme sayfasına yönlendir. ?>" method="post" enctype='multipart/form-data'>
            
            <div class="space-y-6">
                <div>
                    <label for="credit_amount" class="form-label block text-sm font-medium text-gray-400 mb-1">Yüklenecek Tutar (₺)</label>
                    <input 
                        type="number" 
                        id="credit_amount" 
                        name="quantity"  <?php // WooCommerce, bu 'quantity' alanını ürün miktarı olarak alır. Fiyat 1 TL olduğu için miktar=tutar olur. ?>
                        class="form-input w-full p-3 bg-gray-800 border border-gray-600 rounded-md text-white text-lg font-semibold" 
                        placeholder="örn: 500" 
                        min="10" <?php // Minimum yüklenecek kredi miktarı ?>
                        value="100" <?php // Varsayılan başlangıç miktarı ?>
                        required>
                </div>
                
                <?php // Hızlı seçim butonları ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <button type="button" class="kredi-hizli-ekle-btn p-3 bg-gray-700 hover:bg-amber-500 hover:text-black rounded-md transition-colors" data-amount="100">100 ₺</button>
                    <button type="button" class="kredi-hizli-ekle-btn p-3 bg-gray-700 hover:bg-amber-500 hover:text-black rounded-md" data-amount="250">250 ₺</button>
                    <button type="button" class="kredi-hizli-ekle-btn p-3 bg-gray-700 hover:bg-amber-500 hover:text-black rounded-md" data-amount="500">500 ₺</button>
                    <button type="button" class="kredi-hizli-ekle-btn p-3 bg-gray-700 hover:bg-amber-500 hover:text-black rounded-md" data-amount="1000">1000 ₺</button>
                </div>

                <?php // Bu gizli alan, hangi ürünün sepete ekleneceğini belirtir. ?>
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $kredi_urunu_id ); ?>" />
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md transition-colors">
                    Ödeme Sayfasına İlerle
                </button>
            </div>

        </form>

    <?php else : // Kredi ürünü bulunamazsa veya satın alınamaz durumdaysa hata mesajı göster ?>

        <div class="woocommerce-error p-4 bg-red-500/10 text-red-400 border border-red-500/30 rounded-md">
            <?php esc_html_e( 'Kredi yükleme sistemi şu anda yapılandırılmamış veya kredi ürünü satın alıma uygun değil. Lütfen site yöneticisi ile iletişime geçin.', 'cicekmezat' ); ?>
        </div>

    <?php endif; ?>

</div>

<script>
// Bu script, sayfadaki kredi yükleme formuna etkileşim ekler.
// main.js dosyası zaten jQuery yüklediği için burada tekrar yüklemeye gerek yoktur,
// ancak bu kodun main.js içine taşınması daha temiz bir yapı sağlar.
jQuery(document).ready(function($) {
    'use strict';
    
    const krediFormu = $('.cart[action*="checkout"]');
    if (!krediFormu.length) return;

    const miktarInput = $('#credit_amount');
    
    // Hızlı kredi ekleme butonlarına tıklandığında, input alanını güncelle.
    $('.kredi-hizli-ekle-btn').on('click', function(e) {
        e.preventDefault(); // Butonun formu göndermesini engelle
        const miktar = $(this).data('amount');
        miktarInput.val(miktar).trigger('change'); // Değeri değiştir ve change olayını tetikle
    });

});
</script>

<?php 
// Şablonun sonunda çalışması gereken kancalar için.
do_action( 'woocommerce_account_kredi-al_endpoint_end' ); 
?>
<?php // --- Bitiş: woocommerce/myaccount/kredi-al.php --- ?>
