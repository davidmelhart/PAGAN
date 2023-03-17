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


function parseSource (string $url): ?string {
        if (strncmp($url, 'user/', 5) === 0) {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9\-\_]{11}$/', $url)) {
            return $url;
        }

        if (preg_match('/(?:watch\?v=|v\/|embed\/|ytscreeningroom\?v=|\?v=|\?vi=|e\/|watch\?.*vi?=|\?feature=[a-z_]*&v=|vi\/)([a-zA-Z0-9\-\_]{11})/', $url, $code)) {
            return $code[1];
        }

        if (preg_match('/([a-zA-Z0-9\-\_]{11})(?:\?[a-z]|\&[a-z])/', $url, $code)) {
            return $code[1];
        }

        if (preg_match('/u\/1\/([a-zA-Z0-9\-\_]{11})(?:\?rel=0)?$/', $url)) {
            return null;
        }

        if (preg_match('/(?:watch%3Fv%3D|watch\?v%3D)([a-zA-Z0-9\-\_]{11})[%&]/', $url, $code)) {
            return $code[1];
        }

        if (preg_match('/watchv=([a-zA-Z0-9\-\_]{11})&list=/', $url, $code)) {
            return $code[1];
        }

        return null;
    }

$title = 'Platform for Affective Game ANnotation';
$css = ['upload.css', 'forms.css'];

// Define variables and initialize with empty values
$project_id = $source_url = $original_name = $project_name = $target = $source_type = $message = $n_of_participant_runs = $endless = "";
$source_url_err = $sound = "";

// Grab username
$participant = $_COOKIE['user'];
$progress = array();
$progress_entry = new stdClass();
$current_run;

$started_project = 0;

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $session_id = getGUID();
    setcookie('session_id', $session_id, strtotime('+7 days'), '/', $_SERVER['SERVER_NAME']);
    $_COOKIE['session_id'] = $session_id;

    $project_id = htmlspecialchars($_GET['id'], ENT_QUOTES, "UTF-8");
    if (empty($project_id)){
        header('location: index.php');
        exit();
    }
    $sql = "SELECT * FROM projects WHERE project_id = :project_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount() == 1){
        $row = $stmt->fetch();
        $project_name = $row['project_name'];
        $target = $row['target'];
        $source_type = $row['source_type'];
        $message = $row['upload_message'];
        $n_of_participant_runs = $row['n_of_participant_runs'];
        $endless = $row['endless'];
        $sound = $row['sound'];
    } else {
        echo "Oops! Something went wrong. Please try again later. 1";
    }
    // Close statement
    unset($stmt);

    if(isset($_COOKIE['progress'])){
        $progress = json_decode($_COOKIE['progress'], true);
        // Check ongoing project
        $this_project = "";
        foreach ($progress as $key => $entry) {
            if ($entry['project_id'] == $project_id) {
                $started_project = 1;
                $this_project = $project_id;
                $current_run = $entry;
                if($current_run['n_runs'] >= $n_of_participant_runs && $endless != 'on') {
                    header("location: end.php?id=".$project_id);
                    exit();
                } elseif ($current_run['n_runs'] >= $n_of_participant_runs && $endless == 'on') {
                    $current_run['n_runs'] = 0;
                    $current_run['seen'] = array();
                    $progress[$key]['n_runs'] = 0;
                    $progress[$key]['seen'] = array();
                    setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
                }
                break;
            }
        }
        if(strlen($this_project) < 1){
            $progress_entry->project_id =  $project_id;
            $progress_entry->seen = array();
            $progress_entry->n_runs = 0;
            array_push($progress, $progress_entry);
            setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
            $current_run['project_id'] = $project_id;
            $current_run['n_runs'] = 0;
            $current_run['seen'] = array();
        }
    } else {
        $progress_entry->project_id = $project_id;
        $progress_entry->seen = array();
        $progress_entry->n_runs = 0;
        array_push($progress, $progress_entry);
        setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
        $current_run['project_id'] = $project_id;
        $current_run['n_runs'] = 0;
        $current_run['seen'] = array();
    }
}

// Variables for file upload
$file_path = "";
$upload_dir = "user_uploads/";
$file_counter = 0;

