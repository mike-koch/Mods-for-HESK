/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.8 from 10th August 2016
 *  Author: Klemen Stirn
 *  Website: https://www.hesk.com
 ********************************************************************************
 *  COPYRIGHT AND TRADEMARK NOTICE
 *  Copyright 2005-2015 Klemen Stirn. All Rights Reserved.
 *  HESK is a registered trademark of Klemen Stirn.

 *  The HESK may be used and modified free of charge by anyone
 *  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
 *  By using this code you agree to indemnify Klemen Stirn from any
 *  liability that might arise from it's use.

 *  Selling the code for this program, in part or full, without prior
 *  written consent is expressly forbidden.

 *  Using this code, in part or full, to create derivate work,
 *  new scripts or products is expressly forbidden. Obtain permission
 *  before redistributing this software over the Internet or in
 *  any other medium. In all cases copyright and header must remain intact.
 *  This Copyright is in full effect in any country that has International
 *  Trade Agreements with the United States of America or
 *  with the European Union.

 *  Removing any of the copyright notices without purchasing a license
 *  is expressly forbidden. To remove HESK copyright notice you must purchase
 *  a license for this script. For more information on how to obtain
 *  a license please visit the page below:
 *  https://www.hesk.com/buy.php
 *******************************************************************************/

function hesk_insertTag(tag) {
    var text_to_insert = '%%' + tag + '%%';
    hesk_insertAtCursor(document.form1.msg, text_to_insert);
    document.form1.message.focus();
}

function hesk_insertAtCursor(myField, myValue) {
    if (document.selection) {
        myField.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
    }
    else if (myField.selectionStart || myField.selectionStart == '0') {
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos)
            + myValue
            + myField.value.substring(endPos, myField.value.length);
    } else {
        myField.value += myValue;
    }
}

function hesk_changeAll(myID) {
    var d = document.form1;
    var setTo = myID.checked ? true : false;

    for (var i = 0; i < d.elements.length; i++) {
        if (d.elements[i].type == 'checkbox' && d.elements[i].name != 'checkall') {
            d.elements[i].checked = setTo;
        }
    }
}

function hesk_attach_disable(ids) {
    for ($i = 0; $i < ids.length; $i++) {
        if (ids[$i] == 'c11' || ids[$i] == 'c21' || ids[$i] == 'c31' || ids[$i] == 'c41' || ids[$i] == 'c51') {
            document.getElementById(ids[$i]).checked = false;
        }
        document.getElementById(ids[$i]).disabled = true;
    }
}

function hesk_attach_enable(ids) {
    for ($i = 0; $i < ids.length; $i++) {
        document.getElementById(ids[$i]).disabled = false;
    }
}

function hesk_attach_toggle(control, ids) {
    if (document.getElementById(control).checked) {
        hesk_attach_enable(ids);
    } else {
        hesk_attach_disable(ids);
    }
}

function hesk_window(PAGE, HGT, WDT) {
    var HeskWin = window.open(PAGE, "Hesk_window", "height=" + HGT + ",width=" + WDT + ",menubar=0,location=0,toolbar=0,status=0,resizable=1,scrollbars=1");
    HeskWin.focus();
}

function hesk_toggleLayerDisplay(nr) {
    if (document.all)
        document.all[nr].style.display = (document.all[nr].style.display == 'none') ? 'block' : 'none';
    else if (document.getElementById)
        document.getElementById(nr).style.display = (document.getElementById(nr).style.display == 'none') ? 'block' : 'none';
}

function hesk_confirmExecute(myText) {
    if (confirm(myText)) {
        return true;
    }
    return false;
}

function hesk_deleteIfSelected(myField, myText) {
    if (document.getElementById(myField).checked) {
        return hesk_confirmExecute(myText);
    }
}

function hesk_rate(url, element_id) {
    if (url.length == 0) {
        return false;
    }

    var element = document.getElementById(element_id);

    xmlHttp = GetXmlHttpObject();
    if (xmlHttp == null) {
        alert("Your browser does not support AJAX!");
        return;
    }

    xmlHttp.open("GET", url, true);

    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            element.innerHTML = xmlHttp.responseText;
        }
    }

    xmlHttp.send(null);
}

function stateChanged() {
    if (xmlHttp.readyState == 4) {
        document.getElementById("rating").innerHTML = xmlHttp.responseText;
    }
}

function GetXmlHttpObject() {
    var xmlHttp = null;
    try {
        // Firefox, Opera 8.0+, Safari
        xmlHttp = new XMLHttpRequest();
    }
    catch (e) {
        // Internet Explorer
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlHttp;
}

var heskKBquery = '';
var heskKBfailed = false;

function hesk_suggestKB() {
    var d = document.form1;
    var s = d.subject.value;
    var m = d.message.value;
    var element = document.getElementById('kb_suggestions');

    if (s != '' && m != '' && (heskKBquery != s + " " + m || heskKBfailed == true)) {
        element.style.display = 'block';
        var params = "p=1&" + "q=" + encodeURIComponent(s + " " + m);
        heskKBquery = s + " " + m;

        xmlHttp = GetXmlHttpObject();
        if (xmlHttp == null) {
            return;
        }

        xmlHttp.open('POST', 'suggest_articles.php', true);
        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                element.innerHTML = xmlHttp.responseText;
                heskKBfailed = false;
            }
            else {
                heskKBfailed = true;
            }
        }

        xmlHttp.send(params);
    }

    setTimeout(function() { hesk_suggestKB(); }, 2000);
}

