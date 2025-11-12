<?php
/**
 * Plugin Name: Silaju AI Plugin
 * Plugin URI: https://example.com/
 * Description: A WordPress plugin to generate text and images using the Google Gemini API.
 * Version: 1.0.0
 * Author: Wan Yuee Low
 * Author URI: https://www.librokloud.com/
 * License: GPL2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SILAJU_AI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SILAJU_AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Enqueue admin scripts and styles.
 */
function silaju_ai_plugin_enqueue_admin_assets() {
    // Enqueue CSS
    wp_enqueue_style( 'silaju-ai-plugin-style', SILAJU_AI_PLUGIN_URL . 'assets/css/style.css', array(), '1.0.0' );

    // Enqueue JS for all admin pages of the plugin
    wp_enqueue_script( 'silaju-ai-plugin-script', SILAJU_AI_PLUGIN_URL . 'assets/js/script.js', array( 'jquery' ), '1.0.0', true );

    // Pass AJAX URL to our script
    wp_localize_script( 'silaju-ai-plugin-script', 'silaju_ai_ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce_text_gen' => wp_create_nonce( 'silaju_ai_text_gen_nonce' ),
        'nonce_image_gen' => wp_create_nonce( 'silaju_ai_image_gen_nonce' ),
        'nonce_save_image' => wp_create_nonce( 'silaju_save_image_nonce' ),
        'nonce_get_tags' => wp_create_nonce( 'silaju_get_tags_nonce' ),
        'nonce_suggest_headlines' => wp_create_nonce( 'silaju_suggest_headlines_nonce' ),
        'nonce_create_draft' => wp_create_nonce( 'silaju_create_draft_nonce' ),
        'nonce_predict_tags' => wp_create_nonce( 'silaju_predict_tags_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'silaju_ai_plugin_enqueue_admin_assets' );

/**
 * Add top-level menu item for the plugin.
 */
function silaju_ai_plugin_add_admin_menu() {
    add_menu_page(
        'Silaju AI Plugin',         // Page title (New Name)
        'Silaju AI Plugin',         // Menu title (New Name)
        'manage_options',           // Capability
        'silaju-ai-plugin',         // Menu slug (New Name)
        'silaju_ai_plugin_settings_page_callback', // Callback function for the main page (settings)
        'dashicons-superhero',      // Icon URL or Dashicon class
        6                            // Position
    );

    add_submenu_page(
        'silaju-ai-plugin',         // Parent slug
        'Silaju AI Settings',       // Page title
        'Settings',                 // Menu title
        'manage_options',           // Capability
        'silaju-ai-plugin',         // Menu slug (same as parent to make it the default)
        'silaju_ai_plugin_settings_page_callback' // Callback
    );

    add_submenu_page(
        'silaju-ai-plugin',         // Parent slug
        'Silaju AI Text Generation',// Page title
        'Text Generation',          // Menu title
        'manage_options',           // Capability
        'silaju-ai-text-generation',// Menu slug
        'silaju_ai_plugin_text_generation_page_callback' // Callback
    );

    add_submenu_page(
        'silaju-ai-plugin',         // Parent slug
        'Silaju AI Image Generation',// Page title
        'Image Generation',         // Menu title
        'manage_options',           // Capability
        'silaju-ai-image-generation',// Menu slug
        'silaju_ai_plugin_image_generation_page_callback' // Callback
    );
}
add_action( 'admin_menu', 'silaju_ai_plugin_add_admin_menu' );

/**
 * Register settings.
 */
function silaju_ai_plugin_settings_init() {
    register_setting(
        'silaju_ai_plugin_settings_group',
        'silaju_ai_api_key'
    );

    register_setting(
        'silaju_ai_plugin_settings_group',
        'silaju_api_endpoint_url'
    );

    add_settings_section(
        'silaju_ai_plugin_main_section', // ID
        'API Key Settings',              // Title
        'silaju_ai_plugin_section_callback', // Callback
        'silaju-ai-plugin'               // Page
    );

    add_settings_field(
        'silaju_ai_api_key_field',       // ID
        'Google Gemini API Key',         // Title
        'silaju_ai_api_key_field_callback', // Callback
        'silaju-ai-plugin',              // Page
        'silaju_ai_plugin_main_section'  // Section
    );

    add_settings_field(
        'silaju_api_endpoint_url_field',
        'SILAJU API Endpoint URL',
        'silaju_api_endpoint_url_field_callback',
        'silaju-ai-plugin',
        'silaju_ai_plugin_main_section'
    );    
}
add_action( 'admin_init', 'silaju_ai_plugin_settings_init' );

/**
 * Section callback.
 */
function silaju_ai_plugin_section_callback() {
    echo '<p>Enter your Google Gemini API Key below to enable the AI features.</p>';
}

/**
 * API Key field callback.
 */
function silaju_ai_api_key_field_callback() {
    $api_key = get_option( 'silaju_ai_api_key' );
    echo '<input type="text" name="silaju_ai_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text" placeholder="Enter your Gemini API Key here">'; // (New Input Name)
    echo '<p class="description">You can obtain your API key from <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</p>';
}

function silaju_api_endpoint_url_field_callback() {
    $endpoint_url = get_option( 'silaju_api_endpoint_url', 'http://localhost:8000' );
    echo '<input type="text" name="silaju_api_endpoint_url" value="' . esc_attr( $endpoint_url ) . '" class="regular-text" placeholder="http://localhost:8000">';
    echo '<p class="description">Enter the base URL for your SILAJU API server (e.g., http://localhost:8000)</p>';
}

/**
 * Callback for the main settings page.
 */
function silaju_ai_plugin_settings_page_callback() {
    require_once SILAJU_AI_PLUGIN_DIR . 'admin/settings.php';
}

/**
 * Callback for the text generation page.
 */
function silaju_ai_plugin_text_generation_page_callback() {
    require_once SILAJU_AI_PLUGIN_DIR . 'admin/text-generation.php';
}

/**
 * Callback for the image generation page.
 */
function silaju_ai_plugin_image_generation_page_callback() {
    require_once SILAJU_AI_PLUGIN_DIR . 'admin/image-generation.php';
}

/**
 * Handle AJAX request for text generation.
 */
function silaju_ai_plugin_handle_text_generation() {
    check_ajax_referer( 'silaju_ai_text_gen_nonce', 'nonce' ); 

    $api_key = get_option( 'silaju_ai_api_key' ); 
    if ( ! $api_key ) {
        wp_send_json_error( 'Gemini API Key is not set. Please configure it in the plugin settings.' );
    }

    $prompt = sanitize_textarea_field( $_POST['prompt'] );
    if ( empty( $prompt ) ) {
        wp_send_json_error( 'Prompt cannot be empty.' );
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key;

    // Define your system prompt here
    $system_prompt = "You are a helpful, professional AI assistant. Provide clear, accurate, and well-structured responses.";

    $body = json_encode( array(
        'contents' => array(
            array(
                'parts' => array(
                    array(
                        'text' => $prompt
                    )
                )
            )
        ),
        'systemInstruction' => array(
            'parts' => array(
                array(
                    'text' => $system_prompt
                )
            )
        ),        
        'generationConfig' => array(
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
        )
    ) );

    $args = array(
        'body'        => $body,
        'headers'     => array( 'Content-Type' => 'application/json' ),
        'method'      => 'POST',
        'timeout'     => 45,
        'blocking'    => true,
        'sslverify'   => false,
        'data_format' => 'body',
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'API Error: ' . $response->get_error_message() );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    if ( isset( $data->candidates[0]->content->parts[0]->text ) ) {
        wp_send_json_success( array( 'generated_text' => $data->candidates[0]->content->parts[0]->text ) );
    } elseif ( isset( $data->error->message ) ) {
        wp_send_json_error( 'Gemini API Error: ' . $data->error->message );
    } else {
        wp_send_json_error( 'Unknown error from Gemini API.' );
    }
}
add_action( 'wp_ajax_silaju_ai_generate_text', 'silaju_ai_plugin_handle_text_generation' ); 

/**
 * Handle AJAX request for image generation.
 */
function silaju_ai_plugin_handle_image_generation() {
    check_ajax_referer( 'silaju_ai_image_gen_nonce', 'nonce' );

    $prompt = sanitize_textarea_field( $_POST['prompt'] );
    $num_images = (int) $_POST['num_images'];
    
    // Clamp number of images to a sensible range (1 to 4)
    $num_images = max( 1, min( 4, $num_images ) );

    $api_key = get_option( 'silaju_ai_api_key' );

    if ( empty( $api_key ) ) {
        wp_send_json_error( 'API Key is not set in settings.' );
    }

    // Updated to use gemini-2.5-flash-image (stable version)
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=' . $api_key;
    
    $system_prompt = "You are a world-class AI image generator. Create high-quality, professional images based on the user's detailed prompt.";

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'systemInstruction' => [
            'parts' => [
                ['text' => $system_prompt]
            ]
        ],
        'generationConfig' => [
            'responseModalities' => ['IMAGE']
        ]
    ];

    $generated_images = [];

    for ($i = 0; $i < $num_images; $i++) {
        $args = array(
            'body'    => json_encode( $payload ),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 90,
        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'API Request Failed: ' . $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['candidates'][0]['content']['parts'] ) ) {
            foreach ( $data['candidates'][0]['content']['parts'] as $part ) {
                if ( isset( $part['inlineData']['data'] ) && isset( $part['inlineData']['mimeType'] ) ) {
                    $generated_images[] = [
                        'base64_image' => 'data:' . $part['inlineData']['mimeType'] . ';base64,' . $part['inlineData']['data'],
                        'prompt' => $prompt
                    ];
                }
            }
        } elseif ( isset( $data['error']['message'] ) ) {
            wp_send_json_error( 'Image Generation API Error: ' . $data['error']['message'] );
        }
    }

    if ( ! empty( $generated_images ) ) {
        wp_send_json_success( array( 'generated_images_data' => $generated_images ) );
    } else {
        wp_send_json_error( 'Failed to generate images. Check API key and service status.' );
    }

    wp_die();
}
add_action( 'wp_ajax_silaju_ai_generate_image', 'silaju_ai_plugin_handle_image_generation' );
add_action( 'wp_ajax_nopriv_silaju_ai_generate_image', 'silaju_ai_plugin_handle_image_generation' );

/**
 * Handle saving the base64 image to the WordPress Media Library.
 * This function remains largely unchanged but is included for completeness.
 */
function silaju_ai_plugin_handle_save_image() {
    check_ajax_referer( 'silaju_save_image_nonce', 'nonce' );

    $base64_image_data = sanitize_text_field( $_POST['image_data'] );
    $image_title = sanitize_text_field( $_POST['image_title'] );

    // Extract raw base64 data and mime type
    if ( preg_match( '/^data:image\/(.*?);base64,(.*)$/', $base64_image_data, $matches ) ) {
        $mime_type = $matches[1];
        $base64_data = $matches[2];
        $image_binary = base64_decode( $base64_data );
    } else {
        wp_send_json_error( array('message' => 'Invalid image data format.') );
    }

    if ( empty( $image_binary ) ) {
        wp_send_json_error( array('message' => 'Image data is empty or corrupted.') );
    }

    // Set file details
    $filename = sanitize_file_name( $image_title . '-' . time() . '.' . $mime_type );
    $upload_dir = wp_upload_dir();
    $upload_file = trailingslashit( $upload_dir['path'] ) . $filename;

    // Save the binary data to the uploads folder
    if ( file_put_contents( $upload_file, $image_binary ) === false ) {
        wp_send_json_error( array('message' => 'Failed to write image file to disk.') );
    }

    // Prepare attachment array for WordPress DB
    $attachment = array(
        'post_mime_type' => 'image/' . $mime_type,
        'post_title'     => $image_title,
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert the attachment into the database
    $attach_id = wp_insert_attachment( $attachment, $upload_file );
    
    // Generate attachment metadata and update database
    if ( !is_wp_error($attach_id) ) {
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        // Success response
        wp_send_json_success( array(
            'message' => 'Image successfully added to media library.',
            'attachment_id' => $attach_id,
            'url' => $upload_dir['url'] . '/' . $filename
        ) );
    } else {
        // Cleanup file if DB insertion failed
        unlink($upload_file);
        wp_send_json_error( array('message' => 'Failed to insert attachment into database.') );
    }

    wp_die();
}
add_action( 'wp_ajax_silaju_save_image', 'silaju_ai_plugin_handle_save_image' ); // Attach save function to AJAX hook

/**
 * NEW: Handle AJAX request to predict tags from content
 */
function silaju_ai_plugin_predict_tags() {
    check_ajax_referer( 'silaju_predict_tags_nonce', 'nonce' );
    
    $content = sanitize_textarea_field( $_POST['content'] );
    
    if ( empty( $content ) ) {
        wp_send_json_error( 'Please generate content first before predicting tags.' );
    }
    
    // Get the API endpoint from settings
    $api_endpoint = get_option( 'silaju_api_endpoint_url', 'http://localhost:8000' );
    
    // Build the URL
    $url = $api_endpoint . '/predict';
    
    // Prepare the request body
    $body = json_encode( array(
        'description' => $content
    ) );
    
    // Make the API request
    $response = wp_remote_post( $url, array(
        'timeout' => 30,
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => $body
    ) );
    
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'API Error: ' . $response->get_error_message() );
    }
    
    $response_body = wp_remote_retrieve_body( $response );
    $data = json_decode( $response_body, true );
    
    if ( ! $data ) {
        wp_send_json_error( 'Invalid API response.' );
    }
    
    // Map API response to tag names
    $predicted_tags = array();
    $label_mapping = array(
        'is_creative' => 'creative',
        'is_enthusiast' => 'enthusiast',
        'is_business' => 'business',
        'is_ecomm' => 'ecomm',
        'is_policy' => 'policy'
    );
    
    foreach ( $label_mapping as $api_label => $tag_slug ) {
        if ( isset( $data[$api_label] ) && $data[$api_label] == 1 ) {
            $predicted_tags[] = $tag_slug;
        }
    }
    
    if ( empty( $predicted_tags ) ) {
        wp_send_json_error( 'No tags were predicted for this content.' );
    }
    
    wp_send_json_success( array( 
        'predicted_tags' => $predicted_tags,
        'raw_response' => $data
    ) );
}
add_action( 'wp_ajax_silaju_predict_tags', 'silaju_ai_plugin_predict_tags' );

/**
 * Handle AJAX request to get all WordPress tags
 */
function silaju_ai_plugin_get_tags() {
    check_ajax_referer( 'silaju_get_tags_nonce', 'nonce' );
    
    $tags = get_tags( array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ) );
    
    $tag_list = array();
    foreach ( $tags as $tag ) {
        $tag_list[] = array(
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug
        );
    }
    
    wp_send_json_success( array( 'tags' => $tag_list ) );
}
add_action( 'wp_ajax_silaju_get_tags', 'silaju_ai_plugin_get_tags' );

