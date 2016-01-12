LineItems = function(id, data) {
    this.parentId = id;
    this.componentId = U.getRandomString();
    this.component = undefined;
    this.componentData = data;
    this.domObject = undefined;
    this.selected = {
        key: 0,
        value: 0
    };
    this.itemsContainerSize = {
        width: 0,
        height:0
    }
    this.componentClasses = {
        main: 'line_items',
        items: 'horizontal-items',
        selected: 'selected',
        item: 'item'
    }
    this.formatters = {
        img: function(imgSrc, imgWith, imgHeight) {
            var img = document.createElement('IMG');
            img.src = imgSrc;
            img.width = imgWith;
            img.height = imgHeight;
            return img;

        },
        default: function(text) {
            var text = document.createTextNode(text);
            return text;
        }

    }
    this.events = {
        'ON_CLICK': 'onClick'
        ,'ON_SELECT': 'onSelect'
    }
}

LineItems.prototype.init = function() {
    this.render();
}

LineItems.prototype.render = function() {
    if (document.getElementById(this.parentId)) {
        var parent = document.getElementById(this.parentId);
        parent.className = this.componentClasses.main;
        var container = document.createElement('DIV');
        parent.appendChild(container);
        container.setAttribute('id', this.componentId);
        for(var dataIndex = 0; dataIndex < this.componentData.length; dataIndex++) {
            var itemContainer = document.createElement('DIV');
            itemContainer.setAttribute('value', this.componentData[dataIndex].key);
            var label = this.format(this.componentData[dataIndex]);
            itemContainer.appendChild(label);
            container.appendChild(itemContainer);
        }
    }
}

LineItems.prototype.format = function(data) {
    var content = undefined;
    switch(data.type) {
        case 'img':
            content = this.formatters.img(data.value || '', data.width || 17, data.height || 15);
            break;
        default:
            content = this.formatters.default(data.value || '');
    }
    return content;
}