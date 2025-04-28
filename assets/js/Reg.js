document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    console.log("A regisztrációs form beküldve.");

    var firstname = document.getElementById('firstname').value.trim();
    var lastname = document.getElementById('lastname').value.trim();
    var email = document.getElementById('email').value.trim();
    var phone = document.getElementById('phone').value.trim();
    var password = document.getElementById('reg_password').value.trim();
    var username = document.getElementById('reg_username').value.trim();
    var msgDiv = document.getElementById('registerMessage');
    
    msgDiv.textContent = '';  // Töröljük a régi hibaüzeneteket
    msgDiv.classList.remove('show');  // Üzenet elrejtése

    // Ellenőrzések először
    const nameRegex = /^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]+$/;
    if (!nameRegex.test(firstname)) {
        msgDiv.style.color = 'red';
        msgDiv.textContent = "A keresztnév csak betűket tartalmazhat!";
        msgDiv.classList.add('show');
        return;
    }
    if (!nameRegex.test(lastname)) {
        msgDiv.style.color = 'red';
        msgDiv.textContent = "A vezetéknév csak betűket tartalmazhat!";
        msgDiv.classList.add('show');
        return;
    }

    // Email validálása
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        msgDiv.style.color = 'red';
        msgDiv.textContent = "Érvénytelen email formátum!";
        msgDiv.classList.add('show');
        return;
    }

    // Telefonszám formázása és validálása: csak számok
    phone = phone.replace(/\D/g, '');  
    var phoneRegex = /^(06|36)[0-9]{9}$/im;
    if (!phoneRegex.test(phone)) {
        msgDiv.style.color = 'red';
        msgDiv.textContent = "Érvénytelen telefonszám formátum!";
        msgDiv.classList.add('show');
        return;
    }

    // Felhasználónév validálása
    const usernameRegex = /^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ0-9_]+$/;
    if (!username || !usernameRegex.test(username)) {
        msgDiv.style.color = 'red';
        msgDiv.textContent = "A felhasználónév csak betűket és számokat tartalmazhat, és nem lehet üres!";
        msgDiv.classList.add('show');
        return;
    }

    // Jelszó validálása
    if (password.length < 6) {
        msgDiv.style.color = 'red';
        msgDiv.textContent = "A jelszónak legalább 6 karakter hosszúnak kell lennie!";
        msgDiv.classList.add('show');
        return;
    }

    // Ha minden validálás rendben van, akkor küldd el a regisztrációs kérést
    fetch('http://localhost/backend/public/account.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'register',
            firstname, lastname, email, phonenumber: phone,
            username, password
        })
    })
    .then(response => response.json().then(data => {
        // Itt már van data, és tudjuk, hogy mi a státus
        if (!response.ok) {
            // Hibakezelés
            msgDiv.style.color = 'red';
            msgDiv.textContent = data.error || "Hiba történt a regisztráció során.";
            msgDiv.classList.add('show');
            // Leállítjuk a láncot, hogy ne fusson tovább a következő .then
            throw new Error(data.error);
        }
        return data;  // sikeres válasz esetén továbbítjuk
    }))
    .then(data => {
        // Ha ide eljutunk, response.ok === true volt
        window.location.href = "Login.html";
    })
    .catch(error => {
        // Ez akkor is lefut, ha rossz státuszú volt (400, 500), vagy hálózati hiba
        if (error.message !== "") {
            // Ha dobott hibaszövegünk van (data.error), azt mutatjuk
            msgDiv.style.color = 'red';
            msgDiv.textContent = error.message;
        } else {
            msgDiv.style.color = 'red';
            msgDiv.textContent = "Kapcsolódási hiba a szerverrel.";
        }
        msgDiv.classList.add('show');
    });
});
