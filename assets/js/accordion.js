/**
 * Lab Reports - Accordion Functionality
 * Version: 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Get all accordion headers
    const accordionHeaders = document.querySelectorAll('.lab-accordion-header');
    
    // Add click event to each header
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const accordionItem = this.parentElement;
            
            // Toggle active class
            accordionItem.classList.toggle('active');
        });
    });
    
});
