<?php
// Redirect to gallery with category filter
$category = isset($_GET['name']) ? urlencode($_GET['name']) : '';
if (empty($category)) {
    header("Location: gallery.php");
} else {
    header("Location: gallery.php?category=" . $category);
}
exit();
?>