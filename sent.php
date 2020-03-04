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
?>
 
    <div id="subheader">
        <h2>[Platform for Affective Game ANnotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./login.php">log in</a></div>
    </div>
 
    <div class="page-header">
        <div>
            <p>We have sent an reset link via email.</p>
        </div>
    </div>
    <div>
    
    </div>    
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>