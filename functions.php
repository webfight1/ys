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
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_custom_css_cb', 101);

function ysse_enqueue_customizer_css() {
    if (is_admin()) {
        return;
    }

    $css = wp_get_custom_css();

    if ($css === '') {
        return;
    }

    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = trim($css);

    wp_register_style('ysse-wp-custom', false, array(), '1.0');
    wp_enqueue_style('ysse-wp-custom');
    wp_add_inline_style('ysse-wp-custom', $css);
}

add_action('wp_enqueue_scripts', 'ysse_enqueue_customizer_css', 20);

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

// Custom rewrite rules for products to be root-level (WPML-aware: registers
// rules for every active language with its URL prefix, using each language's
// translated slug).
function custom_products_rewrite_rules() {
    // Skip admin/AJAX/REST requests — rewrite rules pole neis kontekstides vaja,
    // ja `wpml_switch_language` kutsumine admin'is segab WPML keele konteksti
    // (nt admin "Edit" link võib peale seda valele lehele suunata).
    // NB: WP-CLI EI ole välistatud, sest `wp rewrite flush` peab need reeglid
    // aktiivseks lugema.
    if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
        if ( is_admin()
            || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }
    }

    $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
    if ( empty( $languages ) ) {
        $languages = array( 'et' => array( 'language_code' => 'et' ) );
    }

    $default_lang = apply_filters( 'wpml_default_language', 'et' );

    foreach ( $languages as $lang_code => $lang_info ) {
        do_action( 'wpml_switch_language', $lang_code );

        $products = get_posts( array(
            'post_type'        => 'products',
            'posts_per_page'   => -1,
            'fields'           => 'ids',
            'suppress_filters' => false,
        ) );

        // NB: WPML eemaldab keele prefiksi (/en/, /fi/) URL-ist enne WP rewrite
        // matchimist, seega kõik reeglid registreeritakse root-leveli — keele
        // info edastatakse `&lang=` query var-i kaudu, et WPML laadiks õige
        // tõlke ja `?products=` query var match'iks õige slug-i.
        foreach ( $products as $product_id ) {
            $slug = get_post_field( 'post_name', $product_id );
            if ( $slug ) {
                add_rewrite_rule(
                    '^' . $slug . '/?$',
                    'index.php?products=' . $slug . '&lang=' . $lang_code,
                    'top'
                );
                add_rewrite_rule(
                    '^' . $slug . '/page/?([0-9]{1,})/?$',
                    'index.php?products=' . $slug . '&paged=$matches[1]&lang=' . $lang_code,
                    'top'
                );
            }
        }
    }

    do_action( 'wpml_switch_language', $default_lang );
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
    wp_register_style('layout-utilities', get_template_directory_uri() . '/assets/css/layout-utilities.css', array(), '1.0', 'all');
    wp_enqueue_style('layout-utilities');

    if (ysse_page_needs_slider()) {
        wp_register_style('slick', get_template_directory_uri() . '/assets/css/slick.css', array(), '1.0', 'all');
        wp_enqueue_style('slick');
    }

    wp_register_style('basic', get_template_directory_uri() . '/style.css', array(), '1.0', 'all');
    wp_enqueue_style('basic');

    wp_register_style('fonts', get_template_directory_uri() . '/fonts/fonts.css', array(), '1.0', 'all');
    wp_enqueue_style('fonts');

    wp_register_style('styles', get_template_directory_uri() . '/scss/css/styles.css', array(), '1.0.46', 'all');
    wp_enqueue_style('styles');

    wp_register_style('styles_mobile', get_template_directory_uri() . '/scss/css/styles_mobile.css', array(), '1.0.20', 'all');
    wp_enqueue_style('styles_mobile');
}

function ysse_acf_image_url($img, $size = 'large') {
    if (empty($img)) {
        return '';
    }

    if (is_numeric($img)) {
        $url = wp_get_attachment_image_url((int) $img, $size);
        return $url ? $url : '';
    }

    if (is_string($img)) {
        return $img;
    }

    if (!empty($img['ID'])) {
        $url = wp_get_attachment_image_url($img['ID'], $size);
        if ($url) {
            return $url;
        }
    }

    if (!empty($img['sizes'][$size])) {
        return $img['sizes'][$size];
    }

    return !empty($img['url']) ? $img['url'] : '';
}

