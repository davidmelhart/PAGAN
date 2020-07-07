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
$css = ['researcher.css'];
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Fetch the projects of the user
if($_SERVER["REQUEST_METHOD"] == "GET"){
    $user = $_SESSION["username"];
    $sql = "SELECT * FROM projects WHERE username = :user ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user", $user, PDO::PARAM_STR);
    $stmt->execute();

    $archived = 'true';
    $archive_length = "SELECT * FROM projects WHERE archived != :archived ORDER BY created_at DESC";
    $length_stmt = $pdo->prepare($archive_length);
    $length_stmt->bindParam(":archived", $archived, PDO::PARAM_STR);
    $length_stmt->execute();
}
include("header.php");
?>
    <div id="subheader">
        <h2>[Platform for Audiovisual General-purpose ANotation]</h2>
        <div class="subheader-buttons"><a class="button" href="./logout.php">log out</a></div>
    </div>
    <div class="page-header">
        <div>
            <p>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Welcome back.</p>
        </div>
        <div class="projects-buttons"><a class="button" href="./archived.php">archived projects</a><a class="button" href="./create_project.php">add a new project</a></div>
    </div>
    <div>
        <?php 
            if($length_stmt->rowCount() < 1){
                echo "You have no live projects.";
            } else {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $filename = str_replace(" ","-",$row['project_name']).'_'.$row['target'].'_'.$row['type'].'_log_'.explode(' ', $row['created_at'])[0];
                    
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
                    if ($row['archived'] != 'true') {
                        echo '
                            <div class="project-container" id="'.$project_id.'">
                                <div class="title-box">
                                    <div>
                                        <h2>'.$row['project_name'].'</h2>
                                        <h3>a <span class="project-info">project<span class="project-info-box">
                                                    <p><strong>annotation type:</strong> '.$rosw['type'].'</p>
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
                                        <span class="share">share this link with your participants:</span>
                                    </div>
                                    <div>
                                        <span class="date">'.$row['created_at'].'</span><br>
                                        <span class="participants">'.$participants.' participants</span><br>
                                        <div class="button-box">';
                                        if ($row['source_type'] == 'upload' || $row['source_type'] == 'user_upload') {
                                            echo '<span><a class="button download" href="util/fetch_vid.php?project_id='.$row['project_id'].'">download videos</a></span>';
                                        }
                                            echo '<span><a class="button download" href="util/fetch_log.php?project_id='.$row['project_id'].'&filename='.$filename.'">download logs</a></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bottom-box">
                                    <div class="link-box">
                                        <input class="link" value="'.$_SERVER['HTTP_HOST'].'/annotation.php?id='.$row['project_id'].'"/>
                                        <a class="button video" href="./annotation.php?id='.$row['project_id'].'&test_mode=True" target="_blank">test ';
                                        if ($row['source_type'] == 'user_youtube' || $row['source_type'] == 'user_upload') {
                                            echo 'upload';
                                        } else{
                                            echo 'video';
                                        }
                                        echo'</a>
                                    </div>
                                    <span class="button archive" data-projectid='.$row['project_id'].' data-archive="true">archive project</span>
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