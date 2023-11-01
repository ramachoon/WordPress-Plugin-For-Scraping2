<?php

defined('ABSPATH') or die("Cannot access pages directly.");

function msiAdminMenu() {    
    add_menu_page(        
        __('MSI-HSUHK Scraping Tool', 'wp-msi-scrapping-news'),
        __('MSI-HSUHK Scraping Tool', 'wp-msi-scrapping-news'),
        'manage_options',
        'msi-administration',
        'msiSetting',
        'dashicons-admin-page'
    );        
}
add_action('admin_menu', 'msiAdminMenu');


add_action( 'admin_enqueue_scripts', 'msiIncludeJS' );

function msiIncludeJS() {
    $version = time();
    $upload_dir = wp_upload_dir();
    $uploads_url = $upload_dir['baseurl'];
    wp_register_script('msi-script-js', MSI_ROOT_URL.'msi_script.js', '', $version);
    wp_enqueue_script('msi-script-js');  
    wp_register_style( 'msi-style-css', MSI_ROOT_URL.'msi_style.css', '', $version );
    wp_enqueue_style( 'msi-style-css' );
    wp_localize_script(
        'msi-script-js', // the handle of the script we enqueued above
        'msi_script_vars', // object name to access our PHP variables from in our script
        // register an array of variables we would like to use in our script
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'msi_update_news_nonce' => wp_create_nonce('msi_update_news_nonce'),
            'update_events_nonce' => wp_create_nonce('msi_update_events_nonce'),
            'save_new_post_nonce' => wp_create_nonce('msi_save_new_post_nonce'),
        )
    );
}


/* Generic message display */
function msiGetMessage($message) {
    if($message) {
        return '<div id="message" class="'.$message['type'].'" style="display:block !important"><p>'.$message['content'].'</p></div>';
    }
    return '';
}


function msiSetting() {

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $sFile = MSI_ROOT_PATH . 'page.inc.php';


    ob_start();
        include( $sFile );
        $sContents = ob_get_contents();
    ob_end_clean();

    // filter content before output
    $sContents = apply_filters( 'msi_admin_page_content', $sContents );
    echo $sContents;
}