function ysse_url_image_dimensions($url, $size = 'full', $fallback_w = 700, $fallback_h = 525) {
    if (empty($url)) {
        return array(
            'width' => $fallback_w,
            'height' => $fallback_h,
        );
    }

    $attachment_id = attachment_url_to_postid($url);

    if ($attachment_id) {
        $src = wp_get_attachment_image_src($attachment_id, $size);

        if ($src && !empty($src[1]) && !empty($src[2])) {
            $width = (int) $src[1];
            $height = (int) $src[2];

            if ($width > 1 && $height > 1) {
                return array(
                    'width' => $width,
                    'height' => $height,
                );
            }
        }

        $meta = wp_get_attachment_metadata($attachment_id);

        if (!empty($meta['width']) && !empty($meta['height'])) {
            $width = (int) $meta['width'];
            $height = (int) $meta['height'];

            if ($width > 1 && $height > 1) {
                return array(
                    'width' => $width,
                    'height' => $height,
                );
            }
        }
    }

    return array(
        'width' => $fallback_w,
        'height' => $fallback_h,
    );
}

function ysse_acf_image_dimensions($img, $size = 'large', $fallback_w = 700, $fallback_h = 525) {
    if (is_string($img)) {
        return ysse_url_image_dimensions($img, $size, $fallback_w, $fallback_h);
    }

    if (is_numeric($img)) {
        $src = wp_get_attachment_image_src((int) $img, $size);

        if ($src && !empty($src[1]) && !empty($src[2])) {
            $width = (int) $src[1];
            $height = (int) $src[2];

            if ($width > 1 && $height > 1) {
                return array(
                    'width' => $width,
                    'height' => $height,
                );
            }
        }

        return ysse_url_image_dimensions(wp_get_attachment_url((int) $img), $size, $fallback_w, $fallback_h);
    }

    $width = $fallback_w;
    $height = $fallback_h;

    if (is_array($img)) {
        if (!empty($img['sizes'][$size . '-width'])) {
            $width = (int) $img['sizes'][$size . '-width'];
        }
        if (!empty($img['sizes'][$size . '-height'])) {
            $height = (int) $img['sizes'][$size . '-height'];
        }
    }

    if (!empty($img['ID'])) {
        $meta = wp_get_attachment_metadata((int) $img['ID']);
        if (!empty($meta['sizes'][$size]['width'])) {
            $width = (int) $meta['sizes'][$size]['width'];
        }
        if (!empty($meta['sizes'][$size]['height'])) {
            $height = (int) $meta['sizes'][$size]['height'];
        }
    }

    return array(
        'width' => $width,
        'height' => $height,
    );
}

function ysse_acf_image_alt($img, $fallback = '') {
    if (is_array($img) && !empty($img['alt'])) {
        return $img['alt'];
    }

    if (!empty($img['ID'])) {
        $alt = get_post_meta((int) $img['ID'], '_wp_attachment_image_alt', true);

        if ($alt) {
            return $alt;
        }
    }

    if (is_numeric($img)) {
        $alt = get_post_meta((int) $img, '_wp_attachment_image_alt', true);

        if ($alt) {
            return $alt;
        }
    }

    return $fallback;
}

function ysse_wpml_ls_html_flag_alt($html, $model = null, $slot = null) {
    if (strpos($html, 'wpml-ls-flag') === false) {
        return $html;
    }

    return preg_replace_callback(
        '/<img([^>]*class="[^"]*wpml-ls-flag[^"]*"[^>]*)alt="[^"]*"([^>]*)>\s*<span class="wpml-ls-native">/',
        function ($matches) {
            return '<img' . $matches[1] . 'alt="" aria-hidden="true"' . $matches[2] . '><span class="wpml-ls-native">';
        },
        $html
    );
}

function ysse_is_gallery_page() {
    if (is_admin()) {
        return false;
    }

    return is_page(array('galerii', 'gallery', 'galleria'))
        || is_page_template('template-gallery.php');
}

function ysse_webp_url($url) {
    if (empty($url) || !is_string($url)) {
        return $url;
    }

    if (ysse_is_gallery_page()) {
        return $url;
    }

    $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

    if (!in_array($ext, array('jpg', 'jpeg', 'png'), true)) {
        return $url;
    }

    $webp_url = preg_replace('/\.' . preg_quote($ext, '/') . '$/i', '.webp', $url);
    $upload_dir = wp_upload_dir();
    $webp_path  = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $webp_url);

    if (file_exists($webp_path)) {
        return $webp_url;
    }

    return $url;
}

function ysse_style_background_image($url) {
    if (empty($url)) {
        return '';
    }

    return ' style="background-image: url(' . esc_url(ysse_webp_url($url)) . ');"';
}

