$(function (){

    var count = 1200; // ustawienie licznika na 60 sekund
    var counter = setInterval(timer, 1000); // ustawienie funkcji odpowiedajacej za cykliczne wywolanie(co 1 s) funkcji timer()

    function timer()
    {
        --count;
        var minutes = Math.floor(count / 60); // obliczanie ile minut zostało
        var sec = count % 60; //obliczanie ile sekund zostało reszta z dzielenia licznika przez 60 sekund

        if(sec<10) sec = '0' + sec; // jeżeli mniej niż 10 sekund to wyświetl sekundy w formacie 00 zamiast 0  
        var out = minutes + ':' + sec; //tekst wyswietlony uzytkownikowi
        $("#timer").html(out); // przypisanie tekstu timera do odpowiedniego elementu html
        if( count <= 0) //licznik osiągnął 0 
        {
            //licznik się wyzerował należy podjąć odpowiednią akcje
            location.replace('/logout');

            clearInterval(counter); //zatrzymanie licznika
            return; 
        }
    }

    $('[data-toggle="tooltip"]').tooltip();
});

$(function() {

            // wybór stopki
            $(".nav-link").on('click',(function(e){
                e.preventDefault();
                var href = $(this).attr('href');
                var link = href.substring(1, href.length);

                $('.footer-pane').hide();
                $('#footer'+link).show();
            }));

            // wybór trasy
            $(".click_route").on('click',(function(e){
                e.preventDefault();
                var id = $(this).attr('id');
                var name = $("#route #"+id).find("#name").text();
                var description = $("#route #"+id).find("#description").text();
                var km = parseFloat($("#route #"+id).find("#km").html());
                var counter_form = parseFloat($("#course_counter").attr("value"));
                var rate_form = parseFloat($("#course_rate").attr("value"));

                var counter = counter_form + km;
                var value = rate_form * km;

                $("#course_description").attr("value", description);
                $("#course_trip").attr("value", name);
                $("#course_km").attr("value", km);

                $("#course_counter").attr("value", counter);
                $("#course_value").attr("value", value.toFixed(2));

                $('#addRoute').modal('hide')
            }));

            // edycja komentarzy print
            $("#coursesList").on('click', '.action-note', (function(e){
                e.preventDefault();
                var id = $(this).attr('id');
                var data = $(this).attr('data');

                $("#edit_comments_id").val(id);
                $("#edit_comments_text").val(data);
            }));

            // zapis komentarza do bazy - edycja
            $("#saveNote").on('click',(function(e){
                e.preventDefault();
                var id = $("#edit_comments_id").attr("value");
                var text = $("#edit_comments_text").val();
                var action = $(this).attr('action');

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: { id: id, text: text },
                    success: function (msg) {
                        $("#coursesList #"+id).find(".action-note").attr('data', text);
                        $("#coursesList #"+id).find(".action-note").text('Zapisano');

                        setTimeout(function(){
                            $("#coursesList #"+id).find(".action-note").text('Uwagi');
                        }, 2000);
                    },
                    error: function (msg) {
                       alert("Nie można zapisać. Wystąpił błąd.");
                   }
               });
            }));

            // przeliczenie wartości po wpisaniu km
            $("#course_km").on('keyup change',(function(e){
                e.preventDefault();
                var km = parseFloat($(this).val());
                var rate = $("#course_rate").val();
                var counter_start = parseFloat($("#end_counter").text());

                var value = km * rate;
                var counter = counter_start + km;

                $("#course_value").val(value.toFixed(2));
                $("#course_counter").val(counter);
            }));

            // klikniecie do edycji rate
            $("#exitInputRate").on('click',(function(e){
                e.preventDefault();
                var x = $("#course_rate").prop("readonly");

                if(x == true) {
                    $("#course_rate").prop("readonly",false);
                } else {
                    $("#course_rate").prop("readonly",true);
                }
            }));

            // klikniecie do edycji value
            $("#exitInputValue").on('click',(function(e){
                e.preventDefault();
                var x = $("#course_value").prop("readonly");

                if(x == true) {
                    $("#course_value").prop("readonly",false);
                } else {
                    $("#course_value").prop("readonly",true);
                }
            }));

            // dodawanie trasy
            $("#addCourseForm").on('submit', (function(e){
                e.preventDefault();
                var action = $(this).attr("action");
                var data = $(this).serialize();

                $("#saveCourse").prop("disabled",true);
                $("#saveCourse").empty();
                $("#saveCourse").append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Zapisuje');

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: data,
                    success: function (msg) {
                        if(msg['danger'] == 'true') {
                            $("#saveCourse").empty();
                            $("#saveCourse").append('Błąd formularza.');
                        } else {
                            $('#coursesList').find('tbody').append(msg);

                            $("#course_description").attr('value', '');
                            $("#course_trip").attr('value', '');
                            $("#course_km").attr('value', '');
                            $("#course_value").attr('value', '0.00');
                            $("#course_comments").attr('value', '');

                            sortTable('#coursesList', '#course_sum_value', '#course_sum_km');

                            $("#saveCourse").empty();
                            $("#saveCourse").append('Zapisano.');
                        }
                    },
                    error: function (msg) {
                        $("#saveCourse").empty();
                        $("#saveCourse").append('Wystąpił błąd z połączeniem.');
                    }
                });

                setTimeout(function(){
                    $("#saveCourse").empty();
                    $("#saveCourse").append('Dodaj');
                    $("#saveCourse").prop("disabled",false);
                }, 3000);

            }));

            // edycja edycji print trasy
            $("#coursesList").on('click', ".action-edit", (function(e){
                e.preventDefault();
                var tr = $(this).parents("tr");
                var date = tr.find("#date").attr('data');
                var description = tr.find("#description").text();
                var trip = tr.find("#trip").text();
                var km = tr.find("#km").text();
                var rate = tr.find("#rate").text();
                var value = tr.find("#value").text();
                var counter = tr.find("#counter").text();
                var id = tr.attr('id');

                $("#e_course_date").val(date);
                $("#e_course_description").val(description);
                $("#e_course_trip").val(trip);
                $("#e_course_value").val(value);
                $("#e_course_km").val(km);
                $("#e_course_rate").val(rate);
                $("#e_course_counter").val(counter);
                $("#e_course_id").val(id);
            }));

            // edycja trasy
            $("#editCourseForm").on('submit',(function(e){
                e.preventDefault();
                var action = $(this).attr("action");
                var data = $(this).serialize();
                var save = $("#e_saveCourse");

                save.prop("disabled",true);
                save.empty();
                save.append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Zapisuje');

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: data,
                    success: function (msg) {
                        var d = new Date(msg['date']);
                        var yyyy = d.getFullYear().toString();
                        var mm = (d.getMonth()+1).toString();
                        var dd  = d.getDate().toString();
                        var date = (dd[1]?dd:"0"+dd[0]) + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + yyyy;

                        var table = $("#"+msg['id']);
                        table.find("#date").text(date);
                        table.find("#description").text(msg['description']);
                        table.find("#trip").text(msg['trip']);
                        table.find("#km").text(msg['km']);
                        table.find("#rate").text(msg['rate']);
                        table.find("#value").text(msg['value']);
                        table.find("#counter").text(msg['counter']);

                        sortTable('#coursesList', '#course_sum_value', '#course_sum_km');

                        save.empty();
                        save.append('Zapisano poprawnie');
                    },
                    error: function (msg) {
                        save.empty();
                        save.append('Wystąpił błąd z połączeniem.');
                    }
                });

                setTimeout(function(){
                    save.empty();
                    save.append('Zapisz');
                    save.prop("disabled",false);

                    $('#editCourse').modal('hide');
                }, 3000);
            }));

            // usuwanie trasy
            $('#coursesList').on('click', ".action-delete", function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const id = $(this).attr('id');

                $('#modal-delete').modal({ backdrop: true, keyboard: true })
                .off('click', '#modal-delete-button')
                .on('click', '#modal-delete-button', function () {
                    $.ajax({
                        url: href,
                        method: 'POST',
                        data: id,
                        success: function (msg) {
                            $('#coursesList #'+id).parents("tr").remove(); 

                            sortTable('#coursesList', '#course_sum_value', '#course_sum_km');
                        },
                        error: function (msg) {}
                    });
                });
            });

            // usuwanie kosztu
            $('#costsList').on('click', ".action-delete", function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const id = $(this).attr('id');

                $('#modal-delete').modal({ backdrop: true, keyboard: true })
                .off('click', '#modal-delete-button')
                .on('click', '#modal-delete-button', function () {
                    $.ajax({
                        url: href,
                        method: 'POST',
                        data: id,
                        success: function (msg) {
                            $('#costsList #'+id).parents("tr").remove(); 

                            sortTable('#costsList', '#costs_sum_value', '#costs_sum_km');
                        },
                        error: function (msg) {}
                    });
                });
            });

            // wybór kosztu
            $(".click_costs").on('click',(function(e){
                e.preventDefault();
                var id = $(this).attr('id');
                var description = $("#cost #"+id).find("#description").text();
                var documentx = $("#cost #"+id).find("#document").text();
                var value = $("#cost #"+id).find("#value").text();

                $("#cost_description").attr("value", description);
                $("#cost_document").attr("value", documentx);
                $("#cost_value").attr("value", value);

                $('#addCosts').modal('hide');
            }));

            // edycja edycji kosztów print
            $("#costsList").on('click', ".action-edit-cost", (function(e){
                e.preventDefault();
                var tr = $(this).parents("tr");
                var date = tr.find("#date").attr('data');
                var documentx = tr.find("#document").text();
                var description = tr.find("#description").text();
                var value = tr.find("#value").text();
                var id = tr.attr('id');

                $("#e_cost_date").val(date);
                $("#e_cost_description").val(description);
                $("#e_cost_document").val(documentx);
                $("#e_cost_value").val(value);
                $("#e_cost_id").val(id);
            }));

            // dodawanie kosztu
            $("#addCostForm").on('submit', (function(e){
                e.preventDefault();
                var action = $(this).attr("action");
                var data = $(this).serialize();

                $("#saveCost").prop("disabled",true);
                $("#saveCost").empty();
                $("#saveCost").append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Zapisuje');

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: data,
                    success: function (msg) {
                        if(msg['danger'] == 'true') {
                            $("#saveCost").empty();
                            $("#saveCost").append('Popraw formularz.');
                        } else {
                            $('#costsList').find('tbody').append(msg);

                            $("#cost_description").attr('value', '');
                            $("#cost_document").attr('value', '');
                            $("#cost_value").attr('value', '0.00');

                            sortTable('#costsList', '#costs_sum_value', '#costs_sum_km');

                            $("#saveCost").empty();
                            $("#saveCost").append('Zapisano poprawnie');
                        }
                    },
                    error: function (msg) {
                        $("#saveCost").empty();
                        $("#saveCost").append('Wystąpił błąd z połączeniem.');
                    }
                });

                setTimeout(function(){
                    $("#saveCost").empty();
                    $("#saveCost").append('Dodaj');
                    $("#saveCost").prop("disabled",false);
                }, 3000);
            }));

            // edycja kosztu
            $("#editCostsForm").on('submit',(function(e){
                e.preventDefault();
                var action = $(this).attr("action");
                var data = $(this).serialize();
                var save = $("#e_saveCost");

                save.prop("disabled",true);
                save.empty();
                save.append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Zapisuje');

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: data,
                    success: function (msg) {
                        var d = new Date(msg['date']);
                        var yyyy = d.getFullYear().toString();
                        var mm = (d.getMonth()+1).toString();
                        var dd  = d.getDate().toString();
                        var date = (dd[1]?dd:"0"+dd[0]) + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + yyyy;

                        var table = $("#"+msg['id']);
                        table.find("#date").text(date);
                        table.find("#document").text(msg['document']);
                        table.find("#description").text(msg['description']);
                        table.find("#value").text(msg['value']);

                        sortTable('#costsList', '#costs_sum_value', '#costs_sum_km');

                        save.empty();
                        save.append('Zapisano poprawnie');
                    },
                    error: function (msg) {
                        save.empty();
                        save.append('Wystąpił błąd z połączeniem.');
                    }
                });

                setTimeout(function(){
                    save.empty();
                    save.append('Zapisz');
                    save.prop("disabled",false);

                    $('#editCosts').modal('hide');
                }, 3000);
            }));

            // edycja formularza VAT
            $("#vatForm").on('submit',(function(e){
                e.preventDefault();
                var action = $(this).attr("action");
                var data = $(this).serialize();
                var save = $("#vatFormSave");

                save.prop("disabled",true);
                save.empty();
                save.append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Zapisuje');

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: data,
                    success: function (msg) {
                        save.empty();
                        save.append('Zapisano poprawnie');
                    },
                    error: function (msg) {
                        save.empty();
                        save.append('Wystąpił błąd z połączeniem.');
                    }
                });

                setTimeout(function(){
                    save.empty();
                    save.append('Zapisz');
                    save.prop("disabled",false);

                    $('#openVatForm').modal('hide');
                }, 3000);
            }));

            // pobieranie csv
            $(".download").on('click',(function(e){
                e.preventDefault();
                var href = $(this).attr('href')

                $.ajax({
                    url: href,
                    method: 'GET',
                    data: href,
                    success: function (msg) {
                        window.location.href = "/"+msg;
                    },
                    error: function (msg) {
                     alert("Nie można pobrać pliku.");
                    }
                });
            }));

            // generowanie zestawienia
            $("#_easyadmin_form_design_element_3-tab").on('click',(function(e){
                e.preventDefault();
                var href = $(this).attr('data');

                $.ajax({
                    url: href,
                    success: function (msg) {
                        $('#tailyList').find('tbody').html(msg);
                        $("#tailyList").show();
                    },
                    error: function (msg) {
                        alert("Nie można pobrać danych.");
                    }
                });
            }));

            // wysyłanie wiadomości do pomocy technicznej
            $("#sendHelp").on('submit',(function(e){
                e.preventDefault();
                var href = $(this).attr('action');
                var text = $(this).find("#send_help_text").val();

                $("#sendHelpButton").prop("disabled",true);
                $("#sendHelpButton").empty();
                $("#sendHelpButton").append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wysyłam...');

                $.ajax({
                    url: href,
                    method: 'GET',
                    data: { text: text },
                    success: function (msg) {
                        if(msg == false) {
                            $("#sendHelpButton").text("Błąd wysyłania wiadomości.");
                        } else {
                            $("#printHelp").append(msg);
                            $("#windowHelp").scrollTop($("#windowHelp")[0].scrollHeight);
                            $("#sendHelpButton").text("Wysłano wiadomość.");
                        }
                    },
                    error: function (msg) {
                        $("#sendHelpButton").text("Błąd nawiązania połączenia.");
                    }
                });

                setTimeout(function(){
                    $("#send_help_text").val('');
                    $("#sendHelpButton").prop("disabled",false);
                    $("#sendHelpButton").text("Wyślij wiadomość");
                }, 3000);
            }));

            // pobieranie wiadomości pomocy technicznej
            $("#help").on('click',(function(e){
                e.preventDefault();
                var href = $(this).attr('href');

                $.ajax({
                    url: href,
                    success: function (msg) {
                        $("#printHelp").html(msg);
                        $("#windowHelp").scrollTop($("#windowHelp")[0].scrollHeight);
                    },
                    error: function (msg) {
                        $("#printHelp").text("Błąd nawiązania połączenia.");
                    }
                });
            })); 
          
            // funkcja sortowania i zliczania tabelek
            function sortTable(table, sum_value, sum_km) {
                var listitems = $(table+' tbody').find('tr').get();

                listitems.sort(function(a, b) {
                    var compA = $(a).find("#date").attr('data');
                    var compB = $(b).find("#date").attr('data');
                    return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
                })
                $(table+' tbody').append(listitems);

                var i = 1;
                var suma = 0;
                var km = 0;
                $(table+' tbody tr').each(function(){
                    var plus = parseFloat($(this).find("#value").text());
                    var plus_km = parseFloat($(this).find("#km").text());

                    if(plus > 0) {
                        suma = suma + plus;
                        km = km + plus_km;
                    }

                    $(this).find("#lp").text(i++);
                });
                $(sum_value).text(suma.toFixed(2));
                $(sum_km).text(km);
            }

        });