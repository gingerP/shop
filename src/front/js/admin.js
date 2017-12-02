var goods = [];
$(document).ready(function() {
    var serviceWrapper = (function() {
        var load = function(apiMethod, params, handlers) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: params,
                url: '/api/' + apiMethod,
                context: document.body
            }).done(function(data) {
                    if (typeof(handlers.success) == 'function') {
                        handlers.success(data);
                    }
                }
            );
        }
        return {
            getGoods: function(id, callback) {
                load('getGoods', {id: -1}, new Handlers(callback));
            },
            getDescriptionKeys: function(callback) {
                load('getDescriptionKeys', null, new Handlers(callback))
            },
            updateGood: function(id, data, callback) {
                load('updateGood', {id: id, data: data}, callback);
            }
        }
    })();

    var tableKeys = ['id', 'key_item', 'name', 'individual', 'person', 'description', 'image_path', 'god_type'];
    var tableReady = false;
    var descriptionsReady = false;
    var pageEvents = (function() {
        var tableRowEvent = function() {
            $('#editor_container input, #editor_container textarea, #editor_container select').val('');
            $('#table_container table tr').removeClass('selected_row');
            $(this).addClass('selected_row');
            var id = this.id;
            var data = (function() {
                for (var goodIndex = 0; goodIndex < goods.length; goodIndex++) {
                    if (goods[goodIndex].id == id ) {
                        return goods[goodIndex];
                    }
                }
            })();
            for (var keyIndex = 0; keyIndex < tableKeys.length; keyIndex++) {
                if (tableKeys[keyIndex] == 'description') {
                    var desc_ = data['description'].split('|');
                    var res = {};
                    for (var valueIndex = 0; valueIndex < desc_.length; valueIndex++) {
                        var keyValue = desc_[valueIndex].split('=');
                        $('#' + keyValue[0]).val(keyValue[1]);
                    }
                }
                $('#' + tableKeys[keyIndex]).val(data[tableKeys[keyIndex]]);
            }
        }
        return {
            initTableRows: function() {
                $('#table_container table tr').on('click', tableRowEvent);
            },
            initTableRow: function(row) {
                $(row).on('click', tableRowEvent);
            },
            initSave: function() {
                $('#save, #add').on('click', function() {
                    var id = null;
                    if (this.id == 'save') {
                        var id = $('#id').val();
                    }
                    var data = {};
                    var exceptKeys = ['id', 'description'];
                    for (var ind = 0; ind < tableKeys.length; ind++) {
                        if (exceptKeys.indexOf(tableKeys[ind]) < 0) {
                            data[tableKeys[ind]] = $('#' + tableKeys[ind]).val();
                        }
                    }
                    var descriptionValues = $('#descriptions textarea');
                    var description = [];
                    for (var descInd = 0; descInd < descriptionValues.length; descInd++) {
                        if (typeof(descriptionValues[descInd].value) != 'undefined' && descriptionValues[descInd].value.trim().length > 0) {
                            description.push(descriptionValues[descInd].id + '=' + descriptionValues[descInd].value);
                        }
                    }
                    data['description'] = description.join('|');
                    serviceWrapper.updateGood(id, data, new Handlers(function(data) {
                        var $message = $('#message');
                        if (!isNaN(parseFloat(data)) || data == null) {
                            $message.css('color', 'green').css('font-size', '17px').css('font-weight', 'bold').html('OK!');
                        } else {
                            $message.css('color', 'red').css('font-size', '17px').css('font-weight', 'bold').html('ERROR!');
                        }
                        setTimeout(function() {
                            $message.html('');
                            serviceWrapper.getGoods(-1, loadGoodsCallback);
                            pageEvents.initTableRows();
                        }, 3000);
                    }));
                });
            },
            initClear: function() {
                $('#clear').on('click', function() {
                    $('#editor_container input, #editor_container textarea, #editor_container select').val('');
                })
            }

        }
    })();
    var loadGoodsCallback = function(data) {
        if (typeof(data) != 'undefined') {
            data.sort(function(o1, o2) {
                var id1 = parseInt(o1.id);
                var id2 = parseInt(o2.id);
                return id1 < id2? 1: -1;
            })
            goods = data;
            var table = $('#table_container table tbody')[0];
            $('tr', table).remove();
            data.forEach(function(element, index) {
                var row = table.insertRow(-1);
                row.id = element[tableKeys[0]];
                for(var index = 0; index < tableKeys.length; index++) {
                    var cell1 = row.insertCell(index);
                    cell1.innerHTML = element[tableKeys[index]];
                }
            });
            tableReady = true;
            pageEvents.initTableRows();
        }
    };
    var loadDescriptionKeysCallback = function(data) {
        var initDescriptionKeys = function(data) {
            if (typeof(data) != 'undefined') {
                var descriptionContainer = $('#descriptions')[0];
                for(keyItem in data) {
                    var container = document.createElement('div');
                    var label = document.createElement('div');
                    label.innerHTML = data[keyItem].trim() != ''? data[keyItem]: keyItem;
                    label.setAttribute('class', 'f-16');
                    var textArea = document.createElement('textarea');
                    textArea.setAttribute('rows', 4);
                    textArea.setAttribute('cols', 40);
                    textArea.setAttribute('id', keyItem);
                    container.appendChild(label);
                    container.appendChild(textArea);
                    descriptionContainer.appendChild(container);
                }
            }
        };
        initDescriptionKeys(data);
        descriptionsReady = true;
    };
    serviceWrapper.getGoods(-1, loadGoodsCallback);
    serviceWrapper.getDescriptionKeys(loadDescriptionKeysCallback);
    pageEvents.initSave();
    pageEvents.initClear();

});