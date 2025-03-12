// Regisztrációs űrlap kezelése
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var firstname = document.getElementById('firstname').value;
    var lastname = document.getElementById('lastname').value;
    var email = document.getElementById('email').value;
    var phone = document.getElementById('phone').value;
    var password = document.getElementById('reg_password').value;
    var username = document.getElementById('reg_username').value;


    fetch('/merged/Controller/account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'register',
            firstname: firstname,
            lastname: lastname,
            email: email,
            phonenumber: phone,
            username: username,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        var msgDiv = document.getElementById('registerMessage');
        if (data.success) {
            window.location.href = "Login.html";
        } else {
            msgDiv.style.color = 'red';
            msgDiv.textContent = data.error;
        }
    })
    .catch(error => {
        console.error('Hiba:', error);
    });
});