// Egyszerű értesítő megjelenítése az oldalon (3 másodpercig)
function showNotification(message) {
  const notification = document.getElementById("notification");
  notification.textContent = message;
  notification.style.display = "block";
  setTimeout(() => {
    notification.style.display = "none";
  }, 3000);
}

// Megjeleníti a kilépés megerősítő felugrót
function showLogoutConfirm() {
  document.getElementById('confirm-overlay').style.display = 'block';
  document.getElementById('confirm-popup').style.display = 'block';
}

// Elrejti a kilépés megerősítőt
function hideLogoutConfirm() {
  document.getElementById('confirm-overlay').style.display = 'none';
  document.getElementById('confirm-popup').style.display = 'none';
}

// Az oldal betöltésekor feltöltjük az adatokat a localStorage-ban eltárolt rendelésből
function populateOrderDetails() {
  const selectedOrder = localStorage.getItem("selectedOrder");
  if (selectedOrder) {
    const order = JSON.parse(selectedOrder);
    document.getElementById("order-id").textContent = order.id || "";
    document.getElementById("order-address").textContent = order.address || "";
    document.getElementById("order-phone").textContent = order.phonenumber || "";
  } else {
    showNotification("Nincs megjeleníthető rendelés adat!");
  }
}

// Rendelés leadása: a status-t "delivered"-re állítjuk
function completeOrder() {
  const selectedOrder = localStorage.getItem("selectedOrder");
  if (!selectedOrder) {
    showNotification("Nincs rendelés kiválasztva!");
    return;
  }
  const order = JSON.parse(selectedOrder);
  const token = localStorage.getItem("token");
  fetch('http://localhost/backend/public/delivery.php?action=update', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
      delivery_id: order.id,
      status: 'delivered'
    })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error("Hibás válasz a szervertől");
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showNotification("Rendelés leadva!");
      localStorage.removeItem("selectedOrder");
      window.location.href = "deliveryperson.html";
    } else {
      showNotification("Hiba történt: " + data.error);
    }
  })
  .catch(err => {
    console.error("Hiba a rendelés leadásánál:", err);
    showNotification("Hiba történt a rendelés leadásánál!");
  });
}

// DOMContentLoaded esemény: listener-ek és adatbetöltés
document.addEventListener('DOMContentLoaded', () => {
  // Rendelés adatainak betöltése
  populateOrderDetails();

  // Rendelés leadása gomb
  document.getElementById('completeOrderBtn').addEventListener('click', completeOrder);

  // Kijelentkezés gomb és megerősítő felugró
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', e => {
    e.preventDefault();
    showLogoutConfirm();
  });

  document.getElementById('confirm-yes').addEventListener('click', () => {
    localStorage.removeItem('userRole');
    localStorage.removeItem('cart');
    localStorage.removeItem('token');
    window.location.href = '/view/customer/Login.html';
  });

  document.getElementById('confirm-no').addEventListener('click', hideLogoutConfirm);
});
