<?php

class SubscribeService {
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
    public function toSubscribeUser($userIdWantSubscribe, $userId) {
        try {
            $userIdWantSubscribe = clearInt($userIdWantSubscribe);
            $userIdWantSubscribe = $this->_db->quote($userIdWantSubscribe);
            
            $sql = "INSERT INTO subscriptions (user_id_want_subscribe, user_id) 
                    VALUES($userIdWantSubscribe, $userId);";
            if (!$this->_db->exec($sql)) {
                throw new Exception("Запрос sql = $sql не был выполнен");
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function toUnsubscribeUser($userIdWantSubscribe, $userId) {
        try {
            $userIdWantSubscribe = clearInt($userIdWantSubscribe);
            $userIdWantSubscribe = $this->_db->quote($userIdWantSubscribe);
            
    
            $sql = "DELETE FROM subscriptions 
                    WHERE user_id_want_subscribe = $userIdWantSubscribe 
                    AND user_id = $userId;";
            if (!$this->_db->exec($sql)) {
                throw new Exception("Запрос sql = $sql не был выполнен");
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function isSubscribedUser($userIdWantSubscribe, $userId){
        try {
            $userIdWantSubscribe = clearInt($userIdWantSubscribe);
            $userIdWantSubscribe = $this->_db->quote($userIdWantSubscribe);
            
            $sql = "SELECT user_id FROM subscriptions 
                    WHERE user_id_want_subscribe = $userIdWantSubscribe 
                    AND user_id = $userId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
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
}