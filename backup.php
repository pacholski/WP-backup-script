<?php
require('wp-config.php'); // Get WordPress constants to vars.
$DB_HOST = DB_HOST;
$DB_USER = DB_USER;
$DB_PASSWORD = DB_PASSWORD;
$DB_NAME = DB_NAME;

// Names of generated files
$BCKP_DB_NAME = "database-bckp".".gz";
$BCKP_FILES_NAME = "files-bckp".".zip";

function getDatabaseZipped($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME) {
        $command = "mysqldump --opt -h '".$DB_HOST."' -u '".$DB_USER."' -p'".$DB_PASSWORD."' '".$DB_NAME."' | gzip > ".$BCKP_DB_NAME."";
        exec($command);
    }

function getFilesZipped() {
        exec("zip -r ".$BCKP_FILES_NAME." './'");
        echo "Remember to delete ".$BCKP_FILES_NAME." after downloading! ";
    }

getFilesZipped();
getDatabaseZipped($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);

// Delete backup script from host.
$toDelete = "backup.php";
if (!unlink($toDelete)) {  
    echo (" $toDelete can't be deleted. ");  
}  
else {  
    echo (" $toDelete deleted. ");  
} 