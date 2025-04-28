// Kosárba helyezés
function addToCart(pizza) {
  let maxQty = 10;
  let qty = parseInt(document.getElementById(`qty-${pizza.id}`).value) || 1;

  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  let existingItem = cart.find(item => item.id === pizza.id);

  let price = pizza.discounted_price ? parseFloat(pizza.discounted_price) : parseFloat(pizza.price);

  if (existingItem) {
    // Ne lépjük túl a maximális mennyiséget
    if (existingItem.quantity + qty > maxQty) {
      showCartModal(`Maximális rendelhető mennyiség: ${maxQty} db`);
      return;
    }
    existingItem.quantity += qty;
  } else {
    if (qty > maxQty) {
      showCartModal(`Maximális rendelhető mennyiség: ${maxQty} db`);
      return;
    }
    pizza.quantity = qty;
    pizza.finalPrice = price;
    cart.push(pizza);
  }

  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartCounter();
  showCartModal(`${pizza.name} (${qty} db) a kosárba került!`);
  loadCart();
}

// Kosár számláló frissítése
// Egyesített verzió (tegyük például a layout.js változatát használni)
function updateCartCounter() {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

  let cartCounter = document.getElementById('cart-counter');
  if (cartCounter) {
      if (totalItems > 0) {
          cartCounter.innerText = totalItems;
          cartCounter.style.display = "inline-block";
      } else {
          cartCounter.style.display = "none";
      }
  }
}


// Kosár tartalmának betöltése és megjelenítése
function loadCart() {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  if (cart.length === 0) {
    document.getElementById('cart-list').innerHTML = "A kosár üres.";
    updateTotal();
    updateCartCounter();
    return;
  }

  let html = '<ul>';
  cart.forEach((item) => {
    html += `<li class="cart-summary">
                  <h5><strong>${item.name}</strong></h5><br> 
                  Ár: ${item.finalPrice} Ft<br><br>
                  Mennyiség: <input style="width: 70px;" type="number" id="cart-qty-${item.id}" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, this.value)">
                  <button class="remove-button" onclick="removeFromCart(${item.id})">Eltávolítás</button>
              </li>`;
  });
  html += '</ul>';
  document.getElementById('cart-list').innerHTML = html;
  updateTotal();
  updateCartCounter();
}

// Mennyiség módosítása a kosárban
function updateQuantity(id, newQty) {
  let maxQty = 10;
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  newQty = parseInt(newQty);

  if (isNaN(newQty) || newQty < 1) {
    newQty = 1;
  } else if (newQty > maxQty) {
    newQty = maxQty;
    showCartModal(`Maximális rendelhető mennyiség: ${maxQty} db`);
  }

  cart.forEach(item => {
    if (Number(item.id) === Number(id)) {
      item.quantity = newQty;
    }
  });

  localStorage.setItem('cart', JSON.stringify(cart));
  loadCart();
  updateCartCounter();
}

// Tétel eltávolítása a kosárból
function removeFromCart(id) {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  cart = cart.filter(item => Number(item.id) !== Number(id));

  localStorage.setItem('cart', JSON.stringify(cart));
  loadCart();
  updateCartCounter();
}

// Összesítés: kiszámolja a kosárban lévő tételek árát
function updateTotal() {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  let total = cart.reduce((sum, item) => {
    return sum + (parseFloat(item.finalPrice) * item.quantity);
  }, 0);
  document.getElementById('total-price').innerText = total.toFixed(0) + ' Ft';
}

// Globális változó a timeout kezelésére
let popupTimeout = null;

function showCartModal(message) {
    // Keressük meg a popup elemet, ha nem létezik, hozzuk létre dinamikusan
    let popup = document.getElementById("popup-notification");
    if (!popup) {
        popup = document.createElement("div");
        popup.id = "popup-notification";
        popup.className = "popup-notification";
        document.body.appendChild(popup);
    }
    
    // Ha már van aktív timeout, töröljük azt
    if (popupTimeout) {
        clearTimeout(popupTimeout);
        popupTimeout = null;
    }
    
    // Állítsuk be az üzenetet
    popup.innerHTML = message;

    
    // Kezdjük el a beúszás animációt:
    // Először töröljük az esetleges korábbi inline animáció értéket
    popup.style.animation = '';
    // Force reflow, hogy az előző animáció vége teljesen törlődjön
    void popup.offsetWidth;
    // Beállítjuk az animációt: 0.5s beúszás "forwards" kitöltéssel, így az üzenet látható marad a helyén
    popup.style.animation = 'popupShow 0.75s forwards';
    
    // 3000 ms (3 s) után indítjuk el az eltűnés animációt
    popupTimeout = setTimeout(() => {
        popup.style.animation = 'popupHide 0.75s forwards';
    }, 3000);
}


