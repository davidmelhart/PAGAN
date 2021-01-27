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
    $css = ['annotation.css'];
    $test_mode = False;
    if (!empty($_GET['test_mode'])){
        $test_mode = 1;
    }

    $project_name = $target = $type = $session_id = "";
    $available_entries = array();
    if (isset($_COOKIE['session_id'])) {
        $session_id = $_COOKIE['session_id'];
    } else {
        $session_id = getGUID();
    }
    $progress = array();
    $current_run;

    $started_project = 0;

    // Init globals
    $project_name = $project_id = $target = $type = $source_type = $video_loading = $n_of_entries = $n_of_participant_runs = $source = $sequence_n = $endless = $sound = $message = "";

    if($_SERVER["REQUEST_METHOD"] == "GET"){
        $project_id = htmlspecialchars($_GET['id'], ENT_QUOTES, "UTF-8");
        if ($test_mode > 0) {
            $project_name = htmlspecialchars($_GET['project_name'], ENT_QUOTES, "UTF-8");
            $target = htmlspecialchars($_GET['target'], ENT_QUOTES, "UTF-8");
            $type = htmlspecialchars($_GET['type'], ENT_QUOTES, "UTF-8");
            $sound = htmlspecialchars($_GET['sound'], ENT_QUOTES, "UTF-8");
            $source_type = htmlspecialchars($_GET['source_type'], ENT_QUOTES, "UTF-8");
            $video_loading = htmlspecialchars($_GET['video_loading'], ENT_QUOTES, "UTF-8");
            $source_url = htmlspecialchars($_GET['source'], ENT_QUOTES, "UTF-8");
            $source = array('source_url' => "https://www.youtube.com/watch?v=".$source_url);
            $source_type = "youtube";
            $video_loading = "random";
            $endless = "on";
            $n_of_entries = 1;
            $n_of_participant_runs = 1;
        } else {
            if (isset($_GET['entry'])) {
                $entry_id = htmlspecialchars($_GET['entry'], ENT_QUOTES, "UTF-8");
            }
            $sql = "SELECT * FROM projects WHERE project_id = :project_id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount() == 1){
                $row = $stmt->fetch();
                $project_name = $row['project_name'];
                $target = $row['target'];
                $type = $row['type'];
                $source_type = $row['source_type'];
                $video_loading = $row['video_loading'];
                $endless = $row['endless'];
                $n_of_entries = $row['n_of_entries'];
                $n_of_participant_runs = $row['n_of_participant_runs'];
                $sound = $row['sound'];
                $message = $row['start_message'];
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            unset($stmt);
            // Manage book-keeping cookie
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
                }
            } else {
                if (!isset($progress_entry)){
                    $progress_entry = $project_id;
                }

                $progress_entry->project_id =  $project_id;
                $progress_entry->seen = array();
                $progress_entry->n_runs = 0;
                array_push($progress, $progress_entry);
                setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
            }

            // Get available entires in the projects
            $available_entries = array();
            $sql = "SELECT * FROM project_entries WHERE project_id = :project_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
            if ($stmt->execute()) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!in_array($row['entry_id'], $current_run['seen'])) {
                        $available_entries[] = $row;
                    }
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            unset($stmt);

            if (empty($entry_id)) {
                if($source_type == "user_upload" || $source_type == "user_youtube") {
                    header("location: upload.php?id=".$project_id);
                    exit();
                } else {
                    if($video_loading == "sequence"){
                        $source = reset($available_entries);
                    } else {
                        $source = $available_entries[array_rand($available_entries)];
                    }
                }
            } else {
                $sql = "SELECT * FROM project_entries WHERE project_id = :project_id AND entry_id = :entry_id LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
                $stmt->bindParam(":entry_id", $entry_id, PDO::PARAM_STR);
                $stmt->execute();
                if($stmt->rowCount() == 1){
                    $row = $stmt->fetch();
                    $source = $row;
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                // Close statement
                unset($stmt);
            }

            // Close connection
            unset($pdo);
        }
    }

    if(!isset($_COOKIE['seen_notice'])) {
        setcookie('seen_notice', 'seen', time()+315400000, '/', $_SERVER['HTTP_HOST']);
    }

    include("header.php");

    if ($test_mode > 0) {
        echo "<div class='subheader-buttons'><a class='button' href='./test.php'>go back</a></div>";
    }
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
    }

    echo '<div class="participant_id">ID: '.$_COOKIE['user'].'</div>';
?>


