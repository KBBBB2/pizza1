 // A kosár tartalmának betöltése a localStorage-ból és megjelenítése
 function loadCart() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if(cart.length === 0) {
      document.getElementById('cart-list').innerHTML = "A kosár üres.";
      updateTotal();
      return;
    }
    
    let html = '<ul>';
    cart.forEach((item) => {
      html += `<li class="cart-summary">
                <strong>${item.name}</strong><br> 
                Ár: ${item.price} Ft<br>
                Mennyiség: <input type="number" id="cart-qty-${item.id}" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, this.value)">
                <button onclick="removeFromCart(${item.id})">Eltávolít</button>
                                
              </li>`;
    });
    html += '</ul>';
    document.getElementById('cart-list').innerHTML = html;
    updateTotal();
  }

  // Mennyiség módosítása a kosárban (és az összeg frissítése)
  function updateQuantity(id, newQty) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    newQty = parseInt(newQty);
    if (isNaN(newQty) || newQty < 1) { newQty = 1; }
    cart.forEach(item => {
    if (Number(item.id) === Number(id)) {
      item.quantity = newQty;
      }
      });

    localStorage.setItem('cart', JSON.stringify(cart));
    updateTotal();
  }

  // Tétel eltávolítása a kosárból
  function removeFromCart(id) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.filter(item => Number(item.id) !== Number(id));
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
  }

  // Összesítés: kiszámolja a kosárban lévő tételek árát a mennyiségek figyelembevételével
  function updateTotal() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let total = cart.reduce((sum, item) => {
      return sum + (parseFloat(item.price) * item.quantity);
    }, 0);
    document.getElementById('total-price').innerText = total.toFixed(0) + ' Ft';
  }

  document.addEventListener("DOMContentLoaded", loadCart);