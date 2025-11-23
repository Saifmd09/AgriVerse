<?php
session_start();
require '../db.php';  // path to DB

$user_id = $_SESSION['farmer_id'];

// 1. Fetch wallet summary
$q = $conn->prepare("SELECT token_balance, total_received, total_spent FROM farmer_wallet WHERE farmer_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$wallet = $q->get_result()->fetch_assoc();

if (!$wallet) {
    echo json_encode(["status" => "ERR", "message" => "Wallet not found"]);
    exit;
}

echo json_encode([
    "status" => "OK",
    "balance" => (int)$wallet["token_balance"],
    "total_received" => (int)$wallet["total_received"],
    "total_spent" => (int)$wallet["total_spent"]
]);
