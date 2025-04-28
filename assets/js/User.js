document.addEventListener("DOMContentLoaded", function() {
    const showPopupNotification = message => {
        const popup = document.getElementById("popup-notification");
        popup.innerHTML = message;
        popup.style.opacity = "1";
        popup.style.animation = "popupShow 0.5s forwards";
        setTimeout(() => popup.style.animation = "popupHide 0.5s forwards", 3000);
      };
    
      // token ellenőrzés
      const token = localStorage.getItem("token");
      if (!token) {
        showPopupNotification("Nincs érvényes token, kérlek jelentkezz be újra!");
        // 3 másodperc múlva átirányít:
        setTimeout(() => window.location.href = "login.html", 3000);
        return;
      }
    // Felhasználói adatok lekérése az oldal betöltésekor
    fetch('http://localhost/backend/public/account.php', { 
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ action: 'getUserData' })
    })
    .then(response => response.json())
    .then(data => {
         if (data.user) {
            document.getElementById('lastname').value  = data.user.lastname || "";
             document.getElementById('firstname').value = data.user.firstname || "";
             document.getElementById('username').value  = data.user.username || "";
             document.getElementById('email').value     = data.user.email || "";
             document.getElementById('phonenumber').value = data.user.phonenumber || "";
         } else {
             alert("Nem sikerült lekérni az adatokat: " + data.error);
         }
    })
    .catch(error => console.error('Error:', error));

    // Mentés gomb eseménykezelője
    document.querySelector('.save-button').addEventListener('click', function() {
        const currentPassword = document.getElementById('current-password').value;
        const newPassword     = document.getElementById('new-password').value;
        const phoneValue       = document.getElementById('phonenumber').value;
        const phoneRegex       = /^(06|36)[0-9]{9}$/;
    
        // Ha a felhasználó beírta a jelenlegi jelszót, csak akkor ellenőrizzük az új jelszó hosszát
        if (currentPassword !== "" && newPassword.length < 6) {
            showPopupNotification("Az új jelszónak legalább 6 karakter hosszúnak kell lennie!");
            return;  // ne küldjük el a kérést
        }

        // Telefonszám ellenőrzése
        if (!phoneRegex.test(phoneValue)) {
            showPopupNotification("A telefonszám formátuma érvénytelen. Például: 36 30 1234567 vagy 06 20 3456789");
            return;
        }
    
        const payload = {
            action: 'update',
            lastname: document.getElementById('lastname').value,
            firstname: document.getElementById('firstname').value,
            username: document.getElementById('username').value,
            email: document.getElementById('email').value,
            phonenumber: document.getElementById('phonenumber').value,
            'current-password': currentPassword,
            'new-password': newPassword
        };
    
        fetch('http://localhost/backend/public/account.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPopupNotification("Sikeres módosítás: " + data.success);
            } else {
                showPopupNotification("Hiba: " + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPopupNotification("Hiba történt a kommunikáció során.");
        });
    });
});    