/**
 * NEW: Handle AJAX request to suggest headlines from SILAJU API
 */
function silaju_ai_plugin_suggest_headlines() {
    check_ajax_referer( 'silaju_suggest_headlines_nonce', 'nonce' );
    
    $selected_tags = isset( $_POST['selected_tags'] ) ? json_decode( stripslashes( $_POST['selected_tags'] ), true ) : array();
    
    if ( empty( $selected_tags ) ) {
        wp_send_json_error( 'Please select at least one tag.' );
    }
    
    // Get the API endpoint from settings
    $api_endpoint = get_option( 'silaju_api_endpoint_url', 'http://localhost:8000' );
    
    // Map tag names to API parameters
    $label_mapping = array(
        'creative' => 'is_creative',
        'enthusiast' => 'is_enthusiast',
        'business' => 'is_business',
        'ecomm' => 'is_ecomm',
        'policy' => 'is_policy'
    );
    
    // Build query parameters
    $query_params = array();
    foreach ( $selected_tags as $tag_slug ) {
        $tag_slug_lower = strtolower( $tag_slug );
        if ( isset( $label_mapping[$tag_slug_lower] ) ) {
            $query_params[$label_mapping[$tag_slug_lower]] = 1;
        }
    }
    
    $query_params['limit'] = 5;
    
    // Build the URL
    $url = $api_endpoint . '/data/relatedarticles?' . http_build_query( $query_params );
    
    // Make the API request
    $response = wp_remote_get( $url, array(
        'timeout' => 30,
    ) );
    
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'API Error: ' . $response->get_error_message() );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! $data ) {
        wp_send_json_error( 'No articles found or invalid API response.' );
    }
    
    // Extract headlines
    $headlines = array();
    foreach ( $data as $article ) {
        if ( isset( $article['headline'] ) ) {
            $headlines[] = $article['headline'];
        }
    }
    
    if ( empty( $headlines ) ) {
        wp_send_json_error( 'No headlines found in the response.' );
    }
    
    wp_send_json_success( array( 'headlines' => $headlines ) );
}
add_action( 'wp_ajax_silaju_suggest_headlines', 'silaju_ai_plugin_suggest_headlines' );

