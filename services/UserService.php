<?php

class UserService {
    public $error;
    private $_dbService, $_db;
    public function __construct() {
        $this->_dbService = DbService::getInstance();
        $this->_db = $this->_dbService->getConnectionToDb();
    }
    public function __destruct() {
        if (!empty($this->error)) {
            throw new Exception($this->error);
        }
    }
    public function addUser($email, $fio, $password, $addSuperuser) {
        try {
            if (!$this->isEmailUnique($email)) {
                return false;
            }
            $rights = $this->_db->quote(RIGHTS_USER);
            if ($addSuperuser) {
                $rights = $this->_db->quote(RIGHTS_SUPERUSER);
            }
            $email = $this->_db->quote($email);
            $fio = $this->_db->quote($fio);
            $date = time();
            $password = $this->_db->quote($password);
            $sql = "INSERT INTO users (email, fio, pass_word, date_time, rights) 
                    VALUES ($email, $fio, $password, $date, $rights);";
            if (!$this->_db->exec($sql)) {
                throw new Exception("Запрос sql = $sql не был выполнен");
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function isEmailUnique($email) {
        try {
            $email = $this->_db->quote($email);
            $sql = "SELECT user_id FROM users WHERE email = $email;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($result['user_id'])){
                    return false; //если есть совпадения, то логин не является уникальным
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function isUser($email, $password) {
        try {
            $email = $this->_db->quote($email);
            $sql = "SELECT user_id, pass_word FROM users 
                    WHERE email = $email;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($result) && password_verify($password, $result['pass_word'])) {
                    return true;
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
    public function getUserIdByEmail($email) {
        $id = null;
        try {
            $email = $this->_db->quote($email);
    
            $sql = "SELECT user_id FROM users WHERE email = $email;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $id = $result['user_id'] ?? null;
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $id;
    }
    public function getUsersByNumber($numberOfUsers , $lessThanMaxId = 0) {
        $users = [];
        try {
            $numberOfUsers  = clearInt($numberOfUsers );
            $lessThanMaxId = clearInt($lessThanMaxId);
            $sql = "SELECT user_id, email, fio, pass_word, date_time, rights 
                    FROM users ORDER BY user_id 
                    DESC LIMIT $lessThanMaxId, $numberOfUsers;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $users[] = $row;
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return $users;
    }
    public function updateUser($userId, $email, $fio, $password) {
        try {
            $userId = clearInt($userId);
            if ($email !== $this->getUserInfoById($userId, 'email')) {
                if (!$this->isEmailUnique($email)) {
                    return false;
                } else {
                    $email = $this->_db->quote($email);
                    $sql = "UPDATE users SET email = $email
                            WHERE user_id = $userId;";
                    if (!$this->_db->exec($sql)) {
                        throw new Exception("Запрос sql = $sql не был выполнен");
                        return false;
                    }
                }
            }
            if ($fio !== $this->getUserInfoById($userId, 'fio')) {
                $fio = $this->_db->quote($fio);
                $sql = "UPDATE users SET fio = $fio 
                        WHERE user_id = $userId;";
                if (!$this->_db->exec($sql)) {
                    throw new Exception("Запрос sql = $sql не был выполнен");
                    return false;
                }
            }
            if ($password !== false) {
                $password = $this->_db->quote($password);
                $sql = "UPDATE users SET pass_word = $password 
                        WHERE user_id = $userId;";
                if (!$this->_db->exec($sql)) {
                    throw new Exception("Запрос sql = $sql не был выполнен");
                    return false;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function getUserInfoById($userId, $whatNeeded = '') {
        $userId = clearInt($userId);
        $result = null;
        try {
            switch ($whatNeeded) {
                case 'email': $query = 'email'; break;
                case 'fio': $query = 'fio'; break;
                case 'rights': $query = 'rights'; break;
                case 'date_time': $query = 'date_time'; break;
                default:  $query = 'user_id, email, fio, date_time, rights';
            }
            $sql = "SELECT $query FROM users WHERE user_id = $userId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($whatNeeded) && !empty($result)) {
                    $result = $result[$whatNeeded];
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $result;
    }
    public function searchUsersByFioAndEmail($searchword, $isSuperuser = false) {
        $results = [];
        try {
            $searchword = clearStr($searchword);
            $searchword = '%' . $searchword . '%';
            $searchword = $this->_db->quote($searchword);
            $sql = "SELECT user_id, fio, email, date_time, rights 
                    FROM users WHERE fio LIKE $searchword;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['user_id']] = $result;
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
            if ($isSuperuser) {
                $sql = "SELECT user_id, fio, email, date_time, rights 
                        FROM users WHERE email LIKE $searchword;";
                $stmt = $this->_db->query($sql);
                if ($stmt != false) {
                    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $results[$result['user_id']] = $result;
                    }
                } else {
                    throw new Exception("Запрос sql = $sql не был выполнен");
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $results;
    }
    public function deleteUserById($userId) {
        $userId = clearstr($userId);
        try {
            $sql = "DELETE FROM users WHERE user_id = $userId;";
            if (!$this->_db->exec($sql)) {
                throw new Exception("Запрос sql = $sql не был выполнен");
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
}
