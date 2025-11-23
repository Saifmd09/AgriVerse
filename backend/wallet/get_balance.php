<?php
session_start();
require '../db.php'; // your DB connection

header("Content-Type: application/json");

// Check login session
if (!isset($_SESSION["farmer_id"])) {
    echo json_encode(["status" => "ERR", "message" => "Not logged in"]);
    exit;
}

$farmer_id = $_SESSION["farmer_id"];

// Fetch wallet balance
$query = $conn->prepare("SELECT balance_tokens FROM farmer_wallet WHERE farmer_id = ?");
$query->bind_param("i", $farmer_id);
$query->execute();
$query->bind_result($balance);
$query->fetch();
$query->close();

if ($balance === null) {
    $balance = 0; // default if no wallet created yet
}

echo json_encode([
    "status" => "OK",
    "data" => [
        "balance" => intval($balance)
    ]
]);
?>
