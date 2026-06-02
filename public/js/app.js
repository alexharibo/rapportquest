/* RapportQuest — app.js */

(function () {
    'use strict';

    const form        = document.getElementById('upload-form');
    const dropZone    = document.getElementById('drop-zone');
    const fileInput   = document.getElementById('pdf-file');
    const fileNameEl  = document.getElementById('file-name');
    const messageEl   = document.getElementById('message-area');
    const submitBtn   = document.getElementById('submit-btn');
    const progressBar = document.getElementById('upload-progress');

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
        fileNameEl.textContent = name || '';
    }

    function setProgress(pct) {
        if (!progressBar) return;
        progressBar.value = pct;
        progressBar.style.display = pct > 0 && pct < 100 ? 'block' : 'none';
    }

    function validateFile(file) {
        if (!file) return 'Vælg venligst en fil.';
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            return 'Kun PDF-filer er tilladt.';
        }
        if (file.size > 20 * 1024 * 1024) {
            return 'Filen må ikke overstige 20 MB.';
        }
        if (file.size === 0) {
            return 'Filen er tom.';
        }
        return null;
    }

    function resetForm() {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Start læringsforløb';
        setProgress(0);
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

    /* ---- Form submit via XHR ---- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearMessage();

        const file  = fileInput.files[0];
        const error = validateFile(file);
        if (error) {
            showMessage(error, 'error');
            return;
        }

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Uploader…';
        setProgress(1);

        const formData = new FormData();
        formData.append('report', file);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                setProgress(Math.round((e.loaded / e.total) * 100));
            }
        });

        xhr.addEventListener('load', function () {
            setProgress(100);
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (_) {
                showMessage('Uventet svar fra serveren. Prøv igen.', 'error');
                resetForm();
                return;
            }

            if (response.success) {
                showMessage('Rapport uploadet! Starter analyse…', 'success');
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 800);
            } else {
                showMessage(response.error || 'Noget gik galt. Prøv igen.', 'error');
                resetForm();
            }
        });

        xhr.addEventListener('error', function () {
            showMessage('Netværksfejl. Tjek din forbindelse og prøv igen.', 'error');
            resetForm();
        });

        xhr.addEventListener('timeout', function () {
            showMessage('Upload tog for lang tid. Prøv en mindre fil.', 'error');
            resetForm();
        });

        xhr.timeout = 60000; // 60 sekunder
        xhr.open('POST', 'upload.php');
        xhr.send(formData);
    });
}());
