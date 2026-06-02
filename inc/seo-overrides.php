<?php
/**
 * SEO meta overrides
 *
 * Centraliseeritud Yoast SEO meta-andmete ülekirjutused.
 * Eesmärk: parandada SERP nähtavust kõrge-prioriteetsetel sihtlehtedel
 * ilma Yoast admin UI sõltuvuseta (kõik git-trackitav).
 *
 * Mõjutab:
 *  - /puitaknad/ — title + meta description (asendab Yoast manuaalset "Puitaknad" overridet)
 *  - Kogu sait — fallback og:image ja twitter:image
 *  - /puitaknad/ — täiendav Service schema node
 *
 * NB: et eemaldada manuaalne Yoast override, kustuta vastav rida wp_postmeta
 * (_yoast_wpseo_title) — või jäta selleks ajaks kui see filter aktiivne on.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kontrollib, kas praegune päring vastab /puitaknad/ sihtlehele.
 *
 * @return bool
 */
function ysse_is_puitaknad_page() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return $post->post_name === 'puitaknad';
}

/**
 * Kontrollib, kas päringuobjekt on `products` custom post type.
 *
 * @return bool
 */
function ysse_is_products_cpt() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	return ( $post instanceof WP_Post ) && $post->post_type === 'products';
}

/**
 * Tagastab praeguse päringu "puhta" URL-i (path ilma query-stringita), absoluutsena.
 *
 * Vajalik kuna `products` CPT on registreeritud `'rewrite' => false`-iga,
 * mistõttu `get_permalink()` tagastab `?products=slug` vormi. Päris kasutaja-
 * URL on `/<slug>/` (Redirection plugin teeb 301 query-vormist).
 * Seetõttu peame canonical/og:url/schema @id jaoks kasutama tegelikku request-i.
 *
 * @return string Absoluutne URL.
 */
function ysse_current_clean_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path = strtok( $uri, '?' );

	// Kui kuidagi sattusime ?products=... vormi (otsekäik), genereerime path-i
	// post_name-st. Sel juhul me ei tea keelt täpselt, aga see on edge-case.
	if ( $path === '/' || $path === '' || strpos( $path, 'products=' ) !== false ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && ! empty( $post->post_name ) ) {
			$path = '/' . $post->post_name . '/';
		} else {
			$path = '/';
		}
	}

	return home_url( $path );
}

/**
 * Konstandid — vaikemeta ja vaikepilt.
 */
define( 'YSSE_PUITAKNAD_TITLE', 'Puitaknad otse tootjalt — kõrge energiatõhusus | Ysse OÜ' );
define( 'YSSE_PUITAKNAD_DESC',  'Kodumaised energiatõhusad puitaknad kolmekordse klaaspaketiga. Tootmine, paigaldus ja transport üle Eesti. Küsi tasuta pakkumist.' );
define( 'YSSE_DEFAULT_SHARE_IMAGE', home_url( '/wp-content/uploads/2019/05/shutterstock_439608823-min.webp' ) );

/**
 * Override SEO title for /puitaknad/.
 *
 * @param string $title Praegune Yoast genereeritud title.
 * @return string
 */
add_filter( 'wpseo_title', function ( $title ) {
	if ( ysse_is_puitaknad_page() ) {
		return YSSE_PUITAKNAD_TITLE;
	}
	return $title;
}, 20 );

/**
 * Override meta description for /puitaknad/.
 */
add_filter( 'wpseo_metadesc', function ( $desc ) {
	if ( ysse_is_puitaknad_page() ) {
		return YSSE_PUITAKNAD_DESC;
	}
	return $desc;
}, 20 );

/**
 * og:title ja og:description peavad kattuma title/desc-iga.
 */
add_filter( 'wpseo_opengraph_title', function ( $title ) {
	if ( ysse_is_puitaknad_page() ) {
		return YSSE_PUITAKNAD_TITLE;
	}
	return $title;
}, 20 );

add_filter( 'wpseo_opengraph_desc', function ( $desc ) {
	if ( ysse_is_puitaknad_page() ) {
		return YSSE_PUITAKNAD_DESC;
	}
	return $desc;
}, 20 );

add_filter( 'wpseo_twitter_title', function ( $title ) {
	if ( ysse_is_puitaknad_page() ) {
		return YSSE_PUITAKNAD_TITLE;
	}
	return $title;
}, 20 );

add_filter( 'wpseo_twitter_description', function ( $desc ) {
	if ( ysse_is_puitaknad_page() ) {
		return YSSE_PUITAKNAD_DESC;
	}
	return $desc;
}, 20 );

/**
 * Sunni canonical ja og:url näitama clean URL-i (mitte ?products=slug).
 *
 * Kriitiline: ilma selleta indekseerib Google `?products=puitaknad`-style
 * URL-id, mitte clean URL-e — kuigi Redirection plugin teeb 301 user-i jaoks.
 * Google järgib canonical-it, mitte 301-i, kui canonical viitab teisele URL-ile.
 */
add_filter( 'wpseo_canonical', function ( $canonical ) {
	if ( ysse_is_products_cpt() ) {
		return ysse_current_clean_url();
	}
	return $canonical;
}, 20 );

add_filter( 'wpseo_opengraph_url', function ( $url ) {
	if ( ysse_is_products_cpt() ) {
		return ysse_current_clean_url();
	}
	return $url;
}, 20 );

