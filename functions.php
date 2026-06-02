<?php
if (function_exists('add_theme_support'))
{
    // Add Menu Support
    add_theme_support('menus');

    // Add Thumbnail Theme Support
    add_theme_support('post-thumbnails');
    add_image_size('large', 700, '', true);
    add_image_size('medium', 250, '', true);
    add_image_size('small', 120, '', true);
    add_image_size('custom-size', 1920, 1080, true);

    add_theme_support('automatic-feed-links');
}

if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title' 	=> 'Ysse Settings',
		'menu_title'	=> 'Ysse Settings',
		'menu_slug'	=> 'ysse-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}

function create_post_type_products(){
	register_taxonomy_for_object_type('post_tag', 'products');
	register_post_type('products',
		array(
			'labels' => array(
				'name' => __('Products', 'products'),
				'singular_name' => __('Product', 'products'),
				'add_new' => __('Add New Products', 'products'),
				'add_new_item' => __('Add New Product', 'products'),
				'edit' => __('Edit Products', 'products'),
				'edit_item' => __('Edit Product', 'products'),
				'new_item' => __('New Product', 'products'),
				'view' => __('View Products', 'products'),
				'view_item' => __('View Product', 'products'),
				'search_items' => __('Search Products', 'products'),
				'not_found' => __('No Products found', 'products'),
				'not_found_in_trash' => __('No Products found in Trash', 'products')
			),
			'public' => true,
			'hierarchical' => false,
			'has_archive' => true,
			'supports' => array(
				'title',
				'editor',
				'thumbnail'
			),
			'rewrite' => false,
			'taxonomies' => array( 'products_categories', 'post_tag' ),
            'can_export' => true,
            'menu_icon'   => 'dashicons-sticky',
			'show_in_rest' => true,
			'rest_base' => 'products',
			'rest_controller_class' => 'WP_REST_Posts_Controller'
		)
	);
}

function create_products_tax() {
	register_taxonomy(
		'products_categories',
		'products',
		array(
			'label' => __('Categories'),
			'rewrite' => array('slug' => 'products_categories'),
			'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true
		)
	);
}

// Root-level permalink for 'products' CPT (WPML-aware)
function custom_products_permalink($post_link, $post) {
    if (is_numeric($post)) {
        $post = get_post($post);
    }
    if (!is_object($post) || $post->post_type !== 'products' || $post->post_status !== 'publish') {
        return $post_link;
    }
    $url = home_url('/' . $post->post_name . '/');
    $lang = apply_filters('wpml_post_language_details', null, $post->ID);
    if (!empty($lang['language_code'])) {
        $url = apply_filters('wpml_permalink', $url, $lang['language_code']);
    }
    return $url;
}
add_filter('post_type_link', 'custom_products_permalink', 20, 2);

// Strip /products/ from Yoast canonical and OG URL
function custom_products_yoast_canonical($url) {
    if (is_string($url) && strpos($url, '/products/') !== false) {
        return preg_replace('#/products/#', '/', $url, 1);
    }
    return $url;
}
add_filter('wpseo_canonical', 'custom_products_yoast_canonical', 20);
add_filter('wpseo_opengraph_url', 'custom_products_yoast_canonical', 20);

// 301 redirect old /products/{slug}/ URLs to root-level /{slug}/
function custom_products_old_url_redirect() {
    if (is_admin() || defined('DOING_AJAX') || defined('DOING_CRON')) {
        return;
    }
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (strpos($request_uri, '/products/') === false) {
        return;
    }
    // Match /products/{slug}/ optionally prefixed with language code (e.g. /en/products/foo/)
    if (preg_match('#^(/[a-z]{2})?/products/([^/?]+)/?(\?.*)?$#i', $request_uri, $m)) {
        $lang_prefix = $m[1];
        $slug = $m[2];
        $query = isset($m[3]) ? $m[3] : '';
        $new_url = home_url($lang_prefix . '/' . $slug . '/' . $query);
        wp_redirect($new_url, 301);
        exit;
    }
}
add_action('template_redirect', 'custom_products_old_url_redirect');

