<?php
$host = "127.0.0.1"; //coloque seu host
$db   = "";//coloque o nome do seu banco de dados
$user = "";// seu usuario
$password = "";// sua senha

try {
  $conn = new PDO("mysql:host=".$host.";dbname=".$db, $user, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
}
