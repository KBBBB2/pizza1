  // Betöltéskor fusson le
  (function() {
    const token = localStorage.getItem('token');
    const role  = localStorage.getItem('userRole');

    // Ha nincs token, vagy nem admin, irányítsuk vissza a bejelentkezőhöz
    if (!token || role !== 'deliveryPerson') {
      window.location.replace('/view/customer/mainpage.html');
    }
  })();
