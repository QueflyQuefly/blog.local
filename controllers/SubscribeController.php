<?php

class SubscribeController {
    public $error;
    private $subscribeService;
    public function __construct(SubscribeService $subscribeService) {
        $this->subscribeService = $subscribeService;
    }
    public function subscribeUser($userIdWantSubscribe, $userId) {
        if ($this->isSubscribedUser($userIdWantSubscribe, $userId)) {
            return $this->subscribeService->toUnsubscribeUser($userIdWantSubscribe, $userId);
        }
        return $this->subscribeService->toSubscribeUser($userIdWantSubscribe, $userId);
    }
    public function isSubscribedUser($userIdWantSubscribe, $userId){
        return $this->subscribeService->isSubscribedUser($userIdWantSubscribe, $userId);
    }
}