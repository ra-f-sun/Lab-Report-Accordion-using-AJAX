/**
 * Lab Reports - Client-Side Force Download (Robust Fetch/Blob Method)
 * Version: 1.5.0 - Adaptive for AJAX-loaded content
 *
 * This is the most reliable client-side method. It fetches the file
 * data as a blob and creates a temporary object URL to trigger the download,
 * bypassing server's inline display headers for same-origin files.
 */

(function() {
    'use strict';

    /**
     * Initialize download handlers for force-download buttons
     * @param {Element} container - The container to search within (defaults to document)
     */
    function initDownloadHandlers(container = document) {
        const downloadButtons = container.querySelectorAll('.force-download-btn:not([data-download-initialized])');

        console.log('Found download buttons:', downloadButtons.length);

        downloadButtons.forEach(a => {
            // Mark as initialized to prevent duplicate handlers
            a.setAttribute('data-download-initialized', 'true');

            a.addEventListener('click', async function(e) {
                e.preventDefault();
                
                const url = this.getAttribute('href');
                
                if (!url) {
                    console.error('Download failed: No URL provided on the button.');
                    alert('Could not download file: URL is missing.');
                    return;
                }

                const urlObj = new URL(url);
                const filename = urlObj.pathname.split('/').pop();
                
                console.log('Downloading:', url);

                const originalHTML = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="btn-icon">⏳</span>';

                try {
                    // Fetch the file with 'cors' mode for same-origin requests
                    const response = await fetch(url, { mode: 'cors' });

                    if (!response.ok) {
                        throw new Error(`Network response was not ok. Status: ${response.status}`);
                    }

                    // Get the data as a blob
                    const blob = await response.blob();

                    // Create a temporary URL for the blob
                    const blobUrl = window.URL.createObjectURL(blob);

                    // Create a hidden link to trigger the download
                    const link = document.createElement('a');
                    link.style.display = 'none';
                    link.href = blobUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    
                    link.click();

                    // Clean up by revoking the object URL and removing the link
                    window.URL.revokeObjectURL(blobUrl);
                    document.body.removeChild(link);

                    console.log('Download completed:', filename);

                } catch (error) {
                    console.error('Download error:', error);
                    alert('Download failed. The file might be private, the link is broken, or your server is blocking the request. Please try the preview button.');
                } finally {
                    // Restore the button's original state
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            });
        });
    }

    /**
     * Get lab report URLs from loaded categories
     * @returns {Array} Array of lab report URLs
     */
    function getLoadedLabReportUrls() {
        const loadedCategories = document.querySelectorAll('.category-item.loaded');
        const urls = [];

        loadedCategories.forEach(categoryDiv => {
            const downloadButtons = categoryDiv.querySelectorAll('.force-download-btn');
            
            downloadButtons.forEach(button => {
                const url = button.getAttribute('href');
                if (url) {
                    urls.push(url);
                }
            });
        });

        return urls;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initDownloadHandlers();
    });

    // Make functions globally accessible
    window.initLabReportDownloads = initDownloadHandlers;
    window.getLoadedLabReportUrls = getLoadedLabReportUrls;

    // Optional: MutationObserver for auto-detection
    if (window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('force-download-btn')) {
                                initDownloadHandlers(node.parentElement);
                            } else if (node.querySelectorAll) {
                                const newButtons = node.querySelectorAll('.force-download-btn');
                                if (newButtons.length > 0) {
                                    initDownloadHandlers(node);
                                }
                            }
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();