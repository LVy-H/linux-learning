<?php
require_once '../src/auth.php';
session_start();
logout();
header("Location: login.php");
exit();