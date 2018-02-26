define([
    'lodash'
], function(_) {

    function initGrid(layout) {
        var grid = layout.cells('a').attachGrid();
        grid.setImagePath(app.dhtmlxImgsPath);
        grid.setHeader('#, Код, Название');
        grid.setInitWidths('40,100,290');
        grid.setColAlign('left,left,left');
        grid.setColTypes('ro,ro,ro');
        grid.setColSorting('int,str,str');
        grid.init();

        grid.attachEvent('onSelectStateChanged', function (id, oldId) {
            var entity = this.getUserData(id, 'entity');
            app.form.unlock();
            var gridState = {
                hasSelection: true
            };
            app.toolbar.onStateChange(gridState);
            app.form.updateFormData(entity);
            if (!entity._isNew) {
                app.images.loadImages(entity);
            } else {
                app.images.clearImages();
                app.images.lock(false);
            }
        });
        grid.attachEvent('onBeforeSelect', function(id, oldId){
            var oldEntity = this.getUserData(oldId, 'entity');
            if (_.isObject(oldEntity) && oldEntity._isNew === true) {
                return false;
            }
            return true;
        });
        app.gridRowConfig = ['_num', 'key_item', 'name'];

        return grid;
    }

    return {
        init: initGrid
    };
});
