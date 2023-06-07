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
        
        function __construct() {
            add_shortcode('mpm_show_main_fields', array($this, 'mpm_show_main_fields_shortcode'));
            add_shortcode('mpm_show_fields', array($this, 'mpm_show_fields_shortcode'));
        }
        
        function execute_actions() {
            // Registramos nuestro custom-post-type, tiene que estar disponible desde el inicio
            add_action('init', array($this, 'mpm_register_review'));
            
            // Crear la metabox que contendrá los custom-post-fields del CPT
            add_action('add_meta_boxes', array($this, 'mpm_add_metabox'));
            
            // Guardar el contenido de los custom-post-fields en la BBDD
            add_action('save_post', array($this, 'mpm_save_custom_fields'));
            
            // Incorporar nuestro custom post type a las consultas por defecto de WP
            add_action('pre_get_posts', array($this, 'mpm_pre_get_posts'));
            
            // Añadir una página de settings a nuestro plugin en el admin-area
            add_action('admin_menu', array($this, 'mpm_reviews_admin_page'));
            
            // Registrar los settings de nuestra pagina de settings
            add_action('admin_init', array($this, 'mpm_reviews_settings_register'));
            
            // Activar el lanzamiento de errores en los settings
            add_action('admin_notices', array($this, 'mpm_reviews_settings_admin_notices'));
            
            // Añadir hojas de estilo y JS al front-end de nuestro plugin
            add_action('wp_enqueue_scripts', array($this, 'mpm_front_enqueue_scripts'));
            add_action('wp_enqueue_scripts', array($this, 'mpm_reviews_css_injection'));
            
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
                'query_var'     => true,                            // Las variables de la query accesibles a través de la función query_var
                'show_in_menu'  => true,                            // Queremos que aparezca una opción en el menu del admin area para manejar nuestro CPT
                'menu_position' => 5,                               // Posición de la opción del CPT en el menu del admin area
                'hierarchical'  => false,                           // No tendremos post derivados de nuestro CPT
                'show_in_rest'  => true,                            // TRUE-> Editor Gutemberg, FALSE-> Editor tradicional de WP
                'menu_icon'     => 'dashicons-smiley',              // Clase css para que aparezca el icono asociado a la opción del menú
                'rewrite'       => array('slug' => 'mpm-reviews'),  // El slug de mi CPT
                'has_archive'   => true,                            // El CPT apareceran en nuestro archive.php y search.php
            );
            register_post_type('mpm_reviews', $args);
            
            // Activar los paneles de categorías y tgas compartidas con los posts normales
            register_taxonomy_for_object_type('category', 'mpm_reviews');
            register_taxonomy_for_object_type('post_tag', 'mpm_reviews');
            
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
            $from               = get_post_meta($review->ID, 'mpm_from', true);
            $to                 = get_post_meta($review->ID, 'mpm_to', true);
            $days               = get_post_meta($review->ID, 'mpm_days', true);
            $rating             = get_post_meta($review->ID, 'mpm_rating', true);

            $percentage_title_1 = get_post_meta($review->ID, 'mpm_percentage_title_1', true);
            $percentage_1       = get_post_meta($review->ID, 'mpm_percentage_1', true);
            $percentage_title_2 = get_post_meta($review->ID, 'mpm_percentage_title_2', true);
            $percentage_2       = get_post_meta($review->ID, 'mpm_percentage_2', true);
            $percentage_title_3 = get_post_meta($review->ID, 'mpm_percentage_title_3', true);
            $percentage_3       = get_post_meta($review->ID, 'mpm_percentage_3', true);

            $photography_level  = get_post_meta($review->ID, 'mpm_photography_level', true);
            $only_adults        = get_post_meta($review->ID, 'mpm_only_adults', true);
            $price              = get_post_meta($review->ID, 'mpm_price', true);
            $max_people         = get_post_meta($review->ID, 'mpm_max_people', true);
            
            $repeteable_fields  = get_post_meta($review->ID, 'mpm_repeteable_fields', true);
            
            // Dibujamos los custom-post-fiels con etiquetas HTML
            ?>
                <div class="flex-container">
                    <div class="generic">
                        <h4>Generic Data</h4>
                        <div class="custom-field">
                            <label for="from">From:</label>
                            <input type="text" id="from" name="mpm_from" value="<?php echo $from;?>"/>
                        </div>
                        <div class="custom-field">
                            <label for="to">To:</label>
                            <input type="text" id="to" name="mpm_to" value="<?php echo $to;?>"/>
                        </div>
                        <div class="custom-field">
                            <label for="days">Days:</label>
                            <input type="text" id="days" name="mpm_days" value="<?php echo $days;?>"/>
                        </div>
                        <div class="custom-field">
                            <label for="rating">Rating:</label>
                            <input type="text" id="rating" name="mpm_rating" value="<?php echo $rating;?>"/>
                        </div>
                        <label class="percentages-title">What to expect?:</label>
                        <div class="custom-field">
                            <label for="percentage_title_1">Title 1:</label>
                            <input type="text" id="percentage_title_1" name="mpm_percentage_title_1" value="<?php echo $percentage_title_1;?>"/>
                            <label for="percentage_1">%:</label>
                            <input type="text" id="percentage_1" name="mpm_percentage_1" value="<?php echo $percentage_1;?>"/>
                            
                            <label for="percentage_title_2">Title 2:</label>
                            <input type="text" id="percentage_title_2" name="mpm_percentage_title_2" value="<?php echo $percentage_title_2;?>"/>
                            <label for="percentage_2">%:</label>
                            <input type="text" id="percentage_2" name="mpm_percentage_2" value="<?php echo $percentage_2;?>"/>
                        
                            <label for="percentage_title_3">Title 3:</label>
                            <input type="text" id="percentage_title_3" name="mpm_percentage_title_3" value="<?php echo $percentage_title_3;?>"/>
                            <label for="percentage_3">%:</label>
                            <input type="text" id="percentage_3" name="mpm_percentage_3" value="<?php echo $percentage_3;?>"/>
                        </div>
                        <div class="custom-field photography_level">
                            <label for="photography_level">Photography level:</label>
                            <input type="text" id="photography_level" name="mpm_photography_level" value="<?php echo $photography_level;?>"/>
                        </div>
                        <div class="custom-field">
                            <label for="only_adults">Only adults:</label>
                            <input type="checkbox" id="only_adults" name="mpm_only_adults" value="YES" <?php if($only_adults=="YES") echo "checked";?>/>
                        </div>
                        <div class="custom-field">
                            <label for="price">Price:</label>
                            <input type="text" id="price" name="mpm_price" value="<?php echo $price;?>"/>
                        </div>
                        <div class="custom-field">
                            <label for="max_people">Max people:</label>
                            <input type="text" id="max_people" name="mpm_max_people" value="<?php echo $max_people;?>"/>
                        </div>
                    </div>
                    <div class="itinerary">
                        <h4>Itinerary</h4>
                        <?php
                            require_once(plugin_dir_path(__FILE__).'includes/itinerarytable.php');
                        ?>
                    </div>
                </div>
            <?php
        }
        
        /**
         *  Función de callback que guardará los custom-post-fields en la BBDD
         *  @param $post_id int post ID
         */
        function mpm_save_custom_fields($post_id) {
            // Determinar si estamos en autosave
            $is_autosave = wp_is_post_autosave($post_id);
            // Determinar si estamos en revisión
            $is_revision = wp_is_post_revision($post_id);
            // Determinar si el campo nonce es válido
            $is_valid_nonce = (isset($_POST['mpm_review_nonce']) && wp_verify_nonce($_POST['mpm_review_nonce'], basename(__FILE__)))? true : false;
            
            if ($is_autosave || $is_revision || !$is_valid_nonce) {
                return;
            }
            // Comprobar que el usuario tiene permisos
                                // capacidad - sobre qué
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
            // Guardamos los campos en la BBDD
            $from               = sanitize_text_field($_POST['mpm_from']);
            $to                 = sanitize_text_field($_POST['mpm_to']);
            $days               = sanitize_text_field($_POST['mpm_days']);
            $rating             = sanitize_text_field($_POST['mpm_rating']);

            $percentage_title_1 = sanitize_text_field($_POST['mpm_percentage_title_1']);
            $percentage_1       = sanitize_text_field($_POST['mpm_percentage_1']);
            $percentage_title_2 = sanitize_text_field($_POST['mpm_percentage_title_2']);
            $percentage_2       = sanitize_text_field($_POST['mpm_percentage_2']);
            $percentage_title_3 = sanitize_text_field($_POST['mpm_percentage_title_3']);
            $percentage_3       = sanitize_text_field($_POST['mpm_percentage_3']);
            
            $photography_level  = sanitize_text_field($_POST['mpm_photography_level']);
            if(isset($_POST['mpm_only_adults'])) {
                $only_adults = "YES";
            } else {
                $only_adults = "NO";
            }
            $price      = sanitize_text_field($_POST['mpm_price']);
            $max_people = sanitize_text_field($_POST['mpm_max_people']);
            
            update_post_meta($post_id, 'mpm_from',               $from);
            update_post_meta($post_id, 'mpm_to',                 $to);
            update_post_meta($post_id, 'mpm_days',               $days);
            update_post_meta($post_id, 'mpm_rating',             $rating);

            update_post_meta($post_id, 'mpm_percentage_1',       $percentage_1);
            update_post_meta($post_id, 'mpm_percentage_title_1', $percentage_title_1);
            update_post_meta($post_id, 'mpm_percentage_2',       $percentage_2);
            update_post_meta($post_id, 'mpm_percentage_title_2', $percentage_title_2);
            update_post_meta($post_id, 'mpm_percentage_3',       $percentage_3);
            update_post_meta($post_id, 'mpm_percentage_title_3', $percentage_title_3);

            update_post_meta($post_id, 'mpm_photography_level',  $photography_level);
            update_post_meta($post_id, 'mpm_only_adults',        $only_adults);
            update_post_meta($post_id, 'mpm_price',              $price);
            update_post_meta($post_id, 'mpm_max_people',         $max_people);
            
            // Guardar los campos dinámicos en la BBDD
            $old = get_post_meta($post_id, 'mpm_repeteable_fields', true);
            $new = array();
            
            // Harvesting
            $days = $_POST['days'];
            $places = $_POST['places'];
            // Intetamos hallar el número de campos
            $count = count($days);
            
            for($i=0; $i<$count; $i++) {  //Recorremos el array de doble dimensión con los campos dinámicos
                if($days[$i] != '') {
                    $new[$i]['days'] = stripslashes(strip_tags($days[$i]));
                    // Si ya hemos metido algo en el primer campo tenemos que respetarlo aunque el segundo esté vacío
                    $new[$i]['places'] = stripslashes(strip_tags($places[$i]));
                }
            }
            
            if (!empty($new) && $new!=$old) {
                update_post_meta($post_id, 'mpm_repeteable_fields', $new);
            } elseif(empty($new) && $old) {
                delete_post_meta($post_id, 'mpm_repeteable_fields', $old);
            }
        }
        
        /**
         *  Función que incorpora nuestro custom post type a las consultas por defecto de WP
         */
        function mpm_pre_get_posts($query){
            // Le indicamos a WP que cuando haga la consulta en la plantilla archive.php tenga en cuenta nuestro CPT
            if(!is_admin() && is_archive() && $query->is_main_query()) {
                // Tenemos que establecer el argumento post_type
                $query->set('post_type', array('post', 'mpm_reviews'));  // En tiempo de ejecución
            }
        }
        
        /**
         *  Función que agrega una nueva opción en el menú del admin area asociada a una página de settings
         */
        function mpm_reviews_admin_page() {
            add_menu_page('PhotoTour Reviews Settings', 'Reviews Settings', 'manage_options', 'reviews-settings', array($this, 'mpm_reviews_settings'), 'dashicons-admin-generic', 6);
        }
        
        /**
         *  Función que dibuja los settings en la página de settings usando HTML y PHP
         */
        function mpm_reviews_settings() {
            require_once(plugin_dir_path(__FILE__).'admin/settings.php');
        }
        
        /**
         *  Función que registra los settings de la página de settings
         */
        function mpm_reviews_settings_register() {
            // register_setting() registra los settings en la tabla wp_options de la BBDD
            //              <nombre_del_setting>, <sección_del_setting>, <callback_validación_de_los_settings>
            register_setting('reviews_settings', 'reviews_settings', array($this, 'mpm_reviews_settings_validation'));
        }
        
        /**
         *  Función que valida los settings del plugin
         *  @param settings Array Contiene los valores de los settings
         */
        function mpm_reviews_settings_validation($settings) {
            // Si no hemos introducido un color todavía se le aplica el color por defecto
            if(!isset($settings['mpm_color'])) {
                $settings['mpm_color'] = '#fcb941'; // --main-color
            }
            
            // Si no tenemos num_tuplas por defecto serán 10 (o si se introduce un número no válido de tuplas)
            if (!isset($settings['mpm_num_tuplas']) || $settings['mpm_num_tuplas'] < 5 || $settings['mpm_num_tuplas'] > 50){
                $settings['mpm_num_tuplas'] = 10;
                // 1. Slug del error
                // 2. Identificador del error
                // 3. Mensaje de error
                // 4. Tipo de error ('error', 'warning', 'succes', 'info')
                add_settings_error('mpm-reviews-settings', 'mpm_num_tuplas_error', 'Please enter a valid number of tuplas per page (5 to 50)', 'error');
            }
            
            // Si no hemos especificado si queremos rating por defecto es YES
            if(!isset($settings['mpm_allowrating'])) {
                $settings['mpm_allowrating'] = "yes";
            }
            
            return $settings;
        }
        
        /**
         *  Función que activa el lanzamiento de errores en los settings
         */
        function mpm_reviews_settings_admin_notices() {
            settings_errors();
        }
        
        /**
         * Función que añade JS y hojas de estilo al front-end de mi CPT
         */
        function mpm_front_enqueue_scripts() {
            wp_register_style('mpm_front_styles', plugins_url('/css/front.css', __FILE__));
            wp_enqueue_style('mpm_front_styles');
        }
        
        /**
         * Función que inyecta css al front end con valores procedentes de los settings del plugin
         */
        function mpm_reviews_css_injection() {
            // Los estilos inyectados para poder aplicar los settings no pueden ir en el constructor
            // de la clase porque se mandarían antes de terminar el diálogo entre el cliente y servidor
            // y eso daría un error (en la biblioteca de medios y en la actualización de los posts)
            
            // Recogemos las opciones de los settings
            $options = get_option('reviews_settings'); // Obtenemos el array con todos los settings
            $color = $options['mpm_color'];
            
            // Guardamos en una variable todos los estilos que queremos inyectar
            $styles = '
                .custom-fields-1, .custom-fields-2, .custom-fields-3, .custom-fields-4 {
                    border: 1px solid '.$color.' !important;
                }
                
                .custom-fields-1::before, .custom-fields-2::before, .custom-fields-3::before, .custom-fields-4::before {
                    color: '.$color.' !important;
                }
            ';
            
            // Registramos y ponemos en la cola la inyección de código
            wp_register_style('mpm_css_injection', false);
            wp_enqueue_style('mpm_css_injection');
            
            // Hacemos la inyección de estilos
            wp_add_inline_style('mpm_css_injection', $styles);
        }
        
        /**
         * Función que añade JS y hojas de estilo al admin área de mi CPT
         */
        function mpm_admin_enqueue_scripts() {
            wp_register_style('mpm_admin_styles', plugins_url('/admin/css/admin.css', __FILE__));
            wp_enqueue_style('mpm_admin_styles');
            
            // Anexar el JS que añade campos dinámicos a nuestro CPT
            wp_register_script('mpm_reviews_js', plugins_url('/admin/js/mpm_reviews_js.js', __FILE__));
            wp_enqueue_script('mpm_reviews_js');
        }
        
        /***************************************** SHORTCODES *************************************/
        
        /**
         *  Función que visualiza los custom-fields esenciales del custom-post type mediante shortcode
         *  @atts array Post ID
         */
        function mpm_show_main_fields_shortcode($atts) {
            // Recuperamos el ID del post que entra como parámetro de entrada
            $postid = shortcode_atts( array(
                    'id' => 0, // Valor que va a tener por defecto... es decir, si no especificamos atributo en la invocación
                ), $atts
            );
            $post_id = $postid['id'];
            ?>
            <div class="custom-fields-1">
                <div class="line-1">
                    <div class="from">
                        <div>From:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_from', true)?></div>
                    </div>
                    <div class="to">
                        <div>To:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_to', true)?></div>
                    </div>
                    <div class="days">
                        <div>Days:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_days', true)?></div>
                    </div>
                    <div class="rating">
                        <div>Rating:</div>
                        <div>
                            <?php
                                $rating = get_post_meta($post_id, 'mpm_rating', true);
                                for($i=0; $i<5; $i++) {
                                    if($i<$rating) {
                                        echo '<span class="dashicons dashicons-star-filled"></span>';
                                    }
                                    else {
                                        echo '<span class="dashicons dashicons-star-empty"></span>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        /**
         *  Función que visualiza los custom-fields del custom-post type mediante shortcode
         *  @atts array Post ID
         */
        function mpm_show_fields_shortcode($atts) {
            // Recuperamos el ID del post que entra como parámetro de entrada
            $postid = shortcode_atts( array(
                    'id' => 0, // Valor que va a tener por defecto... es decir, si no especificamos atributo en la invocación
                ), $atts
            );
            $post_id = $postid['id'];
            ?>
            <div class="custom-fields-1">
                <div class="line-1">
                    <div class="from">
                        <div>From:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_from', true)?></div>
                    </div>
                    <div class="to">
                        <div>To:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_to', true)?></div>
                    </div>
                    <div class="days">
                        <div>Days:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_days', true)?></div>
                    </div>
                    <div class="rating">
                        <div>Rating:</div>
                        <div>
                            <?php
                                $rating = get_post_meta($post_id, 'mpm_rating', true);
                                for($i=0; $i<5; $i++) {
                                    if($i<$rating) {
                                        echo '<span class="dashicons dashicons-star-filled"></span>';
                                    }
                                    else {
                                        echo '<span class="dashicons dashicons-star-empty"></span>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="custom-fields-2">
                <label class="percentages-title">What to expect?:</label>
                <div class="line-2">
                    <div class="circle-progress circle-progress-bg-pasific" data-value=".<?php echo get_post_meta($post_id, 'mpm_percentage_1', true)?>" data-size="125" data-thickness="5">
                        <span class="circle-progress-value"></span>
                        <span class="circle-progress-title"><?php echo get_post_meta($post_id, 'mpm_percentage_title_1', true)?></span>
                    </div>
                    <div class="circle-progress circle-progress-bg-primary" data-value=".<?php echo get_post_meta($post_id, 'mpm_percentage_2', true)?>" data-size="125" data-thickness="5">
                        <span class="circle-progress-value"></span>
                        <span class="circle-progress-title"><?php echo get_post_meta($post_id, 'mpm_percentage_title_2', true)?></span>
                    </div>
                    <div class="circle-progress circle-progress-bg-success" data-value=".<?php echo get_post_meta($post_id, 'mpm_percentage_3', true)?>" data-size="125" data-thickness="5">
                        <span class="circle-progress-value"></span>
                        <span class="circle-progress-title"><?php echo get_post_meta($post_id, 'mpm_percentage_title_3', true)?></span>
                    </div>
                </div>
            </div>
            <div class="custom-fields-3">
                <div class="line-3">
                    <div class="photography_level">
                        <div>Photography level:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_photography_level', true)?></div>
                    </div>
                    <div class="only_adults">
                        <div>Only adults:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_only_adults', true)?></div>
                    </div>
                    <div class="price">
                        <div>Price:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_price', true)?></div>
                    </div>
                    <div class="max_people">
                        <div>Max people:</div>
                        <div class="data"><?php echo get_post_meta($post_id, 'mpm_max_people', true)?></div>
                    </div>
                </div>
            </div>
            <div class="custom-fields-4">
                <div class="line-4">
                    <?php
                        $itinerary = get_post_meta($post_id, 'mpm_repeteable_fields', true);
                    ?>
                    <div class="col-days">
                        <div class="days-title">Days</div>
                        <?php
                            if($itinerary) {
                                foreach($itinerary as $field) {
                                    echo '<div class="data">'.$field['days'].'</div>';
                                }
                            }
                        ?>
                    </div>
                    <div class="col-places">
                        <div class="places-title">Places</div>
                        <?php
                            if($itinerary) {
                                foreach($itinerary as $field) {
                                    if($field['places'] != '') {
                                        echo '<div class="data">'.$field['places'].'</div>';
                                    } else {
                                        echo '<div class="data">NULL</div>';
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }
        
        /**
         *  Función que activa el plugin
         */
        function mpm_activation_plugin() {
            flush_rewrite_rules();
        }
        
        /**
         *  Función que desactiva el plugin
         */
        function mpm_deactivation_plugin() {
            flush_rewrite_rules();
        }
    }
    
    $review = new Reviews();
    $review->execute_actions();
    
    // Registramos los hooks para lanzar código cuando se active o desactive el plugin
    //                       <archivo del plugin>, <callback>
    register_activation_hook(__FILE__, array($review, 'mpm_activation_plugin'));
    register_deactivation_hook(__FILE__, array($review, 'mpm_deactivation_plugin'));