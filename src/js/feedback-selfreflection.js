// Default strings
feedbackString = feedbackString || {
    errorCommentStr: 'Please, introduce some comment.',
    errorCharactersStr: 'All items should have at least %s characters.',
    errorItemsStr: 'Please, introduce at least %s item(s).',
    errorSavedStr: '<strong>Error!</strong> Unable to save your feedback. Try again later.',
    savedOkStr: '<strong>Success!</strong> Your feedback was saved.'
};
// Default validation rules
feedbackValidation = feedbackValidation || {
    minWellItems: 1,
    minErrorItems: 1,
    itemsMinChars: 3,
    commentsRequired: false
};

$(function () {
    // add item to list helper
    function addFeedbackToList($list, $input) {
        var inputValue = $input.val().trim();
        if (!inputValue) { // If empty, do not append.
            return false;
        }
        $list.append(
            "<li class='panel panel-default'>" +
            "<div class='panel-body'>" +
            "<span class='added-feedback'>" +
            inputValue +
            "</span>" +
            "<a href='javascript:' class='close' aria-hidden='true'>&times;</a>" +
            "</div>" +
            "</li>"
        );
        $input.val('');
    }

    // input event handlers
    for (var m = 0; m < 2; ++m) {
        var selector = m === 0 ? 'selfreflection' : 'partner';
        for (var k = 1; k < 3; ++k) {
            $("#" + selector + "-feedback-button-" + k).click((function (k, selector) {
                return function () {
                    addFeedbackToList($("#" + selector + "-feedback-list-" + k), $("input[name=" + selector + "-feedback-" + k + "]"));
                }
            })(k, selector));
            $("input[name=" + selector + "-feedback-" + k + "]").keypress((function (k, selector) {
                return function (e) {
                    if (13 === parseInt(e.keyCode)) {
                        addFeedbackToList($("#" + selector + "-feedback-list-" + k), $("input[name=" + selector + "-feedback-" + k + "]"));
                        return false;
                    }
                }
            })(k, selector));
        }
    }
    // remove item list event handler
    $(".feedback-list").on('click', '.close', function () {
        $(this).closest("li").remove();
    });
    // form is sending flag
    var isSending = false;

    // send form helpers
    function trimElementContent(n) {
        return $(n).html().trim();
    }

    function disableInputs() {
        $('.feedback-list .close').hide();
        $('input[name*="-feedback-"]').prop('disabled', true);
        $('button[id*="-feedback-"]').attr("disabled", "disabled");
        $('textarea[name*="-feedback-"]').prop('disabled', true);
    }

    function enableInputs() {
        $('.feedback-list .close').show();
        $('input[name*="-feedback-"]').prop('disabled', false);
        $('button[id*="-feedback-"]').removeAttr("disabled");
        $('textarea[name*="-feedback-"]').prop('disabled', false);
    }

    function removeInputs() {
        $('.feedback-list .close').remove();
        $('.feedback-form').remove();
        $('#save-feedback-data').remove();
    }

    function fetchFormData() {
        var selfWellList = $('#selfreflection-feedback-list-1').find('.added-feedback');
        selfWellList = $.map(selfWellList, trimElementContent);
        var selfErrorsList = $('#selfreflection-feedback-list-2').find('.added-feedback');
        selfErrorsList = $.map(selfErrorsList, trimElementContent);
        var selfOtherComments = $('textarea[name=selfreflection-feedback-3]').val().trim();
        var partnerWellList = $('#partner-feedback-list-1').find('.added-feedback');
        partnerWellList = $.map(partnerWellList, trimElementContent);
        var partnerErrorsList = $('#partner-feedback-list-2').find('.added-feedback');
        partnerErrorsList = $.map(partnerErrorsList, trimElementContent);
        var partnerOtherComments = $('textarea[name=partner-feedback-3]').val().trim();
        return {
            selfWellList: selfWellList,
            selfErrorsList: selfErrorsList,
            selfOtherComments: selfOtherComments,
            partnerWellList: partnerWellList,
            partnerErrorsList: partnerErrorsList,
            partnerOtherComments: partnerOtherComments
        };
    }

    function validateItemsMinChars(list, minChars) {
        var valid = true;
        list.forEach(function (element) {
            if (typeof element !== 'string' || element.length < minChars) {
                valid = false;
            }
        });

        return valid;
    }

    function notifyValidationError(errorType, $target, extraParam) {
        var errorMsg = '';
        switch (errorType) {
            case 'requiredComments':
                errorMsg = feedbackString.errorCommentStr;
                break;
            case 'minChars':
                errorMsg = feedbackString.errorCharactersStr.replace('%s', extraParam);
                break;
            case 'minItems':
                errorMsg = feedbackString.errorItemsStr.replace('%s', extraParam);
                break;
            default:
                return false;
        }

        $target.find('.form-group').addClass('has-error');
        $target.find('.validation-errors').removeClass('hidden').append(
            "<p class='text-danger'>" + errorMsg + "</p>"
        );
    }

    function validateFormData(data, rules) {
        var error = false;

        var $selfreflectionFeedbackForm1 = $('#selfreflection-feedback-form-1'),
            $selfreflectionFeedbackForm2 = $('#selfreflection-feedback-form-2'),
            $selfreflectionFeedbackForm3 = $('#selfreflection-feedback-form-3'),
            $partnerFeedbackForm1 = $('#partner-feedback-form-1'),
            $partnerFeedbackForm2 = $('#partner-feedback-form-2'),
            $partnerFeedbackForm3 = $('#partner-feedback-form-3');

        if (rules.minWellItems && data.selfWellList.length < rules.minWellItems) {
            notifyValidationError('minItems', $selfreflectionFeedbackForm1, rules.minWellItems);
            error = true;
        }
        if (rules.minWellItems && data.partnerWellList.length < rules.minWellItems) {
            notifyValidationError('minItems', $partnerFeedbackForm1, rules.minWellItems);
            error = true;
        }
        if (rules.minErrorItems && data.selfErrorsList.length < rules.minErrorItems) {
            notifyValidationError('minItems', $selfreflectionFeedbackForm2, rules.minErrorItems);
            error = true;
        }
        if (rules.minErrorItems && data.partnerErrorsList.length < rules.minErrorItems) {
            notifyValidationError('minItems', $partnerFeedbackForm2, rules.minErrorItems);
            error = true;
        }
        if (rules.itemsMinChars) {
            if (!validateItemsMinChars(data.selfWellList, rules.itemsMinChars)) {
                notifyValidationError('minChars', $selfreflectionFeedbackForm1, rules.itemsMinChars);
                error = true;
            }
            if (!validateItemsMinChars(data.partnerWellList, rules.itemsMinChars)) {
                notifyValidationError('minChars', $partnerFeedbackForm1, rules.itemsMinChars);
                error = true;
            }
            if (!validateItemsMinChars(data.selfErrorsList, rules.itemsMinChars)) {
                notifyValidationError('minChars', $selfreflectionFeedbackForm2, rules.itemsMinChars);
                error = true;
            }
            if (!validateItemsMinChars(data.partnerErrorsList, rules.itemsMinChars)) {
                notifyValidationError('minChars', $partnerFeedbackForm2, rules.itemsMinChars);
                error = true;
            }
        }
        if (rules.commentsRequired && !data.selfOtherComments) {
            notifyValidationError('requiredComments', $selfreflectionFeedbackForm3);
            error = true;
        }
        if (rules.commentsRequired && !data.partnerOtherComments) {
            notifyValidationError('requiredComments', $partnerFeedbackForm3);
            error = true;
        }

        return !error;
    }

    function loadSavedData(data) {
        // Show textareas saved text
        var $savedSelfOtherCommentsP = $('#saved-self-other-comments');
        if (data.selfOtherComments) {
            $savedSelfOtherCommentsP.html(data.selfOtherComments);
        }
        $savedSelfOtherCommentsP.removeClass('hidden');
        var $savedPartnerOtherCommentsP = $('#saved-partner-other-comments');
        if (data.partnerOtherComments) {
            $savedPartnerOtherCommentsP.html(data.partnerOtherComments);
        }
        $savedPartnerOtherCommentsP.removeClass('hidden');
    }

    function notifyPostError() {
        $('.post-feedback-alerts-container').append(
            '<div class="alert alert-error alert-dismissible">' +
            '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' +
            feedbackString.errorSavedStr +
            '</div>'
        );
    }

    function notifyPostSuccess() {
        $('.post-feedback-alerts-container').append(
            '<div class="alert alert-success alert-dismissible">' +
            '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' +
            feedbackString.savedOkStr +
            '</div>'
        );
    }

    function clearNotifications() {
        $('.post-feedback-alerts-container').empty();
        $('form[id*=-feedback-form-]').find('.form-group').removeClass('has-error');
        $('.validation-errors').empty();
    }

    function addPendingFormData() {

        for (var m = 0; m < 2; ++m) {
            var selector = m === 0 ? 'selfreflection' : 'partner';
            for (var k = 1; k < 3; ++k) {

                var val = $("input[name=" + selector + "-feedback-" + k + "]").val();
                if (typeof val === 'string' && val.length >= feedbackValidation.itemsMinChars) {
                    addFeedbackToList($("#" + selector + "-feedback-list-" + k), $("input[name=" + selector + "-feedback-" + k + "]"));
                }
            }
        }
    }

    // send form handler
    $('#save-feedback-data').click(function () {
        if (isSending) {
            return false;
        }

        isSending = true;

        addPendingFormData();

        // disable all edition inputs while sending
        disableInputs();
        // clear previous validation and requests success/error notifications
        clearNotifications();

        // fetch all introduced data
        var __formData = fetchFormData();

        // validate introduced data
        if (!validateFormData(__formData, feedbackValidation)) {
            enableInputs();
            isSending = false;
            return false;
        }

        // post data
        $.post('api/postFeedback.php', __formData, function (res) {
            if (res.result === 'ok') {
                notifyPostSuccess();
                removeInputs();
                loadSavedData(__formData);
            } else {
                notifyPostError();
                enableInputs();
            }
        }).fail(function () {
            notifyPostError();
            enableInputs();
        }).always(function () {
            isSending = false;
        });
    });
});
