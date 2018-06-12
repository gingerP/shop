(function () {

    function FeedbackComponent(container) {
        var self = this;
        self._$container = $(container);
        self._$submitBtn = self._$container.find('[name=submit]');
        self._$inputs = self._$container.find('.input');
        self._$messageInput = self._$container.find('[name=message-input]');
        self._$messageInputValidationMessage = self._$container.find('.message-validation');
        self._$emailInput = self._$container.find('[name=email-input]');
        self._$emailInputValidationMessage = self._$container.find('.email-validation');
        self._$nameInput = self._$container.find('[name=name-input]');
        self._$nameInputValidationMessage = self._$container.find('.name-validation');
        self._$successResponseMessage = self._$container.find('.email-success-response-message');
    }

    FeedbackComponent.prototype.initialize = function init() {
        var self = this;
        self.isFormSubmit = false;
        self.initializeEvents();
        self.initializeFormValidation();
    };

    FeedbackComponent.prototype.initializeFormValidation = function initializeFormValidation() {
        var self = this;
        function isMessageValid(value) {
            return AuUtils.hasContent(value) && (value.length > 100000 || value.length === 0);
        }

        function isNameValid(value) {
            return AuUtils.hasContent(value) && value.length > 50;
        }
        self._$messageInput.on('change paste focus textInput input', function () {
            if (self.isFormSubmit) {
                if (isMessageValid(self._$messageInput.val())) {
                    self._$messageInput.addClass('validation_error');
                    self._$messageInputValidationMessage
                        .addClass('validation_message').val('Сообщение слишком большое.');
                } else {
                    self._$messageInput.removeClass('validation_error');
                    self._$messageInputValidationMessage.removeClass('validation_message').val('');
                }
            }
        });
        self._$nameInput.on('change paste focus textInput input', function () {
            if (self.isFormSubmit) {
                if (isNameValid(self._$nameInput.val())) {
                    self._$nameInput.addClass('validation_error');
                    self._$nameInputValidationMessage.addClass('validation_message').val('Имя слишком большое.');
                } else {
                    self._$nameInput.removeClass('validation_error');
                    self._$nameInputValidationMessage.removeClass('validation_message').val('');
                }
            }
        });
    };

    FeedbackComponent.prototype.isFeedbackNotActive = function isFeedbackNotActive() {
        return !popup.isVisible;
    };

    FeedbackComponent.prototype.isFormValid = function isFormValid() {
        var self = this;
        self._$inputs.trigger('change');
        return self._$container.find('.validation_message').length === 0;
    };

    FeedbackComponent.prototype.isEmptyMessage = function isEmptyMessage() {
        var self = this;
        return self._$messageInput.val() === '';
    };

    FeedbackComponent.prototype.validateForm = function validateForm() {

    };

    FeedbackComponent.prototype.initializeEvents = function initEvents() {
        var self = this;

        function onEmailSendFailed() {
            self._$submitBtn.removeClass('input_disable').trigger('mouseleave');
        }

        function onEmailSuccessCallback(data) {
            self._$submitBtn.removeClass('input_disable').trigger('mouseleave');
            self._$successResponseMessage.show();
            clearTimeout(self._successTimeout);
            self._successTimeout = setTimeout(function () {
                self._$successResponseMessage.hide();
                if (data === true) {
                    self.isFormSubmit = false;
                    self._$inputs.val('');
                    $('.validation_error', self.$component).removeClass('validation_error');
                }
            }, 5000);

        }

        function canSendEmail() {
            return !self._$submitBtn.hasClass('input_disable') && self.isFormValid() && !self.isEmptyMessage();
        }

        self._$submitBtn.on('click', function () {
            self.isFormSubmit = true;
            self.validateForm();
            if (canSendEmail()) {
                var data = {
                    message: self._$messageInput.val(),
                    name: self._$nameInput.val(),
                    email: self._$emailInput.val(),
                    product: self.getPageProductCode(),
                    page: window.location.href
                };
                self._$submitBtn.addClass('input_disable');

                self.sendEmail(data).then(onEmailSuccessCallback).fail(onEmailSendFailed);
            }
            return false;
        });
    };

    FeedbackComponent.prototype.getPageProductCode = function getPageProductCode() {
        var urlParts = window.location.href.split('/');
        if (urlParts.length && urlParts[3] === 'products') {
            return urlParts[4];
        }
        return '';
    };

    FeedbackComponent.prototype.sendEmail = function sendEmail(data) {
        return $.ajax({
            type: 'POST',
            contentType: 'application/json;charset=utf-8',
            data: JSON.stringify(data),
            url: '/api/email',
            context: document.body
        });
    };

    window.FeedbackComponent = FeedbackComponent;

})();
