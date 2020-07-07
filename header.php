<?php
// // Initialize the session
// session_start();
// # Generate User if User does not exists
// if(!isset($_COOKIE['user'])){
// 	$id = getGUID();
//     setcookie('user', $id, time()+315400000,"/");
//     $_COOKIE['user'] = $id;
// }

// $current_page = explode(".", $_SERVER['REQUEST_URI'])[0];

// # Generates GUID for username
// function getGUID(){
//     mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
//     $charid = strtoupper(md5(uniqid(rand(), true)));
//     $hyphen = chr(45);
//     $uuid = substr($charid, 0, 8).$hyphen
//         	.substr($charid, 8, 4).$hyphen
//         	.substr($charid,12, 4).$hyphen
//             .substr($charid,16, 4).$hyphen
//         	.substr($charid,20,12);
//     return $uuid;
// }

// Header HTML block
// parameters:
// 	title - string; fills in title tag, defaults to "Platform for Affective Game ANnotation"
// 	css - array of strings; additional stylesheet paths, defaults to None
echo
	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
	<HTML>
	<HEAD>
		<title>PAGAN | [';
		if(isset($title)){
			echo $title;
		} else {
			echo "Platform for Audiovisual General-purpose ANotation";
		}
echo   ']</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="apple-touch-icon" sizes="180x180" href="/static/favicon/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/static/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/static/favicon/favicon-16x16.png">
		<link rel="manifest" href="/static/favicon/site.webmanifest">
		<link rel="shortcut icon" href="/static/favicon/favicon.ico">
		<meta name="msapplication-TileColor" content="#343434">
		<meta name="msapplication-config" content="/static/favicon/browserconfig.xml">
		<link rel="shortcut icon" href="/static/favicon/favicon-16x16.png" type="image/x-icon" />
		<link href="https://fonts.googleapis.com/css?family=Raleway:400,600,900" rel="stylesheet">
		<meta name="theme-color" content="#191919" />

		<!--TODO: fill in OG properties-->
		<meta property="og:url" content=""/>
		<meta property="og:title" content=""/>
		<meta property="og:image" content="">
		<meta property="og:description" content=""/>
		<meta property="og:type" content="website"/>

		<link rel="stylesheet" type="text/css" href="/static/css/base.css" />
		';

if(isset($css)){
	foreach ($css as &$style) {
		echo
			'<link rel="stylesheet" type="text/css" href="/static/css/'.$style.'" />
			';
	}
}

echo
	'</HEAD>
	<BODY>
		<div id="main-content">';
		if (isset($help) && $help == True) {
			echo '<!--<div class="help-container">
				<div class="help-tooltip">Click the icon to receive useful information about the current page.</div>
				<div class="icon help">?</div>
			</div>-->';
		}

		if ($current_page != "/annotation" 
			&& $current_page != "/end" 
			&& $current_page != "/upload"
			&& $current_page != "/play"
			&& $current_page != "/collection"
			// && explode("/", $current_page)[1] != "collection" 
			// && explode("/", $current_page)[1] != "play" 
			// && explode("/", $current_page)[1] != "play_demo"
			// && explode("/", $current_page)[1] != "play_experiment"
			// && explode("/", $current_page)[1] != "annotation_experiment"
		) {
			echo '
			<header>
				<div id="title">
					<a href="./index.php">
					<h1>PAGAN</h1>
					</a>
				</div>
				<nav>
					<ul>
						<a '; if($current_page == "/howto"){echo 'class="current"';} echo ' href="./howto.php"><li>How To & Test</li></a>
						<a '; if($current_page == "/projects" || $current_page == "/archived"){echo 'class="current"';} echo' href="./projects.php"><li>My Projects</li></a>
						<a '; if($current_page == "/datasets"){echo 'class="current"';} echo' href="./datasets.php"><li>Datasets</li></a>
						<a '; if($current_page == "/terms"){echo 'class="current"';} echo' href="./terms.php"><li>Terms</li></a>
						<a '; if($current_page == "/contact"){echo 'class="current"';} echo' href="./contact.php"><li>Contact</li></a>
					</ul>
				</nav>
			</header>
			';
		}
?>