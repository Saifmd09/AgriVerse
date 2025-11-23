<?php
require 'db.php';
require 'json.php';

$category = $_GET['category'] ?? 'all';

if ($category === 'all') {
    $sql = "SELECT id, name, category, description, price_tokens, image_url
            FROM marketplace_products WHERE is_active=1 ORDER BY id DESC LIMIT 200";
    $res = $conn->query($sql);
} else {
    $stmt = $conn->prepare("SELECT id, name, category, description, price_tokens, image_url
                            FROM marketplace_products WHERE is_active=1 AND category=? ORDER BY id DESC LIMIT 200");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $res = $stmt->get_result();
}

$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;

json_ok($data);
