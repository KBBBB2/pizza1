

// Adatok lekérése az API-ból
function fetchPizzas() {
    fetch('http://localhost/backend/public/menu.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateTable(data.data); // Ha sikeres, feltöltjük a táblázatot
            } else {
                showPopup("Hiba történt az adatok lekérésekor: " + data.error);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            showPopup("Hiba történt a kapcsolat során.");
        });
}

// Táblázat feltöltése a lekért adatokkal
function populateTable(pizzas) {
    const tbody = document.querySelector('#pizzaTable tbody');
    tbody.innerHTML = ''; // Az összes sor törlése a táblázatban
    pizzas.forEach(pizza => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', pizza.id);

        const imagePath = pizza.image;

        const priceHtml = pizza.discounted_price != null
            ? `<td class="price"><s class="original">${pizza.price} Ft</s><br> ${pizza.discounted_price} Ft</td>`
            : `<td class="price">${pizza.price} Ft</td>`;

        tr.innerHTML = `
            <td>${pizza.id}</td>
            <td class="name">${pizza.name}</td>
            <td class="crust">${pizza.crust}</td>
            <td class="cutstyle">${pizza.cutstyle}</td>
            <td class="pizzasize">${pizza.pizzasize}</td>
            <td class="ingredient">${pizza.ingredient}</td>
            ${priceHtml}
            <td class="action-buttons">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <button class="edit-btn btn">Módosít</button>
                    <button class="delete-btn btn">Törlés</button>
                </div>
            </td>
            <td><img src="${imagePath}" alt="Pizza ${pizza.id}" width="50"></td>
        `;

        tr.querySelector('.edit-btn').addEventListener('click', () => openModal(pizza));
        tr.querySelector('.delete-btn').addEventListener('click', deleteRow);

        tbody.appendChild(tr); // Új sor hozzáadása a táblázathoz
    });
}

// Keresési funkció
document.getElementById('search').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#pizzaTable tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('.name').textContent.toLowerCase();
        const crust = row.querySelector('.crust').textContent.toLowerCase();
        const cutstyle = row.querySelector('.cutstyle').textContent.toLowerCase();
        const pizzasize = row.querySelector('.pizzasize').textContent.toLowerCase();
        const ingredient = row.querySelector('.ingredient').textContent.toLowerCase();

        // A keresési feltétel ellenőrzése
        if (name.includes(query) || crust.includes(query) || cutstyle.includes(query) || pizzasize.includes(query) || ingredient.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Új rekord beszúrása
function addNewRow() {
    const tbody = document.querySelector('#pizzaTable tbody');
    const tr = document.createElement('tr');
    tr.setAttribute('data-new', 'true');

    tr.innerHTML = `
        <td></td>
        <td><input class="edit-input" placeholder="Név" required/></td>
        <td><input class="edit-input" placeholder="Tészta" required/></td>
        <td><input class="edit-input" placeholder="Vágás módja" required/></td>
        <td><input class="edit-input" placeholder="Méret" required/></td>
        <td><input class="edit-input" placeholder="Összetevők" required/></td>
        <td><input type="number" class="edit-input" placeholder="Ár" required/></td>
        <td>
            <button class="create-save-btn">Mentés</button>
            <button class="create-cancel-btn">Mégsem</button>
        </td>
        <td>
            <input type="file" class="image-input" name="image" accept="image/jpg" required>
        </td>
    `;

    tbody.insertBefore(tr, tbody.firstChild); // Új sor hozzáadása a táblázat tetejére
    tr.querySelector('.create-save-btn').addEventListener('click', createNewRecord);
    tr.querySelector('.create-cancel-btn').addEventListener('click', () => tr.remove());
}

// Új rekord mentése az API-nak
function createNewRecord(e) {
    const row = e.target.closest('tr');
    const formData = new FormData();

    // Adatok összegyűjtése
    formData.append('action', 'uploadPizza');
    const fields = ['name', 'crust', 'cutstyle', 'pizzasize', 'ingredient', 'price'];
    fields.forEach((field, index) => {
        const cell = row.children[index + 1]; // az input cellák
        const input = cell.querySelector('input');
        formData.append(field, input.value);
    });

    // Kép fájl kezelése
    const fileInput = row.querySelector('.image-input');
    if (fileInput.files.length > 0) {
        formData.append('image', fileInput.files[0]);
    } else {
        showPopup("Kérem, válasszon egy képfájlt!");
        return;
    }

    // Küldés a backend-nek
    fetch('/backend/public/menu.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // A válaszban vissza kell adni az új rekord ID-ját és fájl kiterjesztését
            row.setAttribute('data-id', data.id);
            row.removeAttribute('data-new');
            row.children[0].textContent = data.id;

            fields.forEach((field, index) => {
                const cell = row.children[index + 1];
                const input = cell.querySelector('input');
                if (input) {
                    cell.textContent = input.value;
                }
                cell.className = field;
            });

            // Kép megjelenítése
            let fileExt = data.fileExt || "jpg"; // A fájl kiterjesztésének alapértelmezett értéke
            const imagePath = pizza.image; 
            const imageCell = document.createElement('td');
            imageCell.innerHTML = `<img src="${data.imagePath}" alt="Pizza ${data.id}" width="50">`;
            row.appendChild(imageCell);

            // Gombok beállítása
            row.children[7].innerHTML = `
                <button class="edit-btn">Módosít</button>
                <button class="save-btn" style="display:none;">Mentés</button>
                <button class="cancel-btn" style="display:none;">Mégsem</button>
                <button class="delete-btn">Törlés</button>
            `;
            row.querySelector('.edit-btn').addEventListener('click', enableEditing);
            row.querySelector('.save-btn').addEventListener('click', saveChanges);
            row.querySelector('.cancel-btn').addEventListener('click', cancelEditing);
            row.querySelector('.delete-btn').addEventListener('click', deleteRow);

            fetchPizzas(); // Újratöltjük a pizzák listáját

        } else {
            showPopup("Hiba történt az új rekord létrehozása során: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error:", err);
        showPopup("Hiba történt a kapcsolat során.");
    });
}

