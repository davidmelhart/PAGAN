<?php
$title = 'Platform for Affective Game ANnotation';
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
    </div>
    <div class="main-text">
        <p>If you have any questions or suggestions regarding the platform or would like to gain access to the full functionality, please don't hesitate to contact us via the following email address:</p>
        <div style="text-align: center; font-size: 1.5em">
            <a class="button" href="mailto:david.melhart@um.edu.mt?subject=Requesting PAGAN access" target="_blank">david.melhart [at] um.edu.mt</a>
        </div>
    </div>
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>