<div class="wrap">
    <h1>Silaju AI Plugin Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'silaju_ai_plugin_settings_group' ); // (New Option Group Name)
        do_settings_sections( 'silaju-ai-plugin' ); // (New Menu Slug)
        submit_button();
        ?>
    </form>
</div>