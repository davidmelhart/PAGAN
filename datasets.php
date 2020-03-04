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
        <div>
            <p>Under construction. No official datasets have been released yet...</p>
        </div>
    </div>
    <div>

    </div>
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>