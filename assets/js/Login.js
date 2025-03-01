// login.js
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var username = document.getElementById('login_username').value;
    var password = document.getElementById('login_password').value;
    fetch('/merged/Controller/account.php', {
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
        var msgDiv = document.getElementById('loginMessage');
        if (data.success) {
            // Elmentjük a kapott role értéket a localStorage-be
            localStorage.setItem("userRole", data.user.role);
            window.location.href = "mainpage.html";
        } else {
            msgDiv.style.color = 'red';
            msgDiv.textContent = data.error;
        }
    })
    .catch(error => {
        console.error('Hiba:', error);
    });
});
