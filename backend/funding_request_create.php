<?php
require 'db.php';
require 'auth_guard.php';
require 'json.php';

$amount = intval($_POST['amount'] ?? 0);
$purpose = $_POST['purpose'] ?? '';
$description = trim($_POST['description'] ?? '');

if ($amount <= 0) json_err('Invalid amount');
if (!in_array($purpose, ['seeds','fertilizer','machinery','irrigation','labor','other'])) json_err('Invalid purpose');

$ins = $conn->prepare("INSERT INTO funding_requests (farmer_id, amount_tokens, purpose, description) VALUES (?,?,?,?)");
$ins->bind_param("iiss", $FARMER_ID, $amount, $purpose, $description);
$ins->execute();
$ins->close();

json_ok('CREATED');
