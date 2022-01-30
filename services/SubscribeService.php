<?php

class SubscribeService {
    public $error;
    private $_db;
    public function __construct() {
        $this->_db = DbService::getInstance();
    }
    public function toSubscribeUser($userIdWantSubscribe, $userId) {
        try {
            $userIdWantSubscribe = clearInt($userIdWantSubscribe);
            $userIdWantSubscribe = $this->_db->quote($userIdWantSubscribe);
            
            $sql = "INSERT INTO subscriptions (user_id_want_subscribe, user_id) 
                    VALUES($userIdWantSubscribe, $userId);";
            if (!$this->_db->exec($sql)) {
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
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
}