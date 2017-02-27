$(document).ready(function(){
    // adauga buton submit in head-ul orders
    $('.panel-heading-action, .pageTitle').show();
    $('.panel-heading-action, .pageTitle').prepend('<div align="right" style="padding-right:3px; float:right;"><input style="height:27px;" type="button" class="button" value="Adauga in lista de livrari Urgent Cargus" id="add_urgent_bulk"></div>');

    // adauga checkbox pentru fiecare comanda
    $('#content table.table tbody tr').each(function () {
        var id_order = $(this).children('td:nth-child(2)').text();
        $(this).children('td:first-child').html('<input type="checkbox" name="orderBox[]" value="' + id_order.trim() + '" />');
    });
    
    // ruleaza ajax in loop pt adaugarea comenzilor selectate in lista de livrare
    $('#add_urgent_bulk').live('click', function () {
        if ($('[name="orderBox[]"]:checked').length == 0) {
            alert('Va rugam sa selectati cel putin o comanda!');
        } else {
            var add = 0;
            var err = 0;
            $('[name="orderBox[]"]:checked').each(function () {
                var id = parseInt($(this).val());
                $.ajax({
                    async: false,
                    url: urgentcargus_url + urgentcargus_admindir + '/index.php?controller=UrgentCargusAdmin&token=true&type=ADDORDER&secret=' + secret + '&id=' + id + '&rand=' + Math.floor((Math.random() * 1000000) + 1),
                    success: function (data) {
                        if (data == 'ok') {
                            ++add;
                        } else {
                            ++err;
                        }
                    }
                });
            });
            if (add > 0) {
                alert(add + ' comenzi au fost adaugate in expeditia curenta UrgentCargus!');
            }
            if (err > 0) {
                alert(err + ' comenzi nu au putut fi adaugate in expeditia curenta UrgentCargus!');
            }
        }
        return false;
    });
});