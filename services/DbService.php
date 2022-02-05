<?php

class DbService {
    private static $_instance;
    private $_db;
    public $error;
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    public function getConnectionToDb() {
        return $this->_db;
    }
    private function __construct() {
        $pathToDbconfig = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'dbconfig.php';
        require_once $pathToDbconfig;
        try {
            $this->_db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        } catch (PDOException $e) {
            try {
                $this->_db = new PDO("mysql:host=$host", $username, $password);

                $password = password_hash('1', PASSWORD_BCRYPT);
                $password = $this->_db->quote($password);
                
                $date = time();
                $rights = $this->_db->quote(RIGHTS_SUPERUSER);
                $this->_db->beginTransaction();

                $sql = "CREATE DATABASE $dbname;

                        USE $dbname;

                        CREATE TABLE users
                        (
                        user_id INT UNSIGNED AUTO_INCREMENT,
                        email VARCHAR(50) UNIQUE,
                        fio VARCHAR(50),
                        pass_word CHAR(60),
                        date_time INT UNSIGNED,
                        rights VARCHAR(20),
                        PRIMARY KEY (user_id)
                        );

                        CREATE TABLE posts
                        (
                        post_id INT UNSIGNED AUTO_INCREMENT,
                        title TINYTEXT,
                        user_id INT UNSIGNED,
                        date_time INT UNSIGNED,
                        content TEXT,
                        PRIMARY KEY (post_id),
                        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
                        );

                        CREATE TABLE comments
                        (
                        comment_id INT UNSIGNED AUTO_INCREMENT,
                        post_id INT UNSIGNED,
                        user_id INT UNSIGNED,
                        date_time INT UNSIGNED,
                        content TEXT,
                        rating INT UNSIGNED,
                        PRIMARY KEY (comment_id),
                        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
                        FOREIGN KEY (post_id) REFERENCES posts (post_id) ON DELETE CASCADE
                        );

                        CREATE TABLE rating_posts
                        (
                        post_id INT UNSIGNED,
                        user_id INT UNSIGNED,
                        rating DECIMAL(1),
                        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
                        FOREIGN KEY (post_id) REFERENCES posts (post_id) ON DELETE CASCADE
                        );

                        CREATE TABLE additional_info_posts
                        (
                        post_id INT UNSIGNED,
                        rating DECIMAL(2,1),
                        count_comments INT UNSIGNED,
                        count_ratings INT UNSIGNED,
                        PRIMARY KEY (post_id),
                        FOREIGN KEY (post_id) REFERENCES posts (post_id) ON DELETE CASCADE
                        );

                        CREATE TABLE rating_comments
                        (
                        comment_id INT UNSIGNED,
                        user_id INT UNSIGNED,
                        FOREIGN KEY (comment_id) REFERENCES comments (comment_id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
                        );

                        CREATE TABLE tag_posts
                        (
                        post_id INT UNSIGNED,
                        tag TINYTEXT,
                        FOREIGN KEY (post_id) REFERENCES posts (post_id) ON DELETE CASCADE
                        );

                        CREATE TABLE subscriptions
                        (
                        user_id_want_subscribe INT UNSIGNED,
                        user_id INT UNSIGNED,
                        FOREIGN KEY (user_id_want_subscribe) REFERENCES users (user_id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
                        );

                        CREATE INDEX fio ON users (fio);
                        CREATE INDEX post_date_time ON posts (date_time, post_id);
                        CREATE INDEX comment_date_time ON comments (date_time, post_id);
                        CREATE INDEX comment_id ON rating_comments (comment_id);
                        CREATE INDEX post_info ON additional_info_posts (post_id, rating, count_comments, count_ratings);
                ";
                $this->_db->exec($sql);

                $sql = "INSERT INTO users
                        (email, fio, pass_word, date_time, rights) 
                        VALUES ('1@1.1', 'Администратор', $password, $date, $rights)
                        ;
                ";
                $this->_db->exec($sql);

                if (!$this->_db->commit()) {
                    echo $this->error = "Возникли ошибки при создании базы данных";
                }
            } catch(PDOException $e) {
                $this->_db->rollBack();
                echo $this->error = $e->getMessage();
            }
        }
    }
}