define('MB', 1048576);
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $project_id = trim($_POST["project-id"]);
    if (isset($_COOKIE['session_id'])) {
        $session_id = $_COOKIE['session_id'];
    } else {
        $session_id = getGUID();
    }

    $sql = "SELECT * FROM projects WHERE project_id = :project_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount() == 1){
        $row = $stmt->fetch();
        $project_name = $row['project_name'];
        $target = $row['target'];
        $source_type = $row['source_type'];
        $message = $row['upload_message'];
        $n_of_participant_runs = $row['n_of_participant_runs'];
        $endless = $row['endless'];
        $sound = $row['sound'];
    } else {
        echo "Oops! Something went wrong. Please try again later. 1";
    }
    // Close statement
    unset($stmt);

    if(isset($_COOKIE['progress'])){
        $progress = json_decode($_COOKIE['progress'], true);
        // Check ongoing project
        $this_project = "";
        foreach ($progress as $key => $entry) {
            if ($entry['project_id'] == $project_id) {
                $started_project = 1;
                $this_project = $project_id;
                $current_run = $entry;
                if($current_run['n_runs'] >= $n_of_participant_runs && $endless != 'on') {
                    header("location: end.php?id=".$project_id);
                    exit();
                } elseif ($current_run['n_runs'] >= $n_of_participant_runs && $endless == 'on') {
                    $current_run['n_runs'] = 0;
                    $current_run['seen'] = array();
                    $progress[$key]['n_runs'] = 0;
                    $progress[$key]['seen'] = array();
                    setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
                }
                break;
            }
        }
        if(strlen($this_project) < 1){
            $progress_entry->project_id =  $project_id;
            $progress_entry->seen = array();
            $progress_entry->n_runs = 0;
            array_push($progress, $progress_entry);
            setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
            $current_run['project_id'] = $project_id;
            $current_run['n_runs'] = 0;
            $current_run['seen'] = array();
        }
    } else {
        $progress_entry->project_id = $project_id;
        $progress_entry->seen = array();
        $progress_entry->n_runs = 0;
        array_push($progress, $progress_entry);
        setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
        $current_run['project_id'] = $project_id;
        $current_run['n_runs'] = 0;
        $current_run['seen'] = array();
    }

    $sql = "SELECT * FROM project_entries WHERE project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
    if ($stmt->execute()) {
        $last_entry;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $last_entry = $row;
            $file_counter ++;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later. 2";
    }
    unset($stmt);

    // Checks if there are files are being uploaded or not
    if (count($_FILES) > 0){
        $file_check = $_FILES['file']['size'];
        // Handle upload errors
        if ($file_check > 0) {
            $file = basename($_FILES["file"]["name"]);
            $fileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
            $file_path = $upload_dir.$project_id.'-'.($file_counter+1).'.'.strtolower($fileType);
            // Check if not fake video
            if(!preg_match('/video\/*/',$_FILES['file']['type'])) {
                $source_url_err = "File is not a video.";
            }
            // Check extention
            if($fileType != "mp4" && $fileType != "mpeg" && $fileType != "avi" && $fileType != "webm" && $fileType != "mov") {
                $source_url_err = "Sorry, only MP4, MPEG, WEBM, MOV, and AVI files are allowed.";
            }
            // Check file size
            if ($_FILES["file"]["size"] > 15000*MB) {
                $source_url_err = "Sorry, the file is too large.";
            }
        }
    }

    // Validate source_url
    if($source_type == "user_youtube"){
        if (!strpos($_POST["source_url"], "youtube")) {
            $source_url_err = "Please enter at least one url to your video.";
        }
    }
    if($source_type == "user_upload"){
        if ($file_check == 0){
            $source_url_err = "Please select at least one video to upload.";
        }
    }

    // Init the project entries (Last of the youtube links is always empty).
    $project_entry = "";
    $entry_id = getGUID();
    if ($source_type == "user_upload"){
        $project_entry = $_FILES["file"]['name'];

        $file = $fileType = $file_path = "";
        if ($file_check > 0 ){
            $file = basename($project_entry);
            $fileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
            // $file_path = $upload_dir.$project_id.'-'.($file_counter+1).'.'.strtolower($fileType);
            $file_path = $upload_dir.$project_id.'_'.$entry_id.'.'.strtolower($fileType);
        }
    } elseif ($source_type == "user_youtube") {
        $project_entry = $_POST["source_url"];
    }

    if(empty($source_url_err)){
        // Prepare a select statement
        $sql = "SELECT id FROM project_entries WHERE source_url = :source_url AND entry_id = :entry_id";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":source_url", $param_source_url, PDO::PARAM_STR);
            $stmt->bindParam(":entry_id", $entry_id, PDO::PARAM_STR);

            // Set parameters
            // $param_entry_id = $file_counter+1;
            if ($source_type == "user_upload"){
                $param_source_url = $file_path;
                // $original_name = $project_id.'-'.($file_counter+1);
                $original_name = basename($_FILES["file"]["name"]); // $project_id.'_'.$entry_id;
            } elseif ($source_type == "user_youtube") {
                $param_source_url = trim($_POST["source_url"]);
                $param_original_name = parseSource($param_source_url); //explode("=", $param_source_url)[1];
            }

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // $entry_id = $file_counter+1;
                if ($source_type == "user_upload"){
                    $source_url = $file_path;
                    // $original_name = $project_id.'-'.($file_counter+1);
                    $original_name = basename($_FILES["file"]["name"]); //$project_id.'_'.$entry_id;
                } elseif ($source_type == "user_youtube") {
                    $source_url = trim($_POST["source_url"]);
                    $original_name = parseSource($param_source_url); //explode("=", $source_url)[1];
                }
            } else{
                echo "Oops! Something went wrong. Please try again later. 3";
            }
        }

        // Close statement
        unset($stmt);

    	$file = $fileType = $file_path = "";
        if ($file_check > 0 ){
            $file = basename($project_entry);
            $fileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
            // $file_path = $upload_dir.$project_id.'-'.($file_counter+1).'.'.strtolower($fileType);
            $file_path = $upload_dir.$project_id.'_'.$entry_id.'.'.strtolower($fileType);
        }

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
                if ($file_check > 0 && $source_type == "user_upload") {
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
                        echo "The file ". basename($_FILES["file"]["name"]). " has been uploaded.";
                    } else {
                        $source_url_err = "Sorry, there was an error uploading your file.";
                    }
                }
                // Redirect to projects page
                header("location: annotation.php?id=".$project_id."&session=".$session_id."&entry=".$entry_id);
                exit();
            } else{
                echo "Something went wrong. Please try again later. 4";
            }
        }
        // Close statement
        unset($stmt);
    }

    // Close connection
    unset($pdo);
}

