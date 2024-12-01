<?php
require_once 'EventController.php';

class PageController {
    private $eventController;
    
    public function __construct($pdo) {
        $this->eventController = new EventController($pdo);
    }
    
    public function showEventsPage() {
        $events = $this->eventController->getEvents();
        $message = isset($_SESSION['message_modal']) ? $_SESSION['message_modal'] : '';
        $message_type = isset($_SESSION['message_modal_type']) ? $_SESSION['message_modal_type'] : 'error';
        
        unset($_SESSION['message_modal']);
        unset($_SESSION['message_modal_type']);
        
        $root = $_SERVER['DOCUMENT_ROOT'] . '/site-photo/';
        require_once $root . 'views/events/index.php';
    }
}
