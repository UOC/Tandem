$(function () {
    $(".viewFeedback").click(function () {
        var $this = $(this);
        var feedbackId = $this.data("feedback-id");
        if (showDelete) {
            window.open("feedback.php?id_feedback=" + feedbackId);
        } else {
            window.location = "feedback.php?id_feedback=" + feedbackId;
        }
    });
    $(".deleteFeedback").click(function () {
        var $this = $(this);
        var deleteId = $this.data("delete-id");
        $('#deleteBtn').data("id-to-delete", deleteId);
        $('#deleteModal').modal('show');
    });
    $("#deleteBtn").click(function () {
        var $this = $(this);
        var deleteId = $this.data("id-to-delete");
        $.ajax({
            type: 'POST',
            url: "deleteTandem.php",
            data: {'id': deleteId},
            success: function (response) {
                // noinspection EqualityComparisonWithCoercionJS
                if (response == 1) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    setTimeout(function () {
                        $('#deleteError').css("display", "block");
                    }, 500);
                }
            }
        });
    });
    $('.alert').tooltip();

    var $showfeedback = $("#showFeedback");
    if (isInstructor) {
        $("#selectUser").change(function () {
            $("#selectUserForm").submit();
        });
        $("#finishedTandem").change(function () {
            $("#selectUserForm").submit();
        });
        $("#tandemType").change(function () {
            $("#selectUserForm").submit();
        });
        $showfeedback.change(function () {
            $("#selectUserForm").submit();
        });
    } else {
        $showfeedback.change(function () {
            $("#showTandemsFeedbackform").submit();
        });
    }

    if (!disableProfileForm) {
        if (showRegistryForm) {
            $("#registry-modal-form").modal("show");
        }
        if (showNewRegistryForm) {
            $("#registry-modal-form-new").modal("show");
        }
        if (showSecondForm) {
            if (showSecondRegistryForm) {
                $("#registry-modal-form-second").modal("show");
            }
            $("#viewProfileFormSecond").click(function () {
                $("#registry-modal-form-second").modal("show");
            });
            //$('.slider1_4').slider({min: '1',max : '4'});
            $('#extra-info-second').on("invalid.bs.validator", function () {
                $("#error_second_form").html('<div class="alert alert-danger" role="alert">' + mustFillStr + '</div>');
            });
        }
    }

    // slider
    $('.slider').slider({min: '0', max: '100'});

    if (!disableProfileForm) {
        $("#viewProfileForm").click(function () {
            if (noTeams) {
                $("#registry-modal-form-new").modal("show");
            } else {
                $("#registry-modal-form").modal("show");
            }
        });
    }

    $("#dateStart").datepicker({dateFormat: 'yy-mm-dd', altFormat: 'dd-mm-yy', firstDay: 1});
    $("#dateEnd").datepicker({dateFormat: 'yy-mm-dd', altFormat: 'dd-mm-yy', firstDay: 1});

    var $extrainfo = $("#extra-info");
    $extrainfo.find("input[type=checkbox]").change(function () {
        var $textarea = $(this).parent().next("textarea");
        $textarea.toggleClass("hide");
        $textarea.val("");
    });
    $("#submit-extra-info").click(function () {
        $("#extra-info").submit();
    });
    $("#submit-extra-info-new").click(function () {
        $("#extra-info-new").submit();
    });
    if (showSecondForm) {
        $("#submit-extra-info-second").click(function () {
            $("#extra-info-second").submit();
        });
    }

    // find all checked checkboxes and open the textarea
    $extrainfo.find("input[type=checkbox]:checked").each(function () {
        var $textarea = $(this).parent().next("textarea");
        $textarea.toggleClass("hide");
    });

    // user feedback starts
    if (userHasFeedbackStars) {
        var $partnerRate = $("#partner_rate");
        $partnerRate.rating({
            stars: 5,
            min: 0.0,
            max: 5.0,
            step: 0.1,
            size: 'xs',
            clearButton: '',
            clearCaption: '',
            readonly: true,
            starCaptions: function (stars) {
                return '' + stars + ' ' + starsStr
            }
        });
        $partnerRate.rating('update', '' + userStars);
    }

    $('.selected-user-select2').select2();
});