function ysse_optimize_image_urls_in_html($html) {
    if (empty($html)) {
        return $html;
    }

    if (ysse_is_gallery_page()) {
        return $html;
    }

    $html = preg_replace_callback(
        '/background-image:\s*url\((["\']?)([^)"\'\s]+)\1\)/i',
        function ($matches) {
            $quote = $matches[1];
            $url   = html_entity_decode($matches[2], ENT_QUOTES, 'UTF-8');
            $webp  = ysse_webp_url($url);

            if ($webp === $url) {
                return $matches[0];
            }

            return 'background-image: url(' . $quote . esc_url($webp) . $quote . ')';
        },
        $html
    );

    $html = preg_replace_callback(
        '/<img\b([^>]*?)\bsrc=(["\'])([^"\']+)\2/i',
        function ($matches) {
            $before = $matches[1];
            $quote  = $matches[2];
            $url    = html_entity_decode($matches[3], ENT_QUOTES, 'UTF-8');

            if (preg_match('/\bdata-magnify-src\b/i', $before . $matches[0])) {
                return $matches[0];
            }

            if (preg_match('/\bysse-gallery-img\b/i', $matches[0])) {
                return $matches[0];
            }

            if (preg_match('/\bclass\s*=\s*["\'][^"\']*(?:product-gallery|ysse-slider|ysse-product-slider)/i', $before)) {
                return $matches[0];
            }

            $webp = ysse_webp_url($url);

            if ($webp === $url) {
                return $matches[0];
            }

            return '<img' . $before . 'src=' . $quote . esc_url($webp) . $quote;
        },
        $html
    );

    return $html;
}

function ysse_get_page_header_image_url($size = 'large') {
    $image = get_field('header_image');

    if (empty($image)) {
        return '';
    }

    if (is_numeric($image)) {
        $url = wp_get_attachment_image_url((int) $image, $size);

        return $url ? $url : '';
    }

    if (is_array($image)) {
        if (!empty($image['sizes'][$size])) {
            return $image['sizes'][$size];
        }

        if (!empty($image['url'])) {
            return $image['url'];
        }
    }

    if (is_string($image)) {
        return $image;
    }

    return '';
}

function ysse_get_lcp_gallery_image_url() {
    if (!is_singular('products')) {
        return '';
    }

    $blocks = get_field('blocks');
    if (empty($blocks) || !is_array($blocks)) {
        return '';
    }

    foreach ($blocks as $block) {
        if (empty($block['block_gallery']) || !is_array($block['block_gallery'])) {
            continue;
        }

        $first = reset($block['block_gallery']);
        $url = ysse_acf_image_url($first, 'large');

        if ($url) {
            return $url;
        }
    }

    return '';
}

function ysse_get_social_icon_alt($url, $icon) {
    $url = strtolower((string) $url);
    $icon = strtolower((string) $icon);

    if (strpos($url, 'facebook') !== false || strpos($icon, 'fb') !== false) {
        return __('Facebook', 'Ysse');
    }

    if (strpos($url, 'youtube') !== false || strpos($icon, 'yt') !== false) {
        return __('YouTube', 'Ysse');
    }

    if (strpos($url, 'instagram') !== false || strpos($icon, 'instagram') !== false) {
        return __('Instagram', 'Ysse');
    }

    if (strpos($url, 'linkedin') !== false || strpos($icon, 'linkedin') !== false) {
        return __('LinkedIn', 'Ysse');
    }

    return __('Sotsiaalmeedia', 'Ysse');
}

function ysse_print_lcp_preloads() {
    if (is_admin()) {
        return;
    }

    $font_base = get_template_directory_uri() . '/fonts/';
    $fonts = array(
        'Muli-Regular.woff2',
        'Muli-Bold.woff2',
        'Muli-SemiBold.woff2',
        'Muli-ExtraBold.woff2',
    );

    foreach ($fonts as $font_file) {
        echo '<link rel="preload" href="' . esc_url($font_base . $font_file) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }

    if (is_singular('products')) {
        $image_url = ysse_get_lcp_gallery_image_url();

        if ($image_url) {
            echo '<link rel="preload" as="image" href="' . esc_url($image_url) . '" fetchpriority="high">' . "\n";
        }

        return;
    }

    $header_image = ysse_get_page_header_image_url('large');

    if ($header_image) {
        $preload_url  = ysse_webp_url($header_image);
        $imagesrcset  = '';
        $imagesizes   = '';
        $type_attr    = strtolower(pathinfo($preload_url, PATHINFO_EXTENSION)) === 'webp' ? ' type="image/webp"' : '';
        echo '<link rel="preload" as="image" href="' . esc_url($preload_url) . '"' . $type_attr . ' fetchpriority="high">' . "\n";
    }
}

