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
$css = ['forms.css'];

$message = $survey = $autofill_id = "";
$project_id = htmlspecialchars($_GET['id'], ENT_QUOTES, "UTF-8");
$participant_id = $_COOKIE['user'];
$past_sessions = array();

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $sql = "SELECT end_message, survey_label, survey_link, autofill_id, autofill_key FROM projects WHERE project_id = :project_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $message = $project['end_message'];
    $label = (!empty($project['survey_label']) ? $project['survey_label'] : 'go to survey');
    $survey = $project['survey_link'];
    $autofill_id = $project['autofill_id'];
    $autofill_key = $project['autofill_key'];
    // Close statement
    unset($stmt);

    $sql = "SELECT DISTINCT session_id FROM logs WHERE participant_id = :participant_id AND project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $param_user = $_COOKIE['user'];
    $stmt->bindParam(":participant_id", $param_user, PDO::PARAM_STR);
    $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
    $stmt->execute();


    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($past_sessions, $row['session_id']);
    }

    // Close connection
    unset($pdo);
}
include("header.php");

echo '<div class="participant_id">ID: '.$_COOKIE['user'].'<br>PAST SESSIONS:<br>'.implode("<br>", $past_sessions).'</div>';
?>
    <div id="end-messages">
        <div>
            <p>Thank you for participating in this experiment!</p>
            <?php
                $unique_key = getGUID();

                if (!empty($message)){
                    echo '<p>'.$message.'</p>';
                }

                if (!empty($survey)){
                    $button = '<a class="button" href="'.$survey;

                    if (!empty($autofill_id) || !empty($autofill_key)) {
                        $separator = '?';
                        $survey_arr = explode('/', $survey);
                        if (strpos(end($survey_arr), '?') !== false) {
                            $separator = '&';
                        }
                        if (!empty($autofill_id)){
                            $button = $button.$separator.'entry.'.$autofill_id.'='.$participant_id;
                            if (!empty($autofill_key)){
                                $button = $button.'&entry.'.$autofill_key.'='.$unique_key;
                            }
                        } else {
                            $button = $button.$separator.'entry.'.$autofill_key.'='.$unique_key;
                        }
                    }

                    $button = $button.'">'.$label.'</a>';
                    echo $button;
                }

            ?>
        <div>
    </div>
<?php
    include("scripts.php");
    $tooltip = '';
    include("footer.php");
?>