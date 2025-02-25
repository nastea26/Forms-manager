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
    <link rel="stylesheet" href="styles/test.css">
    <link rel="stylesheet" href="styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Forms</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module" src="./js/shareModal.js" defer></script>
    <script>
        $(document).ready(function() {
            let allForms = []; // Store all forms in memory

            // Function to display filtered and sorted forms
            function displayForms(forms) {
                if (forms.length > 0) {
                    let formsHTML = '';
                    forms.forEach(function(form) {
                        formsHTML += `
                <li class="recent-form-item">
                    <a href="php/view_form.php?q=${form.link}" class="recent-form-link">
                        <div class="recent-form-title">${form.title}</div>
                        <div class="recent-form-description">${form.description}</div>
                    </a>
                </li>`;
                    });
                    $('.recent-forms-list').html(formsHTML);
                } else {
                    $('.recent-forms-list').html('<p>No forms found.</p>');
                }
            }

            function displayTemplates(templates) {
                if (templates.length > 0) {
                    let templatesHTML = '';
                    const templatesContainerElement = document.querySelector('.templates-list');
                    templatesContainerElement.textContent = '';
                    templates.forEach(function(template) {
                        templateInnerText = template.title;
                        linkElement = document.createElement('a');
                        link = 'php/edit_form.php?template=true&q=' + template.link;
                        linkElement.href = link;
                        linkElement.innerText = templateInnerText;
                        divElement = document.createElement('div');
                        divElement.setAttribute('class', 'template-item');
                        divElement.appendChild(linkElement);
                        templatesContainerElement.appendChild(divElement);
                    });
                    $('.template-list').html(templatesHTML);
                } else {
                    $('.template-list').html('<p>No templates found.</p>');
                }
            }

            // Function to filter forms based on search query
            function filterForms(query) {
                return allForms.filter(form =>
                    form.title.toLowerCase().includes(query.toLowerCase()) ||
                    form.description.toLowerCase().includes(query.toLowerCase())
                );
            }

            // Function to sort forms based on selected criteria
            function sortForms(forms, criteria) {
                if (criteria === 'date-desc') {
                    return forms.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                } else if (criteria === 'date-asc') {
                    return forms.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                } else if (criteria === 'title-asc') {
                    return forms.sort((a, b) => a.title.localeCompare(b.title));
                } else if (criteria === 'title-desc') {
                    return forms.sort((a, b) => b.title.localeCompare(a.title));
                }
                return forms;
            }

            // Function to update the displayed forms based on search and order
            function updateFormsDisplay() {
                const query = $('#form-search').val();
                const orderBy = $('#order-by').val();

                let filteredForms = filterForms(query);
                let sortedForms = sortForms(filteredForms, orderBy);

                displayForms(sortedForms);
            }

            // Clear search input field on clear button click
            $('#clear-btn').on('click', function() {
                $('#form-search').val(''); // Clear input field
                updateFormsDisplay(); // Reset displayed forms
            });

            // Trigger search and sort on input or dropdown change
            $('#form-search').on('input', updateFormsDisplay);
            $('#order-by').on('change', updateFormsDisplay);

            // Load all forms initially
            function loadForms() {
                $('#loading-spinner').show(); // Show the loading spinner
                $.ajax({
                    url: 'php/userFormsAPI.php?action=fetchRecentForms',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#loading-spinner').hide(); // Hide the loading spinner
                        if (data.error) {
                            $('.recent-forms-section').html('<p>' + data.error + '</p>');
                        } else {
                            allForms = data.forms; // Save all forms in memory
                            templates = data.templates; // Save all templates in memory
                            displayTemplates(templates)
                            updateFormsDisplay(); // Display sorted and filtered forms
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
        });
    </script>

</head>
<?php include 'assets/header.php'; ?>

<body>
    <main>
        <!-- Display modal if the user created a form -->
        <?php if ($_SESSION['sharePopup']): ?>
            <script type="module">
                import Modal from './js/shareModal.js';

                // Initialize the modal
                const modal = new Modal();
                modal.initModal();

                // Example: Open the modal with a link
                document.addEventListener('DOMContentLoaded', () => {
                    modal.showModal(<?php echo json_encode($_SESSION['shareLink']); ?>);
                });
                <?php
                $_SESSION['sharePopup'] = false;
                $_SESSION['shareLink'] = '';
                ?>
            </script>
        <?php endif ?>
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
                </div>
            </div>
        </section>
        <section class="recent-forms-section">
            <h2>Recent Forms</h2>

            <!-- Search bar -->
            <div class="forms-query-section">
                <div class="order-by-container">
                    <select id="order-by" class="order-by-dropdown">
                        <option value="date-desc" selected>Date (Newest First)</option>
                        <option value="date-asc">Date (Oldest First)</option>
                        <option value="title-asc">Title (A-Z)</option>
                        <option value="title-desc">Title (Z-A)</option>
                    </select>
                </div>
                <div class="search-container">
                    <input type="text" id="form-search" placeholder="Search your forms..." />
                    <button id="clear-btn" aria-label="Clear Search">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </div>
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