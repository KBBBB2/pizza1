// Egyszerű értesítő üzenet megjelenítése az oldalra (3 másodpercig)
function showNotification(message) {
  const notification = document.getElementById("notification");
  notification.textContent = message;
  notification.style.display = "block";
  setTimeout(() => {
    notification.style.display = "none";
  }, 3000);
}

// logout confirm megjelenítése/elrejtése
function showLogoutConfirm() {
  document.getElementById('confirm-overlay').style.display = 'block';
  document.getElementById('confirm-popup').style.display   = 'block';
}

function hideLogoutConfirm() {
  document.getElementById('confirm-overlay').style.display = 'none';
  document.getElementById('confirm-popup').style.display   = 'none';
}

// Rendelések betöltése (csak pending státuszú rendeléseket jelenítünk meg)
function loadOrders() {
  const token = localStorage.getItem("token");
  fetch('http://localhost/backend/public/delivery.php?action=readall', {
    headers: { 'Authorization': 'Bearer ' + token }
  })
  .then(response => {
    if (!response.ok) throw new Error("Hibás válasz a szervertől");
    return response.json();
  })
  .then(data => {
    if (data.success) {
      const pendingOrders = data.deliveries.filter(o => o.status === 'pending');
      const tableBody = document.getElementById('ordersTableBody');
      tableBody.innerHTML = "";
      pendingOrders.forEach(order => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${order.id}</td>
          <td>${order.address}</td>
          <td>${order.phonenumber}</td>
          <td><button class="button">Rendelés felvétele</button></td>
        `;
        tr.querySelector('button').addEventListener('click', () => {
          localStorage.setItem('selectedOrder', JSON.stringify(order));
          pickupOrder(order.id);
        });
        tableBody.appendChild(tr);
      });
    } else {
      showNotification("Hiba az adatok betöltésekor: " + data.error);
    }
  })
  .catch(err => {
    console.error("Hiba a kérések során:", err);
    showNotification("Hiba történt az adatok betöltésekor!");
  });
}

// Rendelés felvétele: a status "in transit"-re módosítása
function pickupOrder(orderId) {
  const token = localStorage.getItem("token");
  fetch('http://localhost/backend/public/delivery.php?action=update', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({ delivery_id: orderId, status: 'in transit' })
  })
  .then(response => {
    if (!response.ok) throw new Error("Hibás válasz a szervertől");
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showNotification("Rendelés felvéve! Átlépünk a részletek oldalra.");
      window.location.href = "delivery.html";
    } else {
      showNotification("Hiba történt: " + data.error);
    }
  })
  .catch(err => {
    console.error("Hiba az order felvételnél:", err);
    showNotification("Hiba történt a rendelés felvételénél!");
  });
}

// Minden DOM elem létrejötte után kötjük a listener-eket
document.addEventListener('DOMContentLoaded', () => {
  // Kijelentkezés gomb
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', e => {
    e.preventDefault();
    showLogoutConfirm();
  });

  // Confirm popup gombjai
  document.getElementById('confirm-yes').addEventListener('click', () => {
    localStorage.removeItem('userRole');
    localStorage.removeItem('cart');
    localStorage.removeItem('token');
    window.location.href = '/view/customer/Login.html';
  });
  document.getElementById('confirm-no').addEventListener('click', () => {
    hideLogoutConfirm();
  });

  // Rendelések betöltése
  loadOrders();
});
