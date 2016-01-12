/**
 * Created by vinni on 5/3/14.
 */

/*
*   getViewPortSize().width - viewPort width
*   getViewPortSize().height - viewPort height
* */

function getViewPortSize() {
    var size = {width: 0, height: 0};

    // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
    if (typeof window.innerWidth != 'undefined') {
        size.width = window.innerWidth;
        size.height = window.innerHeight;
    }

    // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
    else if (typeof document.documentElement != 'undefined'
        && typeof document.documentElement.clientWidth != 'undefined'
        && document.documentElement.clientWidth != 0) {
        size.width = document.documentElement.clientWidth;
        size.height = document.documentElement.clientHeight;
    }

    // older versions of IE
    else {
        size.width = document.getElementsByTagName('body')[0].clientWidth;
        size.height = document.getElementsByTagName('body')[0].clientHeight;
    }
    return size;
}
