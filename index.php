<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <title>Forms</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch recent forms if user is logged in
            <?php if (isset($_SESSION['user_id'])): ?>
                $.ajax({
                    url: 'php/userFormsAPI.php?action=fetchRecentForms',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            $('.recent-forms-section').html('<p>' + data.error + '</p>');
                        } else if (data.length > 0) {
                            let formsHTML = '';
                            data.forEach(function(form) {
                                formsHTML += `
                                <li class="recent-form-item">
                                    <a href="php/view_form.php?id=${form.id}" class="recent-form-link">
                                        <div class="recent-form-title">${form.title}</div>
                                        <div class="recent-form-description">${form.description}</div>
                                    </a>
                                </li>`;
                            });
                            $('.recent-forms-list').html(formsHTML);
                        } else {
                            $('.recent-forms-section').html('<p>No recent forms found.</p>');
                        }
                    },
                    error: function() {
                        $('.recent-forms-section').html('<p>Failed to load recent forms.</p>');
                    }
                });
            <?php endif; ?>
        });
    </script>
</head>
<?php include 'assets/header.php'; ?>

<body>
    <main>
        <section class="top-section">
            <div class="create-form-container">
                <button class="create-form-btn">+ Blank Form</button>
            </div>
            <div class="templates-container">
                <h2>Templates</h2>
                <div class="templates-list">
                    <div class="template-item">Template 1</div>
                    <div class="template-item">Template 2</div>
                    <div class="template-item">Template 3</div>
                </div>
            </div>
        </section>

        <?php if (isset($_SESSION['user_id'])): ?>
            <section class="recent-forms-section">
                <h2>Recent Forms</h2>
                <ul class="recent-forms-list">
                    <!-- Recent forms will be loaded here via AJAX -->
                </ul>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>