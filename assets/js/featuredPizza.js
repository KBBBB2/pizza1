document.addEventListener("DOMContentLoaded", function () {
    // Az endpoint elérési útja – ellenőrizd, hogy helyes legyen!
    const apiUrl = "/merged/controller/feateruredPizza.php";

    fetch(apiUrl)
      .then(response => {
          if (!response.ok) {
              throw new Error("HTTP error: " + response.status);
          }
          return response.json();
      })
      .then(data => {
          renderFeaturedPizzas(data);
      })
      .catch(error => {
          console.error("Hiba az adatok lekérésekor:", error);
      });
});

function renderFeaturedPizzas(pizzas) {
    const container = document.getElementById("featured-pizzas");
    if (!container) return;

    let html = "";
    pizzas.forEach(pizza => {
        // Biztosítjuk, hogy a kép URL ne legyen undefined
        const imageUrl = `/merged/assets/images/${pizza.pizza_id}/pizza_${pizza.pizza_id}.jpg`;

        html += `
            <div class="col-md-4 col-sm-6">
                <div class="card">
                    <img src="${imageUrl}" class="card-img-top" alt="${pizza.name}">
                    <div class="card-body text-center">
                        <h5 class="card-title">${pizza.name}</h5>
                        <s class="card-text original">${pizza.price} Ft</s>
                        <p class="card-text">${pizza.discounted_price} Ft</p>
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
            </div>
        `;
    });
    container.innerHTML = html;
}
  // A pizza kosárba helyezése a megadott mennyiséggel
  function addToCart(pizza) {
    let qty = 1;

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