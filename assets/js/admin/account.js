// Függvény, amely lekéri az account rekordokat, opcionálisan keresési feltétellel
function loadAccounts(query = '') {
    $.ajax({
      url: '/merged/controller/adminAccount.php',
      type: 'GET',
      data: { action: 'getAccounts', q: query },
      dataType: 'json',
      success: function(response) {
        var tbody = $('#accountsTable tbody');
        tbody.empty();
        $.each(response, function(index, account) {
          var row = $('<tr></tr>');
          // Csak a szükséges oszlopok megjelenítése (id, password nélkül)
          row.append('<td>' + account.firstname + '</td>');
          row.append('<td>' + account.lastname + '</td>');
          row.append('<td>' + account.username + '</td>');
          row.append('<td>' + account.email + '</td>');
          row.append('<td>' + account.phonenumber + '</td>');
          row.append('<td>' + account.created + '</td>');
          row.append('<td>' + (account.locked == 1 ? 'Igen' : 'Nem') + '</td>');
          row.append('<td>' + (account.disabled == 1 ? 'Igen' : 'Nem') + '</td>');
          
          // Akciók: ideiglenes tiltás és végleges tiltás gombok
          var actionsTd = $('<td class="action-container"></td>');
          
          // Ideiglenes tiltás gomb
          var tempBanBtn = $('<button>Ideiglenes tiltás</button>');
          tempBanBtn.click(function(){
            // Ha még nincs megjelenítve a mező, akkor jelenjen meg egy input mező a tiltási idő megadásához
            if ($(this).siblings('.tempDuration').length === 0) {
              var durationInput = $('<input type="text" class="tempDuration" placeholder="Pl. 5m, 2h, 1d">');
              var confirmBtn = $('<button>Megerősít</button>');
              confirmBtn.click(function(){
                var duration = durationInput.val();
                // Itt adhatsz validációt a formátumra, ha szükséges
                $.ajax({
                  url: '/merged/controller/adminAccount.php',
                  type: 'POST',
                  data: { action: 'tempBan', id: account.id, duration: duration },
                  success: function(){
                    alert("ideglenes tiltás: sikeresen végrehajta");
                    loadAccounts($('#search').val());
                  }
                });
              });
              $(this).after(confirmBtn).after(durationInput);
            }
          });
          actionsTd.append(tempBanBtn);
          
          // Végleges tiltás gomb
          var permBanBtn = $('<button>Tartós tiltás</button>');
          permBanBtn.click(function(){
            if (confirm("Biztosan végleges tiltás?")) {
              $.ajax({
                url: '/merged/controller/adminAccount.php',
                type: 'POST',
                data: { action: 'permBan', id: account.id },
                success: function(){
                  alert("végleges tiltás: sikeresen végrehajtva");
                  loadAccounts($('#search').val());
                }
              });
            }
          });
          actionsTd.append(permBanBtn);
          
          row.append(actionsTd);
          tbody.append(row);
        });
      }
    });
  }
  
  $(document).ready(function(){
    loadAccounts();
    
    // Keresés gomb eseménykezelője
    $('#searchBtn').click(function(){
      var query = $('#search').val();
      loadAccounts(query);
    });
  });

document.addEventListener("DOMContentLoaded", loadAccounts);