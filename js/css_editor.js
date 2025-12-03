document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('styleForm');
    const previewFrame = document.getElementById('previewFrame');
    const templateSelect = document.getElementById('templateSelect');
    const messageArea = document.getElementById('messageArea');
    
    // Inputs
    const inputs = {
        fontFamily: document.getElementById('fontFamily'),
        titleSize: document.getElementById('titleSize'),
        bodySize: document.getElementById('bodySize'),
        textDecoration: document.getElementById('textDecoration'),
        bgUpload: document.getElementById('bgUpload')
    };

    // Template Definitions
    const templates = {
        frutiger_aero: {
            fontFamily: 'Segoe UI, Arial, sans-serif',
            titleSize: '2.5rem',
            bodySize: '1.1rem',
            textDecoration: 'none',
            bgClass: 'template-frutiger',
            cardClass: 'post-card glass-card'
        },
        pink_classic: {
            fontFamily: 'Arial, sans-serif',
            titleSize: '2rem',
            bodySize: '1rem',
            textDecoration: 'none',
            bgClass: 'template-pink',
            cardClass: 'post-card solid-card'
        }
    };

    // Live Preview Updater
    function updatePreview() {
        const postTitle = previewFrame.querySelector('.post-title');
        const postContent = previewFrame.querySelector('.post-content');
        const postCard = previewFrame.querySelector('.post-card');
        
        // Apply Typography
        if (postTitle) postTitle.style.fontSize = inputs.titleSize.value;
        
        if (postContent) {
            postContent.style.fontFamily = inputs.fontFamily.value;
            postContent.style.fontSize = inputs.bodySize.value;
            postContent.style.textDecoration = inputs.textDecoration.value;
        }

        // Apply Template Classes
        const currentTemplate = templateSelect.value;
        
        // Reset classes
        previewFrame.className = 'preview-content';
        postCard.className = 'post-card';
        
        if (currentTemplate === 'frutiger_aero') {
            previewFrame.classList.add('template-frutiger');
            postCard.classList.add('glass-card');
            
            // Default background for Frutiger if no custom image
            if (!previewFrame.style.backgroundImage) {
                previewFrame.style.backgroundImage = 'url("img/backy.jpeg")';
            }
        } else if (currentTemplate === 'pink_classic') {
            previewFrame.classList.add('template-pink');
            postCard.classList.add('solid-card');
            
            // Default background for Pink if no custom image
            if (!previewFrame.style.backgroundImage) {
                previewFrame.style.background = 'linear-gradient(135deg, #FFE5F0 0%, #FFB6D9 100%)';
            }
        }
    }

    // Event Listeners for Inputs
    Object.values(inputs).forEach(input => {
        if(input) {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        }
    });

    // Template Switcher
    templateSelect.addEventListener('change', function() {
        const tpl = templates[this.value];
        if (tpl) {
            // Confirm before overwriting if user has changes? 
            // For simplicity, we just overwrite values to match template defaults
            inputs.fontFamily.value = tpl.fontFamily;
            inputs.titleSize.value = tpl.titleSize;
            inputs.bodySize.value = tpl.bodySize;
            inputs.textDecoration.value = tpl.textDecoration;
            
            updatePreview();
        }
    });

    // Background Image Preview
    inputs.bgUpload.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewFrame.style.backgroundImage = `url('${e.target.result}')`;
                // Also update the small preview box
                const box = document.getElementById('currentBgDisplay');
                box.innerHTML = `<img src="${e.target.result}" alt="New Preview"><span class="bg-name">New Image Selected</span>`;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Form Submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerText;
        
        saveBtn.disabled = true;
        saveBtn.innerText = 'guardando...';
        messageArea.style.display = 'none';

        fetch('save_blog_style.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            messageArea.style.display = 'block';
            if (data.success) {
                messageArea.className = 'success-msg';
                messageArea.innerText = data.message;
            } else {
                messageArea.className = 'error-msg';
                messageArea.innerText = 'Error: ' + data.message;
            }
        })
        .catch(error => {
            messageArea.style.display = 'block';
            messageArea.className = 'error-msg';
            messageArea.innerText = 'error de red';
            console.error(error);
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerText = originalText;
        });
    });

    // Initial Preview Load
    updatePreview();
});
