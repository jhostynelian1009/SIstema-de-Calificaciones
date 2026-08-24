import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-initialize Bootstrap Toasts
    const toastElList = document.querySelectorAll('.toast');
    toastElList.forEach((toastEl) => {
        const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
        toast.show();
    });

    // 2. Centralized Confirmation Modal for data-confirm-message
    document.addEventListener('click', (e) => {
        const confirmBtn = e.target.closest('[data-confirm-message]');
        if (!confirmBtn) return;

        e.preventDefault();
        const message = confirmBtn.getAttribute('data-confirm-message') || '¿Está seguro de realizar esta acción?';
        const title = confirmBtn.getAttribute('data-confirm-title') || 'Confirmación requerida';

        let modalEl = document.getElementById('globalConfirmModal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'globalConfirmModal';
            modalEl.className = 'modal fade';
            modalEl.setAttribute('tabindex', '-1');
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-dark" id="globalConfirmModalTitle"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0 text-secondary" id="globalConfirmModalBody"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="globalConfirmModalProceedBtn">Confirmar</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modalEl);
        }

        const modalTitle = modalEl.querySelector('#globalConfirmModalTitle');
        const modalBody = modalEl.querySelector('#globalConfirmModalBody');
        const proceedBtn = modalEl.querySelector('#globalConfirmModalProceedBtn');

        modalTitle.textContent = title;
        modalBody.textContent = message;

        // Custom style for destructive actions
        if (confirmBtn.classList.contains('btn-danger') || confirmBtn.getAttribute('data-confirm-variant') === 'danger') {
            proceedBtn.className = 'btn btn-danger';
        } else {
            proceedBtn.className = 'btn btn-primary';
        }

        const modalInstance = new bootstrap.Modal(modalEl);

        const handleProceed = () => {
            modalInstance.hide();
            proceedBtn.removeEventListener('click', handleProceed);

            if (confirmBtn.tagName === 'FORM' || confirmBtn.type === 'submit') {
                const parentForm = confirmBtn.closest('form');
                if (parentForm) parentForm.submit();
            } else if (confirmBtn.tagName === 'A' && confirmBtn.href) {
                window.location.href = confirmBtn.href;
            } else if (confirmBtn.closest('form')) {
                confirmBtn.closest('form').submit();
            }
        };

        proceedBtn.replaceWith(proceedBtn.cloneNode(true));
        const newProceedBtn = modalEl.querySelector('#globalConfirmModalProceedBtn');
        newProceedBtn.addEventListener('click', handleProceed);

        modalInstance.show();
    });

    // 3. Double Submit Prevention & Loading Spinner for Forms
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            if (this.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.hasAttribute('data-no-spinner')) {
                this.dataset.submitting = 'true';
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...`;

                // Fallback reset if page doesn't unload in 10 seconds
                setTimeout(() => {
                    this.dataset.submitting = 'false';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            }
        });
    });

    // 4. Character Counter for textareas with data-character-counter
    document.querySelectorAll('[data-character-counter]').forEach((input) => {
        const targetId = input.getAttribute('data-character-counter');
        const counterEl = document.getElementById(targetId);
        if (!counterEl) return;

        const updateCount = () => {
            const currentLength = input.value.length;
            const maxLength = input.getAttribute('maxlength') || 255;
            counterEl.textContent = `${currentLength} / ${maxLength}`;
        };

        input.addEventListener('input', updateCount);
        updateCount();
    });
});