// Custom rewrite rules for products to be root-level
function custom_products_rewrite_rules() {
    // Get all products slugs
    $products = get_posts(array(
        'post_type' => 'products',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));
    
    foreach ($products as $product_id) {
        $slug = get_post_field('post_name', $product_id);
        if ($slug) {
            add_rewrite_rule(
                '^' . $slug . '/?$',
                'index.php?products=' . $slug,
                'top'
            );
            add_rewrite_rule(
                '^' . $slug . '/page/?([0-9]{1,})/?$',
                'index.php?products=' . $slug . '&paged=$matches[1]',
                'top'
            );
        }
    }
}

// navigation
function template_nav() {
	wp_nav_menu(
	array(
		'theme_location'  => 'main-menu',
		'menu'            => '',
		'container'       => 'div',
		'container_class' => 'menu-{menu slug}-container',
		'container_id'    => '',
		'menu_class'      => 'menu',
		'menu_id'         => '',
		'echo'            => true,
		'fallback_cb'     => 'wp_page_menu',
		'before'          => '',
		'after'           => '',
		'link_before'     => '<span>',
		'link_after'      => '</span>',
		'items_wrap'      => '<ul>%3$s</ul>',
		'depth'           => 0,
		'walker'          => ''
		)
	);
}

// Load styles
function template_styles() {
    wp_register_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css', array(), '1.0', 'all');
    wp_enqueue_style('bootstrap');

    wp_register_style('slick', get_template_directory_uri() . '/assets/css/slick.css', array(), '1.0', 'all');
    wp_enqueue_style('slick');

    wp_register_style('basic', get_template_directory_uri() . '/style.css', array(), '1.0', 'all');
    wp_enqueue_style('basic');

    wp_register_style('fonts', get_template_directory_uri() . '/fonts/fonts.css', array(), '1.0', 'all');
    wp_enqueue_style('fonts');

    wp_register_style('styles', get_template_directory_uri() . '/scss/css/styles.css', array(), '1.0', 'all');
    wp_enqueue_style('styles');

    wp_register_style('styles_mobile', get_template_directory_uri() . '/scss/css/styles_mobile.css', array(), '1.0', 'all');
    wp_enqueue_style('styles_mobile');
}


function template_scripts() {
   if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {

        wp_register_script('script', get_template_directory_uri() . '/js/script.js', array('jquery'), '1.0.0');
        wp_enqueue_script('script');

        wp_register_script('magnify', get_template_directory_uri() . '/assets/js/jquery.magnify.js', array('jquery'), '1.0.0');
        wp_enqueue_script('magnify');

        wp_register_script('slick', get_template_directory_uri() . '/assets/js/slick.min.js', array('jquery'), '1.0.0');
        wp_enqueue_script('slick');

        wp_register_script('bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', array('jquery'), '1.0.0');
        wp_enqueue_script('bootstrap');

        wp_register_script('cookie', get_template_directory_uri() . '/js/lib/jquery.cookie.js', array('jquery'), '1.0.0');
        wp_enqueue_script('cookie');
   }
}

// Funktsioon praeguse keele tuvastamiseks
function get_current_language() {
    $current_url = $_SERVER['REQUEST_URI'];

    if (strpos($current_url, '/en/') !== false || strpos($current_url, '/en') !== false) {
        return 'en';
    } elseif (strpos($current_url, '/fi/') !== false || strpos($current_url, '/fi') !== false) {
        return 'fi';
    } else {
        return 'et'; // vaikimisi eesti keel
    }
}