/**
 * NEW: Handle AJAX request to create a draft post
 */
function silaju_ai_plugin_create_draft_post() {
    check_ajax_referer( 'silaju_create_draft_nonce', 'nonce' );
    
    $title = sanitize_text_field( $_POST['title'] );
    $content = wp_kses_post( $_POST['content'] );
    $selected_tags = isset( $_POST['selected_tags'] ) ? json_decode( stripslashes( $_POST['selected_tags'] ), true ) : array();
    
    if ( empty( $title ) || empty( $content ) ) {
        wp_send_json_error( 'Title and content are required.' );
    }
    
    // Get tag IDs from slugs/names
    $tag_ids = array();
    foreach ( $selected_tags as $tag_slug ) {
        $tag = get_term_by( 'slug', sanitize_title( $tag_slug ), 'post_tag' );
        if ( $tag ) {
            $tag_ids[] = $tag->term_id;
        }
    }
    
    // Create the post
    $post_data = array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'draft',
        'post_type'    => 'post',
        'tags_input'   => $tag_ids
    );
    
    $post_id = wp_insert_post( $post_data );
    
    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( 'Failed to create post: ' . $post_id->get_error_message() );
    }
    
    $edit_link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
    
    wp_send_json_success( array(
        'post_id' => $post_id,
        'edit_link' => $edit_link,
        'message' => 'Draft post created successfully!'
    ) );
}
add_action( 'wp_ajax_silaju_create_draft_post', 'silaju_ai_plugin_create_draft_post' );
