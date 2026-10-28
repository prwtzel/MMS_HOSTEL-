<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#17251f">
  <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><span class="brand-mark">M</span><span>MMS <em>HOSTEL</em></span></a>
    <button class="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>
    <nav class="main-nav">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <a href="<?= BASE_URL ?>/rooms.php">Stay with us</a>
      <a href="<?= BASE_URL ?>/index.php#experience">Experience</a>
      <a href="<?= BASE_URL ?>/index.php#contact">Contact</a>
      <a class="nav-cta" href="<?= BASE_URL ?>/rooms.php">Book a room <span>↗</span></a>
    </nav>
  </div>
</header>
<main>