?>

<?php
    include("header.php");
    if(!isset($_COOKIE['seen_notice'])) {
    echo "<div id='cookie_notice'>
            <h3>Hello!</h3>
            <p>Thank you for your help in this experiment!</p>
            <p>By participating in this study you understand and consent to your labelling data to be stored and used in further experiments and/or made public as part of a larger dataset.</p>
            <p>Don't worry, all the data is collected anonymously and cannot be traced back to you.</p>
            <br>
            <p>We use scripts and persistent cookies on this platform. Our applications run on JavaScript, while cookies help us collecting and organising data.</p>
            <p>Please keep scripts and cookies enabled for science!</p>
            <button>Alright, I understand</button>
        </div>
        <div id='cookie_wall'></div>";
        setcookie('seen_notice', 'seen', strtotime('+365 days'), '/', $_SERVER['HTTP_HOST']);
    }
    echo '<div class="participant_id">ID: '.$_COOKIE['user'].'<br>SESSION: '.$session_id.'</div>';
?>

<div class="inner">
    <div class="upload-container">
        <div id='intro'>
            <?php
            if($started_project > 0){
                echo '<p class="welcome">Video Upload for '.$project_name.'</p>';
            } else {
                echo '<p class="welcome">Welcome to '.$project_name.'!</p>';
            }
            echo '<p>An experiment on '.$target.'.</p>
            <p class="instructions">Please ';
            if($source_type == 'user_youtube') {
                echo 'link a YouTube video';
            } else {
                echo 'upload a video';
            }
            echo ' to start labelling it.</p>
            <p>More instructions will follow on labelling on the next screen.</p>';
            if ($sound === 'on'){
                echo '<p>Audio is enabled in this experiment.<br>Please turn on your speakers or headphones.</p>';
            }
            echo '<p>'.$message.'</p>';
            if($endless != 'on') {
                echo '<p class=counter>video '.(string)($current_run['n_runs']+1).' out of '.(string)$n_of_participant_runs.'</p>';
            }
            ?>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        	<div class="form-group">
                <label><?php
                	if ($source_type == "user_upload") {
                		echo "Upload Video";
                	} else if ($source_type == "user_youtube") {
        				echo "Link YouTube Video";
                	} else {
                		echo "Upload";
                	}
                ?></label>
                <div class="form-group <?php echo (!empty($source_url_err)) ? 'has-error' : ''; ?>" id="project-entries">
                    <span class="help-block"><?php echo $source_url_err; ?></span>
                    <input type="hidden" name="project-id" value="<?php echo $project_id;?>">
                    <?php
                    if ($source_type == "user_upload") {
                        echo '<input type="file" name="file" class="form-control" id="file-source" accept="video/mp4,video/avi,video/webm,video/mpeg,video/mov" value="">';
                    } else if ($source_type == "user_youtube") {
                        echo '<input type="text" name="source_url" class="form-control youtube-source" value="">';
                    }
                    ?>
                </div>
            </div>

            <div class="form-group" id="submit">
                <input type="submit" class="button" value="submit">
                <input type="reset" class="button" value="reset">
            </div>
        </form>
    </div>
</div>

<?php
    $scripts = ['cookie_notice.js'];
    include("scripts.php");
    $tooltip = '';
    include("footer.php");
?>