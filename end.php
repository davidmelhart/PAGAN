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

$current_page = explode(".", $_SERVER['REQUEST_URI'])[0];

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
$css = ['forms.css'];

$message = $survey = $autofill_id = "";
$project_id = htmlspecialchars($_GET['id'], ENT_QUOTES, "UTF-8");
$participant_id = $_COOKIE['user'];

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $sql = "SELECT end_message, survey_link, autofill_id FROM projects WHERE project_id = :project_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $message = $project['end_message'];
    $survey = $project['survey_link'];
    $autofill_id = $project['autofill_id'];
    // Close statement
    unset($stmt);       
	// Close connection
	unset($pdo);
}
include("header.php");

echo '<div class="participant_id">ID: '.$_COOKIE['user'].'</div>';
?>
    <div id="end-messages">
        <div>
            <p>Thank you for participating in this experiment!</p>
            <?php
                
                if (!empty($message)){
                    echo '<p>'.$message.'</p>';
                }
                if (!empty($survey)){
                	if (!empty($autofill_id)){
				$separator = '?';
				if (strpos(end(explode('/', $survey)), '?') !== false) {
				    $separator = '&';
				}
                		echo '<a class="button" href="'.$survey.$separator.'entry.'.$autofill_id.'='.$participant_id.'">go to survey</a>';
                	} else {
						echo '<a class="button" href="'.$survey.'">go to survey</a>';
                	}
                    
                } 
	            
            ?>
        <div>
    </div>
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>
