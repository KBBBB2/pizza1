// Couponok betöltése az API-ból
function fetchCoupons(query = '') {
  let url = '/merged/controller/adminCoupon.php?action=read';
  if(query !== '') {
      url += '&q=' + encodeURIComponent(query);
  }
  fetch(url)
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector('#couponTable tbody');
      tbody.innerHTML = '';
      data.forEach(coupon => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', coupon.id);
        tr.innerHTML = `
          <td>${coupon.id}</td>
          <td class="name">${coupon.name}</td>
          <td class="description">${coupon.description}</td>
          <td class="code">${coupon.code}</td>
          <td class="discount_type">${coupon.discount_type}</td>
          <td class="discount_value">${coupon.discount_value}</td>
          <td class="expiration_date">${coupon.expiration_date}</td>
          <td class="is_active">${coupon.is_active == 1 ? "Aktív" : "Inaktív"}</td>
          <td>
            <button class="edit-btn" onclick="editCoupon(${coupon.id})">Szerkesztés</button>
            <button class="delete-btn" onclick="deleteCoupon(${coupon.id})">Törlés</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    });
}

// Új coupon rekord beszúrása inline módon (a táblázatba)
function addNewCouponRow() {
  const tbody = document.querySelector('#couponTable tbody');
  const tr = document.createElement('tr');
  tr.setAttribute('data-new', 'true');
  tr.innerHTML = `
    <td></td>
    <td><input class="edit-input" placeholder="Name" required/></td>
    <td><input class="edit-input" placeholder="Description" required/></td>
    <td><input class="edit-input" placeholder="Code" required/></td>
    <td><input class="edit-input" placeholder="Discount Type" required/></td>
    <td><input type="number" class="edit-input" placeholder="Discount Value" required/></td>
    <td><input type="datetime-local" class="edit-input" placeholder="Expiration Date" required/></td>
    <td>
      <select class="edit-input" required>
        <option value="1">Aktív</option>
        <option value="0">Inaktív</option>
      </select>
    </td>
    <td>
      <button class="create-save-btn">Mentés</button>
      <button class="create-cancel-btn">Mégsem</button>
    </td>
  `;
  // Az új sort a táblázat tetejére tesszük
  tbody.insertBefore(tr, tbody.firstChild);

  // Gombok esemény hozzárendelése
  tr.querySelector('.create-save-btn').addEventListener('click', createNewCouponRecord);
  tr.querySelector('.create-cancel-btn').addEventListener('click', cancelNewCouponRow);
}

// Új coupon rekord mentése az API-nak
function createNewCouponRecord(e) {
  const row = e.target.closest('tr');
  const formData = new FormData();

  // Adatok összegyűjtése az input mezőkből
  const name = row.children[1].querySelector('input').value;
  const description = row.children[2].querySelector('input').value;
  const code = row.children[3].querySelector('input').value;
  const discount_type = row.children[4].querySelector('input').value;
  const discount_value = row.children[5].querySelector('input').value;
  const expiration_date = row.children[6].querySelector('input').value;
  const is_active = row.children[7].querySelector('select').value;

  formData.append('name', name);
  formData.append('description', description);
  formData.append('code', code);
  formData.append('discount_type', discount_type);
  formData.append('discount_value', discount_value);
  formData.append('expiration_date', expiration_date);
  formData.append('is_active', is_active);

  fetch('/merged/controller/adminCoupon.php?action=create', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if(data.success) {
      // Beállítjuk az új rekord id-ját és visszaállítjuk a cellák tartalmát
      row.setAttribute('data-id', data.id);
      row.removeAttribute('data-new');
      row.children[0].textContent = data.id;
      row.children[1].textContent = name;
      row.children[2].textContent = description;
      row.children[3].textContent = code;
      row.children[4].textContent = discount_type;
      row.children[5].textContent = discount_value;
      row.children[6].textContent = expiration_date;
      row.children[7].textContent = is_active == "1" ? "Aktív" : "Inaktív";
      row.children[8].innerHTML = `
        <button class="edit-btn" onclick="editCoupon(${data.id})">Szerkesztés</button>
        <button class="delete-btn" onclick="deleteCoupon(${data.id})">Törlés</button>
      `;
      // Frissítjük a táblázatot (opcionális)
      fetchCoupons();
    } else {
      alert("Hiba történt az új coupon létrehozása során: " + data.error);
    }
  })
  .catch(err => {
    console.error("Error:", err);
    alert("Hiba történt a kapcsolat során.");
  });
}

// Új sor szerkesztésének megszakítása (az új sor törlése)
function cancelNewCouponRow(e) {
  const row = e.target.closest('tr');
  row.remove();
}

// Coupon szerkesztése inline módon (a sor cellái input mezőkké alakulnak)
function editCoupon(id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) {
    alert("Coupon nem található.");
    return;
  }

  // Lekérjük az aktuális értékeket
  const currentName = row.querySelector('.name').textContent;
  const currentDescription = row.querySelector('.description').textContent;
  const currentCode = row.querySelector('.code').textContent;
  const currentDiscountType = row.querySelector('.discount_type').textContent;
  const currentDiscountValue = row.querySelector('.discount_value').textContent;
  let expiration = row.querySelector('.expiration_date').textContent;
  let expirationValue = expiration;
  if(expiration.includes(" ")) {
    let parts = expiration.split(" ");
    expirationValue = parts[0] + "T" + parts[1].substring(0,5);
  }
  const currentIsActiveText = row.querySelector('.is_active').textContent.trim();
  const currentIsActive = currentIsActiveText === "Aktív" ? "1" : "0";

  // Eredeti sor tartalmának megőrzése visszavonáshoz
  const originalHTML = row.innerHTML;

  // A sor celláit input mezőkre cseréljük
  row.innerHTML = `
    <td>${id}</td>
    <td><input type="text" class="edit-input" value="${currentName}" required /></td>
    <td><input type="text" class="edit-input" value="${currentDescription}" required /></td>
    <td><input type="text" class="edit-input" value="${currentCode}" required /></td>
    <td><input type="text" class="edit-input" value="${currentDiscountType}" required /></td>
    <td><input type="number" class="edit-input" value="${currentDiscountValue}" required /></td>
    <td><input type="datetime-local" class="edit-input" value="${expirationValue}" required /></td>
    <td>
      <select class="edit-input" required>
        <option value="1" ${currentIsActive === "1" ? "selected" : ""}>Aktív</option>
        <option value="0" ${currentIsActive === "0" ? "selected" : ""}>Inaktív</option>
      </select>
    </td>
    <td>
      <button class="update-save-btn">Mentés</button>
      <button class="update-cancel-btn">Mégse</button>
    </td>
  `;
  row.setAttribute("data-editing", "true");

  // Mentés gomb eseménykezelője
  row.querySelector('.update-save-btn').addEventListener('click', function() {
    const inputs = row.querySelectorAll('input.edit-input, select.edit-input');
    const updatedName = inputs[0].value;
    const updatedDescription = inputs[1].value;
    const updatedCode = inputs[2].value;
    const updatedDiscountType = inputs[3].value;
    const updatedDiscountValue = inputs[4].value;
    const updatedExpiration = inputs[5].value;
    const updatedIsActive = row.querySelector('select.edit-input').value;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('name', updatedName);
    formData.append('description', updatedDescription);
    formData.append('code', updatedCode);
    formData.append('discount_type', updatedDiscountType);
    formData.append('discount_value', updatedDiscountValue);
    formData.append('expiration_date', updatedExpiration);
    formData.append('is_active', updatedIsActive);

    fetch('/merged/controller/adminCoupon.php?action=update', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if(data.success) {
        // Sikeres frissítés után a sor statikus nézetre vált vissza
        row.innerHTML = `
          <td>${id}</td>
          <td class="name">${updatedName}</td>
          <td class="description">${updatedDescription}</td>
          <td class="code">${updatedCode}</td>
          <td class="discount_type">${updatedDiscountType}</td>
          <td class="discount_value">${updatedDiscountValue}</td>
          <td class="expiration_date">${updatedExpiration.replace("T", " ")}</td>
          <td class="is_active">${updatedIsActive === "1" ? "Aktív" : "Inaktív"}</td>
          <td>
            <button class="edit-btn" onclick="editCoupon(${id})">Szerkesztés</button>
            <button class="delete-btn" onclick="deleteCoupon(${id})">Törlés</button>
          </td>
        `;
        row.removeAttribute("data-editing");
      } else {
        alert("Frissítés hiba: " + data.error);
      }
    })
    .catch(err => {
      console.error("Error:", err);
      alert("Kapcsolati hiba történt.");
    });
  });

  // Mégse gomb eseménykezelője: visszaállítja az eredeti sort
  row.querySelector('.update-cancel-btn').addEventListener('click', function() {
    row.innerHTML = originalHTML;
    row.removeAttribute("data-editing");
  });
}

// Rekord törlése
function deleteCoupon(id) {
  if(confirm('Biztos törlöd a coupon-t?')) {
    const formData = new FormData();
    formData.append('id', id);
    fetch('/merged/controller/adminCoupon.php?action=delete', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if(data.success) {
        fetchCoupons();
      } else {
        alert('Hiba: ' + data.error);
      }
    });
  }
}

// Az "Új Coupon hozzáadása" gombra inline új sor beszúrása
document.getElementById('addCouponBtn').addEventListener('click', addNewCouponRow);

// Kereső gomb eseménykezelője
document.getElementById('couponSearchBtn').addEventListener('click', function() {
  const query = document.getElementById('couponSearchInput').value;
  fetchCoupons(query);
});

// Oldal betöltésekor a couponok lekérése
fetchCoupons();
