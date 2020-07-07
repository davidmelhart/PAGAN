<?php
    require_once "../config.php";

    $archived = filter_input(INPUT_POST,'archived',FILTER_SANITIZE_STRING);
    $project_id = filter_input(INPUT_POST,'project_id',FILTER_SANITIZE_STRING);

    // Prepare an insert statement
    $sql = "UPDATE projects
            SET archived = :archived
            WHERE project_id = :project_id";
     
    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":archived", $param_archived, PDO::PARAM_STR);
        $stmt->bindParam(":project_id", $param_project_id, PDO::PARAM_STR);

        // Set parameters
        $param_project_id = $project_id;
        $param_archived = $archived;
        
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