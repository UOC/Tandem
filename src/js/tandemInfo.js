$(function () {
    var interval = setInterval(function () {
        $.ajax({
            type: 'POST',
            url: "getCurrentUserCount.php",
            data: {
                "active_tandems": 1
            },
            dataType: "JSON",
            success: function (json) {
                if (json && typeof json.users_en !== "undefined" && typeof json.users_es !== "undefined") {
                    $('#UsersWaitingEn').html(json.users_en);
                    $('#UsersWaitingEs').html(json.users_es);
                    $('#totalActiveTandems').html(json.active_tandems);
                }
            }
        });
    }, 2500);

    $("#tandemByDate").datepicker({
        dateFormat: 'yy-mm-dd', altFormat: 'dd-mm-yy', firstDay: 1,
        onSelect: function (date) {
            $.ajax({
                type: 'POST',
                url: "getNumTandemsByDate.php",
                dataType: "JSON",
                data: {date: date},
                success: function (json) {
                    if (json && typeof json.tandems !== "undefined") {
                        $('#nTandemsDate').html(json.tandems);
                    }
                }
            });
        }
    });

    $("#tandemFailedSuccessByDateStart").datepicker({
        dateFormat: 'yy-mm-dd',
        altFormat: 'dd-mm-yy',
        firstDay: 1
    });
    $("#tandemFailedSuccessByDateEnd").datepicker({
        dateFormat: 'yy-mm-dd',
        altFormat: 'dd-mm-yy',
        firstDay: 1
    });
    $("#startDateCurrentRanking").datepicker({dateFormat: 'yy-mm-dd', altFormat: 'dd-mm-yy', firstDay: 1});
    $("#endDateCurrentRanking").datepicker({dateFormat: 'yy-mm-dd', altFormat: 'dd-mm-yy', firstDay: 1});
    $("#view_details_en_US").click(function () {
        showUserList('en_US');
    });
    $("#view_details_es_ES").click(function () {
        showUserList('es_ES');
    });
    $("#view_details_all").click(function () {
        showUserList('all');
    });

    function showUserList(lang) {
        $('#view_details_' + lang).attr('disabled', true);
        $.ajax({
            type: 'GET',
            url: "getUsersWaiting.php",
            dataType: "JSON",
            data: {lang: lang},
            success: function (json) {
                $('#modalTitle').html("Details of users waiting to practice "
                    + ((lang === "es_ES") ? "Spanish" : (lang === "all") ? "" : "English"));
                var contentModalDetails = '';
                if (json && json.length > 0) {
                    contentModalDetails = '<ul>';
                    for (var i = 0; i < json.length; i++) {
                        contentModalDetails += '<li>' + json[i].fullname + '</li>';
                    }
                    contentModalDetails += '</ul>';

                } else {
                    contentModalDetails = "<?php echo $LanguageInstance->get('Not user found')?>";
                }
                $('#contentModalDetails').html(contentModalDetails);
                $('#modalUser').modal();
                $('#view_details_' + lang).attr('disabled', false);
            }
        });
    }
});
