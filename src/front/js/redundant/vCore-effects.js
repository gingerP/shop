/*
var inputHoverModule = (function() {
    var instance = undefined;
    var createInstance = function() {
        var hoverSpeed = 100;
        var hoverClass = 'input_hover';
        var hoverTextColorSelector = '.input_hover_color';
        var ignoredClasses = ['input_disable', 'input_hover_untouchable'];
        function animationStop(jQueryObject) {
            jQueryObject.stop(true, false);
        };
        function hoverIn() {
            var $thiz = $(this);
            var $colors = $(hoverTextColorSelector, $thiz);
            if (!$thiz.hasClasses(ignoredClasses)) {
                var oldBackgroundColor = $thiz.css('background-color');
                var newBackgroundColor = tinycolor(oldBackgroundColor).isDark()? tinycolor(oldBackgroundColor).darken(5).toString(): tinycolor(oldBackgroundColor).darken(10).toString();
                var animationObj = {};
                var colorAnimationObj = {};
                animationStop($thiz);
                animationStop($colors);
                /!*colorAnimationObj.color = '#ffffff';
                animationObj.color = '#ffffff';*!/
                if (this.tagName != 'svg') {
                    animationObj.backgroundColor = newBackgroundColor;
                } else {
                    animationObj.fill = newBackgroundColor;
                }
                //text color
                /!*if ($colors.length && (!U.hasContent($colors.attr('temp_color')) || $colors.attr('temp_color') == '')) {
                    $colors.attr('temp_color', $colors.css('color'));
                } else if (!U.hasContent($thiz.attr('temp_color')) || $thiz.attr('temp_color') == '') {
                    $thiz.attr('temp_color', $thiz.css('color'));
                }*!/
                //background
                if (!U.hasContent($thiz.attr('temp_background')) || $thiz.attr('temp_background') == '') {
                    $thiz.attr('temp_background', oldBackgroundColor);
                }
                $thiz.animate(animationObj, hoverSpeed);
                $colors.animate(colorAnimationObj, hoverSpeed);
            }
        };
        function hoverOut() {
            var $thiz = $(this);
            var $colors = $(hoverTextColorSelector, $thiz);
            if (!$thiz.hasClasses(ignoredClasses)) {
                var animationObj = {};
                var colorAnimationObj = {};

                var customHoverOut = $thiz.attr('customHoverOut');
                if (typeof(window[customHoverOut]) == "function" && !window[customHoverOut]()) {
                    return;
                }
                animationStop($thiz);
                animationStop($colors);
                //text color
                /!*if ($colors.length && (U.hasContent($colors.attr('temp_color')))) {
                    $colors.attr('temp_color', $colors.css('color'));
                    colorAnimationObj.color = $colors.attr('temp_color');
                } else if (U.hasContent($thiz.attr('temp_color'))) {
                    animationObj.color = $thiz.attr('temp_color');
                }*!/
                //text color
                if (U.hasContent($thiz.attr('temp_background'))) {
                    if (this.tagName != 'svg') {
                        animationObj.backgroundColor = $thiz.attr('temp_background');
                    } else {
                        animationObj.fill = $thiz.attr('temp_background');
                    }
                }
                $thiz.animate(animationObj, hoverSpeed);
                $colors.animate(colorAnimationObj, hoverSpeed);
            }
        };

        function resetToInitialState(context) {
            if (typeof(context) != 'undefined') {
                hoverOut.call(context);
            }
        };
        function updateHover(domElement) {
            $(typeof domElement != 'undefined'? domElement:'.' + hoverClass)
                .hover(hoverIn, hoverOut)
                .mousedown(
                function() {
                    var $thiz = $(this);
                    if (!$thiz.hasClasses(ignoredClasses)) {
                        $thiz.finish();
                        var color = $thiz.attr('temp_background');
                        $thiz.animate({backgroundColor: tinycolor(color).darken(10).toString()}, hoverSpeed);
                    }
                }
                ).mouseup(
                    function() {
                        var $thiz = $(this);
                        if (!$thiz.hasClasses(ignoredClasses)) {
                            $thiz.finish();
                            var color = $thiz.attr('temp_background');
                            $thiz.animate({backgroundColor: tinycolor(color).darken(5).toString()}, hoverSpeed);
                        }
                    }
                )
        };
        return {
            hoverIn: function(context) {
                if (typeof(context) != 'undefined') {
                    hoverIn.call(context);
                }
            },
            hoverOut: function(context) {
                if (typeof(context) != 'undefined') {
                    hoverOut.call(context);
                }
            },
            reset: function(context) {
                resetToInitialState(context);
            },
            updateHover: function(domElement) {
                updateHover(domElement)
            },
            updateHovers: function() {
                updateHover();
            }
        }
    }
    return {
        inn: function() {
            if (!instance) {
                instance = createInstance();
            }
            return instance;
        },
        update: function() {
            if (!instance) {
                instance = createInstance();
            }
            instance.updateHovers();
        }
    }
})();
*/


//-----------------------------------------------------------------------------------//


//-----------------------------------------------------------------------------------//


TransparentIcons = function(viewPortSelector, iconSelector) {
    this.viewPortSelector = viewPortSelector || '.icon_viewPort';
    this.iconSelector = iconSelector || '.icon';
    this.$iconsViewports = undefined;
    this.$icons = undefined;
    this.vizibilityFlag = 'visible';
    this.animationSpeed = 50;
};

TransparentIcons.prototype.init = function() {
/*    var thiz = this;
    this.$iconsViewports = $(this.viewPortSelector);
    //ie8 has not support X:not(selector)
    this.$iconsViewports.hover(
        function() {
            var $icon = $(thiz.iconSelector, this);
            if (!$icon.hasClass(thiz.vizibilityFlag)) {
                $icon.fadeIn(50);
            }
        },
        function() {
            var $icon = $(thiz.iconSelector, this);
            if (!$icon.hasClass(thiz.vizibilityFlag)) {
                $icon.fadeOut(50);
            }
        }
    );

    this.$icons = $(this.iconSelector);
    this.$icons.hover(
        function() {
            $(this).animate({opacity: 0.5}, 50);
        },
        function() {
            $(this).animate({opacity: 0.3}, 50);
        }
    ).mousedown(
        function() {
            $(this).animate({opacity: 0.8}, 50);
        }
    ).mouseup(
        function() {
            $(this).animate({opacity: 0.5}, 50);
        }
    );*/
};