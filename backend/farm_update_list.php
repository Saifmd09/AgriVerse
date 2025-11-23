<?php
require 'db.php';
require 'auth_guard.php';
require 'json.php';

$stmt = $conn->prepare("
    SELECT 
        id,
        update_date,
        day_in_cycle AS day,
        title,
        description,
        image_path
    FROM farm_updates
    WHERE farmer_id = ?
    ORDER BY id DESC
    LIMIT 200
");

$stmt->bind_param("i", $FARMER_ID);
$stmt->execute();
$res = $stmt->get_result();

$updates = [];
while ($row = $res->fetch_assoc()) {

    $updates[] = [
        "id" => $row["id"],
        "date" => date("Y-m-d", strtotime($row["update_date"])),
        "day" => intval($row["day"]),
        "title" => $row["title"],
        "description" => $row["description"],
        "image_url" => $row["image_path"] ? "/" . $row["image_path"] : null
    ];
}

$stmt->close();

json_ok($updates);
