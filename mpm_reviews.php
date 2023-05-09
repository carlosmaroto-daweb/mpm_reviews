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
            // Registramos nuestro custom-post-type, tiene que estar disponible desde el inicio
            add_action('init', array($this, 'mpm_register_review'));
            
            // Crear la metabox que contendrá los custom-post-fields del CPT
            add_action('add_meta_boxes', array($this, 'mpm_add_metabox'));
            
            // Añadir hojas de estilo y JS al back-end de nuestro plugin
            add_action('admin_enqueue_scripts', array($this, 'mpm_admin_enqueue_scripts'));
        }
        
        /**
         * Función que configura y registra nuestro custom-post-type reviews
         */
        function mpm_register_review() {
            $supports = array(
                'title',          // Activamos el panel para introducir el título del CPT
                'editor',         // Activamos el panel para editar el contenido del CPT
                'excerpt',        // Activamos el panel para introducir el extracto del CPT
                'thumbnail',      // Activamos el panel para la imagen representativa del CPT
                'author',         // Activamos el panel para seleccionar el autor del CPT
                'comments',       // Activamos el panel para poder seleccionar si habilitamos o no comentarios del CPT
                //'custom-fields',  // Activamos el panel para introducir campos adicionales al CPT
            );
            $labels = array(
                'name'           => _x('Reviews', 'plural'),
                'singular_name'  => _x('Review', 'singular'),
                'menu_name'      => _x('Reviews', 'admin menu'),
                'menu_admin_bar' => _x('Reviews', 'admin bar'),
                'add_new'        => _x('Add New Review', 'add_new'),
                'all_items'      => __('All Reviews'),
                'add_new_item'   => __('Add New Review'),
                'view_item'      => __('View Reviews'),
                'search'         => __('Search Review'),
                'not_found'      => __('No Reviews found...'),
            );
            $args = array(
                // Argumentos de tipo array
                'supports'      => $supports,  // Se usa para indicar los paneles del CPT en el admin area - los soportes
                'labels'        => $labels,    // Personaliza las etiquetas que aparecen en el admin area para ese CPT
                
                // Parametros individuales
                'public'        => true,                            // Hacemos visible nuestro CPT tanto para el admin area y el front-end
                'wp_query'      => true,                            // Nuestro CPT será accesible mediante la clase WP_Query()
                'show_in_menu'  => true,                            // Queremos que aparezca una opción en el menu del admin area para manejar nuestro CPT
                'menu_position' => 5,                               // Posición de la opción del CPT en el menu del admin area
                'hierarchical'  => false,                           // No tendremos post derivados de nuestro CPT
                'show_in_rest'  => true,                            // TRUE-> Editor Gutemberg, FALSE-> Editor tradicional de WP
                'has_archive'   => true,                            // El CPT apareceran en nuestro archive.php y search.php
                'menu_icon'     => 'dashicons-smiley',              // Clase css para que aparezca el icono asociado a la opción del menú
                'rewrite'       => array('slug' => 'mpm-reviews'),  // El slug de mi CPT
                
                // PENDIENTE!!!!!!!!
                //'query_var'     => true, // Las variables de la query accesibles a través de la función query_var
                //'taxonomies'  => array('post_tag', 'category'), // Activo las categorias y tags de los post normales para el CPT
            );
            register_post_type('mpm_reviews', $args);
            
            flush_rewrite_rules();
        }
        
        /**
         * Función que crea una metabox para albergar los custom-post-fields
         * @param $screens object de la clase WP_Screen
         */
        function mpm_add_metabox($screens) {
            // En todas las pantallas donde aparezca mi CPT deberemos crear la metabox de los custom-post-fields
            $screens = array('mpm_reviews'); // Se hace un filtro con las pantallas donde aparece el CPT
            foreach($screens as $screen) {
                // <id de la metabox>, <Título de la metabox>, <función de callback>, <pantalla>, <contexto>
                add_meta_box('review', 'PhotoTour Reviews', array($this, 'mpm_reviews_metabox_callback'), $screen, 'advanced');
            }
        }
        
        /**
         * Función que dibuja los custom-post-fields y gestiona la seguridad
         * @param $review object tipo review
         */
        function mpm_reviews_metabox_callback($review) {
            // Nos debemos asegurar de que todas las peticiones se realizan desde nuestro sitio web creando un campo nonce
            //  <fichero que hace la petición>, <nombre del campo nonce>
            wp_nonce_field(basename(__FILE__), 'mpm_review_nonce');
            
            // Data harvesting
            $numpistas      = get_post_meta($review->ID, 'mpm_numpistas', true);
            $totalkms       = get_post_meta($review->ID, 'mpm_$totalkms', true);
            $negras         = get_post_meta($review->ID, 'mpm_negras', true);
            $rojas          = get_post_meta($review->ID, 'mpm_rojas', true);
            $azules         = get_post_meta($review->ID, 'mpm_azules', true);
            $verdes         = get_post_meta($review->ID, 'mpm_verdes', true);
            $totalremontes  = get_post_meta($review->ID, 'mpm_totalremontes', true);
            $parking        = get_post_meta($review->ID, 'mpm_parking', true);
            $tarifa         = get_post_meta($review->ID, 'mpm_tarifa', true);
            $anio           = get_post_meta($review->ID, 'mpm_anio', true);
            $rating         = get_post_meta($review->ID, 'mpm_rating', true);
            
            $horario        = get_post_meta($review->ID, 'mpm_horario', true);
            
            // Dibujamos los custom-post-fielsa xon etiquetas HTML
            ?>
                <div class="flex-container">
                    <div class="generic">
                        <h4>Generic Data</h4>
                        <div class="custom-field">
                            <label for="numpistas">Número de Pistas:</label>
                            <input type="text" id="numpistas" name="mpm_numpistas" value="<?php echo $numpistas;?>"/>
                        </div>
                        <div class="custom-field">
                            <label for="totalkms">Total KMs:</label>
                            <input type="text" id="totalkms" name="mpm_totalkms" value="<?php echo $totalkms;?>"/>
                        </div>
                    </div>
                    <div class="horario">
                        <h4>Horarios</h4>
                    </div>
                </div>
            <?php
        }
        
        /**
         * Función que añade JS y hojas de estilo al admin área de mi CPT
         */
        function mpm_admin_enqueue_scripts() {
            wp_register_style('mpm_admin_styles', plugins_url('/admin/css/admin.css', __FILE__));
            wp_enqueue_style('mpm_admin_styles');
        }
    }
    
    $review = new Reviews();
    $review->execute_actions();