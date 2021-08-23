<?php
    require_once "../config.php";

    $time_stamp = filter_input(INPUT_POST,'epoch',FILTER_SANITIZE_STRING);
    $videotime = filter_input(INPUT_POST,'timestamp',FILTER_SANITIZE_STRING);
    $annotation_value = filter_input(INPUT_POST,'value',FILTER_SANITIZE_STRING);
    $project_id = filter_input(INPUT_POST,'project_id',FILTER_SANITIZE_STRING);
    $entry_id = filter_input(INPUT_POST,'entry_id',FILTER_SANITIZE_STRING);
    $session_id = filter_input(INPUT_POST,'session_id',FILTER_SANITIZE_STRING);
    $original_name = filter_input(INPUT_POST,'original_name',FILTER_SANITIZE_STRING);
    $annotation_type = filter_input(INPUT_POST,'annotation_type',FILTER_SANITIZE_STRING);
    $participant_id = $_COOKIE['user'];

    // Prepare an insert statement
    $sql = "INSERT INTO logs (project_id, entry_id, participant_id, session_id, original_name, time_stamp, videotime, annotation_value, annotation_type)
    VALUES (:project_id, :entry_id, :participant_id, :session_id, :original_name, :time_stamp, :videotime, :annotation_value, :annotation_type)";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":project_id", $param_project_id, PDO::PARAM_STR);
        $stmt->bindParam(":entry_id", $param_entry_id, PDO::PARAM_STR);
        $stmt->bindParam(":participant_id", $param_participant_id, PDO::PARAM_STR);
        $stmt->bindParam(":session_id", $param_session_id, PDO::PARAM_STR);
        $stmt->bindParam(":original_name", $param_original_name, PDO::PARAM_STR);
        $stmt->bindParam(":time_stamp", $param_time_stamp, PDO::PARAM_INT);
        $stmt->bindParam(":videotime", $param_videotime, PDO::PARAM_STR);
        $stmt->bindParam(":annotation_value", $param_annotation_value, PDO::PARAM_STR);
        $stmt->bindParam(":annotation_type", $param_annotation_type, PDO::PARAM_STR);

        // Set parameters
        $param_project_id = $project_id;
        $param_entry_id = $entry_id;
        $param_participant_id = $participant_id;
        $param_session_id = $session_id;
        $param_original_name = $original_name;
        $param_time_stamp = $time_stamp;
        $param_videotime = $videotime;
        $param_annotation_value = $annotation_value;
        $param_annotation_type = $annotation_type;

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Redirect to login page
            //header("location: login.php");
        } else{
            echo "Something went wrong. Please try again later.";
        }
    }
    // Close statement
    unset($stmt);
    // Close connection
    unset($pdo);
?>