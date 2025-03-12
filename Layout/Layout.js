document.addEventListener("DOMContentLoaded", function () {
    fetch("/merged/Layout/Layout.html")
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

                // Megkeressük a kosár elemet, hogy előtte szúrjuk be az új linkeket
                var cartElement = headerRight.querySelector('a.cart-button');

                // Dokumentum fragment segítségével építjük fel a kívánt sorrendet
                var frag = document.createDocumentFragment();

                // Ha admin a felhasználó, beszúrjuk az Admin linket
                if (userRole === 'admin') {
                    var adminLink = document.createElement('a');
                    adminLink.href = "/merged/view/admin/menu.html";
                    adminLink.className = "button";
                    adminLink.style.textDecoration = "none";
                    adminLink.innerHTML = "<b>Admin</b>";
                    frag.appendChild(adminLink);
                }

                // Kijelentkezés link létrehozása
                var logoutLink = document.createElement('a');
                logoutLink.href = "#";
                logoutLink.className = "button";
                logoutLink.style.textDecoration = "none";
                logoutLink.innerHTML = "<b>Kijelentkezés</b>";
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Töröljük a felhasználói adatokat
                    localStorage.removeItem("userRole");
                    // Töröljük a kosár adatait is
                    localStorage.removeItem("cart");
                    // További tárolt adatok törlése szükség esetén
                    window.location.href = "login.html";
                });
                frag.appendChild(logoutLink);

                // User icon link létrehozása, amely a user.html-re navigál
                var userIconLink = document.createElement('a');
                userIconLink.href = "user.html";
                userIconLink.style.textDecoration = "none";
                // Létrehozunk egy <img> elemet a user ikonhoz
                var userImg = document.createElement('img');
                userImg.src = "/merged/Layout/design images/user-icon.png";
                userImg.alt = "User";
                userImg.style.width = "55px";   // igény szerint módosítható
                userImg.style.height = "55px";
                // Az <img> elem beillesztése az <a> tag-be
                userIconLink.appendChild(userImg);
                frag.appendChild(userIconLink);

                // A fragmentet beszúrjuk a kosár link elé
                headerRight.insertBefore(frag, cartElement);
            }
        })
        .catch(error => console.error("Hiba történt a layout betöltésekor:", error));

    window.addEventListener('resize', adjustPizzaImageSize);
    window.addEventListener('load', adjustPizzaImageSize); 

    function adjustPizzaImageSize() {
    const pizzaImg = document.querySelector('.pizza-image'); 
    const windowWidth = window.innerWidth;

    const maxWidth = 500;
    const minWidth = 200;

    let newWidth = (windowWidth / 1920) * maxWidth; 

    newWidth = Math.max(minWidth, Math.min(newWidth, maxWidth));

    pizzaImg.style.width = `${newWidth}px`;
    pizzaImg.style.height = 'auto';
    }

    window.addEventListener('resize', function() {
        const pizzaImg = document.querySelector('.pizza-image');
        if (window.innerWidth <= 900) {
            pizzaImg.style.display = 'none';
        } else {
            pizzaImg.style.display = 'block'; 
        }
    });
    
});
