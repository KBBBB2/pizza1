document.addEventListener("DOMContentLoaded", function() {
    // Felhasználói adatok lekérése az oldal betöltésekor
    fetch('/merged/Controller/account.php', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getUserData' })
    })
    .then(response => response.json())
    .then(data => {
         if (data.user) {
             document.getElementById('firstname').value = data.user.firstname || "";
             document.getElementById('lastname').value  = data.user.lastname || "";
             document.getElementById('username').value  = data.user.username || "";
             document.getElementById('email').value     = data.user.email || "";
             document.getElementById('phonenumber').value     = data.user.phonenumber || "";
         } else {
             alert("Nem sikerült lekérni az adatokat: " + data.error);
         }
    })
    .catch(error => console.error('Error:', error));

    // Mentés gomb eseménykezelője
    document.querySelector('.save-button').addEventListener('click', function() {
         const payload = {
             action: 'update',
             firstname: document.getElementById('firstname').value,
             lastname: document.getElementById('lastname').value,
             username: document.getElementById('username').value,
             email: document.getElementById('email').value,
             phonenumber: document.getElementById('phonenumber').value,
             'current-password': document.getElementById('current-password').value,
             'new-password': document.getElementById('new-password').value
         };

         fetch('/merged/Controller/account.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/json' },
             body: JSON.stringify(payload)
         })
         .then(response => response.json())
         .then(data => {
             if (data.success) {
                 alert("Sikeres módosítás: " + data.success);
             } else {
                 alert("Hiba: " + data.error);
             }
         })
         .catch(error => console.error('Error:', error));
    });
});

