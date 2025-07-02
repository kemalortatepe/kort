/**
 * assets/js/main.js
 *
 * Çiçek Mezatı Teması için ana JavaScript dosyası.
 * Bu dosya, jQuery kullanarak tema genelindeki tüm ön yüz (front-end)
 * etkileşimlerini yönetir. Kayıt formu doğrulaması, "Hesabım" sayfasındaki
 * interaktif elemanlar ve genel AJAX işlemleri burada yer alır.
 *
 * `functions.php` -> `inc/enqueue.php` dosyasında `wp_localize_script` ile tanımlanan
 * `cm_ajax_object` objesi, bu script içinde kullanılabilir. Bu obje,
 * WordPress'in AJAX URL'ini ve güvenlik anahtarını (nonce) içerir.
 *
 * @package CicekMezat
 */

jQuery(document).ready(function($) {
    'use strict';

    // ==========================================================================
    // 1. GENEL ETKİLEŞİMLER VE YARDIMCI FONKSİYONLAR
    // ==========================================================================
    
    /**
     * Genel bir bildirim gösterme fonksiyonu.
     * @param {string} mesaj Gösterilecek mesaj.
     * @param {string} tip Bildirim tipi ('success' veya 'error').
     * @param {jQuery} konteyner Bildirimin ekleneceği jQuery nesnesi.
     */
    function gosterBildirim(mesaj, tip, konteyner) {
        var renkSinifi = tip === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        var bildirimHtml = '<div class="bildirim-kutusu p-4 mb-4 text-sm rounded-lg ' + renkSinifi + '" role="alert">' + mesaj + '</div>';
        
        konteyner.prepend(bildirimHtml);
        // Belirli bir süre sonra bildirimi kaldır
        setTimeout(function() {
            konteyner.find('.bildirim-kutusu').first().fadeOut(500, function() { $(this).remove(); });
        }, 5000);
    }


    // ==========================================================================
    // 2. MÜŞTERİ KAYIT FORMU DOĞRULAMASI (template-kayit.php)
    // ==========================================================================
    
    const kayitFormu = $('#musteri-kayit-formu');

    // Eğer sayfada kayıt formu varsa, ilgili işlemleri yap
    if (kayitFormu.length) {
        const passwordInput = $('#reg_password');
        const confirmPasswordInput = $('#reg_password2');
        const registerBtn = kayitFormu.find('button[type="submit"]');
        
        // Şifrelerin eşleşip eşleşmediğini kontrol et
        function sifreleriKontrolEt() {
            if (passwordInput.val() !== confirmPasswordInput.val()) {
                // Şifreler uyuşmuyorsa, onay alanının altına bir hata mesajı ekle
                // Önce mevcut hata mesajını kaldır
                $('#sifre-hata-mesaji').remove();
                confirmPasswordInput.after('<p id="sifre-hata-mesaji" class="text-red-500 text-xs mt-1">Şifreler uyuşmuyor.</p>');
                return false;
            } else {
                $('#sifre-hata-mesaji').remove();
                return true;
            }
        }

        // Formun gönderilmeye hazır olup olmadığını kontrol et
        function formuGuncelle() {
            const sartlarKabulEdildi = $('#terms').is(':checked');
            const sifrelerUyusuyor = sifreleriKontrolEt();
            
            // Tüm koşullar sağlandığında butonu aktif et
            if (sartlarKabulEdildi && sifrelerUyusuyor && passwordInput.val().length > 0) {
                registerBtn.prop('disabled', false);
            } else {
                registerBtn.prop('disabled', true);
            }
        }

        // Şifre alanları ve şartlar kutusu her değiştiğinde kontrolü tetikle
        kayitFormu.find('input[required], #terms').on('keyup change', formuGuncelle);

        // Sayfa yüklendiğinde butonun durumunu ayarla
        formuGuncelle();
    }


    // ==========================================================================
    // 3. HESABIM > KREDİ AL SAYFASI ETKİLEŞİMLERİ
    // ==========================================================================

    const krediYukleFormu = $('#kredi-yukle-formu');
    
    if (krediYukleFormu.length) {
        const krediMiktarInput = $('#credit_amount');
        
        // Hızlı kredi ekleme butonlarına tıklandığında
        $('.kredi-hizli-ekle-btn').on('click', function(e) {
            e.preventDefault();
            var miktar = $(this).data('amount');
            krediMiktarInput.val(miktar);
        });
    }

    // ==========================================================================
    // 4. AKTİF AJAX ÖRNEĞİ
    // ==========================================================================

    // Bu fonksiyon, bir butona tıklandığında çalışacak örnek bir AJAX çağrısıdır.
    // Tema dosyalarınızda `.kredi-sorgula-butonu` class'ına sahip bir buton oluşturarak test edebilirsiniz.
    $(document).on('click', '.kredi-sorgula-butonu', function(e) {
        e.preventDefault();
        var button = $(this);

        // AJAX isteğini başlat
        $.ajax({
            // `cm_ajax_object` objesi, `inc/enqueue.php` dosyasındaki
            // `wp_localize_script` ile PHP'den JavaScript'e aktarılmıştır.
            url: cm_ajax_object.ajax_url, 
            type: 'POST',
            data: {
                action: 'get_user_credit', // PHP tarafında `wp_ajax_get_user_credit` kancasını tetikler
                nonce: cm_ajax_object.nonce, // Güvenlik doğrulaması için
                // Buraya başka veriler de eklenebilir.
                // ornek_veri: 'merhaba' 
            },
            dataType: 'json', // Sunucudan JSON formatında yanıt beklediğimizi belirtiyoruz.
            beforeSend: function() {
                // İstek gönderilmeden hemen önce butonu pasif hale getir ve metnini değiştir.
                button.text('Sorgulanıyor...').prop('disabled', true);
            },
            success: function(response) {
                // İstek başarılı olduğunda çalışır.
                if (response.success) {
                    // PHP'den `wp_send_json_success` ile gönderilen veriye erişim
                    // ve bu veriyi kullanarak bir bildirim gösterme.
                    gosterBildirim('Güncel Kredi Bakiyeniz: ' + response.data.formatted_balance, 'success', button.parent());
                } else {
                    // PHP'den `wp_send_json_error` ile gönderilen hata mesajı
                    gosterBildirim('Hata: ' + response.data.message, 'error', button.parent());
                }
            },
            error: function(xhr, status, error) {
                // Sunucu hatası veya ağ sorunu gibi durumlarda çalışır.
                console.error("AJAX Hatası: ", error);
                gosterBildirim('Sunucuyla iletişim kurulamadı.', 'error', button.parent());
            },
            complete: function() {
                // İstek başarılı veya başarısız olsun, her durumda çalışır.
                // Butonu tekrar aktif hale getir.
                button.text('Kredimi Sorgula').prop('disabled', false);
            }
        });
    });

});
// --- Bitiş: assets/js/main.js ---
