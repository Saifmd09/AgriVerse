<?php
include "../db.php";
session_start();

if(!isset($_SESSION['farmer_id'])){
    echo json_encode(["status" => "ERR", "message" => "Unauthorized"]);
    exit;
}

$farmer_id = $_SESSION['farmer_id'];

$crop_type = $_POST['crop_type'];
$crop_start_date = $_POST['crop_start_date'];
$crop_duration_days = $_POST['crop_duration_days'];
$land_area_acres = $_POST['land_area_acres'];
$notes = $_POST['notes'];

// default: upcoming or active
$crop_status = "upcoming";

// Insert query
$stmt = $conn->prepare("
    INSERT INTO crop_cycle 
(farmer_id, crop_type, crop_start_date, crop_duration_days, land_area_acres, notes)
VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issids",
    $farmer_id,
    $crop_type,
    $crop_start_date,
    $crop_duration_days,
    $land_area_acres,
    $notes
);

if($stmt->execute()){
    echo json_encode(["status" => "OK", "message" => "Crop cycle saved"]);
} else {
    echo json_encode(["status" => "ERR", "message" => $stmt->error]);
}
?>
