<?php
session_start();

// Kezdeti kosár
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

    // Válasz visszaküldése JSON formátumban AJAX-hoz
    header('Content-Type: application/json');
    echo json_encode($_SESSION['cart']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Rendelés</title>
    <style>
        .menu-item {
            display: inline-block;
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .menu-item img {
            max-width: 100px;
            display: block;
            margin: 0 auto;
        }
        .menu-item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <main>
        <section class="menu">
            <!-- Menü elemek -->
            <?php
            $menuItems = [
                ['name' => 'Négy Sajtos Pizza', 'price' => 2700, 'image' => 'pizza images/cheese-pizza.png'],
                ['name' => 'Húsimádó Pizza', 'price' => 3200, 'image' => 'pizza images/meatlover-pizza.jpg'],
                ['name' => 'Vegetáriánus Pizza', 'price' => 2900, 'image' => 'pizza images/veggie-pizza.png'],
                ['name' => 'Négy Sajtos Pizza', 'price' => 2700, 'image' => 'pizza images/cheese-pizza.png'],
                ['name' => 'Húsimádó Pizza', 'price' => 3200, 'image' => 'pizza images/meatlover-pizza.jpg'],
                ['name' => 'Vegetáriánus Pizza', 'price' => 2900, 'image' => 'pizza images/pepperoni-pizza.png'],
                ['name' => 'Pepperoni pizza', 'price' => 3000, 'image' => 'pizza images/veggie-pizza.png'],
                ['name' => 'Margherita Pizza', 'price' => 2500, 'image' => 'pizza images/margeritha-pizza.jpg'],
                ['name' => 'Hawaii Pizza', 'price' => 3100, 'image' => 'pizza images/hawaii-pizza.jpg'],
                ['name' => 'BBQ csirke Pizza', 'price' => 3500, 'image' => 'pizza images/bbq-pizza.jpg'],
                ['name' => 'Csípős Pizza', 'price' => 2800, 'image' => 'pizza images/spicy-pizza.jpg'],
                ['name' => 'Tenger gyümölcsei Pizza', 'price' => 3700, 'image' => 'pizza images/seafood-pizza.jpg'],
                ['name' => 'Négy Sajtos Pizza', 'price' => 2700, 'image' => 'pizza images/cheese-pizza.png'],
                ['name' => 'Húsimádó Pizza', 'price' => 3200, 'image' => 'pizza images/meatlover-pizza.jpg'],
                ['name' => 'Vegetáriánus Pizza', 'price' => 2900, 'image' => 'pizza images/pepperoni-pizza.png'],
                ['name' => 'Pepperoni pizza', 'price' => 3000, 'image' => 'pizza images/veggie-pizza.png'],
                ['name' => 'Margherita Pizza', 'price' => 2500, 'image' => 'pizza images/margeritha-pizza.jpg'],
                ['name' => 'Hawaii Pizza', 'price' => 3100, 'image' => 'pizza images/hawaii-pizza.jpg'],
                ['name' => 'BBQ csirke Pizza', 'price' => 3500, 'image' => 'pizza images/bbq-pizza.jpg'],
                ['name' => 'Csípős Pizza', 'price' => 2800, 'image' => 'pizza images/spicy-pizza.jpg'],
                ['name' => 'Tenger gyümölcsei Pizza', 'price' => 3700, 'image' => 'pizza images/seafood-pizza.jpg'],
                ['name' => 'Négy Sajtos Pizza', 'price' => 2700, 'image' => 'pizza images/cheese-pizza.png'],
                ['name' => 'Húsimádó Pizza', 'price' => 3200, 'image' => 'pizza images/meatlover-pizza.jpg'],
                ['name' => 'Vegetáriánus Pizza', 'price' => 2900, 'image' => 'pizza images/pepperoni-pizza.png'],
                ['name' => 'Pepperoni pizza', 'price' => 3000, 'image' => 'pizza images/veggie-pizza.png'],
                ['name' => 'Margherita Pizza', 'price' => 2500, 'image' => 'pizza images/margeritha-pizza.jpg'],
                ['name' => 'Hawaii Pizza', 'price' => 3100, 'image' => 'pizza images/hawaii-pizza.jpg'],
                ['name' => 'BBQ csirke Pizza', 'price' => 3500, 'image' => 'pizza images/bbq-pizza.jpg'],
                ['name' => 'Csípős Pizza', 'price' => 2800, 'image' => 'pizza images/spicy-pizza.jpg'],
                ['name' => 'Tenger gyümölcsei Pizza', 'price' => 3700, 'image' => 'pizza images/seafood-pizza.jpg'],
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
                echo '<form method="POST" class="pizza-form">';
                echo '<div class="menu-item">';
                echo '<img src="' . $item['image'] . '" alt="' . $item['name'] . '">';
                echo '<h3>' . $item['name'] . '</h3>';
                echo '<p>' . $item['price'] . ' Ft</p>';
                echo '<input type="hidden" name="pizza_name" value="' . $item['name'] . '">';
                echo '<input type="hidden" name="pizza_price" value="' . $item['price'] . '">';
                echo '</div>';
                echo '</form>';
            }
            ?>
        </section>
    </main>

    <section class="cart">
        <h2>Kosár</h2>
        <ul id="cart-list">
            <?php
            foreach ($_SESSION['cart'] as $item) {
                echo '<li>' . $item['name'] . ' - ' . $item['price'] . ' Ft</li>';
            }
            ?>
        </ul>
    </section>

    <script>
        // Form elküldés alapértelmezett működésének megakadályozása
        document.addEventListener("DOMContentLoaded", () => {
            const forms = document.querySelectorAll(".pizza-form");
            const cartList = document.getElementById("cart-list");

            forms.forEach((form) => {
                form.addEventListener("click", (e) => {
                    e.preventDefault(); // Ne töltsön újra
                    const formData = new FormData(form);

                    // Küldjük el az adatokat AJAX-szal PHP-hoz
                    fetch("", {
                        method: "POST",
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((cart) => {
                            // Kosár frissítése az új adatokkal
                            cartList.innerHTML = ""; // Töröljük a jelenlegi listát
                            cart.forEach((item) => {
                                const li = document.createElement("li");
                                li.textContent = `${item.name} - ${item.price} Ft`;
                                cartList.appendChild(li);
                            });
                            
                        })
                        .catch((error) => console.error("Hiba történt:", error));
                });
            });
        });
    </script>
</body>
</html>
