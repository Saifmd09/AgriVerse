<?php
require 'db.php';
require 'auth_guard.php';
require 'json.php';

$q = $conn->prepare("SELECT id, amount_tokens, purpose, description, status, created_at
                     FROM funding_requests WHERE farmer_id=? ORDER BY id DESC");
$q->bind_param("i", $FARMER_ID);
$q->execute();
$res = $q->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
$q->close();

json_ok($out);
