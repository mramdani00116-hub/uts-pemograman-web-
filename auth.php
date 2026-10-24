<?php
require_once __DIR__ . '/functions.php';
if (empty($_SESSION['user_id'])) redirect('login.php');
