


// Global változók
let originalData = [];
let couponTypes = [];

// Modal bezárása
function closeModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.classList.remove('centered-modal');
}

// Kupon szerkesztése
function editCoupon(couponId) {
    const row = document.querySelector(`tr[data-id='${couponId}']`);
    const columns = row.querySelectorAll('td');

    originalData = {
        id: couponId,
        name: columns[1].textContent,
        description: columns[2].textContent,
        code: columns[3].textContent,
        discount_type: columns[4].textContent,
        discount_value: columns[5].textContent,
        expiration_date: columns[6].textContent,
        is_active: columns[7].textContent === "Aktív" ? 1 : 0
    };

    document.getElementById('editName').value = columns[1].textContent;
    document.getElementById('editDescription').value = columns[2].textContent;
    document.getElementById('editCode').value = columns[3].textContent;
    document.getElementById('editDiscountType').value = columns[4].textContent;
    document.getElementById('editDiscountValue').value = columns[5].textContent;
    document.getElementById('editExpirationDate').value = columns[6].textContent;
    document.getElementById('editIsActive').value = columns[7].textContent === "Aktív" ? 1 : 0;

    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    modal.classList.add('centered-modal');

    document.getElementById('editCouponForm').dataset.couponId = couponId;
}

// Kupon mentése
function saveCoupon() {
    // Lekérjük a mezők értékeit
    const name = document.getElementById('editName').value.trim();
    const description = document.getElementById('editDescription').value.trim();
    const code = document.getElementById('editCode').value.trim();
    const discountType = document.getElementById('editDiscountType').value.trim();
    const discountValue = document.getElementById('editDiscountValue').value.trim();
    const expirationDate = document.getElementById('editExpirationDate').value.trim();

    // Ellenőrizzük, hogy üres-e valamelyik kötelező mező
    if (!name || !description || !code || !discountType || !discountValue || !expirationDate) {
        showPopup('Kérem, töltsön ki minden kötelező mezőt!');
        return; // Nem folytatjuk a mentést
    }

    const couponId = document.getElementById('editCouponForm').dataset.couponId;

    const updatedData = {
        action: 'update',
        id: couponId,
        name: name,
        description: description,
        code: code,
        discount_type: discountType,
        discount_value: discountValue,
        expiration_date: expirationDate,
        is_active: document.getElementById('editIsActive').value
    };

    fetch('http://localhost/backend/public/adminCoupon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedData)
    })
    .then(response => response.json())
    .then(() => {
        fetchCoupons();
        closeModal();
    })
    .catch(err => {
        console.error('Hiba:', err);
        showPopup('Hiba történt a kapcsolat során.');
    });
}


function cancelEdit() {
    document.getElementById('editName').value = originalData.name;
    document.getElementById('editDescription').value = originalData.description;
    document.getElementById('editCode').value = originalData.code;
    document.getElementById('editDiscountType').value = originalData.discount_type;
    document.getElementById('editDiscountValue').value = originalData.discount_value;
    document.getElementById('editExpirationDate').value = originalData.expiration_date;
    document.getElementById('editIsActive').value = originalData.is_active;

    closeModal();
}

// Kuponok lekérése és megjelenítése
function fetchCoupons() {
    fetch('http://localhost/backend/public/adminCoupon.php?action=read')
        .then(response => {
            if (!response.ok) throw new Error('Hiba történt a kuponok lekérése során');
            return response.json();
        })
        .then(data => {
            originalData = data;
            renderCoupons(data);
        })
        .catch(error => {
            console.error('Hiba:', error);
            showPopup('Hiba történt a kuponok betöltése során.');
        });
}

