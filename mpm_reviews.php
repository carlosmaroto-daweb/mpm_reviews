<?php
    /**
     * Plugin Name
     *
     * @package     ReviewsPackage
     * @author      Carlos Maroto
     * @copyright   2023 Photo Tour
     * @license     GPL-2.0-or-later
     *
     * @wordpress-plugin
     * Plugin Name: Reviews
     * Plugin URI:  https://example.com/plugin-name
     * Description: Workshops Photo Tours by Dream Photo Expeditions. All our Photography Tours and Workshops are organized by our own official Travel Agency.
     * Version:     1.0.0
     * Author:      Carlos Maroto
     * Author URI:  https://example.com
     * Text Domain: plugin-slug
     * License:     GPL v2 or later
     * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
     */
    
    // Evitar que un usuario ejecute el plugin tecleando su url
    defined('ABSPATH') or die('Hey Bro! You cannot access this file... twat!');
    
    class Reviews {
        
        function execute_actions() {
            
        }
        
    }
    
    $review = new Reviews();
    $review->execute_actions();