function ysse_print_critical_css() {
    if (is_admin()) {
        return;
    }

    $path = get_template_directory() . '/assets/css/critical-lcp.css';

    if (!is_readable($path)) {
        return;
    }

    $css = file_get_contents($path);

    if ($css === false || $css === '') {
        return;
    }

    echo '<style id="ysse-critical-lcp">' . $css . '</style>' . "\n";
}

function ysse_clean_content_links($content) {
    if (empty($content) || !is_string($content)) {
        return $content;
    }

    $content = preg_replace(
        '/(<a\b([^>]*)>\s*(?:<span[^>]*>)?\s*)([^<]+?)(\s*(?:<\/span>)?\s*<\/a>)\s*<a\b[^>]*>\s*(?:<span[^>]*>)?\s*\.\s*(?:<\/span>)?\s*<\/a>/iu',
        '$1$3.$4',
        $content
    );

    $content = preg_replace(
        '/<a\b[^>]*>\s*(?:<span[^>]*>)?\s*[.,;:]\s*(?:<\/span>)?\s*<\/a>/iu',
        '',
        $content
    );

    $content = preg_replace_callback(
        '/\bhref=(["\'])tel:([^"\']+)\1/i',
        function ($matches) {
            $phone = preg_replace('/[^\d+]/', '', html_entity_decode($matches[2], ENT_QUOTES, 'UTF-8'));

            return 'href=' . $matches[1] . 'tel:' . $phone . $matches[1];
        },
        $content
    );

    return $content;
}

function ysse_fix_minify_js_in_html($html) {
    if (strpos($html, '/cache/minify/') === false) {
        return $html;
    }

    $html = preg_replace(
        '/(<script\b[^>]*src=[\'"][^\'"]*\/cache\/minify\/[^\'"]+\.js[^>]*)\sasync/i',
        '$1 defer',
        $html
    );

    $html = preg_replace(
        '/<script\s+async\s+src=([^\s>]*\/cache\/minify\/[^\s>]+\.js)([^>]*)>/i',
        '<script defer src=$1$2>',
        $html
    );

    return preg_replace_callback(
        '/<script\b([^>]*\/cache\/minify\/[^>\s]+\.js[^>]*)>/i',
        function ($matches) {
            if (preg_match('/\b(async|defer)\b/i', $matches[1])) {
                return '<script' . $matches[1] . '>';
            }

            return '<script defer' . $matches[1] . '>';
        },
        $html
    );
}

function ysse_async_minify_css_in_html($html) {
    return $html;
}

function ysse_fix_html_lang_attribute($html) {
    $html = preg_replace('/<html\s*\R+\s*lang=/i', '<html lang=', $html);
    $html = preg_replace(
        '/<html\s+lang=([a-z]{2}(?:-[A-Za-z0-9]+)?)(?=\s|>)/i',
        '<html lang="$1"',
        $html
    );

    if (preg_match('/\blang=(["\'])([^"\']+)\1/i', $html, $lang_match, PREG_OFFSET_CAPTURE, 0)) {
        return $html;
    }

    if (preg_match('/<html\b[^>]*>/i', $html, $html_tag) && strpos($html_tag[0], 'lang=') === false) {
        $html = preg_replace('/<html\b/i', '<html lang="' . esc_attr(get_bloginfo('language') ?: 'et') . '"', $html, 1);
    }

    return $html;
}

function ysse_strip_premature_tracking_scripts($html) {
    if (!ysse_page_has_contact_form()) {
        $html = preg_replace('/<script\b[^>]*\brecaptcha[^>]*>[\s\S]*?<\/script>/i', '', $html);
        $html = preg_replace('/<script\b[^>]*gstatic\.com[^>]*recaptcha[^>]*>[\s\S]*?<\/script>/i', '', $html);
    }

    $html = preg_replace(
        '/(<body[^>]*>)\s*<script\b[^>]*googletagmanager\.com\/gtag\/js[^>]*>[\s\S]*?<\/script>\s*<script>[\s\S]*?AW-944505518[\s\S]*?<\/script>/i',
        '$1',
        $html
    );

    $html = preg_replace('/<script\b[^>]*\bgoogletagmanager\.com\/gtag\/js\b[^>]*>[\s\S]*?<\/script>/i', '', $html);
    $html = preg_replace('/<script\b[^>]*>[\s\S]*?\bgtag\s*\([\s\S]*?AW-944505518[\s\S]*?<\/script>/i', '', $html);
    $html = preg_replace('/<script\b[^>]*>\s*var gtm4wp_datalayer_name[\s\S]*?<\/script>/i', '', $html);
    $html = preg_replace('/<script\b[^>]*data-pagespeed-no-defer>\s*<\/script>/i', '', $html);
    $html = preg_replace('/<script\b[^>]*>\s*console\.warn[\s\S]*?GTM4WP[\s\S]*?<\/script>/i', '', $html);

    return $html;
}

