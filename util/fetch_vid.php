<?php
    require_once "../config.php";

    if($_SERVER["REQUEST_METHOD"] == "GET"){
        $project_id = $_GET['project_id'];
        $zip = new ZipArchive();
        $tmp_file = tempnam('.','');
        $zip_name = $project_id.".zip"; // Zip name
        $zip->open($tmp_file,  ZipArchive::CREATE);
        $files = scandir('../user_uploads/');
        //echo '<pre>' , var_dump($files) , '</pre>';
        foreach ($files as $file) {
            echo $file;
            if (substr($file, 0, strlen($project_id)) === $project_id) {
                # download file
                $download_file = file_get_contents('../user_uploads/'.$file);
                #add it to the zip
                $zip->addFromString(basename($file), $download_file);
            }
        }
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename='.$zip_name);
        readfile($tmp_file);
        unlink($tmp_file);
    }
?>