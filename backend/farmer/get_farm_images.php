<?php
session_start();
header("Content-Type: application/json");
require_once "../db.php"; // your mysqli connection file

// Check login farmer_id
if (!isset($_SESSION['farmer_id'])) {
    echo json_encode([
        "status" => "ERR",
        "message" => "Farmer not logged in"
    ]);
    exit;
}

$farmerId = $_SESSION['farmer_id'];

try {
    // Fetch last verification record
    $stmt = $conn->prepare("
        SELECT farm_images
        FROM farm_verification
        WHERE farmer_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->bind_param("i", $farmerId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            "status" => "OK",
            "data" => ["images" => []]
        ]);
        exit;
    }

    $row = $result->fetch_assoc();
    $raw = trim($row["farm_images"]);

    $images = [];

    // If JSON array stored
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $images = $decoded;
    } else {
        // If comma-separated
        $images = array_filter(array_map("trim", explode(",", $raw)));
    }

    // Add folder path if required
    $basePath = "../uploads/farm_verification/";

    $processed = [];
    foreach ($images as $img) {
        // If it's already full URL or starts with /
        if (preg_match("/^https?:\/\//", $img) || str_starts_with($img, "/")) {
            $processed[] = $img;
        } else {
            $processed[] = $basePath . $img;
        }
    }

    echo json_encode([
        "status" => "OK",
        "data" => ["images" => $processed]
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "ERR",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