function ysse_strip_render_blocking_bloat($html) {
    if (empty($html)) {
        return $html;
    }

    $html = preg_replace('/<style\b[^>]*\bid=["\']?wp-block-library-inline-css["\']?[^>]*>[\s\S]*?<\/style>/i', '', $html);
    $html = preg_replace('/<style>\s*\.blue-message\{[\s\S]*?<\/style>/i', '', $html);

    if (!ysse_page_has_contact_form()) {
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?custom-file-wrapper[\s\S]*?<\/style>/i', '', $html);
    }

    return $html;
}

function ysse_dequeue_block_library_assets() {
    if (is_admin()) {
        return;
    }

    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('global-styles');
}

function ysse_strip_gtm4wp_dead_code($html) {
    if (strpos($html, 'gtm4wp') === false && strpos($html, 'GTM4WP') === false) {
        return $html;
    }

    $html = preg_replace('/<!-- Google Tag Manager for WordPress by gtm4wp\.com -->[\s\S]*?<!-- End Google Tag Manager for WordPress by gtm4wp\.com -->/i', '', $html);

    return $html;
}

function ysse_strip_unused_third_party_assets($html) {
    if (strpos($html, 'mailmunch') !== false || strpos($html, '_mmunch') !== false) {
        $html = preg_replace('/<script\b[^>]*>\s*var _mmunch[\s\S]*?<\/script>/i', '', $html);
        $html = preg_replace('/<script\b[^>]*\bmailmunch\b[^>]*>[\s\S]*?<\/script>/i', '', $html);
        $html = preg_replace('/<script\b[^>]*a\.mailmunch\.co[^>]*>[\s\S]*?<\/script>/i', '', $html);
        $html = preg_replace('/<link\b[^>]*mailmunch[^>]*>/i', '', $html);
        $html = preg_replace('/<style\b[^>]*mailmunch[^>]*>[\s\S]*?<\/style>/i', '', $html);
        $html = preg_replace('/<div\b[^>]*mailmunch[^>]*>[\s\S]*?<\/div>/i', '', $html);
    }

    if (strpos($html, 'ajax.googleapis.com') !== false && strpos($html, '/cache/minify/') !== false) {
        $html = preg_replace('/<script\b[^>]*ajax\.googleapis\.com[^>]*jquery[^>]*>\s*<\/script>\s*/i', '', $html);
    }

    return $html;
}

function ysse_lazy_load_below_fold_images($html) {
    if (empty($html) || strpos($html, '<img') === false) {
        return $html;
    }

    return preg_replace_callback(
        '/<img\b([^>]*)>/i',
        function ($matches) {
            $attrs = $matches[1];

            if (preg_match('/\bloading\s*=/i', $attrs)) {
                return $matches[0];
            }

            if (preg_match('/\bfetchpriority\s*=\s*["\']high["\']/i', $attrs)
                || preg_match('/\bclass\s*=\s*["\'][^"\']*content_header_bg/i', $attrs)) {
                return $matches[0];
            }

            return '<img loading="lazy"' . $attrs . '>';
        },
        $html
    );
}

function ysse_optimize_output_html($html) {
    $html = ysse_fix_html_lang_attribute($html);
    $html = ysse_add_content_image_dimensions($html);
    $html = ysse_optimize_image_urls_in_html($html);
    $html = ysse_strip_render_blocking_bloat($html);
    $html = ysse_async_minify_css_in_html($html);
    $html = ysse_fix_minify_js_in_html($html);
    $html = ysse_strip_gtm4wp_dead_code($html);
    $html = ysse_strip_unused_third_party_assets($html);
    $html = ysse_strip_premature_tracking_scripts($html);
    $html = ysse_optimize_tracking_output($html);

    return $html;
}

function ysse_move_jquery_to_footer() {
    if (is_admin()) {
        return;
    }

    wp_scripts()->add_data('jquery', 'group', 1);
    wp_scripts()->add_data('jquery-core', 'group', 1);
    wp_scripts()->add_data('jquery-migrate', 'group', 1);
}

function ysse_defer_theme_scripts($tag, $handle, $src) {
    if (is_admin() || strpos($src, '/cache/minify/') === false) {
        return $tag;
    }

    if (strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
        return $tag;
    }

    return str_replace('<script ', '<script defer ', $tag);
}

function ysse_async_theme_styles($html, $handle, $href, $media) {
    return $html;
}

function ysse_dequeue_unused_plugin_assets() {
    wp_dequeue_style('cms-navigation-style-base');
    wp_dequeue_style('cms-navigation-style');
}

