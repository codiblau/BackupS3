<?php

class ConnexioInterceptor {

    private static $instancia;
    private static $connexio;
    
    private static $server;
    private static $username;
    private static $password;
    private static $db;

    private function __construct($MYSQL_BBDD) {

        self::$server = MYSQL_SERVER;
        self::$username = MYSQL_USER;
        self::$password = MYSQL_PASS;
        self::$db = $MYSQL_BBDD;

        ConnexioInterceptor::connect();
    }

    public function __destruct() {
        self::$connexio = null;
    }

    /**
     * @return this
     */
    public static function getInstance($MYSQL_BBDD=NULL) {
        if (!self::$instancia) {
            self::$instancia = new ConnexioInterceptor($MYSQL_BBDD);
        }

        return self::$instancia;
    }

    private static function connect() {
        try {
            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true
            );

            if(self::$db !== null){
                self::$connexio = new PDO("mysql:host=".self::$server.";dbname=".self::$db, self::$username, self::$password, $options);
                self::$connexio->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                self::$connexio = new PDO("mysql:host=".self::$server, self::$username, self::$password, $options);
            }
            
        } catch (PDOException $e) {
                print_r($e);
            exit();
        }
    }

    public static function startTransaction() {
        if(self::$connexio == null){
            ConnexioInterceptor::connect();
        }
        
        if (self::$connexio->inTransaction()) {
            self::$connexio->rollback();
        }
        self::$connexio->beginTransaction();
    }

    public static function endTransaction($commit) {
        if ($commit) {
            self::$connexio->commit();
            //self::$connexio = null;
        }
    }

    public function consulta($sql, $fetch = FETCH_VOID) {
        try {
            $stmt = self::$connexio->query($sql);

            if ($fetch == FETCH_OBJECT) {
                return $stmt->fetchObject();
            } elseif ($fetch == FETCH_LIST) {
                return $stmt->fetchAll(PDO::FETCH_CLASS, "stdClass");
            } elseif ($fetch == FETCH_COUNT) {
                return $stmt->fetchColumn(); // Num rows
            } elseif ($fetch == FETCH_LASTID) {
                return self::$connexio->lastInsertId();
            } else {
                return;
            }
        } catch (Exception $e) {
            $errortxt = '';
            switch ($e->errorInfo[1]) {
                case 1451:
                    $errortxt = "No es pot eliminar o actualitzar aquest element perquè té altres elements associats.";
                    break;
                default:
                        $errortxt = "Number of error: " . $e->getMessage() . " Error: " . $sql;
                        
                    break;
            }
            echo $errortxt;
            if (self::$connexio->inTransaction()) {
                self::$connexio->rollback();
            }
            exit();
        }
    }

    public function __sleep() {
        return array('server', 'username', 'password', 'db');
    }

    public function __wakeup() {
        ConnexioInterceptor::connect();
    }

}

?>