<?php
session_start();
require "db.php";

if (!isset($_SESSION["investor_id"])) {
    exit("NOT_LOGGED_IN");
}

$id = $_SESSION["investor_id"];

$citizen_type = $_POST["citizen_type"] ?? "";
if (!$citizen_type) exit("Select citizenship document type.");

$uploadDir = "../uploads/investors/";

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

function saveProof($key, $dir) {
    if (!isset($_FILES[$key])) return "";
    $ext = pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION);
    $newName = uniqid().".".$ext;
    $path = $dir . $newName;

    if (move_uploaded_file($_FILES[$key]["tmp_name"], $path)) {
        return $newName;
    }
    return "";
}

$citizen_proof = saveProof("citizen_proof", $uploadDir);
$bank_proof = saveProof("bank_proof", $uploadDir);

if (!$citizen_proof || !$bank_proof) exit("UPLOAD_FAILED");

// Update
$stmt = $conn->prepare("
UPDATE investor_verification 
SET citizen_type=?, citizen_proof=?, bank_proof=?, status='pending' 
WHERE investor_id=?
");

$stmt->bind_param("sssi", $citizen_type, $citizen_proof, $bank_proof, $id);
$stmt->execute();
$stmt->close();

echo "SUCCESS";
?>
