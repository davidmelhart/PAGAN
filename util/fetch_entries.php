<?php
    require_once "../config.php";
    if($_SERVER["REQUEST_METHOD"] == "GET"){
        $project_id = $_GET['project_id'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$project_id.'_entries.csv"');

        $sql = "SELECT * FROM project_entries WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":project_id", $project_id, PDO::PARAM_STR);
        $stmt->execute();


        $response = implode(",", array('EntryID', 'OriginalName', 'DatabaseName'))."\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $database_name = str_replace('user_uploads/', '', $row['source_url']);
            $response .= implode(",",array($row['entry_id'], $row['original_name'], $database_name))."\n";
        }

        // Close statement
        unset($stmt);
        // Close connection
        unset($pdo);
        echo $response;
    }
?>