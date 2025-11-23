<?php
require "../db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["farmer_id"])) {
    echo json_encode(["status" => "ERR", "message" => "Not logged in"]);
    exit;
}

$farmer_id = $_SESSION["farmer_id"];


// TOTAL REQUESTED
$q1 = $conn->query("SELECT SUM(amount_tokens) AS total FROM funding_requests WHERE farmer_id=$farmer_id");
$total_requested = $q1->fetch_assoc()["total"] ?? 0;

// AVAILABLE FUNDS
$q2 = $conn->query("SELECT balance_tokens FROM farmer_wallet WHERE farmer_id=$farmer_id");
$balance = $q2->fetch_assoc()["balance_tokens"] ?? 0;

// Additional Needed = requested - balance
$needed = max(0, $total_requested - $balance);

echo json_encode([
    "status" => "OK",
    "data" => [
        "total_requested"    => (int)$total_requested,
        "available_funds"    => (int)$balance,
        "additional_needed"  => (int)$needed
    ]
]);
