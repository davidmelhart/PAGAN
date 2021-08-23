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
            <a class="button" href="./test.php">test it out</a>
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
        <h2>Overview</h2>

        <p>If you use PAGAN in your scientific work, please cite it as:<br>
        <span style="font-weight: 600">
        Melhart D., Liapis A. & Yannakakis G. N. (2019). PAGAN: Video Affect Annotation Made Easy. In <i>Proceedings of the 8th International Conference on Affective Computing and Intelligent Interaction (ACII)</i>, Cambridge, United Kingdom.
        </span></p>

        <p>PAGAN can run in any browser and only requries a desktop computer with a conventinal keyboard for the annotator application to work. The framework consists of an administrative dashboard for researchers (accessible under <a href="./projects.php">My Projects</a>) and a separate participant interface to minimise distraction during the annotation process.</p>

        <h2>Participant Interface & Annotator Application</h2>
        <img src="./static/img/welcome.png" />
        <span class="subscript">Greeting screen in the beginning of the annotation.</span>

        <p>PAGAN supports three different kind of annotation frameworks, <strong>GTrace<sup>1</sup></strong>, based on a popular annotation protocol in affective computing, <strong>RankTrace<sup>2</sup></strong>, an intuitive solution for ordinal affect annotation, and <strong>BTrace<sup>3</sup></strong> a simplified binary labelling tool based on AffectRank.</p>

        <img src="./static/img/annotation_types.png" />
        <span class="subscript">From top to bottom: GTrace, RankTrace, and BTrace interfaces.</span>

        <h2>Test Application</h2>
        <p>A test application can be found here: <a class="button" href="./test.php">test it out</a></p>
        <p>The test only needs a title, annotation target, annotaton type (see image above), a YouTube source, and whether the videos are played with sound. After the annotation is done, the log file is automatically served and downloaded on the annotator's computer. The test application can be used freely in research project where the researcher is present and sets up the system for the participant manually before annotation.</p>

        <h2>Administrative Dashboard & Project Creation</h2>
        <p>Advanced users can use the administrative dashboard to create new projects and manage existing ones, accessing the collected annotation logs. Registered researchers have the option to upload videos instead of using YouTube link and can task their participants with uploading or linking to videos. This way PAGAN can accommodate different research protocols and setups.</p>
        <img src="./static/img/projects.png" />
        <span class="subscript">Administrative dashboard, projects overview.</span>

        <p>PAGAN is highly customizable, with many different options regarding the setup and information shared with the participants.</p>

        <img src="./static/img/pagan_setup1.png" />
        <span class="subscript">Project creation and corresponding annotator interface when uploading videos.</span>
        <br>
        <img src="./static/img/pagan_setup2.png" />
        <span class="subscript">Project creation and corresponding annotator interface when tasking the participants to upload videos.</span>

        <p>Finally, PAGAN also supports a light integration into Google Forms, which can help the platform fit more seamlessly into larger experimental designs.</p>

        <ol class="citations">
            <li>R. Cowie, M. Sawey, C. Doherty, J. Jaimovich, C. Fyans, and P. Stapleton, “Gtrace:  General  trace  program  compatible  with  emotionml,”  in <i>2013 Humaine Association Conference on Affective Computing and Intelligent Interaction. </i> IEEE, 2013, pp. 709–710.</li>
            <li>P.  Lopes,  G.  N.  Yannakakis,  and  A.  Liapis,  “RankTrace:  Relative  andunbounded affect annotation,” in <i>Proceedings of the International Conference on Affective Computing and Intelligent Interaction.</i> IEEE, 2017, pp. 158–163.</li>
            <li>G.  N.  Yannakakis  and  H.  P.  Martinez,  “Grounding  truth  via  ordinalannotation,” in <i>Proceedings of the International Conference on Affective Computing and Intelligent Interaction. </i> IEEE, 2015, pp. 574–580.</li>
        </ol>
    </div>
<?php
    include("scripts.php");
    $tooltip = '';
    include("footer.php");
?>