jQuery(document).ready(function($) {

    $('.lab-accordion-header').on('click', function(){
        var $item = $(this).parent();
        var $content = $item.find('.category-content');
        var categoryValue = $item.data('category-value');

        console.log('Clicked accordion: ', categoryValue);

        // Toggle active class
        $item.toggleClass('active');

        // Only load if opening and not already loaded
        if($item.hasClass('active') && !$item.hasClass('loaded')) {
            
            $.ajax({
                url: labReportsAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'load_products',
                    nonce: labReportsAjax.nonce,
                    category_value: categoryValue
                },
                beforeSend: function() {
                    $content.find('.lab-accordion-inner').html('<p>Loading products...</p>');
                },
                success: function(response) {
                    console.log('Response for', categoryValue, ':', response);
                    
                    if(response.success) {
                        console.log('Found ' + response.data.count + ' products');
                        console.log('Data:', response.data.data);
                        
                        // Build HTML for products
                        if(response.data.data.length > 0) {
                            var html = '';
                            
                            response.data.data.forEach(function(product) {
                                var displayName = product.lab_report_display_name || product.product_title;
                                var productUrl = product.lab_report_url;
                                
                                html += '<div class="product-item-simple" data-product="' + escapeHtml(displayName) + '">';
                                html += '    <span class="product-name">' + escapeHtml(displayName) + '</span>';
                                html += '    <div class="batch-actions">';
                                html += '        <a href="' + escapeHtml(productUrl) + '" ';
                                html += '           class="batch-btn download-btn force-download-btn" ';
                                html += '           title="Download" ';
                                html += '           rel="noopener noreferrer">';
                                html += '            <img src="' + labReportsAjax.assetsUrl + '/images/download.png" alt="Download" width="24" height="24" />';
                                html += '        </a>';
                                html += '        <a href="' + escapeHtml(productUrl) + '" ';
                                html += '           class="batch-btn preview-btn" ';
                                html += '           target="_blank" ';
                                html += '           rel="noopener noreferrer" ';
                                html += '           title="Preview">';
                                html += '            <img src="' + labReportsAjax.assetsUrl + '/images/preview.png" alt="Preview" width="24" height="24" />';
                                html += '        </a>';
                                html += '    </div>';
                                html += '</div>';
                            });
                            
                            $content.find('.lab-accordion-inner').html(html);
                            
                            // Mark as loaded
                            $item.addClass('loaded');
                        } else {
                            $content.find('.lab-accordion-inner').html('<p>No products found.</p>');
                        }
                        
                    } else {
                        console.log('Error:', response.data.message);
                        $content.find('.lab-accordion-inner').html('<p>Error: ' + response.data.message + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    $content.find('.lab-accordion-inner').html('<p class="error">Failed to load products.</p>');
                }
            });
        }
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});