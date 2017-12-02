DropLeftComponent = function DropLeftComponent(parentId, componentData, selectedId){
    this.parentId = parentId;
    this.componentId = U.getRandomString();
    this.component = undefined;
    this.componentData = componentData;
    this.selectedId = selectedId;
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
        main: 'drop-left',
        items: 'horizontal-items',
        selected: 'selected',
        item: 'drop-left-item'
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

DropLeftComponent.prototype.init = function() {
    this.component = this.render();
    this.initEvents();
}

DropLeftComponent.prototype.render = function() {
    var component = document.createElement('DIV');
    component.setAttribute('id', this.componentId);
    component.style.position = 'relative';
    this.domObject = document.getElementById(this.parentId);
    this.domObject.classList.add(this.componentClasses.main);
    this.domObject.appendChild(component);
    var itemsContainer = document.createElement('DIV');
    itemsContainer.classList.add(this.componentClasses.items);
    itemsContainer.style.width = '0px';
    itemsContainer.style.overflow = 'hidden';
    component.appendChild(itemsContainer);
    var _itemsContainer = document.createElement('DIV');
    itemsContainer.appendChild(_itemsContainer);
    this.itemsContainerSize.width = 0;
    this.itemsContainerSize.height = 0;
    for(var dataIndex = 0; dataIndex < this.componentData.length; dataIndex++) {
        var item = document.createElement('DIV')
        item.setAttribute('value', this.componentData[dataIndex].key);
        item.classList.add(this.componentClasses.item);
        var content = this.format(this.componentData[dataIndex]);
        item.appendChild(content);
        _itemsContainer.appendChild(item);
        this.itemsContainerSize.width += item.offsetWidth /*+ 2*/; //without margins
        this.itemsContainerSize.height = Math.max(this.itemsContainerSize.height, item.offsetHeight);
    }
    _itemsContainer.style.width = this.itemsContainerSize.width;
    _itemsContainer.style.height = this.itemsContainerSize.height;



    var selectedContainer = document.createElement('DIV');
    selectedContainer.className = this.componentClasses.selected;
    if (this.componentData.length > 0) {
        var defaultSelected = document.createElement('DIV');
        defaultSelected.classList.add(this.componentClasses.item);
        if (this.selectedId) {
            for(var dataIndex = 0; dataIndex < this.componentData.length; dataIndex++) {
                if (this.componentData[dataIndex].key == this.selectedId) {
                    defaultSelected.appendChild(this.format(this.componentData[dataIndex]));
                    this.selected = {
                        key: this.componentData[dataIndex].key,
                        value: this.componentData[dataIndex].value
                    }
                    break;
                }
            }
        } else {
            defaultSelected.appendChild(this.format(this.componentData[0]));
            this.selected.key = this.componentData[0].key;
            this.selected.value = this.componentData[0].value;
        }
        selectedContainer.appendChild(defaultSelected);
    }
    component.appendChild(selectedContainer);
    return component;
}

DropLeftComponent.prototype.initEvents = function() {
    var thiz = this;
    $(document).ready(function() {
        $('#' + thiz.componentId + ' .' + thiz.componentClasses.items + ' div div').click(function(eventData) {
            var item = thiz._getItemDOM(eventData.target);
            if (item != undefined) {
                for(var attrIndex = 0, attr = item.attributes, max = item.attributes.length; attrIndex < max && item.attributes[attrIndex].nodeName == 'value'; attrIndex++) {
                    var value = attr[attrIndex].value;
                    thiz.changeSelectedValue(value);
                    thiz.hideItems();
                }
            }
        });
        $('#' + thiz.componentId + ' .' + thiz.componentClasses.selected + ' div').click(function(eventData) {
            thiz.isItemsOpen()? thiz.hideItems(): thiz.openItems();
        });
    })
}

DropLeftComponent.prototype.changeSelectedValue = function(value) {
    var selected = $(this.getSelectedItemContainerDOM()).find('div');
    if (selected && selected.length > 0) {
        selected[0].innerHTML = '';
        selected[0].appendChild(this.format(this.getItem(value)));
    }
}

DropLeftComponent.prototype.openItems = function(duration, delay, callback) {
    var itemsContainer = this.getItemsContainerDOM();
    $(itemsContainer).delay(delay || 200).animate({width: this.itemsContainerSize.width, left: -this.itemsContainerSize.width}, duration || 400, callback);
}

DropLeftComponent.prototype.hideItems = function(duration, delay, callback) {
    var itemsContainer = this.getItemsContainerDOM();
    $(itemsContainer).delay(delay || 200).animate({width: 0, left: 0}, duration || 400, callback);
}

DropLeftComponent.prototype.isItemsOpen = function() {
    var itemsContainer = this.getItemsContainerDOM();
    return itemsContainer && itemsContainer.style.width > '0px';
}

DropLeftComponent.prototype.getItemsContainerDOM = function() {
    return $('#' + this.componentId + ' .' + this.componentClasses.items)[0];
}

DropLeftComponent.prototype.getSelectedItemContainerDOM = function() {
    return $('#' + this.componentId + ' .' + this.componentClasses.selected)[0];
}

DropLeftComponent.prototype.getItem = function(key) {
    for(var dataIndex = 0; dataIndex < this.componentData.length; dataIndex++) {
        if (this.componentData[dataIndex].key == key) {
            return this.componentData[dataIndex];
        }
    }
    return '';
}

DropLeftComponent.prototype._getItemDOM = function(childDOM, depth) {
    var depth = depth || 0;
    var maxDepth = 3;
    if (typeof(childDOM.classList) == 'object' && childDOM.classList.contains(this.componentClasses.item)) {
        return childDOM;
    } else if (typeof(childDOM.parentElement) == 'object' && maxDepth > depth) {
        return this._getItemDOM(childDOM.parentElement, ++depth);

    } else {
        return undefined;
    }
}

DropLeftComponent.prototype.format = function(data) {
    var content = undefined;
    switch(data.type) {
        case 'img':
            content = this.formatters.img(data.value || '', data.width || 30, data.height || 30);
            break;
        default:
            content = this.formatters.default(data.value || '');
    }
    return content;
}
