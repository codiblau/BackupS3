<?php
//Config
//MySQL
define("MYSQL_SERVER", "localhost");
define("MYSQL_USER", "your-user");
define("MYSQL_PASS", "your-password");
define("MYSQL_EXCLUDE_BBDD", "information_schema;mysql;performance_schema;phpmyadmin"); //separate by ;
define("BACKUP_PATH_MYSQLDUMP", "mysqldump");

//PDO
define("FETCH_OBJECT", "FETCH_OBJECT");
define("FETCH_LIST", "FETCH_LIST");
define("FETCH_COUNT", "FETCH_COUNT");
define("FETCH_LASTID", "FETCH_LASTID");
define("FETCH_VOID", "FETCH_VOID");

//AMAZON
define("BUCKET", "your-bucket-name");
define("AWS_PUBLIC_KEY", "your-public-amazon-aws-key");
define("AWS_PRIVATE_KEY", "your-private-amazon-aws-key");

//TEMPORAL PATH
define("TMP_PATH", "/tmp/");

//SUDO
define("SUDO_PASSWORD", "");

//BACKUP FILES
define("BACKUP_PATH_FOLDERS", 'etc/apache2 etc/mysql var/www');
?>