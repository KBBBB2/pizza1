// Funkció: Intersection Observer beállítása a fade-in elemekhez
function observeFadeIn() {
    const faders = document.querySelectorAll('.fade-in');

    const options = {
        threshold: 0.1  // Az elem 10%-ának látszódnia kell a viewportban
    };

    const appearOnScroll = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Ha az elem a viewportba érkezik, hozzáadjuk a visible osztályt
                entry.target.classList.add('visible');
            } else {
                // Ha az elem elhagyja a viewportot, eltávolítjuk a visible osztályt
                entry.target.classList.remove('visible');
            }
        });
    }, options);

    faders.forEach(fader => {
        appearOnScroll.observe(fader);
    });
}

function loadFeaturedPizzas() {
    fetch('http://localhost/backend/public/menu.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('featured-pizzas').innerText = "Hiba: " + data.error;
                return;
            }

            let pizzas = data.data.slice(0, 3);
            let html = '';
            pizzas.forEach(pizza => {
                const imagePath = pizza.image;

                let priceHtml = pizza.discounted_price != null
                    ? `<p class="card-text"><span class="strikethrough-price">${pizza.price} Ft</span> <br> ${pizza.discounted_price} Ft</p>`
                    : `<p class="card-text">${pizza.price} Ft</p>`;

                html += `<div class="col-md-3 fade-in">
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
                                        <img src="/layout/design images/shopping-cart.png" class="cart-img ms-3" onclick='addToCart(${JSON.stringify(pizza)})' style="cursor: pointer; width: 50px; height: 50px;">
                                    </div>
                                </div>
                            </div>
                          </div>`;
            });
            document.getElementById('featured-pizzas').innerHTML = html;
            observeFadeIn(); // Aktiváljuk a fade-in-t
            setupDetailsAnimation('#featured-pizzas'); // Hozzáadjuk az animációt a <details>-hez            
        });
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


document.addEventListener("DOMContentLoaded", loadFeaturedPizzas);
