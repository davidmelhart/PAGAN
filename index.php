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
    </div>
    <div class="main-text">
    	<h2>Introduction</h2>
        <p>With the advances of machine learning, the necessity for larger datasets has grown stronger. This issue is even more critical in the field of affective computing, where collecting large amounts of emotional data can be an arduous task as existing annotation frameworks often require installation, programming knowledge, or direct researcher supervision.</p>

		<p>PAGAN [Platform for Audiovisual General-purpose ANnotation] was designed to address these issues by providing an easy-to-use framework for multi-purpose audiovisual annotation.</p>

		<img src="./static/img/pagan_intro.png" />
		<span class='subscript'>Left: PAGAN project creation; right: PAGAN annotation with RankTrace using the SEMAINE database<sup>1</sup>.</span>

		<p>PAGAN was developed at the Institute of Digital Games at the University of Malta</p>
		<div style="text-align: center">
			<img src="./static/img/uom-logo.png" style="width: 200px" />
		</div>
		<p>The tool is free for scientific use. An open test application can be found on the <a href="./howto.php">How To & Test</a> page.</p>
		<p>Students and researchers interested in using the framework should send an email to
			<a href="mailto:david.melhart@um.edu.mt?subject=Requesting PAGAN access" target="_blank">david.melhart [at] um.edu.mt</a>
		with a pargraph describing their project to gain access to the full project creation and management functionalities.</p>

		<!--<p>If you use PAGAN in your scientific work, please cite as:
		</p>-->

		<!--<p>PAGAN is under the Affero GPL General Public License. A copy of this license may be downloaded from the <a href="./terms.php">Terms</a> section. The sourcecode of the application is available on GitHub.</p>-->
        <ol class="citations">
            <li>G.  McKeown,  M.  Valstar,  R.  Cowie,  M.  Pantic,  and  M.  Schroder,  “The semaine database: Annotated multimodal records of emotionally colored conversations between a person and a limited agent,” <i>IEEE Transactionson Affective Computing</i>, vol. 3, no. 1, pp. 5–17, 2012.</li>
        </ol>
    </div>
<?php
    include("scripts.php");
    $tooltip = '';
    include("footer.php");
?>