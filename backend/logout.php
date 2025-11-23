<?php
session_start();
session_unset();
session_destroy();

$reason = $_GET['reason'] ?? '';
header("Location: ../login-page.html?reason=$reason");
exit;
