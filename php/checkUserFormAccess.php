<?php

function checkAccessToFrom(object $formHandler, $formId, string $pageTitle): bool
{
    $usersForms = $formHandler->getUserForms($_SESSION['user_id']);
    $usersFormsIdsArray = [];
    foreach ($usersForms as $userFrom) {
        $usersFormsIdsArray[] = $userFrom['id'];
    }

    $res = in_array((int)$formId, $usersFormsIdsArray);
    if (!$res) {
?>
        <!DOCTYPE html>
        <html lang='en'>

        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link rel="stylesheet" href="../styles/header.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <title><?php echo $pageTitle; ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                }
            </style>
        </head>

        <body>
            <?php include '../assets/header.php';
            createHeader("../");
            ?>
            <main>
                <?php
                if ($pageTitle == "Edit Form") {
                    echo "<h1 style='font-size:35px;text-align:center;'>YOU DO NOT HAVE ACCESS TO EDIT FORM!</h1>";
                } else {
                    echo "<h1 style='font-size:35px;text-align:center;'>YOU DO NOT HAVE ACCESS TO VIEW THE RESULTS OF THIS FORM!</h1>";
                }
                ?>

            </main>
        </body>

        </html>
<?php
    }
    return !$res;
}
