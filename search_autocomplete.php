<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$limit = 10;

if (strlen($term) < 2) {
    echo json_encode([]);
    exit();
}

$search_term = '%' . $term . '%';
$stmt = $conn->prepare("SELECT DISTINCT hashtags FROM image_library WHERE hashtags LIKE ? LIMIT ?");
$stmt->bind_param("si", $search_term, $limit);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $tags = explode(' ', $row['hashtags']);
    foreach ($tags as $tag) {
        if (stripos($tag, $term) !== false && !in_array($tag, $suggestions)) {
            $suggestions[] = $tag;
        }
    }
}

$suggestions = array_slice($suggestions, 0, $limit);
echo json_encode($suggestions);
exit;
?>