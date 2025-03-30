function loadFeaturedPizzas() {
    fetch('/merged/Controller/menu.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('featured-pizzas').innerText = "Hiba: " + data.error;
                return;
            }

            let pizzas = data.data.slice(0, 3);
            let html = '';
            pizzas.forEach(pizza => {
                let imagePath = `/merged/assets/images/${pizza.id}/pizza_${pizza.id}.jpg`;

                let priceHtml = pizza.discounted_price != null
                    ? `<p class="card-text"><span class="strikethrough-price">${pizza.price} Ft</span> <br> ${pizza.discounted_price} Ft</p>`
                    : `<p class="card-text">${pizza.price} Ft</p>`;

                html += `<div class="col-md-4">
                            <div class="card border-0 rounded-lg shadow-sm">
                                <img src="${imagePath}" class="card-img-top" alt="${pizza.name}">
                                <div class="card-body text-center">
                                    <h5 class="card-title">${pizza.name}</h5>
                                    ${priceHtml}
                                    <details>
                                        <summary>Összetevők</summary>
                                        <ul>
                                            <li><b>Tészta:</b> ${pizza.crust}</li>
                                            <li><b>Szeletelés:</b> ${pizza.cutstyle}</li>
                                            <li><b>Méret:</b> ${pizza.pizzasize}</li>
                                            <li><b>Hozzávalók:</b> ${pizza.ingredient}</li>
                                        </ul>
                                    </details>

                                    <!-- Kosár gomb és mennyiségmező középen -->
                                    <div class="d-flex justify-content-center align-items-center mt-3">
                                        <input type="number" id="qty-${pizza.id}" value="1" min="1" class="form-control" style="background-color: #fff3d7; width: 60px; text-align: center;">
                                        <img src="/merged/layout/design images/shopping-cart.png" class="cart-img ms-3" onclick='addToCart(${JSON.stringify(pizza)})' style="cursor: pointer; width: 50px; height: 50px;">
                                    </div>
                                </div>
                            </div>
                          </div>`;
            });
            document.getElementById('featured-pizzas').innerHTML = html;
        });
}

document.addEventListener("DOMContentLoaded", loadFeaturedPizzas);
