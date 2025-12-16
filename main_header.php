<?php

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']);
$userName = $isLoggedIn ? $_SESSION['user']['name'] : '';
$userRole = $isLoggedIn ? $_SESSION['user']['role'] : '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="shortcut icon" href="assets/image/a.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/mobile.css"> 
    <title>Acctverse</title>
     <style>
    /* Smooth transitions between light/dark */
    html {
      transition: background-color 0.4s ease, color 0.4s ease;
    }
  </style>
</head>
<body class="w-full m-0 bg-white transition-colors duration-500">
