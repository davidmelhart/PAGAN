<?php
$title = 'Platform for Affective Game ANnotation';
$css = ['researcher.css', 'forms.css'];
include("header.php");

// Initialize the session
session_start();
 
// Check if the user is logged in, otherwise redirect to login page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: projects.php");
    exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$email = $email_err = "";
$username = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
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
                } else{
                    $email = trim($_POST["email"]);
                    $sql = "SELECT username FROM users WHERE email = :email";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt->execute();
                    $username = $stmt->fetch(PDO::FETCH_ASSOC)['username'];
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        unset($stmt);
    }
        
    // Check input errors before updating the database
    if(empty($email_err)){
        // Prepare an update statement
        $sql = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":token", $param_token, PDO::PARAM_STR);
            
            // Set parameters
            $param_email = $email;
            $param_token = bin2hex(random_bytes(16));
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Send email to user with the token in a link they can click on
                $to = $email;
                $subject = "Password Reset - PAGAN [Platform for Affective Game ANnotation]";
                $msg = 
"Dear ".$username.",

You can reset your password by clicking at the link below:
https://pagan.davidmelhart.com/reset.php?token=".$param_token."

This is an automated message, please don't reply directly to it.
If you have any further inquiries about the service please address it to:
david.melhart@um.edu.mt

Best regards,
David Melhart
Institute of Digital Games
University of Malta
http://www.institutedigitalgames.com/";
                $headers = "From: PAGAN [Platform for Affective Game ANnotation] <noreply@davidmelhart.com>";
                mail($to, $subject, $msg, $headers);
                header('location: pending.php?email=' . $email);

                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("location: sent.php");
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
?>
 
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./register.php">register</a><a class="button" href="./login.php">log in</a></div>
    </div>
 
    <div class="page-header">
        <div>
            <p>In case you forgot your password, you can request a password reset here.</p>
        </div>
    </div>
    <div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                <label>Email Address</label>
                <span class="help-block"><?php echo $email_err; ?></span>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
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