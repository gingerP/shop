$(document).ready(function() {
    app = {};
    U.dhtmlxDOMPreInit(document.documentElement, document.body);
    createPage();
})


function createPage() {
    init();
};

function init() {
    app.layout = initLayout();
    app.menu = components.createMenu(app.layout);
    app.toolbar = components.createToolbar(app.layout);
    app.grid = initGrid();
    loader.reloadGrid(app.grid);
}

function initLayout() {
    var layout = new dhtmlXLayoutObject({
        parent: document.body,
        pattern: "2U",
        cells: [
            {id: "a", text: "Контакты", width: 450},
            {id: "b", text: "Детали контакта"}
        ]
    });
    layout.cells("a").hideHeader();
    layout.detachHeader();
    return layout;
}

function initGrid() {
    var grid = app.layout.cells("a").attachGrid();
    grid.setImagePath("../../../codebase/imgs/");
    grid.setHeader("ID, Описание");
    grid.setInitWidths("40, 390");
    grid.setColAlign("left, left");
    grid.setColTypes("ro,ro");
    grid.setColSorting("int,str");
    grid.init();
    return grid;
}

var loader = {
    reloadGrid: function(grid) {
        serviceWrapper.getAddresses(null, function(contacts) {
            if (contacts.length) {
                for (var contactIndex = 0; contactIndex < contacts.length; contactIndex++) {
                    var entity = components.prepareEntityToGrid(contacts[contactIndex], ["id", "description"])
                    grid.addRow(contacts[contactIndex].id, entity);
                }
                grid.sortRows(0,"int","asc");
            }
        });
    }
}
