<?php
$title = 'Platform for Affective Game ANnotation';
$css = ['forms.css'];
include("header.php");

// Initialize the session
session_start();

// Include config file
require_once "config.php";
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
                		echo '<a class="button" href="'.$survey.'&entry.'.$autofill_id.'='.$participant_id.'">go to survey</a>';
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