<?php
if (!isset($counterID)) {
    header("Location: ./");
    exit(1);
}
$onload = "";
$direction = getTextValue('dir', $lang);

if (isset($_COOKIE['clerkID'])) {
    $_SESSION['clerkID'] = $_COOKIE['clerkID'];
    $_SESSION['counterID'] = $counterID;
    $onload += " onload=\"login(" . $_COOKIE['clerkID'] . ",'" . $_COOKIE['clerkPassword'] . "',1,$counterID);\" ";
}
?>

<html>
    <head>
        <title>iDEAL-Q&reg; <?php echo getTextValue('interface', $lang); ?></title>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/common.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="../css/counter.css">
        <link rel="shortcut icon" href="../files/shortcut_icons/counter.png"/>
    </head>
    <body class="bdy-login" 
          style="direction: <?php echo $direction; ?>" 
          onload=" <?php echo $onload; ?>" 
          onkeydown='keyDown(event);'>
        <div class="container body-content">
            <div class="text-center logo-big">
                <img alt="main-logo" class="" src="../files/logos/systemlogo-md.png">
            </div>
            <div class="container">
                <div class="form-signin">
                    <div class="text-center info-login">
                        iDEAL-Q &reg; Queue Management System
                        <h3 class="form-signin-heading text-center"><?php echo getTextValue('clerkLogin', $lang) . " " . $counterNo; ?></h3>
                    </div>

                    <label for="username" class="sr-only"><?php echo getTextValue('clerkName', $lang); ?></label>
                    <input type="text" id="username" class="form-control" placeholder="<?php echo getTextValue('clerkName', $lang); ?>" required autofocus>
                    <label for="password" class="sr-only"><?php echo getTextValue('password', $lang); ?></label>
                    <input type="password" id="password" class="form-control" placeholder="<?php echo getTextValue('password', $lang); ?>" required>
                    <div class="checkbox text-justify">
                        <label class="form-label text-dark">
                            <input  type="checkbox" checked="checked" id="autoLogin" > 
                            <span><?php echo getTextValue('autoLogin', $lang); ?></span>
                        </label>
                    </div>
                    <button onclick="login(document.getElementById('username').value, document.getElementById('password').value, document.getElementById('autoLogin').checked,<?php echo $counterNo; ?>);" class="btn btn-lg btn-primary btn-block" ><?php echo getTextValue('login', $lang) ?></button>
                </div>
            </div>

        </div>
        <footer>
            <div class="row">
                <p class="text-center blue"> iDEAL-Q ® Queue Management System  <span class="ftr-logo-con"><a href="http:\\www.idealchip.com" title="idealchip website" target="_blank"><img src="../files/logos/logo-ideal.ico" alt=""></a></span> Idealchip Electronics, Inc.  &copy; 1997-<?php echo date("Y") ?></p>
            </div>
        </footer>
        <script src="../js/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script type="text/javascript">

                        $(".btn").mouseup(function () {
                            $(this).blur();
                        });

                        function keyDown(event) {
                            if (event.keyCode == 13)
                                login(document.getElementById('username').value,
                                        document.getElementById('password').value,
                                        document.getElementById('autoLogin').checked,<?php echo $counterID; ?>);
                        }

                        //==============================================================  | other vars

                        var loginXML = new XMLHttpRequest();

                        //==============================================================  | login / logout


                        loginXML.onreadystatechange = function () {
                            if (loginXML.status == 200 && loginXML.readyState == 4)
                            {
                                var data = parseInt(loginXML.responseText);
                                if (data == 1)
                                {
                                    location.reload();
                                } else if (data == 2)
                                {
                                    alert("<?php echo getTextValue('errorLogin', $lang); ?>" + " - wrong clerk name!");
                                } else if (data == 3)
                                {
                                    alert("<?php echo getTextValue('errorLogin', $lang); ?>" + " - wrong password!");
                                } else
                                {
                                    alert("<?php echo getTextValue('errorLogin', $lang); ?>");
                                }
                            }
//                           
                        };

                        function login(username, hashPassword, autoLogin, counter) {
                            loginXML.open("post", "../api/counter/?op=11", true);
                            loginXML.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            loginXML.send("username=" + username + "&password=" + hashPassword + "&autologin=" + autoLogin + "&counter=" + counter);
                        }
        </script>
    </body>
</html>
