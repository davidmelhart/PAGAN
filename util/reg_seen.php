<?php
    require_once "../config.php";    

	$project_id = filter_input(INPUT_POST,'project_id',FILTER_SANITIZE_STRING);
	$entry_id = filter_input(INPUT_POST,'entry_id',FILTER_SANITIZE_STRING);
	$progress = json_decode($_COOKIE['progress'], true);
    foreach ($progress as $key => $entry) {
        if ($entry['project_id'] == $project_id) {
        	$videos_so_far = $entry['seen'];
        	array_push($videos_so_far, $entry_id);
            $progress[$key]['seen'] = $videos_so_far;
            $progress[$key]['n_runs'] += 1;
        }
    }
    setcookie('progress', json_encode($progress), strtotime('+7 days'), '/', $_SERVER['HTTP_HOST']);
?>