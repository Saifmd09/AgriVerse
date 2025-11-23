<?php
// Call this on farmer-protected endpoints
session_start();
if (!isset($_SESSION['farmer_id'])) {
    http_response_code(401);
    exit('UNAUTHENTICATED');
}
$FARMER_ID = intval($_SESSION['farmer_id']);
