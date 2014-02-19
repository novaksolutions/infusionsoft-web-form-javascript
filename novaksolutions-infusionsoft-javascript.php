<?php

/*
Plugin Name: Infusionsoft® Web Form JavaScript
Plugin URI: http://novaksolutions.com/wordpress-plugins/infusionsoft-webform-javascript/
Description: Easily insert Infusionsoft® web forms into your posts or pages.
Author: Novak Solutions
Version: 1.1.1
Author URI: http://novaksolutions.com/
*/

function novaksolutions_wf_shortcode($attributes) {
    $return = '';

    if (!empty($attributes['css'])) {
        $return .= "<div class=\"javascript-container\" style=\"{$attributes['css']}\"><script type=\"text/javascript\" src=\"{$attributes['src']}\"></script></div>";

    } else {
        $return .= "<script type=\"text/javascript\" src=\"{$attributes['src']}\"></script>";
    }

    return $return;
}
add_shortcode(get_option('novaksolutions_wf_setting_shortcode', 'javascript'), 'novaksolutions_wf_shortcode');


function novaksolutions_wf_replace_js($data, $postarr = null) {
    // Define string parts that we are looking to replace
    $search = array(
        '&lt;script type=\"text/javascript\" src=\"',
        '\"&gt;&lt;/script&gt;',
    );
    
    // Define replacement string parts
    $replace = array(
        '[' . get_option('novaksolutions_wf_setting_shortcode', 'javascript') . ' src="',
        '"/]',
    );
    
    // Perform search/replace
    $data['post_content'] = str_replace($search, $replace, $data['post_content']);
    
    return $data;
}
if (get_option('novaksolutions_wf_setting_autoreplace', 1)) {
    add_filter('wp_insert_post_data', 'novaksolutions_wf_replace_js');
}


function novaksolutions_wf_plugin_action_links( $links, $file ) {
    if ( $file == plugin_basename( dirname(__FILE__).'/novaksolutions-infusionsoft-javascript.php' ) ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=novaksolutions-wf-settings' ) . '">'.__( 'Settings' ).'</a>';
    }

    return $links;
}
add_filter('plugin_action_links', 'novaksolutions_wf_plugin_action_links', 10, 2);

function novaksolutions_wf_admin_menu() {
    $page_title = 'Infusionsoft® Web Form JavaScript Settings';
    $menu_title = 'Web Form JavaScript';
    $capability = 'manage_options';
    $menu_slug = 'novaksolutions-wf-settings';
    $function = 'novaksolutions_wf_settings';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}
add_action('admin_menu', 'novaksolutions_wf_admin_menu');

function novaksolutions_wf_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    echo '<h2>Infusionsoft® Web Form JavaScript Settings</h2>';
    
    echo '<form method="POST" action="options.php">';
    settings_fields('novaksolutions-wf-settings');   //pass slug name of page
    do_settings_sections('novaksolutions-wf-settings');    //pass slug name of page
    submit_button();
    echo '</form>';
    ?>

    <h3>Usage Instructions</h3>
    <p>If you've enabled auto-replace above, then simply paste the web form Javascript Snippet into your post or page. When you publish or update your post, the snippet will be converted into the appropriate shortcode. Be sure to paste it into the Visual editor and not the Text editor.</p>
    <p>Alternatively, you can use a shortcode. Get the URL from the Javascript Snippet and put it in a shortcode like this:</p>
    <pre>[<?php echo get_option('novaksolutions_wf_setting_shortcode', 'javascript'); ?> src="https://example.infusionsoft.com/app/form/iframe/5d07ccaa3e9ab94dea1f6982da9fb266"/]</pre>

    <h3>Like this plugin?</h3>
    <p>Visit <a href="http://novaksolutions.com/?utm_source=wordpress&utm_medium=link&utm_campaign=wf">Novak Solutions</a> to find dozens of free tips, tricks, and tools to help you get the most out of Infusionsoft®.</p>

    <?php
}


function novaksolutions_wf_settings_api_init() {
    // Add the section to reading settings so we can add our
    // fields to it
    add_settings_section('novaksolutions_wf_setting_section',
        null,
        null,
        'novaksolutions-wf-settings');
    
    // Add the field with the names and function to use for our new
    // settings, put it in our new section
    add_settings_field('novaksolutions_wf_setting_shortcode',
        'Shortcode',
        'novaksolutions_wf_setting_callback_function_shortcode',
        'novaksolutions-wf-settings',
        'novaksolutions_wf_setting_section');

    add_settings_field('novaksolutions_wf_setting_autoreplace',
        'Auto Replace',
        'novaksolutions_wf_setting_callback_function_autoreplace',
        'novaksolutions-wf-settings',
        'novaksolutions_wf_setting_section');
    
    // Register our setting so that $_POST handling is done for us and
    // our callback function just has to echo the <input>
    register_setting('novaksolutions-wf-settings','novaksolutions_wf_setting_shortcode', 'novaksolutions_wf_setting_shortcode_sanitize');
    register_setting('novaksolutions-wf-settings','novaksolutions_wf_setting_autoreplace');
}

add_action('admin_init', 'novaksolutions_wf_settings_api_init');

function novaksolutions_wf_setting_shortcode_sanitize($string) {
    // Shortcode should, ideally, consist ONLY of lowercase letters.
    $string = preg_replace( '/[^a-z]/i', "", sanitize_title($string, 'javascript'));
    if(empty($string)) {
        $string = 'javascript';
    }

    return $string;
}

function novaksolutions_wf_setting_callback_function_shortcode() {
    echo '<input name="novaksolutions_wf_setting_shortcode" type="text" value=" ' . get_option('novaksolutions_wf_setting_shortcode', 'javascript') . '" class="code" /> <p class="description">You can change the default shortcode, <strong>javascript</strong>, to something else if needed. This doesn\'t update any posts or pages that are currently using the original shortcode.</p>';
}

function novaksolutions_wf_setting_callback_function_autoreplace() {
    echo '<input name="novaksolutions_wf_setting_autoreplace" type="checkbox" value="1" class="code" ' . checked( 1, get_option('novaksolutions_wf_setting_autoreplace', 1), false ) . ' /> Automatically convert JavaScript to use the shortcode';
}

