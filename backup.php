<?php
require('wp-config.php'); // Get WordPress constants to vars.
$DB_HOST = DB_HOST;
$DB_USER = DB_USER;
$DB_PASSWORD = DB_PASSWORD;
$DB_NAME = DB_NAME;

    function getDatabaseZipped($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME) {
        $command = "mysqldump --opt -h '".$DB_HOST."' -u '".$DB_USER."' -p'".$DB_PASSWORD."' '".$DB_NAME."' | gzip > database.gz";
        exec($command);
    }

    function getFilesZipped() {
        shell_exec("zip -r files.zip './'");
    }

getFilesZipped();
getDatabaseZipped($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);

echo "<a href='files.zip'>Download Files</a><br>";
echo "<a href='database.gz'>Download Database</a><br><br>";
        echo "<b style='color:red;'>Remember to delete files after downloading! </b><br>";

// Delete backup script from host.
$toDelete = "backup.php";
if (!unlink($toDelete)) {  
    echo ("$toDelete can't be deleted.");  
}  
else {  
    echo ("$toDelete deleted.");  
} 