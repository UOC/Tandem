// Default strings
mailingStrings = mailingStrings || {
    inProgress: 'In progress',
    doneWithoutErrors: 'Done without errors.',
    doneWithErrors: 'Finished with errors.'
};

$(function () {
    var $confirmModal = $("#confirm-modal");
    $('.btn-confirm').click(function () {
        var $confirmButton = $(this);
        var currentAction = $confirmButton.data('action');
        var actionDescription = $confirmButton.closest('tr').find('.tool-name').html();
        $('input[name="modal-action"]').val(currentAction);
        $('#modal-action-name').html(actionDescription);
        $confirmModal.modal('show');
    });

    $("#modal-btn-cancel").on("click", function () {
        $("#confirm-modal").modal('hide');
    });

    function runAction(action, onSuccess, onError) {
        var controller;
        switch (action) {
            case 'send-ranking':
                controller = 'api/mailingSendRankingToAllUsers.php';
                break;
            default:
                onError();
                break;
        }

        $.post(controller, function (res) {
            if (res.result === 'ok') {
                onSuccess();
            } else {
                onError();
            }
        }).fail(function () {
            onError();
        });
    }

    $("#modal-btn-confirm").on("click", function () {
        $("#confirm-modal").modal('hide');

        var action = $('input[name="modal-action"]').val();
        var $actionBtn = $('a[data-action="' + action + '"]');
        var $status = $actionBtn.closest('tr').find('.status');

        $status.html(mailingStrings.inProgress);
        runAction(action, function () {
            $status.html(mailingStrings.doneWithoutErrors);
        }, function () {
            $status.html(mailingStrings.doneWithErrors);
        });
    });
});
