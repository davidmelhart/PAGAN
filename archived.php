<?php
$title = 'Platform for Affective Game ANnotation';
$css = ['researcher.css'];
include("header.php");

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Fetch the projects of the user
if($_SERVER["REQUEST_METHOD"] == "GET"){
    $user = $_SESSION["username"];
    $sql = "SELECT * FROM projects WHERE username = :user ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user", $user, PDO::PARAM_STR);
    $stmt->execute();

    $archived = 'true';
    $archive_length = "SELECT * FROM projects WHERE archived = :archived ORDER BY created_at DESC";
    $length_stmt = $pdo->prepare($archive_length);
    $length_stmt->bindParam(":archived", $archived, PDO::PARAM_STR);
    $length_stmt->execute();
}

?>
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./logout.php">log out</a></div>
    </div>
    <div class="page-header">
        <div>
            <p>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Welcome back.</p>
        </div>
        <div class="projects-buttons"><a class="button" href="./projects.php">live projects</a><a class="button" href="./create_project.php">add a new project</a></div>
    </div>
    <div>
        <?php 
            if($length_stmt->rowCount() < 1){
                echo "You have no archived projects.";
            }else{
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $filename = str_replace(" ","-",$row['project_name']).'_'.$row['target'].'_log_'.explode(' ', $row['created_at'])[0];
                    
                    $project_id = $row['project_id'];
                    $sql = "SELECT participant_id FROM logs WHERE project_id = :project_id";
                    $stmt2 = $pdo->prepare($sql);
                    $stmt2->bindParam(":project_id", $project_id, PDO::PARAM_STR);                
                    $stmt2->execute();
                    $participants = array();
                    while ($participant = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                        array_push($participants, $participant['participant_id']);
                    }
                    $participants = (count(array_unique($participants)));
                    unset($stmt2);
                    if ($row['archived'] === 'true') {
                        echo '
                            <div class="project-container" id="'.$project_id.'">
                                <div class="title-box">
                                    <div>
                                        <h2>'.$row['project_name'].'</h2>
                                        <h3>a past <span class="project-info">project<span class="project-info-box">
                                                    <p><strong>annotation type:</strong> '.$row['type'].'</p>
                                                    <p><strong>source type:</strong> '.$row['source_type'].'</p>
                                                    <p><strong>video loading:</strong> '.$row['video_loading'].'</p>
                                                    <p><strong>endless mode:</strong> '.$row['endless'].'</p>
                                                    <p><strong>sound:</strong> '.$row['sound'].'</p>
                                                    <p><strong>number of entries:</strong> '.$row['n_of_entries'].'</p>
                                                    <p><strong>participant completes:</strong> '.$row['n_of_participant_runs'].'</p>';
                                                    if(!empty($row['survey_link'])){echo "
                                                        <p><strong>survey_link:</strong> ".$row['survey_link']."</p>";}
                                                echo '</span></span>
                                             on '.$row['target'].'</h3>
                                    </div>
                                    <div>
                                        <span class="date">'.$row['created_at'].'</span><br>
                                        <span class="participants">'.$participants.' participants</span><br>';
                                        if ($row['source_type'] == 'upload' || $row['source_type'] == 'user_upload') {
                                            echo '<span><a class="button download" href="util/fetch_vid.php?project_id='.$row['project_id'].'">download videos</a></span>';
                                        }
                                        echo '<span><a class="button download" href="util/fetch_log.php?project_id='.$row['project_id'].'&filename='.$filename.'">download logs</a></span><span class="button archive" data-projectid='.$row['project_id'].' data-archive="false">restore project</span>
                                    </div>
                                </div>
                            </div>
                        ';
                    }
                }
                // Close statement
                unset($stmt);
                // Close connection
                unset($pdo);
            }
        ?>
    </div>

<?php
    $scripts = ['researcher.js'];
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>