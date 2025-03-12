<?php
// Állítsuk be a megfelelő header-t JSON válaszhoz
header('Content-Type: application/json');

require_once '../model/Pizza.php';

$pizzaModel = new Pizza();
$featuredPizzas = $pizzaModel->getFeaturedPizzas();

// JSON-ként visszaküldjük az adatokat
echo json_encode($featuredPizzas);
?>
