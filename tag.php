<?php
// Redirect to gallery with tag filter
$tag = isset($_GET['name']) ? urlencode($_GET['name']) : '';
if (empty($tag)) {
    header("Location: gallery.php");
} else {
    header("Location: gallery.php?tag=" . $tag);
}
exit();
?>