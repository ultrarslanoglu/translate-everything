<?php
/*
Plugin Name: Translate Everything
Description: A plugin to translate everything on the site using OpenAI API and change the site language based on user location.
Version: 1.0
Author: Your Name
*/

// OpenAI API anahtarınızı burada tanımlayın
define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY');

// Kısa kod işlevi
function translate_everything_shortcode() {
    ob_start();
    ?>
    <div id="translate-everything">
        <form id="translate-form">
            <label for="translate-text">Metni Girin:</label><br>
            <textarea id="translate-text" name="translate-text" rows="4" cols="50"></textarea><br>
            <label for="target-language">Hedef Dil:</label><br>
            <select id="target-language" name="target-language">
                <option value="en">İngilizce</option>
                <option value="tr">Türkçe</option>
                <!-- Başka diller ekleyebilirsiniz -->
            </select><br><br>
            <button type="button" onclick="translateText()">Çevir</button>
        </form>
        <div id="translation-result"></div>
    </div>
    <script>
        function translateText() {
            var text = document.getElementById('translate-text').value;
            var targetLanguage = document.getElementById('target-language').value;
            fetch('https://your-website.com/wp-json/translate-everything/v1/translate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ text: text, target_language: targetLanguage })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('translation-result').innerText = data.translation;
            });
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('translate_everything', 'translate_everything_shortcode');

// REST API route ekleme
add_action('rest_api_init', function () {
    register_rest_route('translate-everything/v1', '/translate', array(
        'methods' => 'POST',
        'callback' => 'translate_everything_translate',
    ));
});

// Çeviri işlevi
function translate_everything_translate(WP_REST_Request $request) {
    $text = $request->get_param('text');
    $target_language = $request->get_param('target_language');
    
    $translation = call_openai_api($text, $target_language);
    
    return new WP_REST_Response(array('translation' => $translation), 200);
}

// OpenAI API'sine istek yapma işlevi
function call_openai_api($text, $target_language) {
    $endpoint = 'https://api.openai.com/v1/translations';
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
    );
    $body = json_encode(array(
        'text' => $text,
        'target_language' => $target_language,
    ));
    
    $response = wp_remote_post($endpoint, array(
        'headers' => $headers,
        'body' => $body,
    ));
    
    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }
    
    $data = json_decode(wp_remote_retrieve_body($response), true);
    return $data['translation'] ?? 'Translation error';
}

// Admin menü öğesi ekleme
function translate_everything_menu() {
    add_menu_page(
        'Translate Everything Settings', 
        'Translate Everything', 
        'manage_options', 
        'translate-everything', 
        'translate_everything_settings_page', 
        'dashicons-translation', 
        100
    );
}
add_action('admin_menu', 'translate_everything_menu');

// Admin ayar sayfası
function translate_everything_settings_page() {
    ?>
    <div class="wrap">
        <h1>Translate Everything Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('translate-everything-settings-group');
            do_settings_sections('translate-everything-settings-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">OpenAI API Key</th>
                    <td><input type="text" name="openai_api_key" value="<?php echo esc_attr(get_option('openai_api_key')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Ayarları kaydetme
function translate_everything_register_settings() {
    register_setting('translate-everything-settings-group', 'openai_api_key');
}
add_action('admin_init', 'translate_everything_register_settings');
?>
