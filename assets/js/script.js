/**
 * Client-side script for Inventory Management System
 * Handles page confirmations, basic validation checks, and visual feedback.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to any link or button with class 'confirm-delete'
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const message = this.getAttribute('data-message') || 'Are you sure you want to delete this record?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Auto-fade dismissible Bootstrap alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            // Using Bootstrap's built-in alert close transition if available
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
});
