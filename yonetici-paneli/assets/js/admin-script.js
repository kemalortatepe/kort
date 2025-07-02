/**
 * assets/js/admin-script.js
 *
 * Yönetici Paneli - Çiçek Mezatı eklentisinin tüm sayfaları için özel JavaScript kodları.
 * Bu dosya, yönetici panelindeki tüm interaktif işlemleri (form gönderimleri,
 * buton tıklamaları, AJAX istekleri vb.) yönetir. Ayrıca, ACF düzenleme
 * ekranlarına dinamik hesaplama gibi özellikler ekler.
 *
 * `class-yp-admin-menus.php` dosyasında `wp_localize_script` ile tanımlanan
 * `yp_ajax_object` objesi, bu script içinde kullanılabilir.
 *
 * @package YoneticiPaneli
 */

jQuery(document).ready(function($) {
    'use strict';

    // ==========================================================================
    // 1. GENEL YARDIMCI FONKSİYONLAR
    // ==========================================================================
    
    /**
     * Yönetici paneli genelinde standart bir bildirim gösterme fonksiyonu.
     * @param {string} mesaj Gösterilecek mesaj.
     * @param {string} tip Bildirim tipi ('success' veya 'error').
     */
    function gosterBildirim(mesaj, tip = 'success') {
        // WordPress'in standart bildirim yapısını kullanıyoruz.
        var renkSinifi = tip === 'success' ? 'notice-success' : 'notice-error';
        var bildirimHtml = '<div class="notice ' + renkSinifi + ' is-dismissible" style="display:none;"><p>' + mesaj + '</p></div>';
        
        // Bildirimi sayfa başlığının altına ekle ve yavaşça göster.
        $('.yp-wrap > h1, .wrap > h1').first().after(bildirimHtml);
        $('.notice').slideDown();

        // Belirli bir süre sonra bildirimi kaldır
        setTimeout(function() {
            $('.notice').slideUp(500, function() { $(this).remove(); });
        }, 5000);
    }


    // ==========================================================================
    // 2. ACF DÜZENLEME EKRANI GELİŞTİRMELERİ
    // ==========================================================================

    // Bu script sadece ACF'nin "cicek" gönderi türü düzenleme sayfasında çalışacak.
    if ($('body.post-type-cicek').length > 0) {
        // Bu fonksiyon, stok birimi değiştikçe toplam stok miktarını anlık olarak hesaplar.
        function stokHesaplamaMotoru() {
            // ACF alanlarını seç. Alan anahtarları (field_xxxx) ACF arayüzünden alınmalıdır.
            const stokMiktariInput = acf.getField('field_667f1b7f0c1b5').$el.find('input'); // stok_miktari
            const stokBirimiSelect = acf.getField('field_667f1b950c1b6').$el.find('select'); // stok_birimi
            const ambalajInput = acf.getField('field_667f1ba90c1b7').$el.find('input'); // ambalajdaki_cicek
            const konteynerInput = acf.getField('field_667f1bc00c1b8').$el.find('input'); // konteynerdeki_ambalaj
            
            // Sonucun yazılacağı alan (metin olarak) ve kaydedilecek gizli alan
            const toplamStokGosterge = $('#acf-field_667f1bd40c1b9'); // toplam_stok_gostergesi (metin alanı)
            
            function hesapla() {
                let miktar = parseInt(stokMiktariInput.val()) || 0;
                let birim = stokBirimiSelect.val();
                let ambalajAdet = parseInt(ambalajInput.val()) || 1;
                let konteynerAdet = parseInt(konteynerInput.val()) || 1;
                let toplam = 0;

                if (birim === 'tane') {
                    toplam = miktar;
                } else if (birim === 'ambalaj') {
                    toplam = miktar * ambalajAdet;
                } else if (birim === 'konteyner') {
                    toplam = miktar * konteynerAdet * ambalajAdet;
                }
                
                // ACF'nin metin alanının değerini güncelle
                toplamStokGosterge.val(toplam.toLocaleString('tr-TR') + ' adet');
            }

            // İlgili alanlardan herhangi biri değiştiğinde hesaplamayı yeniden yap.
            $(document).on('keyup change', '#acf-field_667f1b7f0c1b5, #acf-field_667f1b950c1b6, #acf-field_667f1ba90c1b7, #acf-field_667f1bc00c1b8', hesapla);
            
            // Sayfa yüklendiğinde ilk hesaplamayı yap.
            hesapla();
        }
        // ACF alanları yüklendikten sonra motoru çalıştır.
        acf.add_action('ready', stokHesaplamaMotoru);
    }
    

    // ==========================================================================
    // 3. MÜŞTERİ YÖNETİMİ AKSİYONLARI (AJAX ile)
    // ==========================================================================
    
    $(document).on('click', '.btn-musteri-aksiyon', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var userId = button.data('user-id');
        var aksiyon = button.data('aksiyon');

        if (!confirm('"' + button.text().trim() + '" işlemini yapmak istediğinizden emin misiniz?')) {
            return;
        }

        $.ajax({
            url: yp_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'yp_musteri_aksiyon', // PHP'de wp_ajax_yp_musteri_aksiyon kancasını tetikler.
                nonce: yp_ajax_object.nonce,
                user_id: userId,
                aksiyon: aksiyon
            },
            dataType: 'json',
            beforeSend: function() {
                button.closest('td').html('<i>İşleniyor...</i>');
            },
            success: function(response) {
                if(response.success) {
                    gosterBildirim(response.data.message, 'success');
                    // En güncel listeyi görmek için 1 saniye sonra sayfayı yenile.
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    gosterBildirim('Hata: ' + response.data.message, 'error');
                    location.reload();
                }
            },
            error: function() {
                 gosterBildirim('Sunucuya bağlanırken bir hata oluştu.', 'error');
            }
        });
    });

    // ==========================================================================
    // 4. YARDIMCI YÖNETİCİ FORMU GÖSTER/GİZLE
    // ==========================================================================
    
    const showAssistantFormBtn = $('#showNewAssistantFormBtn');
    const newAssistantFormWrapper = $('#newAssistantFormWrapper');
    const cancelAssistantFormBtn = $('#cancelNewAssistantBtn');

    if (showAssistantFormBtn.length) {
        showAssistantFormBtn.on('click', function() {
            newAssistantFormWrapper.slideDown(); // Formu aşağı kayarak aç
            $(this).hide(); // "Yeni Ekle" butonunu gizle
        });

        cancelAssistantFormBtn.on('click', function() {
            newAssistantFormWrapper.slideUp(); // Formu yukarı kayarak kapat
            showAssistantFormBtn.show(); // "Yeni Ekle" butonunu tekrar göster
        });
    }

});
// --- Bitiş: assets/js/admin-script.js ---
