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

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Define variables and initialize with empty values
$project_name = $target = $type = $aspect_ratio = $source_type = $video_loading = $endless = $n_of_entries = $n_of_participant_runs = $n_of_participant_uploads = $sound = $upload_message = $start_message = $end_message = $survey_link = $autofill_id = $monochrome = $ranktrace_rate = $ranktrace_smooth = $gtrace_control = $gtrace_update = $gtrace_click = $gtrace_rate = $tolerance = "";
$project_name_err = $target_err = $source_url_err = "";

// Grab username
$username = $_SESSION["username"];

// Variables for file upload
$file_path = "";
$upload_dir = "user_uploads/";

define('MB', 1048576);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $project_id = getGUID();
    // Checks if there are files are being uploaded or not
    $file_check = array_sum($_FILES['file']['size']);
    // Handle upload errors
    if ($file_check > 0) {
        for ($x = 0; $x < count($_FILES["file"]["name"]); $x++) {
            $file = basename($_FILES["file"]["name"][$x]);
            $fileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
            $file_path = $upload_dir.$project_id.'-'.($x+1).'.'.strtolower($fileType);
            // Check if not fake video
            if(!preg_match('/video\/*/',$_FILES['file']['type'][$x])) {
                $source_url_err = "File is not a video.";
            }
            // Check extention
            if($fileType != "mp4" && $fileType != "mpeg" && $fileType != "avi" && $fileType != "webm" && $fileType != "mov") {
                $source_url_err = "Sorry, only MP4, MPEG, WEBM, MOV, and AVI files are allowed.";
            }
            // Check file size
            if ($_FILES["file"]["size"][$x] > 15000*MB) {
                $source_url_err = "Sorry, the file is too large.";
            }
        }
    }

    // Validate project_name
    if(empty(trim($_POST["project_name"]))){
        $project_name_err = "Please enter an project name.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM projects WHERE project_name = :project_name";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":project_name", $param_project_name, PDO::PARAM_STR);

            // Set parameters
            $param_project_name = htmlspecialchars(trim($_POST["project_name"]));

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $project_name = htmlspecialchars(trim($_POST["project_name"]));
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        unset($stmt);
    }

    // Validate target
    if(empty(trim($_POST["target"]))){
        $target_err = "Please enter an annotation target.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM projects WHERE target = :target";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":target", $param_target, PDO::PARAM_STR);

            // Set parameters
            $param_target = htmlspecialchars(trim($_POST["target"]));

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $target = htmlspecialchars(trim($_POST["target"]));
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        unset($stmt);
    }

    // Validate aspect_ratio
    if(empty(trim($_POST["aspect-ratio"]))){
        $aspect_ratio = "full width";
        $param_aspect_ratio = "full width";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM projects WHERE aspect_ratio = :aspect_ratio";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":aspect_ratio", $param_aspect_ratio, PDO::PARAM_STR);

            // Set parameters
            $param_aspect_ratio = htmlspecialchars(trim($_POST["aspect-ratio"]));

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $aspect_ratio = htmlspecialchars(trim($_POST["aspect-ratio"]));
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        unset($stmt);
    }

    // Get annotation type
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE type = :type";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":type", $param_type, PDO::PARAM_STR);

        // Set parameters
        $param_type = htmlspecialchars(trim($_POST["type"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $type = htmlspecialchars(trim($_POST["type"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get ranktrace monochrome
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE monochrome = :monochrome";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":monochrome", $param_monochrome, PDO::PARAM_STR);

        // Set parameters
        if (isset($_POST["monochrome"])){
            $param_monochrome = htmlspecialchars(trim($_POST["monochrome"]));
        } else {
            $param_monochrome = NULL;
        }

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if (isset($_POST["monochrome"])){
                $monochrome = htmlspecialchars(trim($_POST["monochrome"]));
            } else {
                $monochrome = 'off';
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get ranktrace ranktrace_smooth
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE ranktrace_smooth = :ranktrace_smooth";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":ranktrace_smooth", $param_ranktrace_smooth, PDO::PARAM_STR);

        // Set parameters
        if (isset($_POST["ranktrace_smooth"])){
            $param_ranktrace_smooth = htmlspecialchars(trim($_POST["ranktrace_smooth"]));
        } else {
            $param_ranktrace_smooth = NULL;
        }

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if (isset($_POST["ranktrace_smooth"])){
                $ranktrace_smooth = htmlspecialchars(trim($_POST["ranktrace_smooth"]));
            } else {
                $ranktrace_smooth = 'off';
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get ranktrace ranktrace_rate
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE ranktrace_rate = :ranktrace_rate";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":ranktrace_rate", $param_ranktrace_rate, PDO::PARAM_STR);

        // Set parameters
        $param_ranktrace_rate = htmlspecialchars(trim($_POST["ranktrace_rate"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $ranktrace_rate = htmlspecialchars(trim($_POST["ranktrace_rate"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get gtrace type
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE gtrace_control = :gtrace_control";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":gtrace_control", $param_gtrace_control, PDO::PARAM_STR);

        // Set parameters
        $param_gtrace_control = htmlspecialchars(trim($_POST["gtrace_control"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $gtrace_control = htmlspecialchars(trim($_POST["gtrace_control"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get gtrace update type
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE gtrace_update = :gtrace_update";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":gtrace_update", $param_gtrace_update, PDO::PARAM_STR);

        // Set parameters
        if (isset($_POST["gtrace_update"])){
            $param_gtrace_update = htmlspecialchars(trim($_POST["gtrace_update"]));
        } else {
            $param_gtrace_update = NULL;
        }

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if (isset($_POST["gtrace_update"])){
                $gtrace_update = htmlspecialchars(trim($_POST["gtrace_update"]));
            } else {
                if ($gtrace_control == "keyboard"){
                    $gtrace_update = 'on';
                } else {
                    $gtrace_update = 'off';
                }
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get gtrace update type
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE gtrace_rate = :gtrace_rate";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":gtrace_rate", $param_gtrace_rate, PDO::PARAM_STR);

        // Set parameters
        $param_gtrace_rate = htmlspecialchars(trim($_POST["gtrace_rate"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $gtrace_rate = htmlspecialchars(trim($_POST["gtrace_rate"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get ranktrace ranktrace_rate
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE gtrace_click = :gtrace_click";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":gtrace_click", $param_gtrace_click, PDO::PARAM_STR);

        // Set parameters
        if (isset($_POST["gtrace_click"])){
            $param_gtrace_click = htmlspecialchars(trim($_POST["gtrace_click"]));
        } else {
            $param_gtrace_click = NULL;
        }

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if (isset($_POST["gtrace_click"])){
                $gtrace_click = htmlspecialchars(trim($_POST["gtrace_click"]));
            } else {
                $gtrace_click = 'off';
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get source_type
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE source_type = :source_type";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":source_type", $param_source_type, PDO::PARAM_STR);

        // Set parameters
        if (isset($_POST["source_type"])){
            $param_source_type = htmlspecialchars(trim($_POST["source_type"]));
        } else {
            $param_source_type = NULL;
        }


        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $source_type = htmlspecialchars(trim($_POST["source_type"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Validate source_url
    if(count($_POST["source_url"]) == 1 && $source_type == "youtube"){
        $source_url_err = "Please enter at least one url to your video.";
    }
    if($file_check == 0 && $source_type == "upload"){
        $source_url_err = "Please select at least one video to upload.";
    }

    // Get video_loading method
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE video_loading = :video_loading";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":video_loading", $param_video_loading, PDO::PARAM_STR);

        // Set parameters
        $param_video_loading = htmlspecialchars(trim($_POST["video_loading"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $video_loading = htmlspecialchars(trim($_POST["video_loading"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get endless mode
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE endless = :endless";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":endless", $param_endless, PDO::PARAM_STR);

        // Set parameters
        if (isset($_POST["endless"])){
            $param_endless = htmlspecialchars(trim($_POST["endless"]));
        } else {
            $param_endless = NULL;
        }
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if (isset($_POST["endless"])){
                $endless = htmlspecialchars(trim($_POST["endless"]));
            } else {
                $endless = 'off';
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get video_loading method
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE n_of_entries = :n_of_entries AND n_of_participant_runs = :n_of_participant_runs";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":n_of_entries", $param_n_of_entries, PDO::PARAM_STR);
        $stmt->bindParam(":n_of_participant_runs", $param_n_of_participant_runs, PDO::PARAM_STR);

        // Set parameters
        if ($source_type == "upload"){
            $param_n_of_entries = count($_FILES["file"]['name']);
        } elseif ($source_type == "youtube") {
            $param_n_of_entries = count($_POST["source_url"]) - 1;
        } elseif ($source_type == "user_upload" || $source_type == "user_youtube") {
            $param_n_of_entries = htmlspecialchars(trim($_POST['n_of_participant_uploads']));
        }

        $actual_runs = $param_n_of_entries;
        if ($video_loading == "random"){
            $input_runs = htmlspecialchars(trim($_POST["n_of_participant_runs"]));
            $input_runs = (is_numeric($input_runs) ? (int)$input_runs : 0);
            if ($input_runs > 0){
                $actual_runs = htmlspecialchars(trim($_POST["n_of_participant_runs"]));
            }
        }

        $param_n_of_participant_runs = $actual_runs;

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if ($source_type == "upload"){
                $n_of_entries = count($_FILES["file"]['name']);
            } elseif ($source_type == "youtube") {
                $n_of_entries = count($_POST["source_url"]) - 1;
            } else {
                $n_of_entries = 0;
            }
            $n_of_participant_runs = $actual_runs;
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get tolerance
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE tolerance = :tolerance";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":tolerance", $param_tolerance, PDO::PARAM_STR);

        // Set parameters
        $param_gtolerance = htmlspecialchars(trim($_POST["tolerance"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $tolerance = htmlspecialchars(trim($_POST["tolerance"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get sound
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE sound = :sound";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":sound", $param_sound, PDO::PARAM_STR);

        // Set parameters
        $param_sound = htmlspecialchars(trim($_POST["video_sound"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $sound = htmlspecialchars(trim($_POST["video_sound"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get upload message
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE upload_message = :upload_message";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":upload_message", $param_upload_message, PDO::PARAM_STR);

        // Set parameters
        $param_upload_message = htmlspecialchars(trim($_POST["upload-message"]), ENT_QUOTES, "UTF-8");

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $upload_message = htmlspecialchars(trim($_POST["upload-message"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get start message
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE start_message = :start_message";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":start_message", $param_start_message, PDO::PARAM_STR);

        // Set parameters
        $param_start_message = htmlspecialchars(trim($_POST["start-message"]), ENT_QUOTES, "UTF-8");

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $start_message = htmlspecialchars(trim($_POST["start-message"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get end message
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE end_message = :end_message";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":end_message", $param_end_message, PDO::PARAM_STR);

        // Set parameters
        $param_end_message = htmlspecialchars(trim($_POST["end-message"]), ENT_QUOTES, "UTF-8");

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $end_message = htmlspecialchars(trim($_POST["end-message"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get survey link
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE survey_link = :survey_link";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":survey_link", $param_survey_link, PDO::PARAM_STR);

        // Set parameters
        $param_survey_link = htmlspecialchars(trim($_POST["survey-link"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $survey_link = htmlspecialchars(trim($_POST["survey-link"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Get autofill id
    // Prepare a select statement
    $sql = "SELECT id FROM projects WHERE autofill_id = :autofill_id";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":autofill_id", $param_autofill_id, PDO::PARAM_STR);

        // Set parameters
        $param_autofill_id = htmlspecialchars(trim($_POST["autofill-id"]));

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $autofill_id = htmlspecialchars(trim($_POST["autofill-id"]));
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);

    // Check input errors before inserting in database
    if(empty($project_name_err) && empty($target_err) && empty($source_url_err)){

        // Prepare an insert statement
        $sql = "INSERT INTO projects (username, project_id, project_name, target, type, aspect_ratio, source_type, video_loading, endless, n_of_entries, n_of_participant_runs, sound, upload_message, start_message, end_message, survey_link, autofill_id, archived, monochrome, ranktrace_smooth, ranktrace_rate, gtrace_control,  gtrace_update, gtrace_click, gtrace_rate, tolerance)
        VALUES (:username, :project_id, :project_name, :target, :type, :aspect_ratio, :source_type, :video_loading, :endless, :n_of_entries, :n_of_participant_runs, :sound, :upload_message, :start_message, :end_message, :survey_link, :autofill_id, :archived, :monochrome, :ranktrace_smooth, :ranktrace_rate, :gtrace_control, :gtrace_update, :gtrace_click, :gtrace_rate, :tolerance)";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":project_id", $param_project_id, PDO::PARAM_STR);
            $stmt->bindParam(":project_name", $param_project_name, PDO::PARAM_STR);
            $stmt->bindParam(":target", $param_target, PDO::PARAM_STR);
            $stmt->bindParam(":type", $param_type, PDO::PARAM_STR);
            $stmt->bindParam(":aspect_ratio", $param_aspect_ratio, PDO::PARAM_STR);
            $stmt->bindParam(":source_type", $param_source_type, PDO::PARAM_STR);
            $stmt->bindParam(":video_loading", $param_video_loading, PDO::PARAM_STR);
            $stmt->bindParam(":endless", $param_endless, PDO::PARAM_STR);
            $stmt->bindParam(":n_of_entries", $param_n_of_entries, PDO::PARAM_STR);
            $stmt->bindParam(":n_of_participant_runs", $param_n_of_participant_runs, PDO::PARAM_STR);
            $stmt->bindParam(":sound", $param_sound, PDO::PARAM_STR);
            $stmt->bindParam(":upload_message", $param_upload_message, PDO::PARAM_STR);
            $stmt->bindParam(":start_message", $param_start_message, PDO::PARAM_STR);
            $stmt->bindParam(":end_message", $param_end_message, PDO::PARAM_STR);
            $stmt->bindParam(":survey_link", $param_survey_link, PDO::PARAM_STR);
            $stmt->bindParam(":autofill_id", $param_autofill_id, PDO::PARAM_STR);
            $stmt->bindParam(":archived", $param_archived, PDO::PARAM_STR);

            $stmt->bindParam(":monochrome", $param_monochrome, PDO::PARAM_STR);
            $stmt->bindParam(":ranktrace_smooth", $param_ranktrace_smooth, PDO::PARAM_STR);
            $stmt->bindParam(":ranktrace_rate", $param_ranktrace_rate, PDO::PARAM_STR);
            $stmt->bindParam(":gtrace_control", $param_gtrace_control, PDO::PARAM_STR);
            $stmt->bindParam(":gtrace_update", $param_gtrace_update, PDO::PARAM_STR);
            $stmt->bindParam(":gtrace_click", $param_gtrace_click, PDO::PARAM_STR);
            $stmt->bindParam(":gtrace_rate", $param_gtrace_rate, PDO::PARAM_STR);
            $stmt->bindParam(":tolerance", $param_tolerance, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;
            $param_project_id = $project_id;
            $param_project_name = $project_name;
            $param_target = $target;
            $param_type = $type;
            $param_aspect_ratio = $aspect_ratio;
            $param_source_type = $source_type;
            $param_video_loading = $video_loading;
            $param_endless = $endless;
            $param_n_of_entries = $n_of_entries;
            $param_n_of_participant_runs = $n_of_participant_runs;
            $param_sound = $sound;
            $param_upload_message = $upload_message;
            $param_start_message = $start_message;
            $param_end_message = $end_message;
            $param_survey_link = $survey_link;
            $param_autofill_id = $autofill_id;
            $param_archived = "false";

            $param_monochrome = $monochrome;
            $param_ranktrace_smooth = $ranktrace_smooth;
            $param_ranktrace_rate = $ranktrace_rate;
            $param_gtrace_control = $gtrace_control;
            $param_gtrace_update = $gtrace_update;
            $param_gtrace_click = $gtrace_click;
            $param_gtrace_rate = $gtrace_rate;
            $param_tolerance = $tolerance;


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

        // Init the project entries (Last of the youtube links is always empty).
        $project_entries = [];
        if ($source_type == "upload"){
            $project_entries = $_FILES["file"]['name'];
        } elseif ($source_type == "youtube") {
            $project_entries = array_slice($_POST["source_url"], 0, -1);
        }

        for ($x = 0; $x < count($project_entries); $x++) {
            $file = $fileType = $file_path = "";
            if ($file_check > 0 ){
                $file = basename($project_entries[$x]);
                $fileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
                $file_path = $upload_dir.$project_id.'-'.($x+1).'.'.strtolower($fileType);
            }

            // Prepare a select statement
            $sql = "SELECT id FROM project_entries WHERE source_url = :source_url AND entry_id = :entry_id";

            if($stmt = $pdo->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":source_url", $param_source_url, PDO::PARAM_STR);
                $stmt->bindParam(":entry_id", $entry_id, PDO::PARAM_STR);

                // Set parameters
                $param_entry_id = $x+1;
                if ($source_type == "upload"){
                    $param_source_url = $file_path;
                    $param_original_name = explode(".", $file)[0];
                } elseif ($source_type == "youtube") {
                    $param_source_url = htmlspecialchars(trim($_POST["source_url"][$x]));
                    $param_original_name = explode("=", $param_source_url)[1];
                }

                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    $entry_id = $x+1;
                    if ($source_type == "upload"){
                        $source_url = $file_path;
                        $original_name = explode(".", $file)[0];
                    } elseif ($source_type == "youtube") {
                        $source_url = htmlspecialchars(trim($_POST["source_url"][$x]));
                        $original_name = explode("=", $source_url)[1];
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            // Close statement
            unset($stmt);

            // Prepare an insert statement
            $sql = "INSERT INTO project_entries (project_id, entry_id, source_type, source_url, original_name)
            VALUES (:project_id, :entry_id, :source_type, :source_url, :original_name)";

            if($stmt = $pdo->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":project_id", $param_project_id, PDO::PARAM_STR);
                $stmt->bindParam(":entry_id", $entry_id, PDO::PARAM_STR);
                $stmt->bindParam(":source_type", $param_source_type, PDO::PARAM_STR);
                $stmt->bindParam(":source_url", $param_source_url, PDO::PARAM_STR);
                $stmt->bindParam(":original_name", $param_original_name, PDO::PARAM_STR);

                // Set parameters
                $param_project_id = $project_id;
                $param_entry_id = $entry_id;
                $param_source_type = $source_type;
                $param_source_url = $source_url;
                $param_original_name = $original_name;

                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    // If successful, also upload files
                    if ($file_check > 0 && $source_type == "upload") {
                        if (move_uploaded_file($_FILES["file"]["tmp_name"][$x], $file_path)) {
                            //echo "The file ". basename($_FILES["file"]["name"][$x]). " has been uploaded.";
                        } else {
                            $source_url_err = "Sorry, there was an error uploading your file.";
                        }
                    }
                    // Redirect to projects page
                    header("location: projects.php");
                } else{
                    echo "Something went wrong. Please try again later.";
                }
            }
            // Close statement
            unset($stmt);
        }

    }

    // Close connection
    unset($pdo);
}
include("header.php");
?>
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./projects.php">go back</a><a class="button" href="./logout.php">log out</a></div>
    </div>
    <div class="page-header">
        <div>
            <p>Create a new project below.</p>
        </div>
    </div>
    <div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group <?php echo (!empty($project_name_err)) ? 'has-error' : ''; ?>">
                <label>Project Title</label>
                <span class="help-block"><?php echo $project_name_err; ?></span>
                <input placeholder="My Project" type="text" name="project_name" class="form-control" value="<?php echo $project_name; ?>">
            </div>
            <div class="form-group <?php echo (!empty($target_err)) ? 'has-error' : ''; ?>">
                <label>Annotation Target</label>
                <span class="help-block"><?php echo $target_err; ?></span>
                <input placeholder="arousal" type="text" name="target" class="form-control" value="<?php echo $target; ?>">
            </div>
            <div class="form-group" id="start-message">
                <label>Optional Target Description</label>
                <textarea class="form-control" placeholder="An optional short description displayed below the automatic instructions at the beginning of the annotation. Use it to help participants understand the labelling task (max 200 characters)." rows="5" cols="46" name="start-message" wrap="soft" maxlength="200" style="overflow:hidden; resize:none;"></textarea>
            </div>
            <div class="form-group">
                <label>Annotation Type</label>
                <div id="type-select" >
                    <input type="radio" name="type" value="ranktrace" checked><span>RankTrace</span>
                    <input type="radio" name="type" value="gtrace"><span>GTrace</span>
                    <input type="radio" name="type" value="binary"><span>BTrace</span>
                    <hr>
                    <input type="checkbox" name="monochrome" value="on"><span>Monochrome Graph</span>
                    <div id="ranktrace-config">
                        <label>Smooth RankTrace Display</label>
                        <input type="checkbox" name="ranktrace_smooth" value="on" checked><span>Smooth RankTrace graph</span><br>
                        <p style="padding-top:5px; margin: 0"><span id="ranktrace_rate-value">Update graph at a <b>500ms</b> interval.</span></p>
                        <input type="range" min="0" max="300" value="15" step="7.5" name="ranktrace_rate" class="form-control">
                        <br><i style="font-size: 0.7em">Smooths the displayed trace (not the collected data).</i>
                    </div>
                    <div id="gtrace-config" class="hidden">
                        <label>GTrace Configuration</label>
                        <input type="radio" name="gtrace_control" value="keyboard"> <span>Keyboard</span>
                        <input type="radio" name="gtrace_control" value="mouse" checked> <span>Mouse</span><br>
                        <span id="mouse-click-box">
                        <hr>
                        <input type="checkbox" name="gtrace_update" value="on" checked><span>Continuous Annotation</span><br>
                        <input type="checkbox" name="gtrace_click" value="on" checked><span>Annotation on Click</span>
                        </span>
                        <span id="rate-box">
                        <hr>
                        <p style="padding-top:5px; margin: 0"><span id="gtrace_rate-value">Update GTrace at a <b>1sec</b> interval.</span></p>
                        <input type="range" min="250" max="3000" value="1000" step="250" name="gtrace_rate" class="form-control">
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Project Source</label>   
                <div id="aspect-ratio">
                    <div><span>Video Aspect Ratio</span> <input type="text" name="aspect-ratio" class="form-control" placeholder="e.g. full width, 4:3, 16:9, 21:9, ..." /></div>
                    <i style="font-size: 0.7em">The default value is "full width".</i>
                </div>
                <span class="help-block"><?php echo $source_url_err; ?></span>
                <div id="source-select">
                    <input type="radio" name="source_type" value="youtube" checked> <span>YouTube</span>
                    <input type="radio" name="source_type" value="upload"> <span>Uploaded Videos</span><br>
                    <hr>
                    <input type="radio" name="source_type" value="user_youtube"> <span>Subject Youtube</span>
                    <input type="radio" name="source_type" value="user_upload"> <span>Subject Upload</span>
                    <!-- <hr> -->
                    <!-- <input type="radio" name="source_type" value="game"> <span>Uploaded Game</span> -->
                </div>
                <div class="form-group <?php echo (!empty($source_url_err)) ? 'has-error' : ''; ?>" id="project-entries">
                    <input type="file" multiple name="file[]" class="form-control hidden" id="file-source"
                    accept="video/mp4,video/avi,video/webm,video/mpeg,video/mov" value="">
                    <input type="text" name="source_url[]" class="form-control youtube-source" value="">
                </div>
            </div>
            <div class="form-group subject-upload hidden" id="upload-message">
                <label>Optional Upload Instructions</label>
                <textarea class="form-control" placeholder="An optional short instruction displayed to your participants when they upload or link to their own videos for annotation (max 500 characters)." rows="13" cols="46" name="upload-message" wrap="soft" maxlength="500" style="overflow:hidden; resize:none;"></textarea>
            </div>
            <div class="form-group" id='loading'>
                <label>How to Load Videos</label>
                <input class="researcher-upload" type="radio" name="video_loading" value="random" checked><span class="researcher-upload">Randomly</span>
                <input type="radio" name="video_loading" value="sequence"> <span style="margin-right: 0;">In Sequence</span>
                <br><input type="checkbox" name="endless" value="on"><span>Endless Mode</span>
            </div>
            <div class="form-group" id='tolerance'>
                <label>Tolerance of Missing Labels</label>
                <p style="padding-top:5px; margin: 0">Completes at least <b><span id="tolerance-value">50</span>%</b> of the task.</p>
                <input type="range" min="1" max="99" value="50" name="tolerance" class="form-control">
            </div>
            <div class="form-group researcher-upload" id="participant-runs">
                <label>Number of Annotations<br>a Participant Completes</label>
                <p style="padding-top:5px; margin: 0">Annotates <strong id="n_run-value">0</strong> out of <strong id="entry-n">0</strong> videos.</p>
                <input type="range" min="0" max="1" value="0" name="n_of_participant_runs" class="form-control">
                <!-- <input placeholder="play all (default)" type="number" min="1" name="n_of_participant_runs" class="form-control"><span> out of </span><strong id="entry-n">0</strong> -->
                <br><i style="font-size: 0.7em">Optional for randomized video order.</i>
            </div>
            <div class="form-group subject-upload hidden" id="participant-uploads">
                <label>Number of Videos<br>a Participant Uploads</label>
                <input value="1" type="number" name="n_of_participant_uploads" class="form-control">
            </div>
            <div class="form-group">
                <label>Play Videos With Sound</label>
                <div id='sound'>
                    <input type="radio" name="video_sound" value="on" checked> <span>Yes</span>
                    <input type="radio" name="video_sound" value="off"> <span>No</span>
                </div>
            </div>
            <div class="form-group" id="end-message">
                <label>Optional End-Plate Message</label>
                <textarea class="form-control" placeholder="An optional short message or instructions displayed to your participants below the automatic thank you message at the end of the annotation (max 200 characters)." rows="5" cols="46" name="end-message" wrap="soft" maxlength="200" style="overflow:hidden; resize:none;"></textarea>
            </div>
            <div class="form-group" id="survey-link">
                <label>Optional Google Forms Survey Link</label>
                <input placeholder="Survey button will not be added if left empty." type="text" name="survey-link" class="form-control survey" value="">
            </div>
            <div class="form-group" id="autofill-id">
                <label>Autofill Participant ID<br>to <span class="project-info">Google Forms<span class="project-info-box" id="google-forms">You can use the Google Form pre-fill entry ids to specify an field for the participant IDs, which are automatically filled in. This option lets you match the completed surveys to the video annotations through an anonymised ID (GUID). For more information see the <a href="https://gsuite.google.com/learning-center/tips/forms/#!/show-tip/pre-fill-form-answers" target="_blank">Google Forms Documentation</a>.</span></span></label>
                <input placeholder='id number of form entry (e.g. 1234567890)' type="text" name="autofill-id" class="form-control" value="">
            </div>
            <div class="form-group" id="submit">
                <input type="submit" class="button" value="submit">
                <input type="reset" class="button" value="reset">
            </div>
        </form>
    </div>
<?php
    $scripts = ['create_project.js'];
    include("scripts.php");
    $tooltip = '';
    include("footer.php");
?>