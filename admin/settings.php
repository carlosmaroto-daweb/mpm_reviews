<h1>Reviews Settings</h1>

<div class="shortcodes-usage">
    <h3>Shortcodes usage for displaying custom-post-fields:</h3>
    <h4>Use this shortcode for custom fields resume in custom-post-type blog template:</h4>
    <blockquote>
        <h3><pre>&lt;?php do_shortcode('[mpm_show_main_fields id="'.$post->ID.'"]'); ?&gt;</pre></h3>
    </blockquote>
    <h4>Use this shortcode for displaying all custom-post-fields:</h4>
    <blockquote>
        <h3><pre>&lt;?php do_shortcode('[mpm_show_all_fields id="'.$post->ID.'"]'); ?&gt;</pre></h3>
    </blockquote>
</div>

<form method="post" action="options.php">
    <?php
        // Especificamos el nombre bajo el que estarán registrados los settings
        settings_fields('reviews_settings');
        // Especificamos el grupo de settings
        do_settings_sections('reviews_settings');
        // Harvesting de los settings
        $options = get_option('reviews_settings');  // Obtenemos el array con todos los settings
        $yescheck = '';
        $nocheck = '';
        if(isset($options['mpm_allowrating']) && $options['mpm_allowrating'] == "yes") {
            $yescheck = "checked";
        } else {
            $nocheck = "checked";
        }
    ?>
    <div class="settings">
        <h3>Customize your plugin</h3>
        <label for="mpm_color">Color:</label>
        <input type="color" id="mpm_color" name="reviews_settings[mpm_color]" value="<?php echo $options['mpm_color'];?>">
        <br/><br/>
        
        <label for="mpm_num_tuplas">Posts per page:</label>
        <input type="number" id="mpm_num_tuplas" size="2" name="reviews_settings[mpm_num_tuplas]" value="<?php echo $options['mpm_num_tuplas'];?>">
        
        <br/><br/>
        <label for="mpm_allowrating">Allow Rating:</label>
        <input type="radio" id="mpm_allowrating" name="reviews_settings[mpm_allowrating]" value="yes" <?php echo $yescheck;?>> YES
        <input type="radio" id="mpm_allowrating" name="reviews_settings[mpm_allowrating]" value="no" <?php echo $nocheck;?>> NO
        <br/><br/>
        <input type="submit" name="submit" class="button button-primary" value="Save Settings"/>
    </div>
</form>