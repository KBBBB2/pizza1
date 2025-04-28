// Állapotváltozó a mód váltásához
let isForgotPassword = false;

document.getElementById('toggleForgotPassword').addEventListener('click', function() {
    isForgotPassword = !isForgotPassword;
    
    const loginUserGroup = document.getElementById('loginUserGroup');
    const loginPasswordGroup = document.getElementById('loginPasswordGroup');
    const loginEmailGroup = document.getElementById('loginEmailGroup');
    const loginSubmit = document.getElementById('loginSubmit');
    const toggleBtn = document.getElementById('toggleForgotPassword');
    const loginMessage = document.getElementById('loginMessage');

    if (isForgotPassword) {
        // Átváltás jelszó visszaállítás módra
        loginUserGroup.style.display = 'none';
        loginPasswordGroup.style.display = 'none';
        loginEmailGroup.style.display = 'block';
        loginSubmit.textContent = 'Jelszó visszaállítása';
        toggleBtn.textContent = 'Vissza';
        loginMessage.textContent = '';

        // Megakadályozzuk, hogy a rejtett mezők validálása hibát okozzon
        document.getElementById('login_username').disabled = true;
        document.getElementById('login_password').disabled = true;
        document.getElementById('login_email').disabled = false;
        document.getElementById('login_email').setAttribute('required', '');
    } else {
        // Vissza a bejelentkezés módra
        loginUserGroup.style.display = 'block';
        loginPasswordGroup.style.display = 'block';
        loginEmailGroup.style.display = 'none';
        loginSubmit.textContent = 'Belépés';
        toggleBtn.textContent = 'Elfelejtettem a jelszót';
        loginMessage.textContent = '';

        document.getElementById('login_username').disabled = false;
        document.getElementById('login_password').disabled = false;
        document.getElementById('login_email').disabled = true;
        document.getElementById('login_email').removeAttribute('required');
    }
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const loginMessage = document.getElementById('loginMessage');

    if (!isForgotPassword) {
        // Bejelentkezési folyamat
        const username = document.getElementById('login_username').value;
        const password = document.getElementById('login_password').value;
        
        fetch('http://localhost/backend/public/account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                username: username,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Login response:", data);
            if (data.success) {
                // Token és userRole tárolása
                localStorage.setItem('token', data.token);
                localStorage.setItem("userRole", data.user?.role || "");
                
                // Ellenőrzés: ha deliveryPerson, akkor a megfelelő oldalra irányítjuk
                if (data.user?.role === 'deliveryPerson') {
                    window.location.href = "/view/deliveryPerson/deliveryPerson.html";
                } else {
                    window.location.href = "mainpage.html";
                }
            } else {
                loginMessage.style.color = 'red';
                loginMessage.textContent = data.error;
            }
        })
        .catch(error => {
            console.error('Hiba:', error);
        });        
    } else {
        // Jelszó visszaállítási folyamat
        const email = document.getElementById('login_email').value;
        if (!email) {
            loginMessage.style.color = 'red';
            loginMessage.textContent = "Kérlek, add meg az email címedet.";
            return;
        }
        
        // csak az email kell, URL-encoded formban
        const formData = new URLSearchParams();
        formData.append('email', email);
    
        fetch('http://localhost/backend/public/forgot_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                loginMessage.style.color = 'green';
                loginMessage.textContent = data.message;
            } else if (data.error) {
                loginMessage.style.color = 'red';
                loginMessage.textContent = data.error;
            }
        })
        .catch(error => {
            console.error('Hiba:', error);
            loginMessage.style.color = 'red';
            loginMessage.textContent = "Hiba történt a kérés során.";
        });
    }
});
