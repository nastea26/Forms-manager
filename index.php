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
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Forms</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module" src="./js/shareModal.js" defer></script>
    <script>
        // Global carousel variables and function
        let currentIndex = 0;
        const slidesToShow = 3; //expected slides to be displayed at once, the rest will be scrolabble 

        function updateArrows() {
            const slides = document.querySelectorAll('.carousel-slide');
            if (!slides.length) return;
            const totalSlides = slides.length;
            const prevArrow = document.querySelector('.carousel-arrow--prev');
            const nextArrow = document.querySelector('.carousel-arrow--next');

            // Disable previous arrow if at the beginning
            if (currentIndex === 0) {
                prevArrow.classList.add('disabled');
            } else {
                prevArrow.classList.remove('disabled');
            }
            // Disable next arrow if at the end
            if (currentIndex >= totalSlides - slidesToShow) {
                nextArrow.classList.add('disabled');
            } else {
                nextArrow.classList.remove('disabled');
            }
        }

        function moveCarousel(direction) {
            const track = document.querySelector('.carousel-track');
            const slides = document.querySelectorAll('.carousel-slide');
            if (!slides.length) return;
            const totalSlides = slides.length;
            const slideWidth = slides[0].getBoundingClientRect().width;

            currentIndex += direction;
            if (currentIndex < 0) {
                currentIndex = 0;
            } else if (currentIndex > totalSlides - slidesToShow) {
                currentIndex = totalSlides - slidesToShow;
            }
            track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
            updateArrows();
        }



        $(document).ready(function() {
            let allForms = []; // Store all forms in memory

            // Display forms remains unchanged...
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

            // Updated displayTemplates function:
            function displayTemplates(templates) {
                if (templates.length > 0) {
                    // Sort templates descending by created_at (if available)
                    templates.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    const track = document.querySelector('.carousel-track');
                    track.textContent = ''; // Clear previous slides
                    templates.forEach(function(template) {
                        const slide = document.createElement('div');
                        slide.classList.add('carousel-slide');
                        const templateItem = document.createElement('div');
                        templateItem.classList.add('template-item');
                        const linkElement = document.createElement('a');
                        linkElement.href = 'php/edit_form.php?template=true&q=' + template.link;
                        linkElement.innerText = template.title;
                        templateItem.appendChild(linkElement);
                        slide.appendChild(templateItem);
                        track.appendChild(slide);
                    });
                    currentIndex = 0; // Reset index when new templates are loaded
                    updateArrows();
                } else {
                    document.querySelector('.carousel-track').innerHTML = '<p>No templates found.</p>';
                }
            }

            // Filtering, sorting, and displaying forms remain unchanged...
            function filterForms(query) {
                return allForms.filter(form =>
                    form.title.toLowerCase().includes(query.toLowerCase()) ||
                    form.description.toLowerCase().includes(query.toLowerCase())
                );
            }

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

            function updateFormsDisplay() {
                const query = $('#form-search').val();
                const orderBy = $('#order-by').val();
                let filteredForms = filterForms(query);
                let sortedForms = sortForms(filteredForms, orderBy);
                displayForms(sortedForms);
            }

            $('#clear-btn').on('click', function() {
                $('#form-search').val('');
                updateFormsDisplay();
            });

            $('#form-search').on('input', updateFormsDisplay);
            $('#order-by').on('change', updateFormsDisplay);

            function loadForms() {
                $('#loading-spinner').show();
                $.ajax({
                    url: 'php/userFormsAPI.php?action=fetchRecentForms',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#loading-spinner').hide();
                        if (data.error) {
                            $('.recent-forms-section').html('<p>' + data.error + '</p>');
                        } else {
                            allForms = data.forms;
                            const templates = data.templates;
                            displayTemplates(templates);
                            updateFormsDisplay();
                        }
                    },
                    error: function() {
                        $('#loading-spinner').hide();
                        alert('Failed to load forms.');
                    }
                });
            }
            loadForms();
        });
    </script>
</head>
<?php
include 'assets/header.php';
createHeader("/");
?>

<body>
    <main>
        <?php if ($_SESSION['sharePopup']): ?>
            <script type="module">
                import Modal from './js/shareModal.js';
                const modal = new Modal();
                modal.initModal();
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
                <!-- Carousel container for templates -->
                <div class="carousel-container">
                    <button class="carousel-arrow carousel-arrow--prev" onclick="moveCarousel(-1)">&#8249;</button>
                    <div class="carousel-track templates-list">
                        <!-- Carousel slides will be populated dynamically -->
                    </div>
                    <button class="carousel-arrow carousel-arrow--next" onclick="moveCarousel(1)">&#8250;</button>
                </div>
            </div>
        </section>
        <section class="recent-forms-section">
            <h2>Recent Forms</h2>
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
            <div id="loading-spinner" style="display: none;">
                <i class="fa fa-spinner fa-spin"></i> Loading...
            </div>
            <ul class="recent-forms-list">
                <!-- Recent forms loaded via AJAX -->
            </ul>
        </section>
    </main>
</body>

</html>