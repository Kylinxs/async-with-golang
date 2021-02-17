
/*** Shared functions ***/
var last_support_event = 0;
function sound() {
    alert(tr('Live Support Alert'));
}

function foo() {
    var ret = msg('tiki-live_support_server.php?op_online=pepe');

    alert(ret);
}

function msg(msg) {
    var req;
    try {
        // for Mozilla
        req = new XMLHttpRequest();

        req.overrideMimeType("text/xml");
    } catch (e) {
        // for IE5+
        req = new ActiveXObject("Msxml2.XMLHTTP");
    }

    req.open("GET", msg, false);
    req.send(null);
    return req.responseText;
}

function msgxml(msg) {
    var req;
    try {
        // for Mozilla
        req = new XMLHttpRequest();

        req.overrideMimeType("text/xml");
    } catch (e) {
        // for IE5+
        req = new ActiveXObject("Msxml2.XMLHTTP");
    }

    req.open("GET", msg, false);
    req.send(null);
    return req.responseXML;
}

function write_msg(txt, role, name) {
    $("#chat_data").append('<span class="' + role + '">('+name+') '+txt+'</span>');

    document.getElementById('data').value = '';
    $("#chat_data").animate({ scrollTop: 100000000000 }, 1000);

    /* And now send the message to the server */
    var ret = msg('tiki-live_support_server.php?write=' + document.getElementById('reqId').value
        + '&msg=' + txt + '&senderId=' + document.getElementById('senderId').value + '&role=' + role + '&name=' + name);
}

function event_poll() {
    evpollInterval = setInterval("pollForEvents()", 5000);
}


function pollForEvents() {
    var ret = msg('tiki-live_support_server.php?get_last_event='
        + document.getElementById('reqId').value + '&senderId=' + document.getElementById('senderId').value);
    if (ret.length > 0) {
        ret = parseInt(ret);
    } else {
        ret = 0;
    }
    /* alert(ret);
    alert(last_support_event); */
    if (ret > last_support_event) {
        while (last_support_event < ret) {
            last_support_event = last_support_event + 1;

            var txt = msg('tiki-live_support_server.php?get_event=' + document.getElementById('reqId').value
                + '&last=' + last_support_event + '&senderId=' + document.getElementById('senderId').value);

            $("#chat_data").append(txt);
            console.log("txt", txt);
        }
        $("#chat_data").animate({ scrollTop: 100000000000 }, 1000);

    }
}

function chat_close(role, user) {
    write_msg('<i>' + user + tr(' has left the chat') + '</i>', role, user);
}

/*** Client window functions ***/
function request_chat(user, tiki_user, email, reason) {
    document.getElementById('request_chat').style.display = 'none';

    document.getElementById('requesting_chat').style.display = 'block';
    var ret = msg('tiki-live_support_server.php?request_chat=1&reason=' + reason + '&user=' + user + '&tiki_user=' + tiki_user + '&email=' + email);
    document.getElementById('reqId').value = ret;
    client_poll();
}

function client_poll() {
    clourInterval = setInterval("pollForAccept()", 10000);
}

function pollForAccept() {
    var ret = msg('tiki-live_support_server.php?get_status=' + document.getElementById('reqId').value);

    if (ret == 'op_accepted') {
        clearInterval(clourInterval);

        window.location.href = 'tiki-live_support_chat_window.php?reqId=' + document.getElementById('reqId').value + '&role=user';
    }
}

function client_close() {
    msg('tiki-live_support_server.php?client_close=' + document.getElementById('reqId').value);
}

/*** Operator console functions ***/
function pollForRequests() {
    var last = msg('tiki-live_support_server.php?poll_requests=1');
    if (last.length > 0) {
        last = parseInt(last);
    } else {
        last = 0;
    }

    if (last > last_support_req) {
        window.location.reload();

        last_support_req = last;
    }
}

function set_operator_status(status) {
    var ret =
        msg('tiki-live_support_server.php?set_operator_status=' + document.getElementById('user').value + '&status=' + status);
}

function console_poll() {
    var ourInterval = setInterval("pollForRequests()", 10000);
}

var clourInterval = null;
