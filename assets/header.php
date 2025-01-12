<?php
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<head>
    <link rel="stylesheet" href="../styles/header.css">
</head>

<header>
    <div class="header-container">
        <a href="/" class="logo">Form Builder</a>
        <button class="hamburger-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <nav class="menu">
            <ul>
                <li><a href="/">Home</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="../create_form.php">Create Form</a></li>
                    <li><a href="../php/list_forms.php">Active Forms</a></li>
                    <li><a href="../php/dashboard.php">My Forms</a></li>
                    <li><a href="../php/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="../login.php">Login</a></li>
                    <li><a href="../register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const menu = document.querySelector('.menu');

        hamburgerMenu.addEventListener('click', function() {
            menu.classList.toggle('menu-open');
        });
    });
</script>