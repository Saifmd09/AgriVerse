<?php
function json_ok($data) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'OK', 'data' => $data]);
    exit;
}
function json_err($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERR', 'message' => $msg]);
    exit;
}
