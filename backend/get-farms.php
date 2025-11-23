<?php
header("Content-Type: application/json");
require __DIR__ . "/db.php";

if (!isset($conn) || !$conn instanceof mysqli) {
    echo json_encode(["status" => "ERROR", "message" => "DB connection failed"]);
    exit;
}

$sql = "
SELECT 
    cc.cycle_id,
    cc.crop_type,
    cc.crop_duration_days,
    f.name AS farmer_name,
    CONCAT(f.village, ', ', f.district, ', ', f.state) AS location,

    -- Needed amount (latest funding request)
    COALESCE((
        SELECT fr.amount_tokens
        FROM funding_requests fr
        WHERE fr.farmer_id = cc.farmer_id
        ORDER BY fr.id DESC
        LIMIT 1
    ), 0) AS needed_amount,

    -- Farm images (if multiple images are stored as JSON)
    COALESCE((
        SELECT fv.farm_images
        FROM farm_verification fv
        WHERE fv.farmer_id = cc.farmer_id
        ORDER BY fv.id DESC
        LIMIT 1
    ), '') AS land_image

FROM crop_cycle cc
JOIN farmers f ON f.id = cc.farmer_id
WHERE cc.crop_status = 'active'
ORDER BY cc.cycle_id DESC;
";

$res = $conn->query($sql);

$farms = [];
while ($row = $res->fetch_assoc()) {

    // Handle multiple images
    $images = [];
    if (!empty($row['land_image'])) {
        // Assuming the farm_images field contains a JSON string of images
        $images = json_decode($row['land_image'], true);
    }

    $farms[] = [
        "id" => $row["cycle_id"],
        "crop" => $row["crop_type"],
        "duration_days" => $row["crop_duration_days"],
        "farmer_name" => $row["farmer_name"],
        "location" => $row["location"],
        "needed_amount" => $row["needed_amount"],
        "images" => $images, // Multiple images
    ];
}

echo json_encode([
    "status" => "OK",
    "farms" => $farms
]);
