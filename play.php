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
  $css = ['play.css'];
  $test_mode = False;
  if (!empty($_GET['test_mode'])){
    $test_mode = 1;
  }

  $project_name = $target = $type = "";
  $available_entries = array();
  $session_id = getGUID();
  setcookie('session_id', $session_id, strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
  $_COOKIE['session_id'] = $session_id;
  $progress = array();
  $current_run;
  // Define variables and initialize with empty values
  $project_id = $game = $entry_id = $source_type = $n_of_participant_runs = "";

  // Grab username
  $participant = $_COOKIE['user'];
  $current_run;

  $started_project = 0;
  $file_counter = 0;

  if($_SERVER["REQUEST_METHOD"] == "GET"){
      $project_id = htmlspecialchars($_GET['id'], ENT_QUOTES, "UTF-8");
      setcookie('project_id', $project_id, strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
      $_COOKIE['project_id'] = $project_id;
      $game = htmlspecialchars($_GET['game'], ENT_QUOTES, "UTF-8");
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
          $source_type = $row['source_type'];
          $n_of_participant_runs = $row['n_of_participant_runs'];
      } else {
          echo "Oops! Something went wrong. Please try again later. 1";
      }
      // Close statement
      unset($stmt);

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

      if (empty($game)) {
          // Manage book-keeping cookie    
          if(isset($_COOKIE['game_progress'])){
              $progress = json_decode($_COOKIE['game_progress'], true);
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
                          $current_run['played'] = array();
                          $progress[$key]['n_runs'] = 0;
                          $progress[$key]['played'] = array();
                          setcookie('game_progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
                      }
                      break;
                  }
              }
              if(strlen($this_project) < 1){
                  $progress_entry->project_id =  $project_id;
                  $progress_entry->played = array();
                  $progress_entry->n_runs = 0;
                  array_push($progress, $progress_entry);
                  setcookie('game_progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
              }
          } else {
              $progress_entry->project_id =  $project_id;
              $progress_entry->played = array();
              $progress_entry->n_runs = 0;
              array_push($progress, $progress_entry);
              setcookie('game_progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
          }

          $all_games = ['TinyCars', 'SolidRally', 'Apex', 'RunNGun', 'Platform', 'Endless', 'TopDown', 'FPS', 'Shootout'];

          foreach ($all_games as $key => $possible_game) {
              if (!in_array($possible_game, $current_run['played'])) {
                $available_entries[] = $possible_game;    
              }
          }
          $game = $available_entries[array_rand($available_entries)];
      }
  }

  include("header.php");
  echo '<div class="participant_id">ID: '.$_COOKIE['user'].' <span>';
  for ($i = 0; $i < $current_run['n_runs']; $i++) {
    echo'■';
  }
  for ($j = 0; $j < $n_of_participant_runs-$current_run['n_runs']; $j++) {
    echo'□';
  }
  echo '</span></div>';
  
  echo "
  <div class='inner'>
    <div class='webgl-content'>
      <link rel='stylesheet' href='games/".$game."/TemplateData/style.css'>
      <div id='gameContainer' style='width: 960px; height: 600px; border-radius: 20px; box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);'></div>
    </div>";

    include('scripts.php');

    echo "
    <script src='games/".$game."/TemplateData/UnityProgress.js'></script>
    <script src='games/".$game."/Build/UnityLoader.js'></script>
    <script>
      var gameInstance = UnityLoader.instantiate('gameContainer', 'games/".$game."/Build/".$game.".json', {onProgress: UnityProgress});
    </script>
    <script type='text/javascript' src='static/js/capture.js'></script>
    <script type='text/javascript'>
      game='".$game."';
      project_id='".$project_id."'; 
      // entry_id='".($file_counter+1)."';
      entry_id='".$session_id."';
      source_url='".$upload_dir.$project_id.'-'.($file_counter+1)."';
      original_name='".$game."';
    </script>
    ";

    include("footer.php");
?>