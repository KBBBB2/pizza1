document.addEventListener("DOMContentLoaded", function() {
    // Betöltjük az oldal tartalmát
    fetch("/Layout/Layout.html")
        .then(response => response.text())
        .then(data => {
            document.getElementById("layout-container").innerHTML = data;

            // Ellenőrizzük, hogy van-e bejelentkezett felhasználó (userRole tárolva)
            var userRole = localStorage.getItem("userRole");
            if (userRole) {
                var headerRight = document.querySelector('.header-right');

                // Töröljük a bejelentkezés és regisztráció linkeket
                var loginLink = headerRight.querySelector('a[href="login.html"]');
                var regLink = headerRight.querySelector('a[href="reg.html"]');
                if (loginLink) loginLink.remove();
                if (regLink) regLink.remove();

                // Megkeressük a kosár elemet
                var cartElement = headerRight.querySelector('a.cart-button');

                // Kosár darabszámláló hozzáadása
                var cartCounter = document.createElement('span');
                cartCounter.id = "cart-counter";
                cartCounter.style.background = "red";
                cartCounter.style.color = "white";
                cartCounter.style.borderRadius = "50%";
                cartCounter.style.padding = "3px 7px";
                cartCounter.style.marginLeft = "5px";
                cartCounter.style.fontSize = "14px";
                cartCounter.style.display = "none"; // Alapból rejtett
                cartElement.appendChild(cartCounter);

                // Dokumentum fragment létrehozása a további linkekhez
                var frag = document.createDocumentFragment();

                // Ha admin a felhasználó, beszúrjuk az Admin linket
                if (userRole === 'admin') {
                    var adminLink = document.createElement('a');
                    adminLink.href = "/view/admin/menu.html";
                    adminLink.className = "button";
                    adminLink.style.textDecoration = "none";
                    adminLink.innerHTML = "Admin";
                    frag.appendChild(adminLink);
                }

                // Kijelentkezés link
                var logoutLink = document.createElement('a');
                logoutLink.href = "#";
                logoutLink.className = "button";
                logoutLink.style.textDecoration = "none";
                logoutLink.innerHTML = "Kijelentkezés";
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    localStorage.removeItem("userRole");
                    localStorage.removeItem("cart");
                    localStorage.removeItem('token');
                    window.location.href = "login.html";
                });
                frag.appendChild(logoutLink);

                // User ikon hozzáadása
                var userIconLink = document.createElement('a');
                userIconLink.href = "/view/customer/user.html";
                userIconLink.style.textDecoration = "none";
                var userImg = document.createElement('img');
                userImg.src = "/Layout/design images/user-icon.png";
                userImg.alt = "User";
                userImg.style.width = "55px";
                userImg.style.height = "55px";
                userIconLink.appendChild(userImg);
                frag.appendChild(userIconLink);

                // Linkek beszúrása a header-be
                headerRight.insertBefore(frag, cartElement);
            }

            // Kosár számláló frissítése betöltéskor
            updateCartCounter();
        })
        .catch(error => console.error("Hiba történt a layout betöltésekor:", error));

    window.addEventListener('resize', adjustPizzaImageSize);
    window.addEventListener('load', adjustPizzaImageSize);

    function adjustPizzaImageSize() {
        const pizzaImg = document.querySelector('.pizza-image');
        if (!pizzaImg) return;
        const windowWidth = window.innerWidth;
        const maxWidth = 510;
        const minWidth = 200;
        let newWidth = (windowWidth / 1920) * maxWidth;
        newWidth = Math.max(minWidth, Math.min(newWidth, maxWidth));
        pizzaImg.style.width = `${newWidth}px`;
        pizzaImg.style.height = 'auto';
    }

    window.addEventListener('resize', function() {
        const pizzaImg = document.querySelector('.pizza-image');
        if (!pizzaImg) return;
        if (window.innerWidth <= 900) {
            pizzaImg.style.display = 'none';
        } else {
            pizzaImg.style.display = 'block';
        }
    });

    // Kosár darabszámláló frissítése
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

    // Ez biztosítja, hogy a kosár frissítésénél a számláló is frissüljön
    document.addEventListener('cartUpdated', function() {
        updateCartCounter();
    });

});