function ysse_footer_menu_heading_markup($item_output, $item, $depth, $args) {
    if (empty($args->theme_location) || $args->theme_location !== 'footer-menu') {
        return $item_output;
    }

    if ($depth !== 0 || !in_array('menu-item-has-children', (array) $item->classes, true)) {
        return $item_output;
    }

    $item_output = preg_replace('/<a\b[^>]*>/i', '<span class="footer-menu-heading">', $item_output, 1);
    $item_output = preg_replace('/<\/a>/i', '</span>', $item_output, 1);

    return $item_output;
}

function ysse_nav_menu_link_attributes($atts, $item, $args) {
    if (!empty($args->theme_location) && $args->theme_location === 'footer-menu' && in_array('menu-item-has-children', (array) $item->classes, true)) {
        return $atts;
    }

    if (empty($atts['href']) || $atts['href'] === '#' || $atts['href'] === home_url('/#')) {
        $title = trim(wp_strip_all_tags($item->title));
        $menu_targets = array(
            'Puitaknad' => '/puitaknad/',
            'Uksed' => '/valisuksed/',
        );

        foreach ($menu_targets as $menu_title => $path) {
            if (strcasecmp($title, $menu_title) === 0) {
                $atts['href'] = home_url($path);
                break;
            }
        }

        if (
            ($atts['href'] === '#' || empty($atts['href']))
            && in_array('menu-item-has-children', (array) $item->classes, true)
        ) {
            $atts['role'] = 'button';
            $atts['aria-haspopup'] = 'true';
        }
    }

    return $atts;
}

function ysse_optimize_content_html($content) {
    if (empty($content) || !is_string($content)) {
        return $content;
    }

    $content = ysse_clean_content_links($content);

    $content = preg_replace(
        '/(<a[^>]*href=["\'][^"\']*galerii[^"\']*["\'][^>]*>\s*<img[^>]*\b)alt=["\']puitaknad["\']/iu',
        '$1alt="' . esc_attr__('Puitaknade galerii', 'Ysse') . '"',
        $content
    );

    $content = ysse_add_content_image_dimensions($content);

    return $content;
}

function ysse_add_content_image_dimensions($content) {
    // Skip WP/Backbone/Underscore template markup ja admin context — selle <img>
    // parser kahjustaks {{ data.X }}, <%= %>, <# #> placeholder'eid (lõhub
    // näiteks WP Media Library tmpl-attachment Underscore template'i).
    if ( ! is_string( $content ) || $content === '' ) {
        return $content;
    }
    if ( is_admin()
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
        || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
        || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
        return $content;
    }
    if ( strpos( $content, '{{' ) !== false
        || strpos( $content, '<#' ) !== false
        || strpos( $content, '<%' ) !== false ) {
        return $content;
    }

    return preg_replace_callback(
        '/<img\b([^>]*?)>/i',
        function ($matches) {
            $tag = $matches[0];
            $needs_dimensions = !preg_match('/\bwidth\s*=/i', $tag);

            if (preg_match('/\bsrc=(["\'])([^"\']+)\1/i', $tag, $src_match)) {
                $url = $src_match[2];
            } elseif (preg_match('/\bsrc=([^\s>]+)/i', $tag, $src_match)) {
                $url = $src_match[1];
            } else {
                return $tag;
            }

            $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
            $fallback_w = 200;
            $fallback_h = 80;

            if (strpos($url, 'eestistoodetud') !== false) {
                $fallback_w = 200;
                $fallback_h = 80;
            }

            $dims = ysse_url_image_dimensions($url, 'full', $fallback_w, $fallback_h);
            $tag_out = $tag;

            if (strpos($url, 'eestistoodetud') !== false) {
                $tag_out = preg_replace('/\balt(?:=(["\'])[^"\']*\1)?/i', '', $tag_out);
                $tag_out = preg_replace('/\balt\b/i', '', $tag_out);
            }

            if ($needs_dimensions) {
                $tag_out = preg_replace(
                    '/<img\b/i',
                    '<img width="' . esc_attr($dims['width']) . '" height="' . esc_attr($dims['height']) . '"',
                    $tag_out,
                    1
                );
            }

            if (strpos($url, 'eestistoodetud') !== false) {
                $tag_out = preg_replace(
                    '/<img\b/i',
                    '<img alt="' . esc_attr__('Eestis toodetud', 'Ysse') . '"',
                    $tag_out,
                    1
                );
            } elseif (!preg_match('/\balt\s*=\s*["\'][^"\']+["\']/i', $tag_out)) {
                $tag_out = preg_replace('/\balt\s*=\s*["\'][\s]*["\']/i', '', $tag_out);
                $tag_out = preg_replace('/\balt\b/i', '', $tag_out);
                $filename = basename(parse_url($url, PHP_URL_PATH), '.' . pathinfo($url, PATHINFO_EXTENSION));
                $filename = str_replace(array('-', '_'), ' ', $filename);

                if ($filename !== '') {
                    $tag_out = preg_replace(
                        '/<img\b/i',
                        '<img alt="' . esc_attr(ucwords($filename)) . '"',
                        $tag_out,
                        1
                    );
                }
            }

            return $tag_out;
        },
        $content
    );
}

