<div class="wrap gemini-ai-wrap">
    <h1>Gemini AI Text Generation</h1>
    <div class="gemini-ai-grid">
        <div class="gemini-ai-sidebar">
            <h2>What Do You Want to Write Today ?</h2>
            <p>Use the input field below to provide a topic or prompt for your article.</p>
            <textarea id="gemini-ai-text-prompt" rows="10" placeholder="e.g., 5 hidden Tokyo neighborhoods only locals know: retro Showa-era streets, authentic Edo atmosphere, family-run restaurants, old shopping arcades, quiet residential gems. Write in first person as a Tokyo local sharing secret spots."></textarea>
            <button id="gemini-ai-generate-text-btn" class="button button-primary button-large">Generate Article</button>
            <div id="gemini-ai-text-status" class="gemini-ai-status"></div>
            
            <!-- NEW: Tag Selection Area -->
            <div class="tag-selection-box">
                <h3>Tags</h3>
                <div id="tag-list-container" class="tag-list">
                    <p class="loading-tags">Loading tags...</p>
                </div>
            </div>
        </div>
        
        <div class="gemini-ai-content">
            <h2>Generated Content</h2>
            <textarea id="gemini-ai-generated-text" rows="20" placeholder="Generated article will appear here..."></textarea>
            
            <!-- NEW: Headlines Section -->
            <div id="suggested-headlines-section" style="display:none; margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
                <h3>Suggested Headlines:</h3>
                <ul id="suggested-headlines-list"></ul>
            </div>
            
            <div class="button-group">
                <button id="gemini-ai-copy-text-btn" class="button button-secondary">Copy to Clipboard</button>
                <button id="gemini-ai-suggest-headlines-btn" class="button button-primary" style="background: #e91e63;">Suggest Headlines</button>
                <button id="gemini-ai-create-draft-btn" class="button button-primary" style="background: #ff9800;">Create Draft Post</button>
            </div>
        </div>
    </div>
</div>