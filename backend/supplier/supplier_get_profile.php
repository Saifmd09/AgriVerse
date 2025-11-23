<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

header("Content-Type: application/json");

require "../db.php";

if (!isset($_SESSION["supplier_id"])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$id = $_SESSION["supplier_id"];

$stmt = $conn->prepare("SELECT * FROM suppliers WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Supplier not found"]);
    exit;
}

$supplier = $result->fetch_assoc();

echo json_encode([
    "status" => "success",
    "supplier" => $supplier
]);
