<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
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
            // Function to load forms
            function loadForms(query = '') {
                $('#loading-spinner').show(); // Show the loading spinner
                $.ajax({
                    url: `php/userFormsAPI.php?action=fetchRecentForms&query=${encodeURIComponent(query)}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#loading-spinner').hide(); // Hide the loading spinner
                        if (data.error) {
                            $('.recent-forms-section').html('<p>' + data.error + '</p>');
                        } else if (data.forms.length > 0) {
                            let formsHTML = '';
                            data.forms.forEach(function(form) {
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
                            $('.recent-forms-list').html('<p>No forms found.</p>');
                        }
                    },
                    error: function() {
                        $('#loading-spinner').hide();
                        alert('Failed to load forms.');
                    }
                });
            }

            // Initial load of forms
            loadForms();

            // Search functionality
            $('#search-btn').click(function() {
                const query = $('#form-search').val();
                if (query.trim() !== '') {
                    loadForms(query);
                } else {
                    alert('Please enter a search query.');
                }
            });
        });
    </script>
</head>
<?php include 'assets/header.php'; ?>

<body>
    <main>
        <section class="top-section">
            <div class="create-form-container">
                <a href="./create_form.php">
                    <button class="create-form-btn">+ Blank Form</button>
                </a>
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
        <section class="recent-forms-section">
            <h2>Recent Forms</h2>

            <!-- Search bar -->
            <div class="search-container">
                <input type="text" id="form-search" placeholder="Search your forms..." />
                <button id="search-btn">Search</button>
            </div>

            <!-- Loading spinner -->
            <div id="loading-spinner" style="display: none;">
                <i class="fa fa-spinner fa-spin"></i> Loading...
            </div>

            <!-- Forms list -->
            <ul class="recent-forms-list">
                <!-- Recent forms will be loaded here via AJAX -->
            </ul>
        </section>
    </main>
</body>

</html>