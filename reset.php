<?php
require_once "config.php";
// Initialize the session
session_start();
// Generate User if User does not exists
if(!isset($_COOKIE['user'])){
$id = getGUID();
  setcookie('user', $id, time()+315400000,"/");
  $_COOKIE['user'] = $id;
}

// Generates GUID for username
function getGUID(){
  mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
  $charid = strtoupper(md5(uniqid(rand(), true)));
  $hyphen = chr(45);
  $uuid = substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
          .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);
  return $uuid;
}

$title = 'Platform for Affective Game ANnotation';
$css = ['researcher.css', 'forms.css'];

// Check if the user is logged in, otherwise redirect to login page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: projects.php");
    exit;
}
 
// Define variables and initialize with empty values
$new_password = $confirm_password = $email = "";
$new_password_err = $confirm_password_err = $email_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $token = htmlspecialchars($_GET['token'], ENT_QUOTES, "UTF-8");
    // Grab the email adress, which matches the token
    $sql = "SELECT email FROM password_resets WHERE token=:token LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();
    $stored_email = $stmt->fetch(PDO::FETCH_ASSOC)['email'];
    unset($stmt);

    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                if($stmt->rowCount() == 0){
                    $email_err = "This email is not registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
                // Also throw error if the token-email does not match the input
                if($stored_email !== $email) {
                    $email_err = "Wrong credentials.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        unset($stmt);
    }

    // Validate new password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have atleast 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
        
    // Check input errors before updating the database
    if(empty($new_password_err) && empty($confirm_password_err) && empty($email_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET password = :password WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // On sucess, destroy token
                $del = $pdo->prepare("DELETE FROM password_resets WHERE token = :token LIMIT 1");
                $del->bindParam(":token", $token, PDO::PARAM_STR);
                $del->execute();

                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("location: login.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        unset($stmt);
    }
    
    // Close connection
    unset($pdo);
}
include("header.php");
?>
 
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./register.php">register</a><a class="button" href="./login.php">log in</a></div>
    </div>
 
    <div class="page-header">
        <div>
            <p>Fill in your email and new password to reset your credentials.</p>
        </div>
    </div>
    <div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]).'?token='.htmlspecialchars($_GET['token'], ENT_QUOTES, "UTF-8"); ?>" method="post">
            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Email Address</label>
                <span class="help-block"><?php echo $email_err; ?></span>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
            </div>
            <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                <label>New Password</label>
                <span class="help-block"><?php echo $new_password_err; ?></span>
                <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
                <input type="password" name="confirm_password" class="form-control">
            </div>
            <div class="form-group" id="submit">
                <input type="submit" class="button" value="submit">
            </div>
        </form>
    </div>    
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>