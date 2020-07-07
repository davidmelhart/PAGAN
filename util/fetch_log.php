<?php
    require_once "../config.php";
    if($_SERVER["REQUEST_METHOD"] == "GET"){
        $project_id = $_GET['project_id'];
        $filename = $_GET['filename'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $sql = "SELECT * FROM logs WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
        $stmt->execute();


        $response = implode(",", array('OriginalName', 'DatabaseName', 'Participant', 'SessionID', 'Timestamp','VideoTime','Value'))."\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $databaseName = $project_id.'-'.$row['entry_id'];
            $response .= implode(",",array($row['original_name'], $databaseName, $row['participant_id'], $row['session_id'], $row['time_stamp'], $row['videotime'], $row['annotation_value']))."\n";
        }

        // Close statement
        unset($stmt);
        // Close connection
        unset($pdo);
        echo $response;
    }
?>