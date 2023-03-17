<?php
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

		<link rel="apple-touch-icon" sizes="180x180" href="static/favicon/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="static/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="static/favicon/favicon-16x16.png">
		<link rel="manifest" href="static/favicon/site.webmanifest">
		<link rel="shortcut icon" href="static/favicon/favicon.ico">
		<meta name="msapplication-TileColor" content="#343434">
		<meta name="msapplication-config" content="static/favicon/browserconfig.xml">
		<link rel="shortcut icon" href="static/favicon/favicon-16x16.png" type="image/x-icon" />
		<link href="https://fonts.googleapis.com/css?family=Raleway:400,600,900" rel="stylesheet">
		<meta name="theme-color" content="#191919" />

		<!--TODO: fill in OG properties-->
		<meta property="og:url" content=""/>
		<meta property="og:title" content=""/>
		<meta property="og:image" content="">
		<meta property="og:description" content=""/>
		<meta property="og:type" content="website"/>

		<link rel="stylesheet" type="text/css" href="static/css/base.css" />
		';

if(isset($css)){
	foreach ($css as &$style) {
		echo
			'<link rel="stylesheet" type="text/css" href="static/css/'.$style.'" />
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

		$current_page = explode("/", explode(".", $_SERVER['REQUEST_URI'])[0]);
		$current_page = end($current_page);

		if ($current_page != "annotation"
			&& $current_page != "end"
			&& $current_page != "upload"
			&& $current_page != "play"
			&& $current_page != "collection"
		) {
			echo '
			<header>
				<div id="title">
					<a href="./">
					<h1>PAGAN</h1>
					</a>
				</div>
				<nav>
					<ul>
						<a '; if($current_page == "howto"){echo 'class="current"';} echo ' href="./howto.php"><li>How To & Test</li></a>
						<a '; if($current_page == "projects" || $current_page == "archived"){echo 'class="current"';} echo' href="./projects.php"><li>My Projects</li></a>
						<a '; if($current_page == "datasets"){echo 'class="current"';} echo' href="./datasets.php"><li>Datasets</li></a>
						<a '; if($current_page == "terms"){echo 'class="current"';} echo' href="./terms.php"><li>Terms</li></a>
						<a '; if($current_page == "contact"){echo 'class="current"';} echo' href="./contact.php"><li>Contact</li></a>
					</ul>
				</nav>
			</header>
			';
		}
?>