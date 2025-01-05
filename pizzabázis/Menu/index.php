<?php
session_start();

// Kezdeti kosár beállítása
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Új elem hozzáadása a kosárhoz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pizza_name']) && isset($_POST['pizza_price'])) {
    $pizza = [
        'name' => $_POST['pizza_name'],
        'price' => $_POST['pizza_price'],
    ];
    $_SESSION['cart'][] = $pizza;

    // Kosár frissítése válaszként JSON-ben
    header('Content-Type: application/json');
    echo json_encode($_SESSION['cart']);
    exit;
}

// Kosár törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'clear') {
    $_SESSION['cart'] = []; // Kosár kiürítése
    echo json_encode([]);
    exit;
}

?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const cartButton = document.querySelector(".cart-button");
    const cartModal = document.getElementById("cart-modal");
    const closeModal = document.querySelector(".close-modal");

    // Modal megnyitása
    cartButton.addEventListener("click", () => {
        
        if (cartModal.style.display === "none") {
            cartModal.style.display = "block";
        } else {
            cartModal.style.display = "none";
        }

    });

    // Modal bezárása
    closeModal.addEventListener("click", () => {
        cartModal.style.display = "none";
    });

    

    // További funkciók, pl. a kosár frissítése
    const cartList = document.getElementById("cart-list");
    const menuItems = document.querySelectorAll(".menu-item");

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
                    updateCartList(cart); // Kosár frissítése
                    cartModal.style.display = "block";
                })
                .catch((error) => console.error("Hiba történt:", error));
        });
    });

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

    function updateCartList(cart) {
        cartList.innerHTML = "";
        if (cart.length > 0) {
            cart.forEach((item) => {
                const li = document.createElement("li");
                li.innerHTML = `${item.name} - ${item.price} Ft`;
                cartList.appendChild(li);
            });
        }
    }
});

</script>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Menü</title>
</head>
<body>
    <!-- Fejléc -->
    <header>
        <div class="header-left">
            <img src="design images/pizza_logo.png" alt="Pizza Logo">
        </div>
        <div class="header-right">
            <button type="submit" class="userSettings-button">
                <img src="design images/user-icon.png" alt="User Icon" width="50" height="50">
            </button>
            <button type="submit" class="cart-button">
                <img src="design images/shopping-cart.png" alt="Shopping Cart" width="50" height="50">
            </button>
        </div>
    </header>

    <main>

        <div id="cart-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Kosár</h2>
                <ul id="cart-list">
                    <!-- Kosár elemek itt jelennek meg -->
                </ul>
                <button id="clear-cart">Kosár törlése</button>
            </div>
        </div>
        <section class="menu">
            <!-- Menü elemek -->
            <?php
            $menuItems = [
                ['name' => 'Négy Sajtos Pizza', 'price' => 2700, 'image' => 'pizza images/cheese-pizza.png'],
                ['name' => 'Húsimádó Pizza', 'price' => 3200, 'image' => 'pizza images/meatlover-pizza.jpg'],
                ['name' => 'Vegetáriánus Pizza', 'price' => 2900, 'image' => 'pizza images/pepperoni-pizza.png'],
                ['name' => 'Pepperoni pizza', 'price' => 3000, 'image' => 'pizza images/veggie-pizza.png'],
                ['name' => 'Margherita Pizza', 'price' => 2500, 'image' => 'pizza images/margeritha-pizza.jpg'],
                ['name' => 'Hawaii Pizza', 'price' => 3100, 'image' => 'pizza images/hawaii-pizza.jpg'],
                ['name' => 'BBQ csirke Pizza', 'price' => 3500, 'image' => 'pizza images/bbq-pizza.jpg'],
                ['name' => 'Csípős Pizza', 'price' => 2800, 'image' => 'pizza images/spicy-pizza.jpg'],
                ['name' => 'Tenger gyümölcsei Pizza', 'price' => 3700, 'image' => 'pizza images/seafood-pizza.jpg'],
                // Add hozzá a többi pizza adatát...
            ];

            foreach ($menuItems as $item) {
                echo '<div class="menu-item" data-name="' . $item['name'] . '" data-price="' . $item['price'] . '">';
                echo '<img src="' . $item['image'] . '" alt="' . $item['name'] . '">';
                echo '<h3>' . $item['name'] . '</h3>';
                echo '<p>' . $item['price'] . ' Ft</p>';
                echo '</div>';
            }
            ?>
        </section>
    </main>
</body>
<!--
<section class="cart">
    <h2>Kosár</h2>
    <ul id="cart-list">-->
        <!-- Kosár elemek itt jelennek meg --><!--
    </ul>
    <button id="clear-cart">Kosár törlése</button>
</section>
-->






<footer> </footer>

</html>
