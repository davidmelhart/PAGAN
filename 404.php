<?php
$title = 'Platform for Affective Game ANnotation';
$css = ['researcher.css', 'forms.css'];
include("header.php");

// Initialize the session
session_start();
 
// Include config file
require_once "config.php";
?>
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons">
            <?php
                if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                    echo '<a class="button" href="./logout.php">log out</a>';
                } else {
                    echo '<a class="button" href="./register.php">register</a><a class="button" href="./login.php">log in</a>';
                }
            ?>
        </div>
    </div>
    <div class="page-header">
        <div>
            <p>This is an error. The place you are looking for does not exist.</p>
        </div>
    </div>
    <div class="error-msg">
        404
    </div>
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>