<?php
include "../db.php";
session_start();

$farmer_id = $_SESSION['farmer_id'];

// Prepare query
$stmt = $conn->prepare("SELECT land_area_acres FROM farmers WHERE id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

if($data){
    echo json_encode([
        "status" => "OK",
        "land_area" => $data["land_area_acres"]
    ]);
} else {
    echo json_encode([
        "status" => "ERR",
        "message" => "Farmer not found"
    ]);
}
?>
