document.addEventListener("DOMContentLoaded", function () {
  // --- DOM elemek lekérése ---
  const submitPaymentButton = document.getElementById("submit-payment");
  const totalAmountElement = document.getElementById("total-amount");

  // Szállítási adatok mezők
  const firstNameInput = document.getElementById("first-name");
  const lastNameInput = document.getElementById("last-name");
  const addressInput = document.getElementById("address");
  const cityInput = document.getElementById("city");
  const zipInput = document.getElementById("zip");
  const phoneInput = document.getElementById("phone");

  // Fizetési mód radio input-ok
  const paymentMethodRadios = document.querySelectorAll("input[name='payment-method']");

  // Popup értesítő
  const showPopupNotification = (message) => {
    const popup = document.getElementById('popup-notification');
    popup.innerHTML = message;
    popup.style.opacity = '1';
    popup.style.animation = 'popupShow 0.5s forwards';
    setTimeout(() => {
      popup.style.animation = 'popupHide 0.5s forwards';
    }, 3000);
  };

  // --- Végösszeg frissítése kuponnal együtt ---
  const getTotalAmount = () => {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const total = cart.reduce((sum, item) => sum + parseFloat(item.finalPrice) * item.quantity, 0);
    const discount = parseFloat(localStorage.getItem('couponDiscount')) || 0;
    let payable = total - discount;
    if (payable < 0) payable = 0;
    return payable.toFixed(0) + ' Ft';
  };

  const updateTotalAmount = () => {
    totalAmountElement.textContent = getTotalAmount();
  };
  updateTotalAmount();

  // --- Input korlátozások ---
  const restrictNameInput = e => e.target.value = e.target.value.replace(/[^a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]/g, '');
  firstNameInput.addEventListener('input', restrictNameInput);
  lastNameInput.addEventListener('input', restrictNameInput);

  const restrictCityInput = e => e.target.value = e.target.value.replace(/[^a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s]/g, '');
  cityInput.addEventListener('input', restrictCityInput);

  const restrictZipInput = e => e.target.value = e.target.value.replace(/[^0-9]/g, '');
  zipInput.addEventListener('input', restrictZipInput);

  phoneInput.addEventListener('input', () => {
    phoneInput.value = phoneInput.value.replace(/\D/g, '');
  });

  const validatePhoneNumber = phone => {
    return /^(06|36)[0-9]{9}$/.test(phone);
  };

  // --- Fizetés véglegesítése ---
  submitPaymentButton.addEventListener('click', event => {
    event.preventDefault();

    // Fizetési mód kiválasztás ellenőrzése
    const selectedRadio = document.querySelector("input[name='payment-method']:checked");
    if (!selectedRadio) {
      showPopupNotification('Kérjük válassza ki a fizetési módot!');
      return;
    }
    const selectedPaymentMethod = selectedRadio.value;

    let isValid = true;

    // Név mezők validálása
    if (!firstNameInput.value.trim()) { showPopupNotification('Kötelező kitölteni a keresztnevet!'); isValid = false; }
    if (!lastNameInput.value.trim())  { showPopupNotification('Kötelező kitölteni a vezetéknevet!'); isValid = false; }

    // Cím és város, irányítószám
    if (!addressInput.value.trim())   { showPopupNotification('Kötelező kitölteni a címet!'); isValid = false; }
    if (!cityInput.value.trim())      { showPopupNotification('Kötelező kitölteni a várost!'); isValid = false; }
    if (!zipInput.value.trim())       { showPopupNotification('Kötelező kitölteni az irányítószámot!'); isValid = false; }

    // Telefonszám validálása
    if (!phoneInput.value.trim()) {
      showPopupNotification('Kötelező kitölteni a telefonszámot!');
      isValid = false;
    } else if (!validatePhoneNumber(phoneInput.value.trim())) {
      showPopupNotification('Hibás telefonszám formátum! Pl.: 36 30 1234567');
      isValid = false;
    }

    if (!isValid) return;

    showPopupNotification('Az adatok megfelelő formátumban lettek megadva.');

    // Backend payload (készpénzes fizetés)
    const orderId = localStorage.getItem('order_id') || 'temp_order_' + Date.now();
    const payload = {
      city: cityInput.value.trim(),
      address: addressInput.value.trim(),
      postal_code: zipInput.value.trim(),
      phonenumber: phoneInput.value.trim(),
      order_id: orderId
    };

    fetch('http://localhost/backend/public/delivery.php?action=payment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // sikeres fizetés után ürítjük a kosarat
        localStorage.removeItem('cart');
        localStorage.removeItem('couponDiscount');
        window.location.href = 'mainpage.html';
      } else {
        showPopupNotification('Hiba: ' + data.error);
      }
    })
    .catch(err => {
      console.error(err);
      showPopupNotification('Kommunikációs hiba történt.');
    });
  });
});
