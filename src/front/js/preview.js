(function () {
    var newXWin = 0;
    var newYWin = 0;
    var widthBig = 1240;
    var heightBig = 2067;
    var widthPreview = 136;
    var heightPreview = 227;
    var zoomWinWidth = 50;
    var zoomWinHeight = 75;
    var currentZoomWin;
    var timerZoomWinHide;
    $(document).ready(function () {

        $(".image_item").mousemove(function (e) {
            if ($(this).hasClass("review")) {
                var div = document.getElementsByClassName("review")[0];
                var x = e.pageX - $(div).offset().left;
                var y = e.pageY - $(div).offset().top;
                moveZoomWin(div, x, y);
                var zoom = document.getElementsByClassName("zoom")[0];
                $(zoom).css("zIndex", 5);
                $(zoom).css("opacity", 1);
                $(zoom).css("left", -getChangePosX());
                $(zoom).css("top", -getChangePosY());
            }
        })

        $(".image_item ").hover(
            function () {
                if ($(this).hasClass("review")) {
                    console.info("hover in");
                    clearTimerZoomWinHide();
                    currentZoomWin = this.getElementsByClassName("zoom_win")[0];
                    var zoom = document.getElementsByClassName("zoom")[0];
                    $(currentZoomWin).css("zIndex", 3);
                    $(currentZoomWin).css("opacity", 0.1);
                    $(zoom).css("zIndex", 5);
                    $(zoom).css("opacity", 1);
                }
            },
            function () {
                if ($(this).hasClass("review")) {
                    console.info("hover out");
                    runTimerZoomWinHide(this);
                }
            }
        )

    })

    function hoverOut(element) {
        currentZoomWin = element.getElementsByClassName("zoom_win")[0];
        console.info("hoverOut animate begin");
        var zoom = document.getElementsByClassName("zoom")[0];
        $(zoom).css("zIndex", 0);
        $(zoom).css("opacity", 0);
    }

    function runTimerZoomWinHide(element) {
        timerZoomWinHide = setTimeout(function () {
            console.info("run hover timer");
            $(element.getElementsByClassName("zoom_win")[0]).css("opacity", 0);
            $(element.getElementsByClassName("zoom_win")[0]).css("zIndex", -1);
            hoverOut(element);
        }, 1000)
    }

    function clearTimerZoomWinHide() {
        console.info("stop hover timer");
        clearTimeout(timerZoomWinHide);
    }

    function getChangePosX() {
        return widthBig * newXWin / widthPreview;
    }

    function getChangePosY() {
        return heightBig * newYWin / heightPreview;
    }

    function moveZoomWin(parent, x, y) {
        if (x > zoomWinWidth / 2 && x < widthPreview - (zoomWinWidth / 2) &&
            y > zoomWinHeight / 2 && y < heightPreview - (zoomWinHeight / 2)) {
            newXWin = x - (zoomWinWidth / 2);
            newYWin = y - (zoomWinHeight / 2);
        }
        if (x > 0 && x <= zoomWinWidth / 2) {
            newXWin = 0;
        } else if (x >= widthPreview - (zoomWinWidth / 2) && x < widthPreview) {
            newXWin = widthPreview - zoomWinWidth;
        } else if (x > zoomWinWidth / 2 && x < widthPreview - (zoomWinWidth / 2)) {
            newXWin = x - (zoomWinWidth / 2);
        }
        if (y > 0 && y <= zoomWinHeight / 2) {
            newYWin = 0;
        } else if (y >= heightPreview - (zoomWinHeight / 2) && y < heightPreview) {
            newYWin = heightPreview - zoomWinHeight;
        } else if (y > zoomWinHeight / 2 && y < heightPreview - (zoomWinHeight / 2)) {
            newYWin = y - (zoomWinHeight / 2);
        }
        $(parent.getElementsByClassName("zoom_win")[0]).css("left", newXWin);
        $(parent.getElementsByClassName("zoom_win")[0]).css("top", newYWin);
    }
})();