function add_file_upload_translation_script() {
    if (!is_admin()) {
        $lang = get_current_language();

        // Tõlked keelte kaupa
        $translations = array(
            'et' => array(
                'button' => 'LISA',
                'no_file' => 'Faili ei ole valitud'
            ),
            'en' => array(
                'button' => 'ADD',
                'no_file' => 'No file selected'
            ),
            'fi' => array(
                'button' => 'LISÄÄ',
                'no_file' => 'Ei tiedostoa valittu'
            )
        );

        $current_translations = $translations[$lang];
        ?>
        <style>
        /* CUSTOM FILE UPLOAD - täielik asendus */
        .wpcf7 .wpcf7-form input[type="file"] {
            opacity: 0 !important;
            position: absolute !important;
            z-index: -1 !important;
        }

        /* Custom file upload wrapper */
        .wpcf7 .wpcf7-form .custom-file-wrapper {
            position: relative !important;
            display: block !important;
            width: 100% !important;
            height: 42px !important;
            background: rgba(255, 255, 255, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            margin-bottom: 15px !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            transition: all 0.3s ease !important;
            overflow: hidden !important;
        }

        .wpcf7 .wpcf7-form .custom-file-wrapper:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }

        /* LISA nupp */
        .wpcf7 .wpcf7-form .custom-file-wrapper .file-button {
            position: absolute !important;
            left: 15px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: rgba(255, 255, 255, 0.3) !important;
            color: white !important;
            padding: 6px 15px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: bold !important;
            pointer-events: none !important;
            transition: background 0.3s ease !important;
        }

        .wpcf7 .wpcf7-form .custom-file-wrapper:hover .file-button {
            background: rgba(255, 255, 255, 0.4) !important;
        }

        /* Faili tekst */
        .wpcf7 .wpcf7-form .custom-file-wrapper .file-text {
            position: absolute !important;
            left: 90px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 13px !important;
            pointer-events: none !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            right: 15px !important;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tõlked JavaScriptis
            const translations = {
                button: '<?php echo $current_translations['button']; ?>',
                noFile: '<?php echo $current_translations['no_file']; ?>'
            };

            // Muudame kõik file inputid custom komponendiks
            const fileInputs = document.querySelectorAll('.wpcf7-form input[type="file"]');

            fileInputs.forEach(function(input) {
                // Loome custom wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'custom-file-wrapper';

                // LISA nupp (tõlgitud)
                const button = document.createElement('div');
                button.className = 'file-button';
                button.textContent = translations.button;

                // Faili tekst (tõlgitud)
                const text = document.createElement('div');
                text.className = 'file-text';
                text.textContent = translations.noFile;

                // Lisame elemendid wrapper'isse
                wrapper.appendChild(button);
                wrapper.appendChild(text);

                // Asendame algse input'i
                input.parentNode.insertBefore(wrapper, input);
                input.style.display = 'none';

                // Click event wrapper'ile
                wrapper.addEventListener('click', function() {
                    input.click();
                });

                // Faili valimise kuulamine
                input.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        const fileName = this.files[0].name;
                        text.textContent = fileName;
                    } else {
                        text.textContent = translations.noFile;
                    }
                });
            });
        });
        </script>
        <?php
    }
}

// Register template Navigation
function register_template_menu()
{
    register_nav_menus(array( // Using array to specify more menus if needed
        'main-menu' => __('Main Menu', 'ysse'),
        'footer-menu' => __('Footer Menu', 'ysse'),
    ));
}

// Remove the <div> surrounding the dynamic navigation to cleanup markup
function my_wp_nav_menu_args($args = '')
{
    $args['container'] = false;
    return $args;
}

// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function my_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}

// Remove invalid rel attribute values in the categorylist
function remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}

// Add page slug to body class, love this - Credit: Starkers Wordpress Theme
function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }

    return $classes;
}

// Remove thumbnail width and height dimensions that prevent fluid images in the_thumbnail
function remove_thumbnail_dimensions( $html )
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}

// Threaded Comments
function enable_threaded_comments()
{
    if (!is_admin()) {
        if (is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
            wp_enqueue_script('comment-reply');
        }
    }
}

function my_custom_mime_types( $mimes ) {

    // New allowed mime types.
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    $mimes['dwg'] = 'dwg';
    $mimes['imagesdwg'] = 'image/dwg';
    $mimes['dwgdoc'] = 'document/dwg';


    return $mimes;
}
add_filter( 'upload_mimes', 'my_custom_mime_types' );

if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => 'Language_Switcher',
        'before_widget' => '<div class = "language_switcher">',
        'after_widget' => '</div>',
    ));

    register_sidebar(array(
        'name' => 'Language_Switcher_Mobile',
        'before_widget' => '<div class = "language_switcher_mobile">',
        'after_widget' => '</div>',
    ));
}

