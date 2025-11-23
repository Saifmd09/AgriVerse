<?php
session_start();
require "db.php";

if (!isset($_SESSION["investor_id"])) {
    echo json_encode(["status" => "NO_LOGIN"]);
    exit;
}

$investor = $_SESSION["investor_id"];

$sql = "
SELECT 
    ft.farmer_id,
    SUM(ft.amount) AS invested,
    (SUM(ft.amount) * 0.12) AS expected_return,
    SUM(ft.amount) AS tokens_allocated
FROM farmer_transactions ft
WHERE ft.investor_id = ?
GROUP BY ft.farmer_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $investor);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "OK",
    "data" => $data
]);
exit;
?>
