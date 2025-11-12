jQuery(document).ready(function($) {
    // We expect the variable 'silaju_ai_ajax_object' to be defined globally by WordPress.
    var ajax_object = typeof silaju_ai_ajax_object !== 'undefined' ? silaju_ai_ajax_object : {};

    // Function to check if a nonce is present and warn the user
    function checkAndWarnNonce(nonceValue, statusElement, buttonElement, type) {
        if (!nonceValue || nonceValue.length < 5) {
            var message = 'Security Error (' + type + ' Nonce Missing). Please perform a **hard refresh** (Ctrl+Shift+R or Cmd+Shift+R) of this page to fix the security token and enable the button.';
            
            // Display error status
            statusElement.removeClass('loading success').addClass('error').html(message).show();
            
            // Disable button
            buttonElement.prop('disabled', true).text('Security Error - Refresh Required');
            
            console.error(type + " Nonce is missing or invalid in localized data. Requires page refresh.");
            return false;
        }
        return true;
    }

    // Text Generation
    $('#gemini-ai-generate-text-btn').on('click', function() {
        console.log("Button clicked. Attempting AJAX..."); 

        var $button = $(this);
        var $status = $('#gemini-ai-text-status');
        var $output = $('#gemini-ai-generated-text');
        var prompt = $('#gemini-ai-text-prompt').val();

        $status.removeClass('success error').addClass('loading').text('Generating text... Please wait.').show();
        $button.prop('disabled', true);
        $output.val('');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'silaju_ai_generate_text', // (New AJAX Action Name)
                nonce: ajax_object.nonce_text_gen,
                prompt: prompt
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass('loading error').addClass('success').text('Text generated successfully!').show();
                    $output.val(response.data.generated_text);
                } else {
                    $status.removeClass('loading success').addClass('error').text('Error: ' + (response.data || 'An unknown error occurred.')).show();
                }
            },
            error: function(xhr, status, error) {
                $status.removeClass('loading success').addClass('error').text('AJAX Error: ' + error).show();
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    $('#gemini-ai-copy-text-btn').on('click', function() {
        var $output = $('#gemini-ai-generated-text');
        $output.select();
        document.execCommand('copy');
        alert('Content copied to clipboard!');
    });


    // Image Generation
    $('#gemini-ai-generate-image-btn').on('click', function() {
        console.log("Image Generation Button clicked. Attempting AJAX...");

        var $button = $(this);
        var $status = $('#gemini-ai-image-status');
        var $output = $('#gemini-ai-generated-images');
        var prompt = $('#gemini-ai-image-prompt').val();
        var numImages = $('#gemini-ai-num-images').val();

        if (!prompt.trim()) {
            $status.removeClass('loading success').addClass('error').text('Please enter a prompt.').show();
            return;
        }

        console.log("DEBUG: Nonce for image generation is:", ajax_object.nonce_image_gen);

        $status.removeClass('success error').addClass('loading').text('Generating ' + numImages + ' images... This may take up to 90 seconds.').show();
        $button.prop('disabled', true);
        $output.html('<p class="loading-message">Generating images... please wait.</p>');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'silaju_ai_generate_image',
                nonce: ajax_object.nonce_image_gen,
                prompt: prompt,
                num_images: numImages
            },
            success: function(response) {
                $button.prop('disabled', false);
                $output.empty(); // Clear loading message

                if (response.success) {
                    $status.removeClass('loading error').addClass('success').text('Images generated successfully!').show();
                    
                    if (response.data.generated_images_data && response.data.generated_images_data.length > 0) {
                        $.each(response.data.generated_images_data, function(index, image_data) {
                            var base64Data = image_data.base64_image;
                            var originalPrompt = image_data.prompt;

                            // Create a sanitized title from the prompt for the save button
                            var sanitizedTitle = originalPrompt.substring(0, 50).replace(/[^a-zA-Z0-9\s]/g, '').trim() || 'gemini-image-' + (index + 1);

                            var imageHtml = `
                                <div class="generated-image-container">
                                    <img src="${base64Data}" alt="${sanitizedTitle}" class="generated-image">
                                    <div class="image-prompt-preview"><strong>Prompt:</strong> ${originalPrompt}</div>
                                    <button class="button button-secondary silaju-save-image-btn" 
                                            data-base64="${base64Data}" 
                                            data-title="${sanitizedTitle}">
                                        <span class="dashicons dashicons-download"></span> Add to Media Library
                                    </button>
                                    <span class="save-status"></span>
                                </div>
                            `;
                            $output.append(imageHtml);
                        });
                    } else {
                        $output.html('<p>No images were returned by the API.</p>');
                    }
                } else {
                    $status.removeClass('loading success').addClass('error').text('Error: ' + (response.data.message || 'An unknown error occurred.')).show();
                    $output.html('<p class="error-message">Failed to generate images. Check the API status above.</p>');
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false);
                $status.removeClass('loading success').addClass('error').text('AJAX Error: ' + error).show();
                $output.html('<p class="error-message">AJAX connection failed.</p>');
                console.error('AJAX Error:', status, error, xhr.responseText);
            }
        });
    });

    // Save Image Handler (Delegated event handling for dynamically created buttons)
    $('#gemini-ai-generated-images').on('click', '.silaju-save-image-btn', function(e) {
        e.preventDefault();

        var $button = $(this);
        if ($button.data('status') === 'saved') return; // Prevent double save

        var $statusSpan = $button.next('.save-status');
        var base64Data = $button.data('base64');
        var imageTitle = $button.data('title');

        $button.addClass('loading').text('Saving...');
        $statusSpan.removeClass('success error').text('');

        $.ajax({
            url: ajax_object.ajax_url, 
            type: 'POST',
            data: {
                action: 'silaju_save_image', 
                nonce: ajax_object.nonce_save_image, 
                image_data: base64Data,
                image_title: imageTitle
            },
            success: function(response) {
                $button.removeClass('loading');
                if (response.success) {
                    $button.html('<span class="dashicons dashicons-yes"></span> Saved');
                    $button.data('status', 'saved').prop('disabled', true); // Disable after success
                    $statusSpan.addClass('success').text('Success! ID: ' + response.data.attachment_id);
                    console.log('Image URL:', response.data.url);
                } else {
                    $button.html('<span class="dashicons dashicons-download"></span> Add to Media Library'); // Reset button text
                    $statusSpan.addClass('error').text('Error: ' + response.data.message);
                    $button.data('status', 'error');
                }
            },
            error: function(xhr, status, error) {
                $button.removeClass('loading').html('<span class="dashicons dashicons-download"></span> Add to Media Library'); // Reset button text
                $statusSpan.addClass('error').text('AJAX Error: ' + error);
                $button.data('status', 'error');
                console.error('AJAX Error:', status, error);
            }
        });
    });

    // Load tags on page load
    function loadTags() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'silaju_get_tags',
                nonce: ajax_object.nonce_get_tags
            },
            success: function(response) {
                if (response.success && response.data.tags) {
                    var $container = $('#tag-list-container');
                    $container.empty();
                    
                    if (response.data.tags.length === 0) {
                        $container.html('<p>No tags found. Please create some tags first.</p>');
                        return;
                    }
                    
                    $.each(response.data.tags, function(index, tag) {
                        var checkboxHtml = `
                            <label class="tag-checkbox-item">
                                <input type="checkbox" name="selected_tags[]" value="${tag.slug}" data-tag-id="${tag.id}">
                                ${tag.name}
                            </label>
                        `;
                        $container.append(checkboxHtml);
                    });
                } else {
                    $('#tag-list-container').html('<p>Failed to load tags.</p>');
                }
            },
            error: function() {
                $('#tag-list-container').html('<p>Error loading tags.</p>');
            }
        });
    }
    
    // Load tags when the page loads (only on text generation page)
    if ($('#tag-list-container').length) {
        loadTags();
    }

    // Suggest Headlines Button
    $('#gemini-ai-suggest-headlines-btn').on('click', function() {
        var $button = $(this);
        var $headlinesSection = $('#suggested-headlines-section');
        var $headlinesList = $('#suggested-headlines-list');
        
        // Get selected tags
        var selectedTags = [];
        $('input[name="selected_tags[]"]:checked').each(function() {
            selectedTags.push($(this).val());
        });
        
        if (selectedTags.length === 0) {
            alert('Please select at least one tag to get headline suggestions.');
            return;
        }
        
        $button.prop('disabled', true).text('Loading Headlines...');
        $headlinesList.html('<li>Loading...</li>');
        $headlinesSection.show();
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'silaju_suggest_headlines',
                nonce: ajax_object.nonce_suggest_headlines,
                selected_tags: JSON.stringify(selectedTags)
            },
            success: function(response) {
                $button.prop('disabled', false).text('Suggest Headlines');
                
                if (response.success && response.data.headlines) {
                    $headlinesList.empty();
                    $.each(response.data.headlines, function(index, headline) {
                        $headlinesList.append('<li>' + headline + '</li>');
                    });
                } else {
                    $headlinesList.html('<li>Error: ' + (response.data || 'Failed to fetch headlines') + '</li>');
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false).text('Suggest Headlines');
                $headlinesList.html('<li>AJAX Error: ' + error + '</li>');
            }
        });
    });

    // Create Draft Post Button
    $('#gemini-ai-create-draft-btn').on('click', function() {
        var $button = $(this);
        var content = $('#gemini-ai-generated-text').val();
        var prompt = $('#gemini-ai-text-prompt').val();
        
        if (!content.trim()) {
            alert('Please generate content first before creating a draft post.');
            return;
        }
        
        // Get selected tags
        var selectedTags = [];
        $('input[name="selected_tags[]"]:checked').each(function() {
            selectedTags.push($(this).val());
        });
        
        // Use first line of content or prompt as title
        var title = content.split('\n')[0].substring(0, 100) || prompt.substring(0, 100) || 'Untitled Post';
        title = title.replace(/^#+\s*/, ''); // Remove markdown headers
        
        $button.prop('disabled', true).text('Creating Draft...');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'silaju_create_draft_post',
                nonce: ajax_object.nonce_create_draft,
                title: title,
                content: content,
                selected_tags: JSON.stringify(selectedTags)
            },
            success: function(response) {
                $button.prop('disabled', false).text('Create Draft Post');
                
                if (response.success) {
                    alert(response.data.message + '\n\nPost ID: ' + response.data.post_id);
                    // Optionally open the edit link
                    if (confirm('Would you like to edit the post now?')) {
                        window.open(response.data.edit_link, '_blank');
                    }
                } else {
                    alert('Error: ' + (response.data || 'Failed to create draft post'));
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false).text('Create Draft Post');
                alert('AJAX Error: ' + error);
            }
        });
    });


    // Get Tag Button - Predict tags from generated content
    $('#gemini-ai-get-tag-btn').on('click', function() {
        var $button = $(this);
        var content = $('#gemini-ai-generated-text').val();
        
        if (!content.trim()) {
            alert('Please generate content first before predicting tags.');
            return;
        }
        
        $button.prop('disabled', true).text('Analyzing...');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'silaju_predict_tags',
                nonce: ajax_object.nonce_predict_tags,
                content: content
            },
            success: function(response) {
                $button.prop('disabled', false).text('Get Tag');
                
                if (response.success && response.data.predicted_tags) {
                    // First, uncheck all checkboxes
                    $('input[name="selected_tags[]"]').prop('checked', false);
                    
                    // Then check the predicted tags
                    var checkedCount = 0;
                    $.each(response.data.predicted_tags, function(index, tagSlug) {
                        var $checkbox = $('input[name="selected_tags[]"][value="' + tagSlug + '"]');
                        if ($checkbox.length) {
                            $checkbox.prop('checked', true);
                            checkedCount++;
                        }
                    });
                    
                    if (checkedCount > 0) {
                        alert('Successfully predicted and selected ' + checkedCount + ' tag(s)!');
                    } else {
                        alert('Tags were predicted but could not be found in your WordPress tags. Please create tags matching: ' + response.data.predicted_tags.join(', '));
                    }
                    
                    // Log raw response for debugging
                    console.log('Prediction response:', response.data.raw_response);
                } else {
                    alert('Error: ' + (response.data || 'Failed to predict tags'));
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false).text('Get Tag');
                alert('AJAX Error: ' + error);
                console.error('Prediction error:', xhr.responseText);
            }
        });
    });
        
});