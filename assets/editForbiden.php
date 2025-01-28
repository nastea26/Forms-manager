<!--FOR WHEN A FORM IS ACTIVE AND USER IS TRYING TO EDIT IT-->
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Edit Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }

        .btn {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        .btn-yes {
            background-color: #28a745;
            color: white;
        }

        .btn-no {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body>

    <?php
    include '../assets/header.php';
    function formHasAnswers(): void
    {
        echo "
    <main>
        <h1 style='font-size:35px;text-align:center;'>This form can't be edited as it already has been answered!</h1>
    </main>
</body>

</html>";
    }

    function formIsActive($db, $link): void
    {

        // Display the message and buttons
        echo "
        <main>
            <h1 style='font-size:35px;text-align:center;'>This form can't be edited as it is currently active!</h1>
            <p>Do you wish to set this form as inactive?</p>
            <form method='POST'>
                <button type='submit' name='action' value='yes' class='btn btn-yes'>Yes</button>
                <button type='submit' name='action' value='no' class='btn btn-no'>No</button>
            </form>
        </main>
    </body>
    </html>
    ";

        //Handle the form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];

            if ($action === 'yes') {
                // Set the form to inactive
                $updateQuery = "UPDATE forms SET is_active = 0 WHERE link = ?";
                $db->searchQuery($updateQuery, [$link]);
                header('Location:edit_form.php?q=' . $link);
                exit();
            } elseif ($action === 'no') {
                // Redirect to dashboard
                header('Location:dashboard.php');
                exit();
            }
        }
    }
