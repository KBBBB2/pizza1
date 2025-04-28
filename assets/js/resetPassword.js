document.addEventListener('DOMContentLoaded', () => {
    const params   = new URLSearchParams(location.search);
    const token    = params.get('token');
    const msgBox   = document.getElementById('resetMessage');
    const pwInput  = document.getElementById('password');
    const pw2Input = document.getElementById('confirm_password');
    const btn      = document.getElementById('resetSubmit');

    const API = 'http://localhost/backend/public/reset_password.php';
  
    if (!token) {
      msgBox.style.color = 'red';
      msgBox.textContent = 'Érvénytelen vagy hiányzó token.';
      btn.disabled = true;
      return;
    }
  
    // Ellenőrizhetjük GET-tel, hogy él-e még a token:
    fetch(`${API}?token=${encodeURIComponent(token)}`, {
        method: 'GET'
      })
      .then(r => r.json())
      .then(j => {
        if (j.error) {
          msgBox.style.color = 'red';
          msgBox.textContent = j.error;
          btn.disabled = true;
        }
      });
  
    btn.addEventListener('click', () => {
      msgBox.textContent = '';
      const pw  = pwInput.value;
      const pw2 = pw2Input.value;
      if (!pw || pw !== pw2) {
        msgBox.style.color = 'red';
        msgBox.textContent = 'A jelszavak nem egyeznek vagy üresek.';
        return;
      }
  
      fetch(`${API}?token=${encodeURIComponent(token)}`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ password: pw, confirm_password: pw2 })
      })
      .then(r => r.json())
      .then(j => {
        if (j.error) {
          msgBox.style.color = 'red';
          msgBox.textContent = j.error;
        } else {
            window.location.href = '/view/customer/login.html?reset=success';
        }
      })
      .catch(() => {
        msgBox.style.color = 'red';
        msgBox.textContent = 'Hiba a szerverrel.';
      });
    });
  });
  