/**
 * assets/js/live-auction-customer.js
 *
 * Müşterinin canlı mezat sayfasındaki (single-mezat.php) tüm etkileşimleri
 * ve WebSocket iletişimini yönetir. Bu script, sunucuyla sürekli bir
 * bağlantı kurarak anlık veri akışını sağlar ve kullanıcı arayüzünü günceller.
 *
 * Bu dosya, `functions.php`'de `is_singular('mezat')` kontrolü ile sadece
 * mezat sayfalarında yüklenir ve `cm_ajax_object` üzerinden PHP'den gelen
 * verileri (URL, Nonce, kullanıcı ID'si vb.) kullanır.
 *
 * @package CicekMezat
 */

jQuery(document).ready(function($) {
    'use strict';

    // Sayfada mezat konteyneri yoksa scripti çalıştırma, bu gereksiz işlemleri önler.
    const auctionContainer = $('#main[data-auction-id]');
    if (!auctionContainer.length) {
        return;
    }

    // --- DEĞİŞKENLER VE DOM ELEMENTLERİ ---
    // PHP'den wp_localize_script ile aktarılan verileri al
    const ws_url = cm_ajax_object.ws_url;
    const auctionId = auctionContainer.data('auction-id');
    const userId = auctionContainer.data('user-id');
    
    // Sık kullanılacak arayüz elemanlarını jQuery nesneleri olarak sakla (performans için)
    const durumGostergesi = $('#mezat-durum-gostergesi');
    const timerHours = $('#timer-hours');
    const timerMinutes = $('#timer-minutes');
    const timerSeconds = $('#timer-seconds');
    const currentPriceEl = $('#current-price');
    const remainingQtyEl = $('#remaining-quantity');
    const buyButton = $('#buyButton');
    const quantityInput = $('#quantityToBuy');
    const messageBox = $('#message-box');
    const historyList = $('#purchase-history-list');
    const noPurchaseMessage = $('#no-purchase-yet');
    const totalItemsEl = $('#total-items-purchased');
    const totalSpentEl = $('#total-amount-spent');

    // Müşterinin bu mezattaki toplam harcamasını ve alımını takip etmek için
    let totalSpent = 0;
    let totalItems = 0;
    let wsConnection; // WebSocket bağlantı nesnesini saklamak için

    // --- WEBSOCKET BAĞLANTI FONKSİYONU ---
    function connect() {
        wsConnection = new WebSocket(ws_url);

        // Bağlantı kurulduğunda
        wsConnection.onopen = function(e) {
            console.log("WebSocket sunucusuna başarıyla bağlanıldı.");
            durumGostergesi.text('Sunucuya Bağlandı').removeClass('bg-gray-600').addClass('bg-blue-600');
            // Sunucuya hangi mezata hangi kullanıcının katıldığını bildir
            wsConnection.send(JSON.stringify({
                command: 'register',
                user_id: userId,
                auction_id: auctionId,
                role: 'user'
            }));
        };

        // Sunucudan yeni bir mesaj geldiğinde
        wsConnection.onmessage = function(e) {
            const data = JSON.parse(e.data);

            switch(data.type) {
                // Ana durum güncelleme mesajı (fiyat, zaman, stok)
                case 'state_update':
                    updateUI(data);
                    break;
                // Kişisel alım başarılı mesajı
                case 'purchase_success':
                    handlePurchaseSuccess(data);
                    break;
                // Kişisel alım hata mesajı
                case 'purchase_error':
                    showMessage(data.message, 'error');
                    break;
                // Odadaki herkese giden genel satış bildirimi
                case 'sale_notification':
                    console.log(data.message);
                    break;
                // Mezatın başladığına dair bildirim
                case 'auction_started':
                    durumGostergesi.text('Canlı').removeClass('bg-yellow-500 bg-blue-600').addClass('bg-green-600');
                    buyButton.prop('disabled', false);
                    quantityInput.prop('disabled', false);
                    break;
                 // Mezatın duraklatıldığına dair bildirim
                case 'auction_paused':
                    durumGostergesi.text('Duraklatıldı').removeClass('bg-green-600').addClass('bg-yellow-500');
                    buyButton.prop('disabled', true);
                    quantityInput.prop('disabled', true);
                    break;
                // Mezatın bittiğine dair bildirim
                case 'auction_ended':
                    handleAuctionEnd(data.message);
                    break;
            }
        };

        // Bağlantı kapandığında
        wsConnection.onclose = function(e) {
            console.log('Sunucu bağlantısı kapandı. Yeniden bağlanmaya çalışılıyor...');
            handleAuctionEnd('Sunucu bağlantısı kesildi. Sayfayı yenileyin.');
            // Otomatik yeniden bağlanma denemesi (isteğe bağlı)
            setTimeout(function() {
                // connect(); // Yeniden bağlanmayı dene
            }, 5000);
        };

        // Hata oluştuğunda
        wsConnection.onerror = function(e) {
            console.error('WebSocket hatası: ', e);
            showMessage('Bağlantı hatası oluştu.', 'error');
        };
    }

    // --- ARAYÜZ GÜNCELLEME FONKSİYONLARI ---
    
    function updateUI(data) {
        currentPriceEl.text('₺' + data.price);
        remainingQtyEl.text(data.remaining_quantity);
        quantityInput.attr('max', data.remaining_quantity);
        
        const t = data.time_left > 0 ? data.time_left : 0;
        timerHours.text(Math.floor(t / 3600).toString().padStart(2, '0'));
        timerMinutes.text(Math.floor((t % 3600) / 60).toString().padStart(2, '0'));
        timerSeconds.text(Math.floor(t % 60).toString().padStart(2, '0'));
    }

    function handlePurchaseSuccess(data) {
        showMessage(`Başarılı! ${data.quantity} adet ürünü ₺${data.cost.toFixed(2)} fiyata aldınız.`, 'success');
        totalItems += parseInt(data.quantity);
        totalSpent += parseFloat(data.cost);
        updatePurchaseHistory(data.quantity, data.cost);
    }
    
    function updatePurchaseHistory(quantity, cost) {
        noPurchaseMessage.hide();
        const pricePerItem = cost / quantity;
        const time = new Date().toLocaleTimeString('tr-TR');
        const newItem = `<li class="bg-white/10 p-2 rounded-md text-xs animate-fade-in"><div class="flex justify-between"><span>Saat: ${time}</span> <strong class="text-white">${quantity} adet</strong></div><div class="flex justify-between items-center mt-1"><span class="text-gray-400">Birim Fiyat: ₺${pricePerItem.toFixed(2)}</span> <strong class="text-amber-300 text-sm">Toplam: ₺${cost.toFixed(2)}</strong></div></li>`;
        const appendedItem = $(newItem).prependTo(historyList);
        setTimeout(() => appendedItem.css('opacity', 1), 50);
        totalItemsEl.text(totalItems + ' adet');
        totalSpentEl.text('₺' + totalSpent.toFixed(2));
    }

    function handleAuctionEnd(message) {
        if(wsConnection) wsConnection.close();
        durumGostergesi.text('Bitti').removeClass().addClass('px-4 py-1 rounded-full font-semibold text-sm text-white bg-red-600');
        buyButton.prop('disabled', true).text('Mezat Sona Erdi');
        quantityInput.prop('disabled', true);
        showMessage(message, 'error');
        setTimeout(() => window.location.href = cm_ajax_object.my_account_url + 'mezat-takvimi', 5000);
    }

    function showMessage(msg, type = 'success') {
        messageBox.text(msg).removeClass('hidden bg-green-200 text-green-800 bg-red-200 text-red-800').addClass(type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800').fadeIn();
        setTimeout(() => messageBox.fadeOut(), 5000);
    }

    // --- OLAY DİNLEYİCİLER (EVENT LISTENERS) ---
    buyButton.on('click', function() {
        const quantity = parseInt(quantityInput.val(), 10);
        const minQuantity = parseInt(quantityInput.attr('min'), 10);

        if (isNaN(quantity) || quantity < minQuantity) {
            showMessage(`Lütfen en az ${minQuantity} adet miktar girin.`, 'error');
            return;
        }

        if (wsConnection && wsConnection.readyState === WebSocket.OPEN) {
            wsConnection.send(JSON.stringify({
                command: 'buy',
                auction_id: auctionId,
                user_id: userId,
                quantity: quantity
            }));
        } else {
            showMessage('Sunucu bağlantısı yok. Lütfen sayfayı yenileyin.', 'error');
        }
    });

    // --- BAŞLANGIÇ ---
    connect(); // WebSocket bağlantısını başlat
});
