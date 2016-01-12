var newXWin = 0;
var newYWin = 0;
var widthBig = 1000;
var heightBig = 1500;
var widthPreview = 150;
var heightPreview = 225;
var zoomWinWidth = 50;
var zoomWinHeight = 75;
$(document).ready ( function () {

    $(".preview").on("mousemove", "img", function(e){
        var parentOffset = $(this).offset();
        var x = e.pageX - parentOffset.left;
        $("#desk_x").text(x);
        var y = e.pageY - parentOffset.top;
        $("#desk_y").text(y);
        moveZoomWin(x, y);
        $("#zoom").css("left", - getChangePosX());
        $("#zoom").css("top", - getChangePosY());
        console.info("winX: " + newXWin + " left: " + getChangePosX());
        console.info("winY: " + newYWin + " top: " + getChangePosY());
    })

    $(".class").one("mousemove", "img", function() {
        console.info("move");
    })

    $(".clas").mousedown(function() {
        var classList = document.getElementsByClassName("class");
        for(i =0; i < classList.length; i++) {
            $(classList[i]).removeClass("class");
        }
        $(this).addClass("class");
    })
})

function getChangePosX() {
    return widthBig * newXWin / widthPreview;
    }

function getChangePosY() {
    return heightBig * newYWin / heightPreview;
    }

function moveZoomWin(x, y) {
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
        $("#zoom_win").css("left", newXWin);
        $("#zoom_win").css("top", newYWin);
    }


