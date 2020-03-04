<?php
$title = 'Platform for Affective Game ANnotation';
$css = ['researcher.css', 'forms.css'];
include("header.php");

// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: projects.php");
    exit;
}

// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $email = $affiliation = $password = $confirm_password = $secret = "";
$username_err = $email_err = $affiliation_err = $password_err = $confirm_password_err = $secret_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }

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
                if($stmt->rowCount() == 1){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }

   // Validate affiliation
    if(empty(trim($_POST["affiliation"]))){
        $affiliation_err = "Please enter an affiliation.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE affiliation = :affiliation";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":affiliation", $param_affiliation, PDO::PARAM_STR);
            
            // Set parameters
            $param_affiliation = trim($_POST["affiliation"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $affiliation = trim($_POST["affiliation"]);
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }

    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate secret
    if(empty(trim($_POST["secret"]))){
        $secret_err = "Please enter your registration key.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM reg_keys WHERE secret = :secret";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":secret", $param_secret, PDO::PARAM_STR);
            
            // Set parameters
            $param_secret = trim($_POST["secret"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $row = $stmt->fetch();
                    $key_id = $row['id'];
                    $del = $pdo->prepare("DELETE FROM reg_keys WHERE id = :id");
                    $del->bindParam(":id", $key_id, PDO::PARAM_STR);
                    $del->execute();
                } else{
                    $secret_err = "Registration key does not exist.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }

    // Check input errors before inserting in database
    if(empty($username_err) && empty($email_err) && empty($affiliation_err) && empty($password_err) && empty($confirm_password_err) && empty($secret_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, email, affiliation, password) VALUES (:username, :email, :affiliation, :password)";
         
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":affiliation", $param_affiliation, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_affiliation = $affiliation;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to login page
                header("location: login.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }
    
    // Close connection
    unset($pdo);
}
?>
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./login.php">log in</a></div>
    </div>

    <div class="page-header">
        <div>
            <p>You can create a new account with your registration key.</p>
            <p>If you don't have a key, please request one.</p>
        </div>
    </div>

    <div class="wrapper">       
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <span class="help-block"><?php echo $username_err; ?></span>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
            </div>
            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Email Address</label>
                <span class="help-block"><?php echo $email_err; ?></span>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
            </div>
            <div class="form-group <?php echo (!empty($affiliation_err)) ? 'has-error' : ''; ?>">
                <label>Affiliation</label>
                <span class="help-block"><?php echo $affiliation_err; ?></span>
                <input list="partners" name="affiliation" class="form-control" value="<?php echo $affiliation; ?>">
                <datalist id="partners">
                    <option value="IDG">
                    <option value="COMnPLAY">
                    <option value="Tabriz University">
                    <option value="WWU Munster">
                </datalist>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <span class="help-block"><?php echo $password_err; ?></span>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
            </div>
            <div class="form-group <?php echo (!empty($secret_err)) ? 'has-error' : ''; ?>">
                <label>Registration Key</label>
                <span class="help-block"><?php echo $secret_err; ?></span>
                <input type="text" name="secret" class="form-control" value="<?php echo $secret; ?>">
            </div>
            <div class="form-group" id="submit">
                <input type="submit" class="button" value="submit">
                <input type="reset" class="button" value="reset">
            </div>
        </form>
    </div>    
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>