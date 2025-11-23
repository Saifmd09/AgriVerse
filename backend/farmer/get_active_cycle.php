<?php
session_start();
include "../db.php";

if (!isset($_SESSION['farmer_id'])) {
    echo json_encode([
        "status" => "ERR",
        "message" => "Not logged in"
    ]);
    exit;
}

$farmer_id = $_SESSION['farmer_id'];

$stmt = $conn->prepare("
    SELECT * FROM crop_cycle 
    WHERE farmer_id = ? AND crop_status = 'active'
    ORDER BY cycle_id DESC 
    LIMIT 1
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "OK",
        "data" => null
    ]);
    exit;
}

$row = $result->fetch_assoc();

// ✅ Correct column name is crop_start_date
$startDate = new DateTime($row["crop_start_date"]);
$now = new DateTime();
$daysPassed = $startDate->diff($now)->days;

// ✅ Duration
$totalDays = intval($row["crop_duration_days"]);

// ✅ Auto-phase logic
if ($daysPassed < 10) {
    $phase = "Planted";
} elseif ($daysPassed < ($totalDays - 7)) {
    $phase = "Growing";
} else {
    $phase = "Harvesting";
}

// ✅ Progress
$progress = ($daysPassed / $totalDays) * 100;
if ($progress > 100) $progress = 100;

echo json_encode([
    "status" => "OK",
    "data" => [
        "crop_type" => $row["crop_type"],
        "days_passed" => $daysPassed,
        "duration" => $totalDays,
        "phase" => $phase,
        "progress" => round($progress)
    ]
]);
?>
