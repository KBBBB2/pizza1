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
                  <strong>${item.name}</strong><br> 
                  Ár: ${item.finalPrice} Ft<br>
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


// Modal megjelenítése
function showCartModal(message) {
  let modal = document.getElementById('cart-modal');
  let modalMessage = document.getElementById('modal-message');

  modalMessage.innerText = message;
  modal.style.display = "block";


  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

  //automatikusan bezárja a modalt
  setTimeout(function () {
    modal.style.display = "none";
  }, 2000);
}


// Oldal betöltésekor automatikusan frissítjük a kosár számlálót és betöltjük a kosár tartalmát
document.addEventListener("DOMContentLoaded", () => {
  loadCart();
  updateCartCounter();
});
