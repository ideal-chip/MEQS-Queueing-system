<?php
error_reporting(0);
session_start();
if (isset($_SESSION['username'])) {
    header("location: ../");
} else {
    //-------------------------------------< db and language >----------
    
    require_once '../../language.php';
    
    if (isset($_SESSION['language'])) {
        $lang = $_SESSION['language'];
    } else {
        $lang = getSetting('defaultLanguage');
    }
    $lang_adminLogin = getTextValue('adminLogin', $lang); 
    //-------------------------------------< vars and post request >----------
    
    $username = $password = "";
    $username_err = $password_err = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter a username.";
        } else {
            $username = trim($_POST["username"]);
        }
        // Validate password
        if (empty(trim($_POST['password']))) {
            $password_err = "Please enter a password.";
        } else {
            $password = trim($_POST['password']);
        }


        // Validate credentials
        if (empty($username_err) && empty($password_err)) {
            // Prepare a select statement
            $sql = "SELECT user_name, user_password, user_privileges, SHA2(?, 256) AS 'password' FROM users WHERE user_name = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ss", $param_password, $param_username);
                // Set parameters
                $param_password = $password;
                $param_username = $username;
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();
                    // Check if username exists, if yes then verify password
                    if ($stmt->num_rows == 1) {
                        // Bind result variables
                        $stmt->bind_result($username, $hashed_password, $priv, $hashed_newPassword);
                        
                        if ($stmt->fetch()) {
                            if ($hashed_newPassword == $hashed_password) {
                                /* Password is correct, so start a new session and
                                  save the username to the session */
                                session_start();
                                $_SESSION['username'] = $username;
                                $_SESSION['userpriv'] = $priv;
                                header("location: ../");
                            } else {
                                // Display an error message if password is not valid
                                $password_err = 'The password you entered was not valid.';
                            }
                        }
                    } else {
                        // Display an error message if username doesn't exist
                        $username_err = 'No account found with that username.';
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            // Close statement
            $stmt->close();
        }
        // Close connection
        $mysqli->close();
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title>login</title>
            <link href="../../css/paper.bootstrap.min.css" rel="stylesheet" type="text/css"/>
            <link href="../../css/common.css" rel="stylesheet" type="text/css"/>
            <link href="../../css/login.css" rel="stylesheet" type="text/css"/>
            <link rel="shortcut icon" href="../../files/shortcut_icons/admin.png">
        </head>
        <body class="">
            <div class="container">

                <div class="bdy-login">
                    <div class="info-login">
                        <div class="logo-big ">
                            <img alt="main-logo" class="" src="../../files/logos/systemlogo-md.png">
                        </div>
                        <div class="bg-blue-deep pad-v-10 arc-top">
                            <h5 class="text-whito"><?php echo $lang_adminLogin ?></h5>
                        </div>
                    </div>
                    <form  method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-signin">
                            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>" >
                                <input type="text" name="username" class="form-control" placeholder="username" autofocus>
                                <span class="help-block"><?php echo $username_err; ?></span>
                            </div>
                            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                                <input type="password" name="password" class="form-control" placeholder="password">
                                <span class="help-block"><?php echo $password_err; ?></span>
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" class="btn btn-primary" value="Login">
                            </div>
                            <br>
                        </div>
                    </form>
                </div>

            </div>
            <div class="footer bg-white-gray pad-v-5">
                <p class="text-center blue"> 
                    iDEAL-Q ® Queue Management System
                    <span class="ftr-logo-con">
                        <a href="http:\\www.idealchip.com" title="idealchip website" target="_blank">
                            <img src="../../files/logos/logo-ideal.ico" alt="">
                        </a>
                    </span>
                    iDEALCHiP Electronics, Inc.  &copy; 1997-<?php echo date("Y") ?>
                </p>
            </div>
        </body>
    </html>
    <?php
}
