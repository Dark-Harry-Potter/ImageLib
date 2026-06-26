<?php
function checkRateLimit($conn, $ip, $action, $max_attempts = 10, $time_window = 300) {
    $stmt = $conn->prepare("SELECT attempts, last_attempt FROM rate_limits WHERE ip_address = ? AND action = ?");
    $stmt->bind_param("ss", $ip, $action);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $last_attempt = strtotime($row['last_attempt']);
        $time_diff = time() - $last_attempt;
        if ($time_diff < $time_window && $row['attempts'] >= $max_attempts) return false;
        if ($time_diff >= $time_window) {
            $stmt = $conn->prepare("UPDATE rate_limits SET attempts = 1, last_attempt = NOW() WHERE ip_address = ? AND action = ?");
            $stmt->bind_param("ss", $ip, $action);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        $stmt = $conn->prepare("UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() WHERE ip_address = ? AND action = ?");
        $stmt->bind_param("ss", $ip, $action);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    $stmt = $conn->prepare("INSERT INTO rate_limits (ip_address, action, attempts, last_attempt) VALUES (?, ?, 1, NOW())");
    $stmt->bind_param("ss", $ip, $action);
    $stmt->execute();
    $stmt->close();
    return true;
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>