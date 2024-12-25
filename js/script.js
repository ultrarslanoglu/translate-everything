/* script.js */
jQuery(document).ready(function($) {
    $('#translate-form').on('submit', function(e) {
        e.preventDefault();
        var text = $('#translate-text').val();
        var targetLanguage = $('#target-language').val();
        
        $.ajax({
            url: '/wp-json/translate-everything/v1/translate',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ text: text, target_language: targetLanguage }),
            success: function(response) {
                $('#translation-result').text(response.translation);
            },
            error: function(error) {
                $('#translation-result').text('Translation error');
            }
        });
    });
});
