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
            <title><?php echo $pageTitle; ?></title>
        </head>

        <body>
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
        //ONCE I GO AROUND CHANING HOW THE STYLES FOR HEADER ARE LINKED I HOPE I DONT FORGET THIS
        include '../assets/header.php';
    }
    return !$res;
}
