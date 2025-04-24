<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST запит отримано!";
    var_dump($_POST);
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo "Метод не дозволено.";
}
?>
