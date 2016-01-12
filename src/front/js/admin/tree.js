$(document).ready(function() {
    U.dhtmlxDOMPreInit(document.documentElement, document.body);
    app = {};
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
            {id: "a", text: "Дерево навигации", width: 450},
            {id: "b", text: "Детали дерева навигации"}
        ]
    });
    layout.cells("a").hideHeader();
    layout.detachHeader();
    return layout;
}

function initGrid() {
    var grid = app.layout.cells("a").attachGrid();
    grid.setImagePath("../../../codebase/imgs/");
    grid.setHeader("ID, Код, Название");
    grid.setInitWidths("40, 100,290");
    grid.setColAlign("left, left,left");
    grid.setColTypes("ro,ro,ro");
    grid.setColSorting("int,str,str");
    grid.init();
    return grid;
}

var loader = {
    reloadGrid: function(grid) {
    }
}
