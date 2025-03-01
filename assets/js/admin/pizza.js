    // Adatok lekérése az API-ból
    function fetchPizzas() {
        fetch('/Merged/Controller/menu.php')
          .then(response => response.json())
          .then(data => {
            if(data.success) {
              populateTable(data.data);
            } else {
              alert("Hiba történt az adatok lekérésekor: " + data.error);
            }
          })
          .catch(err => {
            console.error("Error:", err);
            alert("Hiba történt a kapcsolat során.");
          });
      }
  
      // Táblázat feltöltése a lekért adatokkal
      // Példa: amikor a pizzákat lekéred és táblázatot töltesz fel
  function populateTable(pizzas) {
    const tbody = document.querySelector('#pizzaTable tbody');
    tbody.innerHTML = ''; // töröljük a korábbi sorokat
  
    pizzas.forEach(pizza => {
      const tr = document.createElement('tr');
      tr.setAttribute('data-id', pizza.id);
      
      // A kép URL összeállítása (feltételezzük, hogy a fájl .jpg formátumú)
      let imagePath = `/merged/assets/images/${pizza.id}/pizza_${pizza.id}.jpg`;
      // (Ellenőrizheted, hogy a kép elérhető-e, vagy használhatsz default képet, ha nem található)
      let priceHtml = '';
      if (pizza.discounted_price != null) {
            priceHtml = `<td class="price"><s class="original">${pizza.price} Ft</s> <br> ${pizza.discounted_price} Ft</td>`;
          } else {
            priceHtml = `<td class="price">${pizza.price} Ft</td>`;
          }
  
      tr.innerHTML = `
        <td>${pizza.id}</td>
        <td class="name">${pizza.name}</td>
        <td class="crust">${pizza.crust}</td>
        <td class="cutstyle">${pizza.cutstyle}</td>
        <td class="pizzasize">${pizza.pizzasize}</td>
        <td class="ingredient">${pizza.ingredient}</td>
        ${priceHtml}
        <td>
          <button class="edit-btn">Módosít</button>
          <button class="save-btn" style="display:none;">Mentés</button>
          <button class="cancel-btn" style="display:none;">Mégsem</button>
          <button class="delete-btn">Törlés</button>
        </td>
        <td>
          <img src="${imagePath}" alt="Pizza ${pizza.id}" width="50">
        </td>
      `;
      tbody.appendChild(tr);
    });
    
    // ... (itt ugyanúgy hozzárendeled az eseménykezelőket, mint eddig)
  
  
  
        // Gomb események hozzárendelése
        document.querySelectorAll('.edit-btn').forEach(button => {
          button.addEventListener('click', enableEditing);
        });
        document.querySelectorAll('.save-btn').forEach(button => {
          button.addEventListener('click', saveChanges);
        });
        document.querySelectorAll('.cancel-btn').forEach(button => {
          button.addEventListener('click', cancelEditing);
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
          button.addEventListener('click', deleteRow);
        });
      }
  
      // Szerkesztési mód engedélyezése a soron (létező rekordoknál)
      function enableEditing(e) {
        const row = e.target.closest('tr');
        row.querySelectorAll('td').forEach((cell, index) => {
          // Az első (ID) és az utolsó (gombok) cella ne legyen szerkeszthető
          if(index === 0 || index === row.children.length - 1) {
            return;
          }
          if(cell.classList.contains('name') || cell.classList.contains('crust') || cell.classList.contains('cutstyle') ||
             cell.classList.contains('pizzasize') || cell.classList.contains('ingredient') || cell.classList.contains('price')) {
            const text = cell.textContent;
            cell.setAttribute('data-original', text);
            cell.innerHTML = `<input class="edit-input" value="${text}" />`;
          }
        });
        row.querySelector('.edit-btn').style.display = 'none';
        row.querySelector('.save-btn').style.display = 'inline';
        row.querySelector('.cancel-btn').style.display = 'inline';
      }
  
      // Szerkesztés megszakítása, eredeti értékek visszaállítása
      function cancelEditing(e) {
        const row = e.target.closest('tr');
        row.querySelectorAll('td').forEach((cell, index) => {
          if(index === 0 || index === row.children.length - 1){
            return;
          }
          if(cell.querySelector('input')) {
            const original = cell.getAttribute('data-original');
            cell.textContent = original;
          }
        });
        row.querySelector('.edit-btn').style.display = 'inline';
        row.querySelector('.save-btn').style.display = 'none';
        row.querySelector('.cancel-btn').style.display = 'none';
      }
  
      // Mentés: módosított adatok elküldése az API-nak
      function saveChanges(e) {
        const row = e.target.closest('tr');
        const id = row.getAttribute('data-id');
        const updatedData = { id: id };
        ['name', 'crust', 'cutstyle', 'pizzasize', 'ingredient', 'price'].forEach(field => {
          const cell = row.querySelector('.' + field);
          if(cell) {
            const input = cell.querySelector('input');
            if(input) {
              updatedData[field] = input.value;
            }
          }
        });
  
        fetch('/Merged/Controller/menu.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(updatedData)
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            row.querySelectorAll('td').forEach((cell, index) => {
              if(index === 0 || index === row.children.length - 1){
                return;
              }
              if(cell.querySelector('input')) {
                cell.textContent = cell.querySelector('input').value;
              }
            });
            row.querySelector('.edit-btn').style.display = 'inline';
            row.querySelector('.save-btn').style.display = 'none';
            row.querySelector('.cancel-btn').style.display = 'none';
          } else {
            alert("Hiba történt a módosítás során: " + data.error);
          }
        })
        .catch(err => {
          console.error("Error:", err);
          alert("Hiba történt a kapcsolat során.");
        });
      }
  
      // Törlés: adott sor törlése az adatbázisból
      function deleteRow(e) {
        if (!confirm("Biztosan törli az adatot?")) {
          return;
        }
        const row = e.target.closest('tr');
        const id = row.getAttribute('data-id');
        fetch('/Merged/Controller/menu.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
          if(data.success){
            row.remove();
          } else {
            alert("Hiba történt a törlés során: " + data.error);
          }
        })
        .catch(err => {
          console.error("Error:", err);
          alert("Hiba történt a törlés során.");
        });
      }
  
      // Új rekord beszúrása: új sor megjelenítése szerkesztési módban
      function addNewRow() {
        const tbody = document.querySelector('#pizzaTable tbody');
        // Létrehozunk egy új sort, amelyhez nincs még id (data-new jelzővel)
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
          <input type="file" class="image-input" name="image" accept="image/jpeg*" required>
          </td>
        `;
        // Új sort a táblázat elejére tesszük
        tbody.insertBefore(tr, tbody.firstChild);
  
        // Gombok esemény hozzárendelése
        tr.querySelector('.create-save-btn').addEventListener('click', createNewRecord);
        tr.querySelector('.create-cancel-btn').addEventListener('click', cancelNewRow);
      }
  
      // Új rekord mentése az API-nak
      // Új rekord mentése az API-nak
  function createNewRecord(e) {
    const row = e.target.closest('tr');
    const formData = new FormData();
    // Itt módosítsd az action értékét:
    formData.append('action', 'uploadPizza'); 
    // Adatok összegyűjtése:
    const fields = ['name', 'crust', 'cutstyle', 'pizzasize', 'ingredient', 'price'];
    fields.forEach((field, index) => {
      const cell = row.children[index + 1];
      const input = cell.querySelector('input');
      formData.append(field, input.value);
    });
    // Fájl input feldolgozása:
    const fileInput = row.querySelector('.image-input');
    if (fileInput.files.length > 0) {
      formData.append('image', fileInput.files[0]);
    } else {
      alert("Kérem, válasszon egy képfájlt!");
      return;
    }
    
    // Küldés a backendnek:
    fetch('/Merged/Controller/menu.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if(data.success) {
        // A backend válaszában vissza kell adnia az új rekord id-ját és a fájl kiterjesztését
        row.setAttribute('data-id', data.id);
        row.removeAttribute('data-new');
        row.children[0].textContent = data.id;
        fields.forEach((field, index) => {
          const cell = row.children[index + 1];
          const input = cell.querySelector('input');
          if(input) {
            cell.textContent = input.value;
          }
          cell.className = field;
        });
        
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
        
        // Előnézet kép megjelenítése (ha szükséges)
        let fileExt = data.fileExt || "jpg";
        let imagePath = `assets/images/${data.id}/pizza_${data.id}.${fileExt}`;
        const imageCell = document.createElement('td');
        imageCell.innerHTML = `<img src="${imagePath}" alt="Pizza ${data.id}" width="50">`;
        row.appendChild(imageCell);
        fetchPizzas();
      } else {
        alert("Hiba történt az új rekord létrehozása során: " + data.error);
      }
    })
    .catch(err => {
      console.error("Error:", err);
      alert("Hiba történt a kapcsolat során.");
    });
  }
  
  
  
      // Új rekord szerkesztésének megszakítása (az új sor törlése)
      function cancelNewRow(e) {
        const row = e.target.closest('tr');
        row.remove();
      }
  
      // Oldal betöltésekor események hozzárendelése és adatok lekérése
      window.onload = function() {
        document.getElementById('add-new').addEventListener('click', addNewRow);
        fetchPizzas();
      };