<?php
session_start();
require "db.php";

$farmer_id = $_SESSION["farmer_id"];
if(!$farmer_id) exit("❌ Not logged in");

function uploadFile($file, $folder){
    $dir = "../uploads/$folder/";
    if(!file_exists($dir)) mkdir($dir,0777,true);

    $name = time() . "_" . basename($file["name"]);
    $path = $dir . $name;

    move_uploaded_file($file["tmp_name"], $path);
    return $name;
}

$land_doc = uploadFile($_FILES["land_doc"], "land_docs");
$selfie = uploadFile($_FILES["selfie"], "selfies");
$farm_photo = uploadFile($_FILES["farm_photo"], "farm_photos");

$fpo_cert = NULL;
if(!empty($_FILES["fpo_certificate"]["name"])){
    $fpo_cert = uploadFile($_FILES["fpo_certificate"], "fpo_docs");
}

$stmt = $conn->prepare("
INSERT INTO farmer_verification 
(farmer_id, land_doc, selfie, farm_photo, farm_lat, farm_lng,
 fpo_name, fpo_state, membership_id, registration_no, fpo_certificate)
VALUES (?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param("isssddsssss",
    $farmer_id,
    $land_doc,
    $selfie,
    $farm_photo,
    $_POST["farm_lat"],
    $_POST["farm_lng"],
    $_POST["fpo_name"],
    $_POST["fpo_state"],
    $_POST["membership_id"],
    $_POST["registration_no"],
    $fpo_cert
);

if($stmt->execute()){
    echo "✅ Verification Submitted!";
} else {
    echo "❌ Failed!";
}
?>
