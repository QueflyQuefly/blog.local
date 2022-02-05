<?php

class SubscribeController {
    public $error;
    private $_subscribeService;
    public function __construct(SubscribeService $subscribeService) {
        $this->_subscribeService = $subscribeService;
    }
    public function subscribeUser($userIdWantSubscribe, $userId) {
        if ($this->isSubscribedUser($userIdWantSubscribe, $userId)) {
            return $this->_subscribeService->toUnsubscribeUser($userIdWantSubscribe, $userId);
        }
        return $this->_subscribeService->toSubscribeUser($userIdWantSubscribe, $userId);
    }
    public function isSubscribedUser($userIdWantSubscribe, $userId){
        return $this->_subscribeService->isSubscribedUser($userIdWantSubscribe, $userId);
    }
}