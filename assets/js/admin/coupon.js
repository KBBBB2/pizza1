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
  
  // Rekord szerkesztése (az eredeti funkció, változtatni lehet, ha inline szerkesztésre is szeretnéd)
  function editCoupon(id) {
    // Példa: az összes coupon adatát lekérjük, majd kiszűrjük a szerkesztendőt
    fetch('/merged/controller/adminCoupon.php?action=read')
      .then(response => response.json())
      .then(data => {
        const coupon = data.find(c => c.id == id);
        if(coupon) {
          // Itt implementálhatod az inline szerkesztést vagy átirányíthatod a meglévő formos szerkesztésre
          // Például: egy modális ablakban, vagy közvetlenül a táblázatban átalakítva az adott sor celláit input mezőkké
          // Ebben a példában egyszerűen alerteljük az adatokat:
          alert("Szerkesztendő coupon: " + JSON.stringify(coupon));
        }
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
  