function render_image_block_shortcode($atts){
    $attributes = shortcode_atts( array(
		'title' => 'something',
		'image' => 'something else',
    ), $atts );

    $html  ='</div></div>';
    $html .= '<div class="post_image d-flex justify-content-center align-items-center " style="background-image:url('.$attributes['image'].');">';
        $html .= '<h1>'.$attributes['title'].'</h1>';
    $html .= '</div>';
    $html .= '<div class="post_content"><div class="post_content_container">';

    return $html;
}

add_shortcode( 'render_image_block', 'render_image_block_shortcode' );


// Add Actions
add_action('get_header', 'template_styles');
add_action('get_header', 'template_scripts');
add_action('wp_footer', 'add_file_upload_translation_script'); // File upload tõlge + CSS
add_action('init', 'register_template_menu');
add_action('init', 'create_post_type_products');
add_action('init', 'create_products_tax');
add_action('init', 'custom_products_rewrite_rules');
add_action('get_header', 'enable_threaded_comments');
// Remove Actions
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Add Filters
add_filter('body_class', 'add_slug_to_body_class');
add_filter('widget_text', 'do_shortcode');
add_filter('widget_text', 'shortcode_unautop');
add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args');
add_filter('the_category', 'remove_category_rel_from_category_list');
add_filter('the_excerpt', 'shortcode_unautop');
add_filter('the_excerpt', 'do_shortcode');
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10);
add_filter('image_send_to_editor', 'remove_thumbnail_dimensions', 10);

// Remove Filters
remove_filter('the_excerpt', 'wpautop');

/** AJAX Functions */
function analyticsCode() {
	include(locate_template("parts/part-analytics-code.php"));
	die;
}

add_action('wp_ajax_getAnalytics', 'analyticsCode');
add_action('wp_ajax_nopriv_getAnalytics', 'analyticsCode');

function headerScripts() {
	include(locate_template("parts/part-header-scripts.php"));
	die;
}

add_action('wp_ajax_getHeaderScripts', 'headerScripts');
add_action('wp_ajax_nopriv_getHeaderScripts', 'headerScripts');

add_filter('wpcf7_spam', '__return_false');
add_filter('wpcf7_skip_spam_check', '__return_true');
// Test meili saatmist
function test_wp_mail() {
    if (isset($_GET['test_mail']) && current_user_can('administrator')) {
        $to = 'maiko@godigital.ee';
        $subject = 'WordPress Mail Test';
        $message = 'See on test meil, et kontrollida kas WordPress mail toimib.';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $sent = wp_mail($to, $subject, $message, $headers);

        if ($sent) {
            echo '<div style="background: green; color: white; padding: 10px;">MAIL LÄKS KÄTTE!</div>';
        } else {
            echo '<div style="background: red; color: white; padding: 10px;">MAIL EI LÄINUD!</div>';
        }

        // Näita meili seadistusi
        echo '<pre>';
        echo 'PHP mail() enabled: ' . (function_exists('mail') ? 'YES' : 'NO') . "\n";
        echo 'WordPress mail settings:' . "\n";
        print_r(wp_mail(null, null, null, null, null, true)); // debug info
        echo '</pre>';
        exit;
    }
}
add_action('init', 'test_wp_mail');

// Contact Form 7 täpsem debug
function detailed_wpcf7_debug() {
    // Meili saatmise õnnestumine
    add_action('wpcf7_mail_sent', function($contact_form) {
        error_log('✅ CF7 SUCCESS: Mail sent for form ID: ' . $contact_form->id() . ' Title: ' . $contact_form->title());
    });

    // Meili saatmise ebaõnnestumine
    add_action('wpcf7_mail_failed', function($contact_form) {
        error_log('❌ CF7 FAILED: Mail failed for form ID: ' . $contact_form->id() . ' Title: ' . $contact_form->title());
    });

    // Vormi esitamise järel
    add_action('wpcf7_submit', function($contact_form, $result) {
        error_log('📝 CF7 SUBMIT: Form submitted. Status: ' . $result['status'] . ' Message: ' . (isset($result['message']) ? $result['message'] : 'No message'));
    }, 10, 2);
}
add_action('init', 'detailed_wpcf7_debug');

?>
