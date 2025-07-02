/**
 * assets/js/live-auction-admin.js
 *
 * Yöneticinin canlı mezat kontrol panelindeki (page-yonetici-canli-mezat.php)
 * tüm etkileşimleri ve WebSocket iletişimini yönetir. Bu script, sunucuyla
 * yönetici rolünde bir bağlantı kurarak anlık veri akışını izler, mezata
 * komutlar gönderir ve yönetici arayüzünü günceller.
 *
 * Bu dosya, `functions.php`'de `is_page_template()` kontrolü ile sadece
 * yönetici canlı mezat sayfasında yüklenir.
 *
 * @package CicekMezat
 */

jQuery(document).ready(function($) {
    'use strict';

    // Sayfada yönetici mezat paneli yoksa scripti çalıştırma.
    const adminPanel = $('#yonetici-canli-mezat-paneli');
    if (!adminPanel.length) {
        return;
    }

    // --- DEĞİŞKENLER VE DOM ELEMENTLERİ ---
    // PHP şablonundan data-* nitelikleri ile aktarılan verileri al
    const auctionId = adminPanel.data('mezat-id');
    // `cm_ajax_object` objesi functions.php'de wp_localize_script ile tanımlanmıştır.
    const adminId = cm_ajax_object.user_id;
    const ws_url = cm_ajax_object.ws_url;
    
    // Sık kullanılacak arayüz elemanlarını jQuery nesneleri olarak sakla (performans için)
    const durumGostergesi = $('#mezat-durum-gostergesi');
    const izlemeFiyat = $('#izleme-anlik-fiyat');
    const izlemeSure = $('#izleme-kalan-sure');
    const izlemeKatilimci = $('#izleme-katilimci-sayisi');
    const izlemeMiktar = $('#izleme-kalan-miktar');
    const satisAkisiListesi = $('#canli-satis-akisi');
    const satisYokMesaji = satisAkisiListesi.find('li'); // "Henüz satış yapılmadı" mesajı

    // Kontrol Butonları
    const btnBaslat = $('#btn-mezat-baslat');
    const btnDurdur = $('#btn-mezat-durdur');
    const btnBitir = $('#btn-mezat-bitir');
    const btnUpdateMinPurchase = $('#btn-update-min-purchase');

    // Ayar Formu
    const ayarFormu = $('#mezat-ayarlari-formu');
    let wsConnection; // WebSocket bağlantı nesnesini saklamak için

    // --- WEBSOCKET BAĞLANTI FONKSİYONU ---
    function connect() {
        wsConnection = new WebSocket(ws_url);

        // Bağlantı kurulduğunda
        wsConnection.onopen = function(e) {
            console.log("Yönetici olarak WebSocket sunucusuna bağlanıldı.");
            durumGostergesi.text('Bağlandı').removeClass('bg-gray-600').addClass('bg-blue-600');
            // Sunucuya yönetici olarak kayıt ol
            wsConnection.send(JSON.stringify({
                command: 'register',
                user_id: adminId,
                auction_id: auctionId,
                role: 'admin'
            }));
        };

        // Sunucudan mesaj geldiğinde
        wsConnection.onmessage = function(e) {
            const data = JSON.parse(e.data);

            switch(data.type) {
                case 'state_update':
                    // Diğer istemcilerden gelen durum güncellemelerini yönetici panelinde göster
                    izlemeFiyat.text('₺' + data.price);
                    izlemeMiktar.text(data.remaining_quantity);
                    const t = data.time_left > 0 ? data.time_left : 0;
                    const hours = Math.floor(t / 3600).toString().padStart(2, '0');
                    const minutes = Math.floor((t % 3600) / 60).toString().padStart(2, '0');
                    const seconds = Math.floor(t % 60).toString().padStart(2, '0');
                    izlemeSure.text(`${hours}:${minutes}:${seconds}`);
                    break;
                case 'sale_notification':
                    // Yeni bir satış olduğunda satış akışını güncelle
                    satisYokMesaji.hide();
                    const newItem = `<li class="p-2 bg-gray-800 rounded-md text-sm animate-fade-in">${data.message}</li>`;
                    satisAkisiListesi.prepend(newItem);
                    break;
                case 'user_count_update':
                    // Katılımcı sayısı güncellendiğinde
                    izlemeKatilimci.text(data.count);
                    break;
                case 'auction_started':
                    durumGostergesi.text('Canlı').removeClass('bg-yellow-500 bg-blue-600').addClass('bg-green-600');
                    break;
                case 'auction_paused':
                    durumGostergesi.text('Duraklatıldı').removeClass('bg-green-600').addClass('bg-yellow-500');
                    break;
                case 'auction_ended':
                    durumGostergesi.text('Bitti').removeClass().addClass('bg-red-600');
                    // Mezat bittiğinde tüm kontrol butonlarını pasif yap
                    btnBaslat.add(btnDurdur).add(btnBitir).prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                    break;
            }
        };

        // Bağlantı kapandığında
        wsConnection.onclose = function(e) {
            console.log('Sunucu bağlantısı kapandı. Yeniden bağlanmaya çalışılıyor...');
            durumGostergesi.text('Bağlantı Kesildi').removeClass().addClass('px-4 py-1 rounded-full font-semibold text-sm text-white bg-red-800');
            setTimeout(connect, 5000); // 5 saniye sonra yeniden bağlanmayı dene
        };

        // Hata oluştuğunda
        wsConnection.onerror = function(e) {
            console.error('WebSocket hatası: ', e);
        };
    }

    // --- YÖNETİCİ AKSİYONLARI ---

    /**
     * WebSocket sunucusuna yönetici komutu gönderen yardımcı fonksiyon.
     * @param {string} subCommand - Gönderilecek komut (start, pause, end, update_settings).
     * @param {object} [extraData={}] - Komutla birlikte gönderilecek ek veriler.
     */
    function sendAdminCommand(subCommand, extraData = {}) {
        if (wsConnection && wsConnection.readyState === WebSocket.OPEN) {
            const command = {
                command: 'admin_command',
                sub_command: subCommand,
                auction_id: auctionId,
                ...extraData
            };
            wsConnection.send(JSON.stringify(command));
        } else {
            alert('WebSocket bağlantısı aktif değil. Lütfen sayfayı yenileyin.');
        }
    }

    // Kontrol Butonları için Olay Dinleyicileri
    btnBaslat.on('click', function() { sendAdminCommand('start'); });
    btnDurdur.on('click', function() { sendAdminCommand('pause'); });
    btnBitir.on('click', function() {
        if(confirm('Mezatı kalıcı olarak sonlandırmak istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
            sendAdminCommand('end');
        }
    });
    btnUpdateMinPurchase.on('click', function() {
        const minPurchaseAmount = $('#setting-min-purchase').val();
        sendAdminCommand('update_min_purchase', { min_purchase: minPurchaseAmount });
    });


    // Canlı Mezat Ayarlarını Güncelleme Formu (AJAX ile)
    ayarFormu.on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        const originalButtonText = submitButton.text();

        // AJAX isteğini başlat
        $.ajax({
            url: cm_ajax_object.ajax_url, // WordPress AJAX işleyicisi
            type: 'POST',
            data: form.serialize() + '&action=yp_canli_ayar_guncelle', // AJAX aksiyonu ve form verileri
            dataType: 'json',
            beforeSend: function() {
                submitButton.text('Güncelleniyor...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Veritabanı güncellemesi başarılı olduktan sonra, değişikliği canlı mezata da bildiriyoruz.
                    sendAdminCommand('update_settings', { settings: response.data.settings });
                    alert('Ayarlar başarıyla kaydedildi ve canlı mezata gönderildi!');
                } else {
                    alert('Hata: ' + response.data.message);
                }
            },
            error: function() {
                alert('Sunucu hatası oluştu. Lütfen tekrar deneyin.');
            },
            complete: function() {
                submitButton.text(originalButtonText).prop('disabled', false);
            }
        });
    });

    // --- BAŞLANGIÇ ---
    connect(); // WebSocket bağlantısını başlat
});
