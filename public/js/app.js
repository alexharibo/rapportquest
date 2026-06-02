/* RapportQuest — app.js */

(function () {
    'use strict';

    const form       = document.getElementById('upload-form');
    const dropZone   = document.getElementById('drop-zone');
    const fileInput  = document.getElementById('pdf-file');
    const fileNameEl = document.getElementById('file-name');
    const messageEl  = document.getElementById('message-area');
    const submitBtn  = document.getElementById('submit-btn');

    /* ---- Helpers ---- */
    function showMessage(text, type) {
        messageEl.textContent = text;
        messageEl.className   = 'message-area ' + type;
    }

    function clearMessage() {
        messageEl.textContent = '';
        messageEl.className   = 'message-area';
    }

    function setFileName(name) {
        fileNameEl.textContent = name ? name : '';
    }

    function validateFile(file) {
        if (!file) return 'Vælg venligst en fil.';
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            return 'Kun PDF-filer er tilladt.';
        }
        if (file.size > 20 * 1024 * 1024) {
            return 'Filen må ikke overstige 20 MB.';
        }
        return null;
    }

    /* ---- File input change ---- */
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        clearMessage();
        if (file) {
            const error = validateFile(file);
            if (error) {
                showMessage(error, 'error');
                setFileName('');
                this.value = '';
            } else {
                setFileName(file.name);
            }
        }
    });

    /* ---- Drag & Drop ---- */
    ['dragenter', 'dragover'].forEach(function (evt) {
        dropZone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        dropZone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        const file = e.dataTransfer.files[0];
        clearMessage();
        if (file) {
            const error = validateFile(file);
            if (error) {
                showMessage(error, 'error');
            } else {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                setFileName(file.name);
            }
        }
    });

    /* ---- Form submit ---- */
    form.addEventListener('submit', function (e) {
        clearMessage();
        const file  = fileInput.files[0];
        const error = validateFile(file);
        if (error) {
            e.preventDefault();
            showMessage(error, 'error');
            return;
        }
        submitBtn.disabled    = true;
        submitBtn.textContent = 'Uploader…';
    });
}());
