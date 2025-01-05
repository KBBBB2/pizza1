document.addEventListener("DOMContentLoaded", () => {
    const cartButton = document.querySelector(".cart-button");
    const cartModal = document.getElementById("cart-modal");
    const closeModal = document.querySelector(".close-modal");
    const cartList = document.getElementById("cart-list");
    const menuItems = document.querySelectorAll(".menu-item");

    // Kosár tartalmának betöltése a szerverről
    function loadCart() {
        fetch("", {
            method: "GET", // A GET kérés lekéri a kosár aktuális tartalmát
        })
            .then((response) => response.json())
            .then((cart) => {
                updateCartList(cart); // Frissítsük a kosár tartalmát
            })
            .catch((error) => console.error("Hiba történt:", error));
    }

    // Modal megnyitása és kosár betöltése
    cartButton.addEventListener("click", () => {
        cartModal.style.display = "block";
        loadCart(); // Kosár tartalmának betöltése
    });

    // Modal bezárása
    closeModal.addEventListener("click", () => {
        cartModal.style.display = "none";
    });

    // Bezárás, ha a modal háttérre kattintunk
    window.addEventListener("click", (event) => {
        if (event.target === cartModal) {
            cartModal.style.display = "none";
        }
    });

    // Pizza hozzáadása a kosárhoz
    menuItems.forEach((item) => {
        item.addEventListener("click", () => {
            const pizzaName = item.querySelector("h3").innerText;
            const pizzaPrice = item.querySelector("p").innerText;

            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ pizza_name: pizzaName, pizza_price: pizzaPrice }),
            })
                .then((response) => response.json())
                .then((cart) => {
                    updateCartList(cart); // Kosár frissítése, ha új elem került bele
                })
                .catch((error) => console.error("Hiba történt:", error));
        });
    });

    // Kosár törlése
    document.getElementById("clear-cart").addEventListener("click", () => {
        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: 'clear' })
        })
            .then((response) => response.json())
            .then(() => {
                updateCartList([]); // Kosár kiürítése
            })
            .catch((error) => console.error("Hiba történt:", error));
    });

    // Kosár frissítése
    function updateCartList(cart) {
        cartList.innerHTML = "";
        if (cart.length > 0) {
            cart.forEach((item) => {
                const li = document.createElement("li");
                li.innerHTML = `${item.name} - ${item.price} Ft`;
                cartList.appendChild(li);
            });
        } else {
            cartList.innerHTML = "<li>A kosár üres.</li>";
        }
    }
});
