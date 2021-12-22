<?php

class getConnectionToDb {
    private static $_instance;
    private static $_db;
    public $error;
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance::$_db;
    }
    private function __construct() {
        $pathToDbconfig = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'dbconfig.php';
        require_once $pathToDbconfig;
        try {
            self::$_db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        } catch (PDOException $e) {
            try {
                self::$_db = new PDO("mysql:host=$host", $username, $password);

                $password = password_hash('1', PASSWORD_BCRYPT);
                $password = self::$_db->quote($password);
                
                $date = time();
                $rights = self::$_db->quote(RIGHTS_SUPERUSER);

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
                        post_id INT UNSIGNED,
                        FOREIGN KEY (comment_id) REFERENCES comments (comment_id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
                        FOREIGN KEY (post_id) REFERENCES posts (post_id) ON DELETE CASCADE
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
                        CREATE INDEX post_id ON rating_posts (post_id);
                        INSERT INTO users
                        (email, fio, pass_word, date_time, rights) 
                        VALUES ('1@1.1', 'Администратор', $password, $date, $rights)
                        ;";
                if (!self::$_db->exec($sql)) {
                    echo $sql;
                    echo $this->error = "База данных не создана";
                } else {
                    return self::$_db;
                }
            } catch(PDOException $e) {
                echo $this->error = $e->getMessage();
            }
        }
    }
}
