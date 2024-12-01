<?php
session_start();
$root = $_SERVER['DOCUMENT_ROOT'] . '/site-photo/';
require_once $root . 'includes/db_connexion.php';
require_once $root . 'controllers/PageController.php';

$pageController = new PageController($pdo);
$pageController->showEventsPage();