let pizzaRowToDelete = null; // Ezt fogjuk beállítani, ha törlés gombra kattintanak

// Például pizza.js-ben a törléshez:
function deleteRow(e) {
    const row = e.target.closest('tr');
    const pizzaId = row.getAttribute('data-id');
    
    showConfirmDialog("Biztosan törölni szeretnéd ezt a pizzát?", function() {
        // Igen gomb: végrehajtjuk a törlést
        fetch('http://localhost/backend/public/adminPizza.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: pizzaId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
            } else {
                showPopup("Hiba történt a pizza törlésénél: " + data.error);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            showPopup("Hiba történt a kapcsolat során.");
        });
    }, function() {
        console.log("Törlés megszakítva");
    });
}




// Módosítás modal megnyitása
function openModal(pizza) {
    document.getElementById('editId').value = pizza.id;
    document.getElementById('editName').value = pizza.name;
    document.getElementById('editCrust').value = pizza.crust;
    document.getElementById('editCutstyle').value = pizza.cutstyle;
    document.getElementById('editSize').value = pizza.pizzasize;
    document.getElementById('editIngredient').value = pizza.ingredient;
    document.getElementById('editPrice').value = pizza.price;
    document.getElementById('editModal').style.display = 'flex';
}

// Módosítás bezárása
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Mentés modalból
document.getElementById('editForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const updatedData = {
        id: document.getElementById('editId').value,
        name: document.getElementById('editName').value,
        crust: document.getElementById('editCrust').value,
        cutstyle: document.getElementById('editCutstyle').value,
        pizzasize: document.getElementById('editSize').value,
        ingredient: document.getElementById('editIngredient').value,
        price: document.getElementById('editPrice').value
    };

    fetch('http://localhost/backend/public/adminPizza.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                fetchPizzas(); // Frissítjük az adatokat
            } else {
                showPopup('Hiba történt: ' + data.error);
            }
        })
        .catch(err => {
            console.error(err);
            showPopup("Hiba történt a kapcsolat során.");
        });
});

// Új pizza hozzáadása gomb eseménykezelője
document.getElementById('add-new').addEventListener('click', function() {
  document.getElementById('addPizzaModal').style.display = 'flex';
});

// Új pizza modal bezárása
function closeAddPizzaModal() {
  document.getElementById('addPizzaModal').style.display = 'none';
}

// Új — multipart/form-data, képfeltöltéssel
document.getElementById('addPizzaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
  
    // ha a backended még mindig vár action-t:
    formData.append('action','uploadPizza');
  
    // biztos, hogy van fájl
    if (!formData.get('image')) {
      return showPopup('Kérem, válasszon egy képfájlt!');
    }
  
    fetch('http://localhost/backend/public/menu.php', {
      method: 'POST',
      body: formData    // NINCS Content-Type megadva!
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        fetchPizzas();
        closeAddPizzaModal();
      } else {
        showPopup('Hiba történt az új pizza hozzáadásakor:\n' + (data.error||'Ismeretlen hiba'));
      }
    })
    .catch(() => showPopup('Hálózati hiba történt.'));
  });
  

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


// Adatok betöltése a lap indításakor
window.onload = fetchPizzas;
