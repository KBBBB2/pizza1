<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /merged/view/customer/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Pizza Adatbázis</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }
    table, th, td {
      border: 1px solid #ddd;
    }
    th, td {
      padding: 8px;
    }
    .edit-input {
      width: 90%;
    }
    #add-new {
      margin-bottom: 10px;
    }

    
  </style>
</head>
<body>
  <h1>Pizza Adatbázis</h1>
  <a href="account.html">account</a><br>
  <a href="coupon.html">coupon</a><br>
  <a href="menu.html">menü</a><br>
  <br>
  <a href="/merged/view/customer/mainpage.html">Customer nézet</a><br>
  <br>

  <!-- Kereső rész -->
  <input type="text" id="search" placeholder="Keresés...">
  <button id="searchBtn">Keresés</button>
  <br><br>
  
  <!-- Új rekord hozzáadásához -->
  <button id="add-new">Új hozzáadása</button>
  
  <table id="pizzaTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Név</th>
        <th>Tészta</th>
        <th>Vágás módja</th>
        <th>Méret</th>
        <th>Összetevők</th>
        <th>Ár</th>
        <th>Műveletek</th>
        <th>Kép</th>
      </tr>
    </thead>
    <tbody>
      <!-- Itt töltjük be az adatokat -->
    </tbody>
  </table>

  <script src="/merged/assets/js/admin/pizza.js"></script>
</body>
</html>
