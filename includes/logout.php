<?php
require_once '../includes/config.php';
session_destroy();
header("Location: " . BASE_URL . "/login.php");
exit;