define([], function () {
    'use strict';

    Handlebars.registerHelper('equal', function(a, b, opts) {
        if(a == b) // Or === depending on your needs
            return opts.fn(this);
        else
            return opts.inverse(this);
    });

    Handlebars.registerHelper('sizeHandler', function (size) {
        return Math.ceil(size.width / 135) * Math.ceil(size.height / 225);
    });
    AuUtils.addHandlebarScript('bookletItem', '\
        <div id="{{id}}" class="item x{{sizeHandler size}}">\
            <div class="content">{{> bookletItemContent}}</div>\
            <div class="editors">\
                <div class="edit-item btn">\
                    <img src="/images/icons/edit.png">\
                    <span class="edit-item-label">Изменить</span>\
                </div>\
                <div class="clear-item btn">\
                    <img src="/images/icons/clear.png">\
                    <span class="edit-item-label">Очистить</span>\
                </div>\
                <div class="delete-item btn">\
                    <img src="/images/icons/delete.png">\
                    <span class="edit-item-label">Удалить</span>\
                </div>\
            </div>\
            <div class="connectors"></div>\
        </div>');
    Handlebars.registerPartial('bookletItem', $("#bookletItem").html());
    AuUtils.addHandlebarScript('bookletItemContent', '\
        <img class="item_image" src="{{renderItemImage image}}"/>\
        <div class="number" style="{{renderItemNumberVisible number}}">{{number}}</div>\
        {{itemLabelGroup listLabels}}');
    AuUtils.addHandlebarScript('itemLabelGroup', '\
        {{#each groups}}\
            <div class="label_group {{@key}}_group">\
            {{#each this}}\
                <div id="{{id}}" class="label {{type}}"><pre>{{text}}</pre></div>\
                {{#equal @index 0}}<div class="label labels_delimiter"></div>{{/equal}}\
            {{/each}}\
            </div>\
        {{/each}}');
    AuUtils.addHandlebarScript('itemLabel', '<div id="{{id}}" class="label {{type}}"><pre>{{text}}</pre></div>');
    Handlebars.registerHelper('itemLabelGroup', function (labels) {
        var groups = {};
        if (labels.length) {
            for (var labelIndex = 0; labelIndex < labels.length; labelIndex++) {
                var label = labels[labelIndex];
                var labelType = /^(.*)_\d{1}$/.exec(label.type);
                if (labelType.length > 1 && AuUtils.hasContent(label.text)) {
                    groups[labelType[1]] = groups[labelType[1]] || [];
                    groups[labelType[1]].push(label);
                }
            }
            if (AuUtils.hasContent(groups)) {
                return new Handlebars.SafeString(Handlebars.compile($('#itemLabelGroup').html())({groups: groups}));
            }
        }
        return '';
    });
    Handlebars.registerHelper('renderItemImage', function (image) {
        return image ? (image.indexOf('data:') === 0 ? image : '/booklet_images/' + image ) : "";
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

    AuUtils.addHandlebarScript('previewContainer', '\
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
    AuUtils.addHandlebarScript('fullpreviewContainer', '\
        <div id="preview_container" class="full_preview">\
            <div class="viewport_container">\
                <div class="viewport_sub">\
                    <div class="background"><img src="{{backgroundImage backgroundImage}}"/></div>\
                    <div class="palls"></div>\
                    <div class="viewport">{{#each listItems}}{{> bookletItem}}{{/each}}</div>\
            </div>\
        </div>');
    AuUtils.addHandlebarScript('booklet', '<div id="{{id}}" class="booklet"></div>');
    AuUtils.addHandlebarScript('borders-default', '\
        <div class="vertical-border-1 item-border horizontal highlight" style="display: none"></div>\
        <div class="vertical-border-2 item-border horizontal highlight" style="display: none"></div>\
        <div class="horizontal-border-1 item-border vertical highlight" style="display: none"></div>\
        <div class="horizontal-border-2 item-border vertical highlight" style="display: none"></div>\
    ');
    Handlebars.registerPartial("bordersDefault", $("#borders-default").html());
});