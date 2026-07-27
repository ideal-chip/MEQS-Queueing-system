
//====================================================================[ admin ]
var ajaxAdmin = new XMLHttpRequest();
ajaxAdmin.onreadystatechange = function () {
    if (ajaxAdmin.status == 200 && ajaxAdmin.readyState == 4)
    {
        if (parseInt(ajaxAdmin.responseText))
        {
            location.replace(".");
        } else
        {
            alert("<?php echo getTextValue('errorLogin', $lang); ?>");
        }
    }
}
function login() {
    var username = document.getElementById('username').value;
    var password = document.getElementById('password').value;
    ajaxAdmin.open("GET", "op.php?op=1&username=" + username + "&password=" + password, true);
    ajaxAdmin.send();
}

// press enter to login
function keyDown(event) {
    if (event.keyCode == 13)
        login();
}

function getInt(str){
    return parseInt(str);
}
