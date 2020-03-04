<?php
$title = 'Platform for Affective Game ANnotation';
$css = ['researcher.css', 'forms.css'];
include("header.php");

// Initialize the session
session_start();

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$project_name = $target = $type = $sound = $source_url = "";
$project_name_err = $target_err = $source_url_err = "";

$source_type = "youtube";
$video_loading = "random";
$endless = "on";
$n_of_entries = 1;
$n_of_participant_runs = 1;

// Grab username
$username = $_SESSION["username"];

// Variables for file upload
$file_path = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $project_id = getGUID();
    $project_name = $_POST["project_name"];
    $target = $_POST["target"];
    $type = $_POST["type"];
    $sound = $_POST["video_sound"];
    $source_url = $_POST["source_url"];
    $source_url = explode("v=", $source_url)[1];
    header("location: annotation.php?id=".$project_id."&test_mode=true&title=".$project_name."&target=".$target."&type=".$type."&sound=".$sound."&source_type=".$source_type."&source=".$source_url."&video_loading=".$video_loading);
    exit();
}
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
            <p>Create a test project below.</p>
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
                <input placeholder="arousal" type="text" name="target" class="form-control" value="">
            </div>
            <div class="form-group">
                <label>Annotation Type</label>
                <div id="type-select" >
                    <input type="radio" name="type" value="ranktrace" checked><span>RankTrace</span>
                    <input type="radio" name="type" value="gtrace"><span>GTrace</span>
                    <input type="radio" name="type" value="binary"><span>Binary</span>
                </div>
            </div>
            <div class="form-group">
                <label>Project Source</label>
                <div class="form-group <?php echo (!empty($source_url_err)) ? 'has-error' : ''; ?>" id="project-entries">
                    <span class="help-block"><?php echo $source_url_err; ?></span>
                    <input type="text" name="source_url" class="form-control youtube-source" value="" placeholder="paste the address of your YouTube video here">
                </div>
            </div>
            <div class="form-group">
                <label>Play Video With Sound</label>
                <div id='sound'>
                    <input type="radio" name="video_sound" value="on" checked> <span>Yes</span>
                    <input type="radio" name="video_sound" value="off"> <span>No</span>
                </div>
            </div>
            <div class="form-group" id="submit">
                <input type="submit" class="button" value="submit">
                <input type="reset" class="button" value="reset">
            </div>
        </form>
    </div>    
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>