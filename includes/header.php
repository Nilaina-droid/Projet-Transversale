<?php 
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['role'])) {
    header("Location: /GESTION_RU/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant universitaire</title>
    <link rel="stylesheet" href="/GESTION_RU/style.css">
</head>
<body class="app-body">

<?php
if($_SESSION['role'] === 'etudiant') {
    include 'navbar_etudiant.php';
}else{
    include 'navbar_gestionnaire.php';
}
?>

<main class="main-content">
