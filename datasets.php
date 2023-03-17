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
include("header.php");
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
            <p>Under construction. No official datasets have been released yet...</p>
        </div>
    </div>
    <div>

    </div>
<?php
    include("scripts.php");   
    $tooltip = '';
    include("footer.php");
?>