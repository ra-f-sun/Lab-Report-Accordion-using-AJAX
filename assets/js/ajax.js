jQuery(document).ready(function($) {
    $.ajax({
        url: lab_reports_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'test_ajax'
        },
        success: function(response) {
            console.log('AJAX Response:', response);
            if (response.success) {
                alert('SUCCESS: ' + response.data.message);
            }
        },
        error: function(error) {
            console.error('AJAX Error:', error);
        }
    });
});
