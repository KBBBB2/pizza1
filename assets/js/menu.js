// Pizzák lekérése az API-ból és megjelenítése a menüben
function loadPizzas() {
  fetch('/merged/Controller/menu.php')
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        document.getElementById('menu-list').innerText = "Hiba: " + data.error;
        return;
      }
      
      let pizzas = data.data;
      if (pizzas.length === 0) {
        document.getElementById('menu-list').innerText = "Nincs megjelenítendő pizza.";
        return;
      }
        
      let html = '';
      pizzas.forEach(pizza => {
        let imagePath = `/merged/assets/images/${pizza.id}/pizza_${pizza.id}.jpg`;
        
        // Ár megjelenítése: ha van discounted_price, akkor az akciós árat jelenítjük meg
        let priceHtml = '';
        if (pizza.discounted_price != null) {
          priceHtml = `<p class="card-text"><s class="original">${pizza.price} Ft</s> <br> ${pizza.discounted_price} Ft</p>`;
        } else {
          priceHtml = `<p class="card-text">${pizza.price} Ft</p>`;
        }

        html += `<div class="col-md-4 col-sm-6">
                    <div class="card">
                        <img src="${imagePath}" class="card-img-top"  alt="${pizza.name}"> 
                        <div class="card-body text-center">
                            <h5 class="card-title">${pizza.name}</h5>
                            ${priceHtml}
                            <details>
                            
                            <summary class="summary">Összetevők</summary>
                            <ul>
                                <li><b>Tészta: </b>${pizza.crust}<br></li>
                                <li><b>Szeletelés: </b>${pizza.cutstyle}<br></li>
                                <li><b>Méret: </b>${pizza.pizzasize}<br></li>
                                <li><b>Hozzávalók: </b>${pizza.ingredient}<br></li>
                            </ul>
                        </details>
                        Mennyiség: <input type="number" id="qty-${pizza.id}" value="1" min="1"><br>
                        <img src="/merged/layout/design images/shopping-cart.png" class="cart-img"onclick='addToCart(${JSON.stringify(pizza)})'>
                        </div>
                    </div>
                  </div>`;
      });

      document.getElementById('menu-list').innerHTML = html;
    })
    .catch(error => {
      document.getElementById('menu-list').innerText = "Hiba történt az adatok betöltése során.";
      console.error('Hiba:', error);
    });
}

// A pizza kosárba helyezése a megadott mennyiséggel
function addToCart(pizza) {
  let qtyInput = document.getElementById('qty-' + pizza.id);
  let qty = parseInt(qtyInput.value);
  if (isNaN(qty) || qty < 1) { qty = 1; }

  // A kosár tartalmát a localStorage-ban tároljuk, alapértelmezetten üres tömb
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  
  // Ha a pizza már szerepel a kosárban, növeljük a mennyiséget
  let existingItem = cart.find(item => item.id === pizza.id);
  if (existingItem) {
    existingItem.quantity += qty;
  } else {
    // Új elem esetén hozzárendeljük a quantity tulajdonságot
    pizza.quantity = qty;
    cart.push(pizza);
  }
  localStorage.setItem('cart', JSON.stringify(cart));
  alert("Pizza kosárba helyezve!");
}

document.addEventListener("DOMContentLoaded", loadPizzas);
