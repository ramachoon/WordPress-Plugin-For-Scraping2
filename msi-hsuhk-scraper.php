<?php

/**

 * Plugin Name

 *

 * @package           HSUHK MSI Scraping Tool

 * @author            Software Engineer

 * @copyright         2021 SoftwareEngineer

 * @license           GPL-2.0-or-later

 *

 * @wordpress-plugin

 * Plugin Name:       HSUHK MSI Scraping Tool
 * Plugin URI:        https://example.com/hsuskscrape 

 * Description:       Tool for scrapping new from hhttps://msi.hsu.edu.hk/en/news-and-announcement/

 * Version:           1.0.0

 * Requires at least: 5.4

 * Requires PHP:      7.2

 * Author:            Software Engineer

 * Author URI:        https://example.com

 * Text Domain:       hsuskscrape

 * License:           GPL v2 or later

 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt

 */

// Prohibit direct script loading.

defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

define('MSI_TABLE', 'msi_descriptions');

define('MSI_ROOT_PATH', plugin_dir_path(__FILE__));

define('MSI_ROOT_URL', plugin_dir_url(__FILE__));

if (!defined('MSI_BASENAME')) {

    define('MSI_BASENAME', plugin_basename(__FILE__));

}


function msi_scrape_load() {

    require_once(MSI_ROOT_PATH . 'msi_functions.php');

	if (is_admin()) {

        require_once(MSI_ROOT_PATH . 'msi_admin.php');

    }

}


/********

 * Hooks *

 ********/

 register_activation_hook(__FILE__, 'msiActivation');

 register_deactivation_hook(__FILE__, 'msiDeactivation');
 
 register_uninstall_hook(__FILE__, 'msiUninstall');
 
 
 
 msi_scrape_load();
 
 ?>
 