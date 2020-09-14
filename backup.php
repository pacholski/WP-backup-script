<?php
require('wp-config.php'); // Get WordPress constants to vars.
$DB_HOST = DB_HOST;
$DB_USER = DB_USER;
$DB_PASSWORD = DB_PASSWORD;
$DB_NAME = DB_NAME;

function getDatabaseZipped($host,$user,$pass,$name, $tables=false, $backup_name=false)
{
    set_time_limit(3000); $mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
    $queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); }
    $content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
    foreach($target_tables as $table){
        if (empty($table)){ continue; }
        $result	= $mysqli->query('SELECT * FROM `'.$table.'`');  	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	$TableMLine=$res->fetch_row();
        $content .= "\n\n".$TableMLine[1].";\n\n";   $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
        for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
            while($row = $result->fetch_row())	{ //when started (and every after 100 command cycle):
                if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
                $content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
                //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
            }
        } $content .="\n\n\n";
    }
    $content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
    $backup_name = 'baza.sql';
    ob_get_clean(); header('Content-Type: application/octet-stream');  header("Content-Transfer-Encoding: Binary");  header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($content, '8bit'): strlen($content)) );    header("Content-disposition: attachment; filename=\"".$backup_name."\"");
    echo $content;
    exit;
}

function getDirItems($dir, &$results = array())
{
    $files = scandir($dir);
    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        list($unused_path, $used_path) = explode(basename(__DIR__) . '/', $path);
        $file_name = $dir . DIRECTORY_SEPARATOR . $value;
        if (!is_dir($path)) {
            $results[] = $used_path;
        } else if ($value != "." && $value != "..") {
            getDirItems($path, $results);
            $results[] = $value . '/';
        }
    }
    return $results;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="UTF-8">
	<title>Stwórz backup plików i bazy danych</title>
	<meta name="robots" content="noindex">
	<style type="text/css">
		body{
			font-family: arial;
			font-size: 14px;
			padding: 0;
			margin: 0;
			text-align: left;
			padding-bottom: 50px;
		}
		h3{
			text-align: center;
		}
		.container{
			width: 600px;
			margin: 100px auto 0 auto;
			max-width: 100%;
		}
		label{
			font-weight: bold;
			margin: 10px 0;
		}
		input[type="text"]{
			border: 1px solid #eee;
			padding: 10px;
			display: block;
			margin: 10px auto;
			width:100%;
		}
		input[type="checkbox"]{
			margin: 10px 0;
		}
		label.fs-label{
			padding-left: 5px;
			font-weight: normal;
		}
		input[type="submit"]{
			padding: 10px 20px;
			display: block;
			margin: 20px auto;
			border: 2px solid green;
			background: #fff;
			width: 100%;
			font-weight: bold;
		}
		.copyright{
			position: fixed;
			bottom:0;
			background: #333;
			color: #fff;
			width: 100%;
			padding: 10px 20px;
			text-align: center;
		}

	</style>
</head>
<body>
	<div class="container">
		<h3>Stwórz backup plików i bazy danych</h3>
		<?php
        // Creating Database.zip




        if(isset($_POST['create-db'])) {
            getDatabaseZipped($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
        }

        // Create Files zip

        if(isset($_POST['create-zip'])) {

            ini_set('max_execution_time', 10000);
            $get_name = "PLIKI";
            $get_ext = '.zip';
            $final_name = $get_name . $get_ext;


            function generate_zip_file($files = array(), $destination = '', $overwrite = false)
            {
                if (file_exists($destination) && !$overwrite) {
                    return false;
                }
                $valid_files = array();
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (file_exists($file) && $file !== "backup.php") {
                            $valid_files[] = $file;
                        }
                    }
                }
                if (count($valid_files)) {
                    $zip = new ZipArchive();
                    if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                        return false;
                    }
                    foreach ($valid_files as $file) {
                        if (file_exists($file) && is_file($file)) {
                            $zip->addFile($file, $file);
                        }
                    }
                    $zip->close();
                    return file_exists($destination);
                } else {
                    return false;
                }
            }


            //if true, good; if false, zip creation failed
            $result = generate_zip_file(getDirItems(dirname(__FILE__)), $final_name);
            if ($result) {
                echo '<p style="text-align: center;">PLIKI DO POBRANIA: <a href="' . $final_name . '">' . $final_name . '</a> <br>';
            } else {
                echo '<p style="text-align: center;">Nie udało się stworzyć pliku ' . $final_name . '</p>';
            }
        }
        ?>
		<form action="" method="POST">
			<p><strong>Wybierz co chcesz spakować</strong></p>
			<p><input type="checkbox" id="select-all-files" value="Zaznacz wszystko"><label for="select-all-files" class="fs-label">WSZYSTKO</label></p>
			<?php
			//$list_all_files_folders = getDirItems(dirname(__FILE__));

			//foreach ($list_all_files_folders as $key => $value) {
				//echo '<input type="checkbox" name="selected_files[]" id="file-'.$key.'" value="'.$value.'" /> <label for="file-'.$key.'" class="fs-label">'.$value.'</label><br />';
			//}
			?>
			<input type="text" id="create" name="create-zip" hidden value="PLIKI" placeholder="" />
			<input type="submit" value="Stwórz kopię plików" />
		</form>
		<!--<input type="submit" style="border: 2px solid red;" class="button" name="select" value="Usuń PLiki" />-->
        <form action="" method="POST">
            <input type="text" id="createdb" name="create-db" hidden value="database" placeholder="" />
            <input type="submit" value="Stwórz kopię bazy danych" />
        </form>
		<br />
	</div>

	<script type="text/javascript" src="//code.jquery.com/jquery-1.12.4.min.js"></script>
	<script type="text/javascript">
		$('#select-all-files').click(function(event) {
		    if(this.checked) {
		        // Iterate each checkbox
		        $(':checkbox').each(function() {
		            this.checked = true;
		        });
		    } else {
		        $(':checkbox').each(function() {
		            this.checked = false;
		        });
		    }
		});
	</script>
</body>
</html>