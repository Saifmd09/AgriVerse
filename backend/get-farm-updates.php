<?php
session_start();
header("Content-Type: application/json");
require "db.php";

$farm_id = $_GET["farm_id"] ?? 0;

$sql = $conn->prepare("
    SELECT update_text, update_image, created_at
    FROM farm_updates
    WHERE farm_id=?
    ORDER BY created_at DESC
");
$sql->bind_param("i", $farm_id);
$sql->execute();
$res = $sql->get_result();

$updates = [];
while ($row = $res->fetch_assoc()) {
    $updates[] = $row;
}

echo json_encode(["status"=>"OK","updates"=>$updates]);
?>