/**
 * Fallback og:image / twitter:image kui Yoast / postmeta-st pole pilti määratud.
 *
 * NB: Yoasti `wpseo_opengraph_image` ja `wpseo_twitter_image` filtrid jooksevad
 * AINULT siis kui Yoastil on mingi pildi-väärtus olemas. Kui Yoast otsustab
 * et og:image tagi ei väljasta üldse (pole featured image ega Yoast Social
 * default-i), siis filter ei käivitu ja tag puudub HTML-st.
 *
 * Lahendus: jätame filtrid alles kui Yoast väljastab pildi (õige sünteesi
 * jaoks) ja lisaks väljastame fallback-i `wp_head`-is, kui pildi-tagi pole.
 */
add_filter( 'wpseo_opengraph_image', function ( $image ) {
	if ( empty( $image ) ) {
		return YSSE_DEFAULT_SHARE_IMAGE;
	}
	return $image;
}, 20 );

add_filter( 'wpseo_twitter_image', function ( $image ) {
	if ( empty( $image ) ) {
		return YSSE_DEFAULT_SHARE_IMAGE;
	}
	return $image;
}, 20 );

/**
 * Output buffer wp_head — kui Yoast lõpetas ja og:image/twitter:image tag
 * on ikkagi puudu, lisa fallback. Käivitub priority 999 → pärast Yoasti.
 */
add_action( 'wp_head', function () {
	// Käivita ainult kui Yoast on aktiivne (muidu konflikt teema OG-ga puudub)
	if ( ! defined( 'WPSEO_VERSION' ) ) {
		return;
	}

	// Märgi flag-iga et tagid lisatakse hilisemas hook-is — siis kontrollime
	// väljundit lõpus. Lihtsam: kasuta wp_head priority 999 ja kontrolli
	// kas tag on juba olemas globaalse muutuja kaudu, mille set-ib Yoast.
	// Praktiline lähenemine: alati lisa fallback meta NIME-RUUMIS prefiksiga
	// `ysse-` mida brauser ignoreerib KUI standardne og:image on olemas.
	// Lihtsam veel: kasuta has_action / get_post_thumbnail_id eelkontrolli.

	$has_image = false;

	// Kontrolli kas leheküljel on featured image — sel juhul Yoast väljastab og:image
	if ( is_singular() && has_post_thumbnail() ) {
		$has_image = true;
	}

	if ( $has_image ) {
		return;
	}

	$img = esc_url( YSSE_DEFAULT_SHARE_IMAGE );
	echo "\n<!-- ysse seo-overrides fallback share image -->\n";
	echo '<meta property="og:image" content="' . $img . '" />' . "\n";
	echo '<meta property="og:image:width" content="1200" />' . "\n";
	echo '<meta property="og:image:height" content="630" />' . "\n";
}, 999 );

/**
 * Laienda Yoast Schema @graph-i Service node-iga /puitaknad/ lehel.
 *
 * Lisab struktureeritud info, mille põhjal Google võib genereerida rich snippetid
 * (Service tüüpi tulemused — teenusepakkuja, pakkumise valdkond, teenusetüüp).
 *
 * @param array $graph Yoast Schema @graph elementide massiiv.
 * @return array
 */
/**
 * Paranda Schema @graph WebPage + BreadcrumbList `@id` ja `url` väärtused
 * kõigil products CPT lehtedel, et nad osutaks clean URL-ile (mitte ?products=).
 */
add_filter( 'wpseo_schema_graph', function ( $graph ) {
	if ( ! ysse_is_products_cpt() ) {
		return $graph;
	}

	$clean_url = ysse_current_clean_url();

	foreach ( $graph as &$node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
			continue;
		}

		$types = is_array( $node['@type'] ) ? $node['@type'] : array( $node['@type'] );

		if ( in_array( 'WebPage', $types, true ) ) {
			$node['@id'] = $clean_url;
			$node['url'] = $clean_url;
		}

		if ( in_array( 'BreadcrumbList', $types, true ) ) {
			$node['@id'] = $clean_url . '#breadcrumb';
		}
	}
	unset( $node );

	return $graph;
}, 15 );

add_filter( 'wpseo_schema_graph', function ( $graph ) {
	if ( ! ysse_is_puitaknad_page() ) {
		return $graph;
	}

	$service_node = array(
		'@type'         => 'Service',
		'@id'           => home_url( '/puitaknad/#service' ),
		'name'          => 'Puitakende tootmine, müük ja paigaldus',
		'serviceType'   => 'Puitakende tootmine',
		'description'   => YSSE_PUITAKNAD_DESC,
		'url'           => home_url( '/puitaknad/' ),
		'areaServed'    => array(
			'@type' => 'Country',
			'name'  => 'Eesti',
		),
		'provider'      => array(
			'@type' => 'Organization',
			'name'  => 'Ysse OÜ',
			'url'   => home_url( '/' ),
		),
		'category'      => 'Aknad ja uksed',
		'audience'      => array(
			'@type' => 'Audience',
			'audienceType' => 'Eraisikud ja ehitusettevõtted',
		),
	);

	$graph[] = $service_node;

	return $graph;
}, 20 );

/**
 * TODO: kui sisule lisatakse FAQ-sektsioon, lülita see sisse,
 * et anda Yoast-ile FAQPage rich snippet andmed.
 *
 * Ootab nähtavate FAQ küsimuste lisamist /puitaknad/ lehele.
 */
