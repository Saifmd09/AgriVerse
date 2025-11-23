<?php
require 'db.php';
require 'auth_guard.php';
require 'json.php';

$update_date = $_POST['update-date'] ?? '';
$day = intval($_POST['update-day'] ?? 0);
$title = trim($_POST['update-title'] ?? '');
$description = trim($_POST['update-description'] ?? '');

if (!$update_date || !$title || !$description) {
    json_err("All fields are required.");
}

$uploadDir = __DIR__ . '/../uploads/farm_updates/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$image_path = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
        json_err("Invalid image format.");
    }

    $newName = uniqid("upd_") . "." . $ext;
    $target = $uploadDir . $newName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        json_err("Image upload failed.");
    }

    $image_path = "uploads/farm_updates/" . $newName;
}

$stmt = $conn->prepare("
    INSERT INTO farm_updates 
    (farmer_id, update_date, day_in_cycle, title, description, image_path)
    VALUES (?,?,?,?,?,?)
");

$stmt->bind_param("isisss",
    $FARMER_ID,
    $update_date,
    $day,
    $title,
    $description,
    $image_path
);

$stmt->execute();
$stmt->close();

json_ok([
    "message" => "Created successfully!",
    "image_url" => $image_path
]);
