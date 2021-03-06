<?php

require 'properties.php';
require 'ConnexioInterceptor.php';
require 'AmazonServei.php';

//Esborrem els arxius per estalviar espai
$patternsToDelete = array('*.gz','*.docx','*.part*','*.jpg','*.jpeg','*.png');

foreach($patternsToDelete as $p){
    $command = 'echo "'.SUDO_PASSWORD.'" | sudo -S rm -rf '.TMP_PATH.$p;
    $out = "";
    $err = "";

    $return = exec($command, $out, $err);
}


$today = new DateTime("now", new DateTimeZone('Europe/Madrid'));
$weekDay = $today->format('w'); // 0 - 6
//Modifiquem variables php
//error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Europe/Madrid');

$arrayfiles = array();


//Doing MySQL backup
$connexioInterceptor = ConnexioInterceptor::getInstance();

$databases = $connexioInterceptor->consulta('show databases', FETCH_LIST);
$excludeDatabases = explode(';', MYSQL_EXCLUDE_BBDD);

foreach ($databases as $db) {
    $database = $db->Database;

    $isExcluded = false;
    foreach ($excludeDatabases as $excluded) {
        if ($database === $excluded) {
            $isExcluded = true;
        }
    }
    if (!$isExcluded) {
        $backupfile = $database . date("Y-m-d") . '.gz';

        $command = BACKUP_PATH_MYSQLDUMP . " --single-transaction --user=" . MYSQL_USER . " '-p" . MYSQL_PASS . "' --host=" . MYSQL_SERVER . " --databases " . $database . " | gzip -9 > " . TMP_PATH . "$backupfile";
        $out = "";
        $err = "";

        $return = exec($command, $out, $err);

        $obj = new stdClass();
        $obj->name = $backupfile;
        $obj->path = TMP_PATH . $backupfile;

        array_push($arrayfiles, $obj);
    }
}
//Finished MySQL backup
//
//Prepare export files
//0 is SUNDAY
if ($weekDay == 0) {
    $command = 'tar -zcPf ' . TMP_PATH . 'full_' . $today->format('Y-m-d') . '.tar.gz -C / ' . BACKUP_PATH_FOLDERS;
    $out = "";
    $err = "";

    $return = exec($command, $out, $err);

    //Divivim en bocins de 5 GB (-d per sufixes amb números, per defecte el sufixe es aa, ab, ac..)
    $command = 'split --bytes=5000M -d ' . TMP_PATH . 'full_' . $today->format('Y-m-d') . '.tar.gz ' . TMP_PATH . 'full_' . $today->format('Y-m-d') . '.part';
    $out = "";
    $err = "";

    $return = exec($command, $out, $err);

    //Número de parts que hem creat
    $numParts = ceil(filesize(TMP_PATH . 'full_' . $today->format('Y-m-d') . '.tar.gz') * .0009765625 * .0009765625 / 5000);

    for ($i = 0; $i < $numParts; $i++) {
        $num = $i;

        if ($num < 10) {
            $num = '0' . $num;
        }

        $obj = new stdClass();
        $obj->name = 'full_' . $today->format('Y-m-d') . '.part' . $num;
        $obj->path = TMP_PATH . 'full_' . $today->format('Y-m-d') . '.part' . $num;

        array_push($arrayfiles, $obj);
    }
}

//Finished export files
//
//Upload to Amazon S3
$amazonServei = AmazonServei::getInstance();

foreach ($arrayfiles as $f) {
    $amazonServei->uploadObject('copia_' . $today->format('Y-m-d') . DIRECTORY_SEPARATOR . $f->name, $f->path);
}


?>