import './bootstrap';

// Modal scrolling fix
document.addEventListener('DOMContentLoaded', function() {
    // Fix modal scrolling issues
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            // Ensure modal content is scrollable
            const modalBody = modal.querySelector('.modal-body');
            const scrollableSections = modal.querySelectorAll('.order-summary-section, .lens-type-section, .modal-scrollable');
            
            if (modalBody) {
                modalBody.style.overflow = 'hidden';
                modalBody.style.display = 'flex';
                modalBody.style.flexDirection = 'column';
            }
            
            scrollableSections.forEach(section => {
                section.style.overflowY = 'auto';
                section.style.overflowX = 'hidden';
                section.style.height = '100%';
            });
        });
        
        modal.addEventListener('shown.bs.modal', function() {
            // Refresh scroll behavior
            const scrollableSections = modal.querySelectorAll('.order-summary-section, .lens-type-section, .modal-scrollable');
            scrollableSections.forEach(section => {
                // Force scrollbar to appear if content overflows
                if (section.scrollHeight > section.clientHeight) {
                    section.style.overflowY = 'auto';
                }
            });
        });
    });
    
    // Handle window resize for modals
    window.addEventListener('resize', function() {
        const activeModal = document.querySelector('.modal.show');
        if (activeModal) {
            const modalDialog = activeModal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.height = 'calc(100vh - 60px)';
                modalDialog.style.maxHeight = 'calc(100vh - 60px)';
            }
        }
    });
});
