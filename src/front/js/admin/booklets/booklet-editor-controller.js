define([], function () {
    'use strict';

    function BookletEditorController() {
    }

    BookletEditorController.prototype.init = function () {
        this.id = undefined;
        this.entity = undefined;
    };

    BookletEditorController.prototype.setEntity = function (entity) {
        this.entity = entity;
    };

    BookletEditorController.prototype.getEntity = function () {
        return this.entity;
    };

    BookletEditorController.prototype.clear = function () {
        this.entity = {};
    };

    BookletEditorController.prototype.updateData = function () {

    };

    return BookletEditorController;
});
