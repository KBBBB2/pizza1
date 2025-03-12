<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /merged/view/customer/mainpage.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Coupon Kezelő</title>
  <style>
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
      padding: 5px;
    }
    #couponFormContainer {
      border: 1px solid #ccc;
      padding: 10px;
      margin-top: 20px;
      width: 300px;
    }
    /* Egyszerű stílus a kereső részhez */
    #couponSearchContainer {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <h1>Coupon lista</h1>
  <a href="account.html">account</a><br>
  <a href="coupon.php">coupon</a><br>
  <a href="menu.php">menü</a><br>
  <br>
  <a href="/merged/view/customer/mainpage.html">Customer nézet</a><br>
  <br>
  
  <!-- Kereső rész -->
  <div id="couponSearchContainer">
    <input type="text" id="couponSearchInput" placeholder="Keresés...">
    <button id="couponSearchBtn">Keresés</button>
  </div>
  
  <button id="addCouponBtn">Új Coupon hozzáadása</button>
  
  <table id="couponTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Code</th>
        <th>Discount Type</th>
        <th>Discount Value</th>
        <th>Expiration Date</th>
        <th>Is Active</th>
        <th>Műveletek</th>
      </tr>
    </thead>
    <tbody>
      <!-- Itt töltjük be dinamikusan a rekordokat -->
    </tbody>
  </table>

  <!-- Rejtett űrlap új coupon felviteléhez és szerkesztéséhez -->
  <div id="couponFormContainer" style="display: none;">
    <h2 id="formTitle">Coupon hozzáadása</h2>
    <form id="couponForm">
      <input type="hidden" id="couponId" name="id" value="">
      <label>
        Name:
        <input type="text" id="name" name="name" required>
      </label>
      <br>
      <label>
        Description:
        <textarea id="description" name="description" required></textarea>
      </label>
      <br>
      <label>
        Code:
        <input type="text" id="code" name="code" required>
      </label>
      <br>
      <label>
        Discount Type:
        <input type="text" id="discount_type" name="discount_type" required>
      </label>
      <br>
      <label>
        Discount Value:
        <input type="number" id="discount_value" name="discount_value" required>
      </label>
      <br>
      <label>
        Expiration Date:
        <input type="datetime-local" id="expiration_date" name="expiration_date" required>
      </label>
      <br>
      <label>
        Is Active:
        <select id="is_active" name="is_active">
          <option value="1">Aktív</option>
          <option value="0">Inaktív</option>
        </select>
      </label>
      <br><br>
      <button type="submit" id="saveBtn">Mentés</button>
      <button type="button" id="cancelBtn">Mégse</button>
    </form>
  </div>

  <script src="/merged/assets/js/admin/coupon.js"></script>
</body>
</html>
