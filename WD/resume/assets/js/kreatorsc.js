jQuery(function ($) {
    var $body = $("body");
    
    $(window).scroll(function () {
        $body.toggleClass("gt200", $(this).scrollTop() > 60);
    });
});



function createCookie(name, value, days)
{
    if (days)
    {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else
    {
        var expires = "";
    }

    document.cookie = name + "=" + value + expires + "; path=/";

    return true;
}

function closeCookieInfo()
{
    if(createCookie('cookiesNotification', 1, 100*365))
    {
        var el = document.getElementById('cookies-notification');
        el.parentNode.removeChild(el);
    }
}

function getCookie(c_name)
{
    var c_value = document.cookie;
    var c_start = c_value.indexOf(" " + c_name + "=");
    if (c_start == -1)
    {
        c_start = c_value.indexOf(c_name + "=");
    }
    if (c_start == -1)
    {
        c_value = null;
    }
    else
    {
        c_start = c_value.indexOf("=", c_start) + 1;
        var c_end = c_value.indexOf(";", c_start);
        if (c_end == -1)
        {
            c_end = c_value.length;
        }
        c_value = unescape(c_value.substring(c_start,c_end));
    }
    return c_value;
}

function createDiv()
{
    var _body = document.getElementsByTagName('body') [0];
    var _div = document.createElement('div');
    _div.id = 'cookies-notification';
    _div.style.width = '100%';
    _div.style.color = '#000000';
    _div.style.position = 'fixed';
    _div.style.zIndex = '1000';
    _div.style.backgroundColor = '#ffffff';
    _div.style.borderBottom = '0px';
    _div.style.bottom = '0';

    _div.innerHTML =
        '<p style="font-size: 14px; line-height: 20px; text-align: center; padding: 5px 120px 5px 20px; margin: 0; BACKGROUND-COLOR: #CCC; COLOR: #000;">' +
            'To ensure high quality of services and convenient use of the website, we use information saved in the browser via cookies. Staying on the site you agree to the use of cookies. You can block them at any time using the settings of your web browser.' +
            '<span onclick="closeCookieInfo()" id="cookies-accept" style="padding: 0 2px; color: #000 !important; display: inline-block; *display: inline; zoom: 1; border: 1px solid #000; -webkit-border-radius: 2px; border-radius: 2px; -moz-border-radius: 2px; position: absolute; right: 20px; top: 5px; height: 20px; cursor: pointer;">Got it!</span>' +
            '</p>';
    _body.insertBefore(_div,_body.firstChild);
}

window.onload=initAll;
function initAll()
{
    if(getCookie('cookiesNotification') == null || 0 == getCookie('cookiesNotification'))
    {
        createDiv();
    }
}