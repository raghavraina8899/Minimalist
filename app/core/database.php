<?php

class Database {
    public function db_connect() {
        try {
            // Adjusted from your original code
            $string = DB_TYPE . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";";
            return $db = new PDO($string, DB_USER, DB_PASS);
            
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public function createDatabaseIfNotExists() {
        try {
            // Adjusted to create database if it doesn't exist
            $string = "mysql:host=" . DB_HOST;
            $pdo = new PDO($string, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
            $pdo->exec($sql);

        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public function createTableIfNotExists() {
        try {
            // Adjusted to create table if it doesn't exist
            $db = $this->db_connect();
            $sql = "
                CREATE TABLE IF NOT EXISTS users_ (
   							id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    						url_address VARCHAR(60) NOT NULL,
    						username VARCHAR(50) UNIQUE,
    						password VARCHAR(255) NOT NULL,
    						email VARCHAR(100) UNIQUE,
    						date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    						phone VARCHAR(15)
							)";
            $db->exec($sql);

        } catch(PDOException $e) {
            die($e->getMessage());
        }

        try {
            // Adjusted to create table if it doesn't exist
            $db = $this->db_connect();
            $sql = "CREATE TABLE IF NOT EXISTS images_ (
						id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
						url_address VARCHAR(60) NOT NULL,
						images VARCHAR(500) UNIQUE,
						date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
						description VARCHAR(1024) UNIQUE,
						title VARCHAR(100)
					)";
            $db->exec($sql);

        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public function read($query, $data = []) {
        try {
            $db = $this->db_connect();
            $stm = $db->prepare($query);

            if (count($data) == 0) {
                $stm = $db->query($query);
                $check = 0;
                if ($stm) {
                    $check = 1;
                }
            } else {
                $check = $stm->execute($data);
            }

            if ($check) {
                $data = $stm->fetchAll(PDO::FETCH_OBJ);
                if (is_array($data) && count($data) > 0) {
                    return $data;
                }
                return false;
            } else {
                return false;
            }

        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public function write($query, $data = []) {
        try {
            $db = $this->db_connect();
            $stm = $db->prepare($query);

            if (count($data) == 0) {
                $stm = $db->query($query);
                $check = 0;
                if ($stm) {
                    $check = 1;
                }
            } else {
                $check = $stm->execute($data);
            }

            if ($check) {
                return true;
            } else {
                return false;
            }

        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }
}

?>
