<?php
require "../db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["farmer_id"])) {
    echo json_encode(["status" => "ERR", "message" => "Not logged in"]);
    exit;
}

$farmer_id = $_SESSION["farmer_id"];


$res = $conn->query("SELECT id, amount_tokens, purpose, description, status, created_at 
                     FROM funding_requests 
                     WHERE farmer_id = $farmer_id 
                     ORDER BY created_at DESC");

$rows = [];

while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

echo json_encode(["status" => "OK", "data" => $rows]);
