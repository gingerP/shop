define([], function () {
    'use strict';

    Handlebars.registerHelper('sizeHandler', function (size) {
        return Math.ceil(size.width / 135) * Math.ceil(size.height / 225);
    });
    U.addHandlebarScript('bookletItem', '\
        <div id="{{id}}" class="item x{{sizeHandler size}}">\
            <div class="content">{{> bookletItemContent}}</div>\
            <div class="editors">\
                <div class="edit_item btn">Edit</div>\
                <div class="clear_item btn">Clear</div>\
                <div class="delete_item btn">Del</div>\
            </div>\
            <div class="connectors"></div>\
        </div>');
    Handlebars.registerPartial('bookletItem', $("#bookletItem").html());
    U.addHandlebarScript('bookletItemContent', '\
        <img class="item_image" src="{{renderItemImage image}}"/>\
        <div class="number" style="{{renderItemNumberVisible number}}">{{number}}</div>\
        {{itemLabelGroup listLabels}}');
    U.addHandlebarScript('itemLabelGroup', '\
        {{#each groups}}\
            <div class="label_group {{@key}}_group">\
            {{#each this}}\
                <div id="{{id}}" class="label {{type}}"><pre>{{text}}</pre></div>\
                {{#equal @index 0}}<div class="label labels_delimiter"></div>{{/equal}}\
            {{/each}}\
            </div>\
        {{/each}}');
    U.addHandlebarScript('itemLabel', '<div id="{{id}}" class="label {{type}}"><pre>{{text}}</pre></div>');
    Handlebars.registerHelper('itemLabelGroup', function (labels) {
        var groups = {};
        if (labels.length) {
            for (var labelIndex = 0; labelIndex < labels.length; labelIndex++) {
                var label = labels[labelIndex];
                var labelType = /^(.*)_\d{1}$/.exec(label.type);
                if (labelType.length > 1 && U.hasContent(label.text)) {
                    groups[labelType[1]] = groups[labelType[1]] || [];
                    groups[labelType[1]].push(label);
                }
            }
            if (U.hasContent(groups)) {
                return new Handlebars.SafeString(Handlebars.compile($('#itemLabelGroup').html())({groups: groups}));
            }
        }
        return '';
    });
    Handlebars.registerHelper('renderItemImage', function (image) {
        return image ? (image.indexOf('data:image') == 0 ? image : '/' + app.bookletImageRoot + '/' + image ) : "";
    });
    Handlebars.registerHelper('renderItemNumberVisible', function (number) {
        return !number ? "display: none;" : "";
    });
    Handlebars.registerHelper('renderBookletBorders', function (templateName) {
        if (templateName) {
            return Handlebars.compile($("#" + templateName).html())();
        }
        return '';
    });
    Handlebars.registerHelper('backgroundImage', function (imagePath) {
        return imagePath || '';
    });
    Handlebars.registerPartial("itemLabel", $("#itemLabel").html());
    Handlebars.registerPartial("bookletItemContent", $("#bookletItemContent").html());

    U.addHandlebarScript('previewContainer', '\
        <div id="preview_container" class="preview">\
            <div class="viewport_container">\
                <div class="viewport_sub">\
                    <div class="background"><img src="{{backgroundImage backgroundImage}}"/></div>\
                    <div class="borders">{{> bordersDefault}}</div>\
                    <div class="palls"></div>\
                    <div class="viewport"></div>\
                    <div id="zoom_slider"></div>\
            </div>\
        </div>');
    U.addHandlebarScript('fullpreviewContainer', '\
        <div id="preview_container" class="full_preview">\
            <div class="viewport_container">\
                <div class="viewport_sub">\
                    <div class="background"><img src="{{backgroundImage backgroundImage}}"/></div>\
                    <div class="palls"></div>\
                    <div class="viewport">{{#each listItems}}{{> bookletItem}}{{/each}}</div>\
            </div>\
        </div>');
    U.addHandlebarScript('booklet', '<div id="{{id}}" class="booklet"></div>');
    U.addHandlebarScript('borders-default', '\
        <div class="vertical-border-1 item-border horizontal highlight" style="display: none"></div>\
        <div class="vertical-border-2 item-border horizontal highlight" style="display: none"></div>\
        <div class="horizontal-border-1 item-border vertical highlight" style="display: none"></div>\
        <div class="horizontal-border-2 item-border vertical highlight" style="display: none"></div>\
    ');
    Handlebars.registerPartial("bordersDefault", $("#borders-default").html());
});