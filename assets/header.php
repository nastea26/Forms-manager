<?php
function createHeader($pathToRoot)
{
    echo "<script src='../js/headerAction.js' defer></script>
<header>
    <div class='header-container'>
        <a href='" . $pathToRoot . "index.php' class='logo'><i class='fas fa-clipboard-list'></i>ChomForm</a>
        <button class='hamburger-menu'>
            <span class='bar'></span>
            <span class='bar'></span>
            <span class='bar'></span>
        </button>
        <nav class='menu'>
            <ul>
                <li><a href='" . $pathToRoot . "index.php'><i class='fas fa-home'></i> Home</a></li>
                <li><a href='" . $pathToRoot . "create_form.php'><i class='fas fa-plus-square'></i> Create Form</a></li>
                <li><a href='" . $pathToRoot . "php/dashboard.php'><i class='fas fa-file-alt'></i> My Forms</a></li>
                <li><a href='" . $pathToRoot . "php/logout.php' class='logout'><i class='fas fa-sign-out-alt'></i> Logout</a></li>
            </ul>
        </nav>
    </div>
</header>";
}
// <li><a href='" . $pathToRoot . "php/list_forms.php'><i class='fas fa-list-alt'></i> Active Forms</a></li>