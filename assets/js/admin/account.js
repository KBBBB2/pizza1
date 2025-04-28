// Függvény, amely lekéri az account rekordokat, opcionálisan keresési feltétellel
function loadAccounts(query = '') {
    let url = 'http://localhost/backend/public/adminAccount.php?action=getAccounts';
    if (query !== '') {
        url += '&q=' + encodeURIComponent(query);
    }

    fetch(url)
        .then(response => response.json())
        .then(accounts => {
            const tbody = document.querySelector('#accountsTable tbody');
            
            // Ellenőrizzük, hogy a tbody létezik
            if (!tbody) {
                console.error('A #accountsTable tbody elem nem található!');
                return;
            }

            tbody.innerHTML = ''; // Töröljük a meglévő sorokat

            accounts.forEach(account => {
                const row = document.createElement('tr');

                // Csak a szükséges oszlopok megjelenítése (id, password nélkül)
                row.innerHTML = `
                    <td>${account.firstname}</td>
                    <td>${account.lastname}</td>
                    <td>${account.username}</td>
                    <td>${account.email}</td>
                    <td>${account.phonenumber}</td>
                    <td>${account.created}</td>
                    <td>${account.locked == 1 ? 'Igen' : 'Nem'}</td>
                    <td>${account.disabled == 1 ? 'Igen' : 'Nem'}</td>
                    <td class="action-container">
                        <button class="temp-ban-btn btn btn-danger tban-button">Ideiglenes tiltás</button>
                        <button class="perm-ban-btn btn-danger pban-button">Végleges tiltás</button>
                    </td>
                `;

                tbody.appendChild(row);

                // Akciók gombok
                const tempBanBtn = row.querySelector('.temp-ban-btn');
                tempBanBtn.addEventListener('click', function () {
                    if (!row.querySelector('.tempDuration')) {
                        const durationInput = document.createElement('input');
                        durationInput.type = 'text';
                        durationInput.classList.add('tempDuration');
                        durationInput.placeholder = 'Pl. 5m, 2h, 1d';
                        const confirmBtn = document.createElement('button');
                        confirmBtn.textContent = 'Megerősít';
                        
                        confirmBtn.classList.add('btn', 'btn-danger', 'confirmBtn');

                        row.querySelector('.action-container').appendChild(durationInput);
                        row.querySelector('.action-container').appendChild(confirmBtn);

                        confirmBtn.addEventListener('click', function () {
                            const duration = durationInput.value;
                            fetch('http://localhost/backend/public/adminAccount.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `action=tempBan&id=${account.id}&duration=${encodeURIComponent(duration)}`
                            })
                                .then(() => {
                                    showPopup("Ideiglenes tiltás: sikeresen végrehajtva");
                                    loadAccounts(document.getElementById('search').value); // Frissíti a listát
                                })
                                .catch(error => {
                                    console.error('Hiba történt a tiltás során:', error);
                                });
                        });
                    }
                });

                // Végleges tiltás 
                const permBanBtn = row.querySelector('.perm-ban-btn');
                permBanBtn.addEventListener('click', function () {
                    // showConfirmDialog most callbackeket kap
                    showConfirmDialog(
                        "Biztosan véglegesen tiltja ezt a felahsználót?",
                        () => {  // onConfirm
                            fetch('http://localhost/backend/public/adminAccount.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    action: 'permBan',
                                    id: account.id
                                })
                            })
                            .then(response => {
                                if (!response.ok) throw new Error('Hálózati válasz hiba');
                                return response.json();  // vagy .text(), ahogy a backend adja vissza
                            })
                            .then(() => {
                                showPopup("Végleges tiltás: sikeresen végrehajtva");
                                loadAccounts(document.getElementById('search').value);
                            })
                            .catch(error => {
                                console.error('Hiba történt a végleges tiltás során:', error);
                            });
                        },
                        () => {
                            // onCancel (opcionális)
                            console.log('Végleges tiltást megszakították.');
                        }
                    );
                });

            });
        })
        .catch(error => {
            console.error('Hiba történt a lekérés során:', error);
        });
}

// Oldal betöltésekor az account rekordok lekérése
document.addEventListener("DOMContentLoaded", function () {
    loadAccounts();

    // Live search eseménykezelője
    document.getElementById('search').addEventListener('input', function () {
        const query = document.getElementById('search').value;
        loadAccounts(query);
    });
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

