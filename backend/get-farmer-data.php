<?php
session_start();
require "db.php";

if (!isset($_SESSION["farmer_id"])) {
    echo json_encode(["status" => "NOT_LOGGED_IN"]);
    exit;
}

$id = $_SESSION["farmer_id"];

$stmt = $conn->prepare("
    SELECT name, email, phone, gender, dob, state, district, village, address,
           land_area_acres, farming_type
    FROM farmers
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
    "status" => "OK",
    "data" => $data
]);
?>