function hesk_suggestKBsearch(isAdmin) {
    var d = document.searchform;
    var s = d.search.value;
    var element = document.getElementById('kb_suggestions');

    if (isAdmin) {
        var path = 'admin_suggest_articles.php';
    }
    else {
        var path = 'suggest_articles.php';
    }

    if (s != '' && (heskKBquery != s || heskKBfailed == true)) {
        element.style.display = 'block';
        var params = "q=" + encodeURIComponent(s);
        heskKBquery = s;

        xmlHttp = GetXmlHttpObject();
        if (xmlHttp == null) {
            return;
        }

        xmlHttp.open('POST', path, true);
        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                element.innerHTML = unescape(xmlHttp.responseText);
                heskKBfailed = false;
            }
            else {
                heskKBfailed = true;
            }
        }

        xmlHttp.send(params);
    }

    setTimeout(function() { hesk_suggestKBsearch(isAdmin); }, 2000);
}

function hesk_suggestEmail(emailField, displayDiv, padDiv, isAdmin, allowMultiple) {
    allowMultiple = allowMultiple || 0;
    var email = document.getElementById(emailField).value;
    var element = document.getElementById(displayDiv);

    if (isAdmin) {
        var path = '../suggest_email.php';
    }
    else {
        var path = 'suggest_email.php';
    }

    if (email != '') {
        var params = "e=" + encodeURIComponent(email) + "&ef=" + encodeURIComponent(emailField) + "&dd=" + encodeURIComponent(displayDiv) + "&pd=" + encodeURIComponent(padDiv);

        if (allowMultiple) {
            params += "&am=1";
        }

        xmlHttp = GetXmlHttpObject();
        if (xmlHttp == null) {
            return;
        }

        xmlHttp.open('POST', path, true);
        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                element.innerHTML = unescape(xmlHttp.responseText);
                element.style.display = 'block';
            }
        }

        xmlHttp.send(params);
    }
}

function hesk_btn(Elem, myClass) {
    Elem.className = myClass;
}

function hesk_checkPassword(password) {

    var numbers = "0123456789";
    var lowercase = "abcdefghijklmnopqrstuvwxyz";
    var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    var punctuation = "!.@$#*()%~<>{}[]";

    var combinations = 0;

    if (hesk_contains(password, numbers) > 0) {
        combinations += 10;
    }

    if (hesk_contains(password, lowercase) > 0) {
        combinations += 26;
    }

    if (hesk_contains(password, uppercase) > 0) {
        combinations += 26;
    }

    if (hesk_contains(password, punctuation) > 0) {
        combinations += punctuation.length;
    }

    var totalCombinations = Math.pow(combinations, password.length);
    var timeInSeconds = (totalCombinations / 200) / 2;
    var timeInDays = timeInSeconds / 86400
    var lifetime = 365000;
    var percentage = timeInDays / lifetime;

    var friendlyPercentage = hesk_cap(Math.round(percentage * 100), 98);

    if (friendlyPercentage < (password.length * 5)) {
        friendlyPercentage += password.length * 5;
    }

    var friendlyPercentage = hesk_cap(friendlyPercentage, 98);

    var progressBar = document.getElementById("progressBar");
    progressBar.style.width = friendlyPercentage + "%";

    if (percentage > 1) {
        // strong password
        progressBar.classList.remove('progress-bar-danger');
        progressBar.classList.remove('progress-bar-warning');
        progressBar.classList.add('progress-bar-success');
        return;
    }

    if (percentage > 0.5) {
        // reasonable password
        progressBar.classList.remove('progress-bar-danger');
        progressBar.classList.remove('progress-bar-success');
        progressBar.classList.add('progress-bar-warning');
        return;
    }

    if (percentage > 0.10 || percentage <= 0.10) {
        // weak password
        progressBar.classList.remove('progress-bar-warning');
        progressBar.classList.remove('progress-bar-success');
        progressBar.classList.add('progress-bar-danger');
        return;
    }

}

function hesk_cap(number, max) {
    if (number > max) {
        return max;
    } else {
        return number;
    }
}

function hesk_contains(password, validChars) {

    count = 0;

    for (i = 0; i < password.length; i++) {
        var char = password.charAt(i);
        if (validChars.indexOf(char) > -1) {
            count++;
        }
    }

    return count;
}

function setCookie(name, value, expires, path, domain, secure) {
    document.cookie= name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires.toGMTString() : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}

function getCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    } else {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1) {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}

function deleteCookie(name, path, domain) {
    if (getCookie(name)) {
        document.cookie = name + "=" +
            ((path) ? "; path=" + path : "") +
            ((domain) ? "; domain=" + domain : "") +
            "; expires=Thu, 01-Jan-70 00:00:01 GMT";
    }
}