function renderCoupons(data) {
    const tbody = document.querySelector('#couponTable tbody');
    tbody.innerHTML = '';

    data.forEach(coupon => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', coupon.id);
        tr.innerHTML = `
            <td>${coupon.id}</td>
            <td>${coupon.name}</td>
            <td>${coupon.description}</td>
            <td>${coupon.code}</td>
            <td>${coupon.discount_type}</td>
            <td>${coupon.discount_value}</td>
            <td>${coupon.expiration_date}</td>
            <td>${coupon.is_active == 1 ? "Aktív" : "Inaktív"}</td>
            <td class="action-buttons">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                <button class="edit-btn" onclick="editCoupon(${coupon.id})">Szerkesztés</button>
                <button class="delete-btn" onclick="deleteCoupon(${coupon.id})">Törlés</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Kupon törlése
function deleteCoupon(couponId) {
    showConfirmDialog("Biztosan törlöd ezt a kupont?", function() {
        // Igen: végrehajtjuk a törlést
        const data = { action: 'delete', id: couponId };

        fetch('http://localhost/backend/public/adminCoupon.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(() => {
            fetchCoupons();
        })
        .catch(error => {
            console.error('Hiba:', error);
            showPopup('Hiba történt a kupon törlése során.');
        });
    }, function() {
        // Mégsem: nincs teendő
        console.log("Törlés megszakítva");
    });
}


// Új kupon hozzáadása
document.getElementById('addCouponBtn').addEventListener('click', function () {
    const modal = document.getElementById('addCouponModal');
    modal.style.display = 'flex';
    modal.classList.add('centered-modal');
});

function closeAddCouponModal() {
    const modal = document.getElementById('addCouponModal');
    modal.style.display = 'none';
    modal.classList.remove('centered-modal');
}

document.getElementById('addCouponForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const couponData = {
        action: 'create',
        name: document.getElementById('newName').value,
        description: document.getElementById('newDescription').value,
        code: document.getElementById('newCode').value,
        discount_type: document.getElementById('newDiscountType').value,
        discount_value: document.getElementById('newDiscountValue').value,
        expiration_date: document.getElementById('newExpirationDate').value,
        is_active: document.getElementById('newIsActive').value
    };

    fetch('http://localhost/backend/public/adminCoupon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(couponData)
    })
    .then(response => response.json())
    .then(() => {
        closeAddCouponModal();
        fetchCoupons();
    })
    .catch(err => {
        console.error("Hiba:", err);
        showPopup("Hiba történt a kapcsolat során.");
    });
});

// Élő szűrés a táblázatban
function handleLiveSearch() {
    const input = document.getElementById('couponTypeSearchInput').value.toLowerCase();
    const filteredData = originalData.filter(coupon => {
        return Object.values(coupon).some(val =>
            String(val).toLowerCase().includes(input)
        );
    });

    renderCoupons(filteredData);
}

document.addEventListener('DOMContentLoaded', function () {
    fetchCoupons();

    const searchInput = document.getElementById('couponTypeSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleLiveSearch);
    }
});

// ----- EGYEDI POPUP-ÉRTESÍTÉS -----
let popupTimeout = null;
function showPopup(message) {
  let popup = document.getElementById("popup-notification");
  if (!popup) {
    popup = document.createElement("div");
    popup.id = "popup-notification";
    popup.className = "popup-notification";
    document.body.appendChild(popup);
  }
  // Timeout törlése, ha futna
  if (popupTimeout) { clearTimeout(popupTimeout); popupTimeout = null; }
  // Üzenet beállítása + animáció indítása
  popup.innerHTML = message;
  popup.style.animation = '';     // töröljük a korábbi animációt
  void popup.offsetWidth;         // reflow
  popup.style.animation = 'popupShow 0.5s forwards';
  // 3 másodperc után elúszik
  popupTimeout = setTimeout(() => {
    popup.style.animation = 'popupHide 0.5s forwards';
  }, 3000);
}

function showConfirmDialog(message, onConfirm, onCancel) {
    let confirmModal = document.getElementById('confirmModal');

    // Ha a modal még nem létezik, hozzuk létre
    if (!confirmModal) {
        confirmModal = document.createElement('div');
        confirmModal.id = 'confirmModal';
        confirmModal.className = 'modal';
        confirmModal.innerHTML = `
            <div class="modal-content confirm-modal">
                <p id="confirm-message">${message}</p>
                <div class="confirm-buttons">
            <button id="confirm-yes" class="btn btn-danger" style="margin-right:10px;">Igen</button>
            <button id="confirm-no" class="btn btn-secondary">Mégsem</button>
                </div>
            </div>
        `;
        document.body.appendChild(confirmModal);
    } else {
        // Módosítsuk a meglévő modal üzenetét
        confirmModal.querySelector('#confirm-message').innerText = message;
    }
    
    // Mutassuk meg a modal ablakot
    confirmModal.style.display = 'flex';
    
    // Állítsuk be a gombok eseménykezelőit
    confirmModal.querySelector('#confirm-yes').onclick = function() {
        confirmModal.style.display = 'none';
        onConfirm && onConfirm();
    };
    confirmModal.querySelector('#confirm-no').onclick = function() {
        confirmModal.style.display = 'none';
        onCancel && onCancel();
    };
}
