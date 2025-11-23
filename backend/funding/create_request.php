<?php
session_start();
require "../db.php";

header("Content-Type: application/json");

// Only farmers can request funds
if (!isset($_SESSION["farmer_id"])) {
    echo json_encode(["status" => "ERR", "message" => "Not logged in"]);
    exit;
}

$farmer_id = $_SESSION["farmer_id"];

$amount = $_POST["amount"] ?? 0;
$purpose = $_POST["purpose"] ?? "";
$description = $_POST["description"] ?? "";

// Validation
if (!$amount || !$purpose) {
    echo json_encode(["status" => "ERR", "message" => "Missing required fields"]);
    exit;
}

// Insert request
$stmt = $conn->prepare("INSERT INTO funding_requests (farmer_id, amount_tokens, purpose, description, status) 
                        VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("iiss", $farmer_id, $amount, $purpose, $description);
$stmt->execute();

echo json_encode([
    "status" => "OK",
    "message" => "Request submitted",
    "request_id" => $stmt->insert_id
]);

$stmt->close();
$conn->close();
