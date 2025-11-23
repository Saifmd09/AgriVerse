<?php
session_start();
require "db.php";

$farmer_id = $_SESSION["farmer_id"] ?? null;
if (!$farmer_id) exit("⚠️ Not logged in.");

// File upload folder
$uploadDir = "../uploads/farm_verification/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Helper function
function saveFile($fileKey, $prefix) {
    global $uploadDir;
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]["error"] !== 0) return null;
    $ext = pathinfo($_FILES[$fileKey]["name"], PATHINFO_EXTENSION);
    $filename = $prefix . "_" . time() . "." . $ext;
    move_uploaded_file($_FILES[$fileKey]["tmp_name"], $uploadDir . $filename);
    return $filename;
}

// Save all uploads
$aadhaar_doc = saveFile("aadhaar_doc", "aadhaar");
$selfie_doc  = saveFile("selfie_doc", "selfie");
$land_doc    = saveFile("land_doc", "land");
$farm_images = [];

if (isset($_FILES["farm_photos"])) {
    foreach ($_FILES["farm_photos"]["tmp_name"] as $i => $tmp) {
        if ($_FILES["farm_photos"]["error"][$i] === 0) {
            $ext = pathinfo($_FILES["farm_photos"]["name"][$i], PATHINFO_EXTENSION);
            $fname = "farm_" . time() . "_$i." . $ext;
            move_uploaded_file($tmp, $uploadDir . $fname);
            $farm_images[] = $fname;
        }
    }
}

$latitude = $_POST["latitude"] ?? null;
$longitude = $_POST["longitude"] ?? null;
$farm_images_json = json_encode($farm_images);

$stmt = $conn->prepare("
INSERT INTO farm_verification 
(farmer_id, aadhaar_doc, selfie_doc, land_doc, farm_images, latitude, longitude, status, submitted_at)
VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
");
$stmt->bind_param("issssss", $farmer_id, $aadhaar_doc, $selfie_doc, $land_doc, $farm_images_json, $latitude, $longitude);

if ($stmt->execute()) {
    echo "SUCCESS";
} else {
    echo "❌ Failed: " . $stmt->error;
}
$stmt->close();
?>