function ysse_optimize_tracking_output($html) {
    if (empty($html)) {
        return $html;
    }

    $html = preg_replace(
        '/<script([^>]*\bsrc=["\'][^"\']*(?:linkedin|licdn)[^"\']*["\'][^>]*)>/i',
        '<script defer$1>',
        $html
    );

    return $html;
}

function ysse_optimize_cached_html($data) {
    if (!is_array($data) || empty($data['content'])) {
        return $data;
    }

    if (!empty($data['c']) && $data['c'] === 'gzip') {
        $html = gzdecode($data['content']);
        if ($html !== false) {
            $html = ysse_optimize_output_html($html);
            $data['content'] = gzencode($html, 6);
        }

        return $data;
    }

    $data['content'] = ysse_optimize_output_html($data['content']);

    return $data;
}

function ysse_page_needs_slider() {
    if (is_front_page()) {
        return false;
    }

    return is_singular('products') || is_page(array('galerii', 'gallery', 'galleria'));
}

function ysse_page_needs_magnify() {
    if (is_page(array('galerii', 'gallery', 'galleria'))) {
        return true;
    }

    if (!is_singular('products')) {
        return false;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return false;
    }

    $color_card_image = get_field('color_card_image', $post_id);
    $color_card_image_zoom = get_post_meta($post_id, 'color_card_image_zoom', true);

    return !empty($color_card_image) && !empty($color_card_image_zoom);
}

function ysse_should_load_mailmunch() {
    return false;
}

function ysse_dequeue_external_cdn_scripts() {
    global $wp_scripts;

    if (!is_object($wp_scripts)) {
        return;
    }

    foreach ($wp_scripts->registered as $handle => $script) {
        if (empty($script->src)) {
            continue;
        }

        if (strpos($script->src, 'ajax.googleapis.com') !== false || strpos($script->src, 'mailmunch.co') !== false) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }
}

function ysse_disable_mailmunch() {
    if (ysse_should_load_mailmunch()) {
        return;
    }

    global $wp_filter;

    foreach (array('wp_head', 'wp_footer', 'wp_enqueue_scripts') as $hook) {
        if (!isset($wp_filter[$hook])) {
            continue;
        }

        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $function = $callback['function'];

                if (
                    is_array($function)
                    && is_object($function[0])
                    && $function[0] instanceof Mailchimp_Mailmunch_Public
                ) {
                    remove_action($hook, $function, $priority);
                }
            }
        }
    }

    wp_dequeue_script('mailmunch-script');
    wp_deregister_script('mailmunch-script');
}

function ysse_page_has_contact_form() {
    if (is_admin()) {
        return false;
    }

    global $post;

    if ($post instanceof WP_Post) {
        if (has_shortcode($post->post_content, 'contact-form-7')) {
            return true;
        }

        if (strpos($post->post_content, '[contact-form-7') !== false) {
            return true;
        }
    }

    if (is_page(array('kontakt', 'contact', 'yhteystiedot'))) {
        return true;
    }

    return false;
}

function ysse_is_test_site() {
    $host = wp_parse_url(home_url(), PHP_URL_HOST);

    return $host === 'test.ysse.ee';
}

function ysse_body_class_scroll_reveal($classes) {
    if (ysse_is_test_site()) {
        $classes[] = 'ysse-scroll-reveal-enabled';
    }

    return $classes;
}

function template_scripts() {
    if ($GLOBALS['pagenow'] == 'wp-login.php' || is_admin()) {
        return;
    }

    wp_enqueue_script('script', get_template_directory_uri() . '/js/script.js', array('jquery'), '1.0.25', true);
    wp_enqueue_script('cookie', get_template_directory_uri() . '/js/lib/jquery.cookie.js', array('jquery'), '1.0.0', true);

    if (ysse_page_needs_slider()) {
        wp_enqueue_script('slick', get_template_directory_uri() . '/assets/js/slick.min.js', array('jquery'), '1.0.0', true);
    }

    if (ysse_page_needs_magnify()) {
        wp_enqueue_script('magnify', get_template_directory_uri() . '/assets/js/jquery.magnify.js', array('jquery'), '1.0.0', true);
    }
}