// Kupon beváltásának kezelése
document.getElementById("apply-coupon").addEventListener("click", function() {
  let couponCode = document.getElementById("coupon-code").value.trim();
  if (!couponCode) {
      showCartModal("Kérjük, adjon meg egy kupon kódot.");
      return;
  }
  
  // Küldjünk egy POST kérést a coupon API felé
  fetch('http://localhost/backend/public/coupon.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify({ code: couponCode })
  })
  .then(response => response.json())
  .then(data => {
      if (data.success && data.coupon) {
          // Kupon helyes, frissítjük az összegsávot
          let discountValue = parseFloat(data.coupon.discount_value);
          let totalEl = document.getElementById("total-price");
          let totalText = totalEl.innerText.replace(' Ft', '');
          let totalAmount = parseFloat(totalText) || 0;
          
          // Frissítsük a kedvezmény mezőt
          document.getElementById("coupon-discount").innerText = discountValue.toFixed(0) + ' Ft';
          
          // Számoljuk ki a fizetendő összeget:
          let payable = totalAmount - discountValue;
          if(payable < 0){
              payable = 0; // ne legyen negatív összeg
          }
          document.getElementById("payable-price").innerText = payable.toFixed(0) + ' Ft';
          localStorage.setItem('couponDiscount', discountValue);
          
          showCartModal(`Kupon sikeresen beváltva: <strong>-${discountValue.toFixed(0)} Ft</strong> kedvezmény`);
      } else {
          showCartModal(data.error || "Kupon érvénytelen.");
          // Ha hiba, akkor töröljük a kedvezmény és fizetendő mezőt, visszaállítva a teljes összeget
          document.getElementById("coupon-discount").innerText = '0 Ft';
          document.getElementById("payable-price").innerText = document.getElementById("total-price").innerText;
          localStorage.setItem('couponDiscount', 0);
      }
  })
  .catch(error => {
      console.error('Hiba a kupon ellenőrzése során:', error);
      showCartModal("Hiba történt a kupon beváltásakor.");
  });
});

// A kosár egyéb funkciói (például updateTotal) módosítása: 
// Frissítsük az updateTotal függvényt, hogy frissítse a fizetendő összeget is, ha nincs kupon beváltva.
function updateTotal() {
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  let total = cart.reduce((sum, item) => {
      return sum + (parseFloat(item.finalPrice) * item.quantity);
  }, 0);
  total = parseFloat(total.toFixed(0));
  document.getElementById('total-price').innerText = total + ' Ft';
  
  // Alapértelmezettként, ha nincs kupon, a fizetendő összeg egyenlő a teljes összeggel
  document.getElementById('payable-price').innerText = total + ' Ft';
}


function setupDetailsAnimation(containerSelector) {
  const detailSections = document.querySelectorAll(`${containerSelector} details`);

  detailSections.forEach(detail => {
      const summaryEl = detail.querySelector('summary');
      const ulEl = detail.querySelector('ul');

      // Inicializálás
      if (!detail.hasAttribute('open')) {
          ulEl.style.maxHeight = "0px";
      } else {
          ulEl.style.maxHeight = ulEl.scrollHeight + "px";
      }

      summaryEl.addEventListener('click', function (e) {
          e.preventDefault(); // Megakadályozzuk az alapértelmezett viselkedést

          if (detail.hasAttribute('open')) {
              // Zárás animáció
              ulEl.style.maxHeight = ulEl.scrollHeight + "px";
              ulEl.getBoundingClientRect(); // Force reflow
              ulEl.style.maxHeight = "0px";

              setTimeout(() => {
                  detail.removeAttribute('open');
                  ulEl.style.maxHeight = "";
              }, 500);
          } else {
              // Nyitás animáció
              detail.setAttribute('open', '');
              ulEl.style.maxHeight = "0px"; // Indulás nulláról
              ulEl.getBoundingClientRect(); // Force reflow
              ulEl.style.maxHeight = ulEl.scrollHeight + "px";
          }
      });
  });
}


// Oldal betöltésekor frissítjük a kosarat és a számlálót
document.addEventListener("DOMContentLoaded", () => {
  loadCart();
  updateCartCounter();
});


// a fájl tetején, a globális showCartModal után
const checkoutButton = document.getElementById("checkout-button");

// kattintás kezelése
checkoutButton.addEventListener("click", function(e) {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  if (cart.length === 0) {
    e.preventDefault();   // ne navigáljon el
    showCartModal("A kosár üres, előbb tegyél valamit a kosárba!");
  }
});
