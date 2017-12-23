function Feedback() {
}

Feedback.prototype.initialize = function init() {
    var vm = this;

    vm.initializeForm();
    vm.$component = $('#feedback');
    vm.$feedbackBlocker = $('.feedback_blocker', vm.$component);
    vm.$feedbackSendBtn = $('.send', vm.$component);
    vm.isFormSubmit = false;
    vm.initializeEvents();
    vm.initializeFormValidation();
};

Feedback.prototype.initializeForm = function initializeForm() {
    $('.bottom_panel_window').prepend(
        "<div id='feedback' class='bottom_panel_item'>\
            <div class='feedback_blocker'></div>\
            <div class='title f-16'>Напишите нам письмо (<a href='mailto:augustova@mail.ru?subject=Сообщение с сайта для ЧТУП\"Августово-Компани\"'>augustova@mail.ru</a>)</div>\
            <div class='feedback_container'>\
                <div class='input_block' >\
                    <label for='message' class='f-15'>Содержимое письма</label>\
                    <textarea id='message' placeholder='Содержимое письма' class='message input f-15' cols='40' rows='5'></textarea>\
                    <div class='message_validation f-11'></div>\
                </div>\
                <div class='input_block' >\
                    <label for='name' class='f-15'>Меня зовут</label>\
                    <input id='name' placeholder='Меня зовут' class='name input f-15'>\
                    <div class='name_validation f-11'></div>\
                </div>\
                <div class='input_block'>\
                    <label for='email' class='f-15'>Мой e-mail</label>\
                    <input id='email' placeholder='Мой e-mail' class='email input f-15'>\
                    <div class='email_validation f-11'></div>\
                </div>\
                <button class='send input_hover input_block f-17 button'>Отправить</button>\
            </div>\
        </div>");
};

Feedback.prototype.initializeFormValidation = function initFormValidation() {
    var vm = this;
    $('.message', vm.$component).on('change paste focus textInput input', function () {
        if (vm.isFormSubmit) {
            var value = $(this).val();
            if (U.hasContent(value) && (value.length > 1000 || value.length == 0)) {
                $('.message', vm.$component).addClass('validation_error');
                $('.message_validation', vm.$component).addClass('validation_message').val('Сообщение слишком большое.');
            } else {
                $('.message', vm.$component).removeClass('validation_error');
                $('.message_validation', vm.$component).removeClass('validation_message').val('');
            }
        }
    });
    $('.name', vm.$component).on('change paste focus textInput input', function () {
        if (vm.isFormSubmit) {
            var value = $(this).val();
            if (U.hasContent(value) && value.length > 50) {
                $('.name', vm.$component).addClass('validation_error');
                $('.name_validation', vm.$component).addClass('validation_message').val('Имя слишком большое.');
            } else {
                $('.name', vm.$component).removeClass('validation_error');
                $('.name_validation', vm.$component).removeClass('validation_message').val('');
            }
        }
    });
};

//popup.setData(popupContent);
Feedback.prototype.isFeedbackNotActive = function isFeedbackNotActive() {
    return !popup.isVisible;
};

Feedback.prototype.validateForm = function validateForm() {
    var vm = this;
    $('.input', vm.$component).trigger('change');
    return $('.validation_message', vm.$component).length == 0;
};

Feedback.prototype.isEmptyMessage = function isEmptyMessage() {
    var vm = this;
    return $('.message', vm.$component).val() == '';
};

Feedback.prototype.initializeEvents = function initEvents() {
    var vm = this;

    function onEmailSendFailed() {
        vm.$feedbackSendBtn.removeClass('input_disable').trigger('mouseleave');
        vm.$feedbackBlocker.removeClass('progress').removeClass('successful').addClass('error');
        setTimeout(function () {
            vm.$feedbackBlocker.fadeOut(100);
        }, 3000);
    }
    function onEmailSendCallback(data) {
        vm.$feedbackSendBtn.removeClass('input_disable').trigger('mouseleave');
        if (data === true) {
            vm.$feedbackBlocker.removeClass('progress').removeClass('error').addClass('successful');
        } else {
            vm.$feedbackBlocker.removeClass('progress').removeClass('successful').addClass('error');
        }
        setTimeout(function () {
            if (data === true) {
                vm.isFormSubmit = false;
                $('input, textarea', vm.$component).val('');
                $('.validation_error', vm.$component).removeClass('validation_error');
            }
            vm.$feedbackBlocker.fadeOut(100);
        }, 3000);
    }

    vm.$feedbackSendBtn.on('click', function () {
        vm.isFormSubmit = true;
        if (!$(this).hasClass('input_disable') && vm.validateForm() && !vm.isEmptyMessage()) {
            var messageBody = $('.message', vm.$component).val();
            var senderName = $('.name', vm.$component).val();
            var senderEmail = $('.email', vm.$component).val();

            var data = {
                message_body: messageBody,
                sender_name: senderName,
                sender_email: senderEmail,
                product: vm.getPageProductCode(),
                page: window.location.href
            };
            vm.$feedbackBlocker.fadeIn(100);
            vm.$feedbackBlocker.removeClass('successful').removeClass('error').addClass('progress');
            vm.$feedbackSendBtn.addClass('input_disable');

            vm.sendEmail(data, onEmailSendCallback, onEmailSendFailed)
        }
    }).focusin(function () {
        vm.$feedbackSendBtn.trigger('mouseenter');
    }).focusout(function () {
        vm.$feedbackSendBtn.trigger('mouseleave');
    });
};

Feedback.prototype.getPageProductCode = function getPageProductCode() {
    var urlParts = window.location.href.split('/');
    if (urlParts.length) {
        var destPart = urlParts[urlParts.length - 1];
        var destParts = destPart.split('&');
        if (destParts.length) {
            while (destParts.length) {
                var part = destParts.pop();
                if (part.indexOf('page_id=') === 0) {
                    return part.split('=')[1];
                }
            }
        }
    }
    return '';
};

Feedback.prototype.sendEmail = function sendEmail(data, callback, callbackFailed) {
    $.ajax({
        type: 'POST',
        contentType: 'application/json;charset=utf-8',
        data: JSON.stringify(data),
        url: '/api/sendFeedbackEmail',
        context: document.body
    })
        .done(callback)
        .fail(callbackFailed);
};
