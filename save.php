<?php

if (!isset($_POST["answer_string"])) {
  die('error');
}

$filename = "computer.txt";
if (isset($_POST["is_hand"]) && intval($_POST["is_hand"]) == 1) {
  $filename = "hand.txt";
}
file_put_contents($filename, $_POST["probID"] . "\n");
file_put_contents($filename, $_POST["answer_string"]. PHP_EOL, FILE_APPEND);

?>
