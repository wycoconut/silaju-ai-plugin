<div class="wrap gemini-ai-wrap">
    <h1>Gemini AI Image Generation</h1>
    <div class="gemini-ai-grid">
        <div class="gemini-ai-sidebar">
            <h2>Image Prompt</h2>
            <textarea id="gemini-ai-image-prompt" rows="10" placeholder="Create an image in the style of a Ghibli-inspired anime oil painting, depicting a quaint, old house in the Japanese countryside. This house, reminiscent of a traditional Izakaya, is surrounded by lush trees and overlooks vibrant rice fields. The scene is bathed in the warm, soft glow of a setting sun, casting gentle shadows and creating a tranquil, serene atmosphere."></textarea>
            <label for="gemini-ai-num-images">Number of Images (1-4):</label>
            <input type="number" id="gemini-ai-num-images" value="1" min="1" max="4" class="small-text">
            <button id="gemini-ai-generate-image-btn" class="button button-primary button-large">Generate Images</button>
            <div id="gemini-ai-image-status" class="gemini-ai-status"></div>
        </div>
        <div class="gemini-ai-content">
            <h2>Generated Images</h2>
            <!-- Removed the prototype note since we are now calling an image generation model -->
            <div id="gemini-ai-generated-images" class="image-gallery">
                <p>Generated images will appear here.</p>
            </div>
        </div>
    </div>
</div>