function ysse_remove_jquery_migrate($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            array('jquery-migrate')
        );
    }
}

function ysse_conditional_cf7_assets($load) {
    return ysse_page_has_contact_form();
}

function ysse_dequeue_unused_assets() {
    if (ysse_page_has_contact_form()) {
        return;
    }

    wp_dequeue_style('contact-form-7');
    wp_dequeue_script('contact-form-7');
    wp_dequeue_script('swv');
    wp_dequeue_script('google-recaptcha');
    wp_dequeue_script('wpcf7-recaptcha');

    if (!ysse_should_load_mailmunch()) {
        wp_dequeue_script('mailmunch-script');
        wp_deregister_script('mailmunch-script');
    }
}

function ysse_maybe_add_file_upload_script() {
    if (ysse_page_has_contact_form()) {
        add_file_upload_translation_script();
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
    $html .= '<div class="post_image d-flex justify-content-center align-items-center "' . ysse_style_background_image($attributes['image']) . '>';
        $html .= '<h1>'.$attributes['title'].'</h1>';
    $html .= '</div>';
    $html .= '<div class="post_content"><div class="post_content_container">';

    return $html;
}

add_shortcode( 'render_image_block', 'render_image_block_shortcode' );


// Add Actions
add_action('init', 'ysse_disable_mailmunch', 20);
add_action('wp', 'ysse_disable_mailmunch', 999);
add_action('wp_enqueue_scripts', 'ysse_move_jquery_to_footer', 1);
add_filter('script_loader_tag', 'ysse_defer_theme_scripts', 10, 3);
add_filter('style_loader_tag', 'ysse_async_theme_styles', 10, 4);
add_action('wp_enqueue_scripts', 'template_styles');
add_action('wp_enqueue_scripts', 'template_scripts');
add_action('wp_head', 'ysse_print_lcp_preloads', 1);
add_action('wp_head', 'ysse_print_critical_css', 2);
add_action('template_redirect', function () {
    // Skip admin, AJAX, REST API, XML-RPC, cron, CLI — output filter on
    // ainult front-end HTML jaoks. REST API on eriti oluline: Gutenberg
    // postitab läbi REST-i ja kui me muudame JSON vastust, salvestamine murdub.
    if ( is_admin()
        || wp_doing_ajax()
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
        || ( defined( 'DOING_CRON' ) && DOING_CRON )
        || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
        return;
    }

    ob_start('ysse_optimize_output_html');
}, 99999);
add_filter('w3tc_process_content', 'ysse_optimize_output_html', 99999);
add_filter('w3tc_pagecache_set', 'ysse_optimize_cached_html', 99999, 3);
add_action('wp_enqueue_scripts', 'ysse_dequeue_block_library_assets', 100);
add_action('wp_enqueue_scripts', 'ysse_dequeue_unused_plugin_assets', 100);
add_action('wp_enqueue_scripts', 'ysse_dequeue_external_cdn_scripts', 999);
add_filter('walker_nav_menu_start_el', 'ysse_footer_menu_heading_markup', 10, 4);
add_filter('nav_menu_link_attributes', 'ysse_nav_menu_link_attributes', 10, 3);
add_filter('the_content', 'ysse_optimize_content_html', 20);
add_filter('acf/format_value/type=wysiwyg', 'ysse_optimize_content_html', 20);
add_filter('acf/format_value/type=textarea', 'ysse_optimize_content_html', 20);
add_action('wp_default_scripts', 'ysse_remove_jquery_migrate');
add_action('wp_footer', 'ysse_maybe_add_file_upload_script');
add_action('wp_enqueue_scripts', 'ysse_dequeue_unused_assets', 100);
add_filter('wpcf7_load_js', 'ysse_conditional_cf7_assets');
add_filter('wpcf7_load_css', 'ysse_conditional_cf7_assets');
add_filter('wpml_ls_html', 'ysse_wpml_ls_html_flag_alt', 10, 3);
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
add_filter('body_class', 'ysse_body_class_scroll_reveal');
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
    ob_start();
    include(locate_template('parts/part-analytics-code.php'));
    echo ysse_optimize_tracking_output(ob_get_clean());
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

/**
 * SEO meta overrides — Yoast title/desc/og/schema filtrid kõrge-prioriteetsete
 * sihtlehtede jaoks. Vt inc/seo-overrides.php täpsema dokumentatsiooni jaoks.
 */
require_once get_template_directory() . '/inc/seo-overrides.php';

/**
 * ACF field group "Tehtud tööd" — AJUTISELT VÄLJAS (media library debug)
 */
// require_once get_template_directory() . '/inc/acf-completed-works.php';

?>
