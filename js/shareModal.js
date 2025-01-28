class Modal {
    constructor() {
        this.modal = null;
        this.overlay = null;
    }

    // Initialize the modal
    initModal() {
        // Create modal HTML structure
        const modalHTML = `
            <div id="modalOverlay" class="modal-overlay" style="display: none;"></div>
            <div id="formCreatedModal" class="modal" style="display: none;">
                <h2>Form Created Successfully!</h2>
                <p>Here’s your form link:</p>
                <div class="form-link-wrapper">
                    <input id="formLinkInput" type="text" readonly value="No link provided" />
                    <button id="copyLinkButton" class="copy-button">
                        <i class="fas fa-copy"></i> Copy Link
                    </button>
                </div>
                <p class="share-text">Or share your form:</p>
                <div class="share-buttons">
                    <a id="shareTwitter" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a id="shareFacebook" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a id="shareWhatsApp" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
                <p class="copy-success-message" id="copySuccessMessage" style="display: none; color: green;">
                    Link copied to clipboard!
                </p>
                <button id="closeModalButton" class="close-modal-button">Close</button>
            </div>
        `;

        // Append modal HTML to the body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Cache modal and overlay elements
        this.modal = document.getElementById('formCreatedModal');
        this.overlay = document.getElementById('modalOverlay');

        // Add event listeners
        this.addEventListeners();
    }

    // Show the modal
    showModal(link) {
        const formLinkInput = document.getElementById('formLinkInput');
        const twitterShare = document.getElementById('shareTwitter');
        const facebookShare = document.getElementById('shareFacebook');
        const whatsappShare = document.getElementById('shareWhatsApp');

        // Update the form link
        formLinkInput.value = link;

        // Update social media share links
        const twitterShareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(link)}&text=${encodeURIComponent("Check out this form I just created!")}`;
        const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;
        const whatsappShareUrl = `https://wa.me/?text=${encodeURIComponent("Check out this form: " + link)}`;

        twitterShare.href = twitterShareUrl;
        facebookShare.href = facebookShareUrl;
        whatsappShare.href = whatsappShareUrl;

        // Show modal and overlay
        this.modal.style.display = 'block';
        this.overlay.style.display = 'block';
    }

    // Hide the modal
    hideModal() {
        this.modal.style.display = 'none';
        this.overlay.style.display = 'none';
    }

    // Add event listeners
    addEventListeners() {
        const copyLinkButton = document.getElementById('copyLinkButton');
        const copySuccessMessage = document.getElementById('copySuccessMessage');
        const closeModalButton = document.getElementById('closeModalButton');

        // Copy to clipboard functionality
        copyLinkButton.addEventListener('click', () => {
            const formLinkInput = document.getElementById('formLinkInput');
            formLinkInput.select();
            document.execCommand('copy');
            copySuccessMessage.style.display = 'block';
            setTimeout(() => {
                copySuccessMessage.style.display = 'none';
            }, 2000);
        });

        // Close modal functionality
        closeModalButton.addEventListener('click', () => this.hideModal());
        this.overlay.addEventListener('click', () => this.hideModal());
    }
}

// Export the Modal class
export default Modal;