<div class="inner">
    <div class="annotation-container">
        <?php
            if ($test_mode > 0) {
                echo "<div id=test-tag>test mode</div>";
            }
        ?>
        <div id="tutorial">
            <div>
                <?php
                    if($endless !== 'on') {
                        echo '<p class=counter>video '.(string)($current_run['n_runs']+1).' out of '.(string)$n_of_participant_runs.'</p>';
                    }
                    if($started_project < 1){
                        echo '<p class="welcome">Welcome to '.$project_name.'!</p>';
                    }
                    if ($sound === 'on'){
                        echo '<p>The video contains audio.<br>Please turn on your speakers or headphones.</p>';
                    }
                    if ($type === 'gtrace') {
                        echo '<p class="instructions">Please use the <span class="key right"></span>(increase) and <span class="key left"></span>(decrease) keys<br>to indicate the <strong>the level of '.$target;
                        if(!empty($message)){echo'*';}
                        echo '</strong> while watching the video.</p>';
                    } else if ($type ==='binary'){
                        echo '<p class="instructions">Please use the <span class="key up"></span>(increase) and <span class="key down"></span>(decrease) keys<br>to indicate <strong>positive or negative changes of '.$target;
                        if(!empty($message)){echo'*';}
                        echo '</strong><br>while watching the video.</p>';
                    } else {
                        echo '<p class="instructions">Please use the scroll-wheel <span class="key scroll-full"></span>to indicate<br>the <strong>changes in the level of '.$target;
                        if(!empty($message)){echo'*';}
                        echo '</strong> while watching the video.</p>';
                    }

                    if (!empty($message)){
                        echo '<p><strong>*</strong>'.$message.'</p>';
                    }
                ?>
                <p id='video-load-notice' style="height: 35px;">Please wait. Your video is loading...</p>
                <p><span id='video-load-icon' class="key wait"></span></p>
                <?php
                if ($type === 'binary') {
                    echo '<img src="static/img/simple_example.png"/>';
                } elseif ($type === 'gtrace') {
                    echo '<img src="static/img/gtrace_example.png"/>';
                } else {
                    echo '<img src="static/img/ranktrace_example.png"/>';
                }
                ?>
            </div>
        </div>
        <div class="messages">
            <div id="pause" class="hidden">
                <p>The video is paused.</p>
                <p>Press <span class="key space"></span> to continue.</p>
                <p style="font-size: 0.7em">If stuck please refresh (Ctrl + R).</p>
            </div>
            <div id="ended" class="hidden">
                <p>The video has ended.</p>
                <p>Please wait until the labels are processed.</p>
                <span class='key wait'></span>
            </div>
        </div>
        <div id="video-shade"></div>
        <div id="video-length">
            <div id="bar" <?php if($source_type == 'game'){echo 'style="opacity:0"';}?>></div>
        </div>
        <?php
            if($source_type == 'youtube' || $source_type == 'user_youtube') {
                echo
                '<div id="player"></div>
                ';
            } else {
                echo '<link rel="preload" as="video" href="'.$source['source_url'].'">
                ';
            }
        ?>
        <video id="video">
            <source id="video-file">
        </video>
        <div id="controls">
            <?php
            if ($type === 'gtrace') {
                echo '<div id="label" class="gtrace"><span>'.$target.'</span></div>
                <div class="gtrace-label positive"><span>high</span></div>
                <div class="gtrace-label neutral"><span>neutral</span></div>
                <div class="gtrace-label negative"><span>low</span></div>
                <div class="keys gtrace"><span class="key left"></span><span>decrease</span></div>';
            } else {
                echo '<div id="label"><span>'.$target.'</span></div>';
            }
            echo '<canvas id="trace" width="810" height="150"></canvas>';
            if ($type === 'gtrace') {
                echo '<div class="keys gtrace"><span class="key right"></span><span>increase</span></div>';
            } else {
                echo '<div class="keys">';
                    if ($type === 'binary') {
                        echo '<span class="key up"></span>';
                        echo '<span>increase (+)</span>';
                    } else {
                        echo '<span class="key scroll-up"></span>';
                        echo '<span>increase</span>';
                    }
                    echo '<hr/>';
                    if ($type === 'binary') {
                        echo '<span>decrease (-)</span>';
                        echo '<span class="key down"></span>';
                    } else {
                        echo '<span>decrease</span>';
                        echo '<span class="key scroll-down"></span>';
                    }

                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php
    $scripts = ['cookie_notice.js', 'annotation.js'];
    include("scripts.php");
    $video = "";
    if($source_type == 'youtube' || $source_type == 'user_youtube'){
        $video = explode("v=", $source['source_url'])[1];
    } else {
        $video = $source['source_url'];
    }
    echo
        '<script type="text/javascript">
            loadVideo(
                annotation_type = "'.$type.'",
                video_type = "'.$source_type.'",
                video = "'.$video.'",
                videoname = "'.$project_name.'",
                target = "'.$target.'",
                project_id = "'.$project_id.'",
                entry_id = "'.$source['entry_id'].'",
                session_id = "'.$session_id.'",
                name = "'.$source['original_name'].'",
                sound = "'.$sound.'",
                test_mode ="'.$test_mode.'"
            );
        </script>
        ';
    $tooltip = '';
    include("footer.php");
?>