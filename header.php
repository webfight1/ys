<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
	<head>



		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php wp_title(''); ?></title>

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">


		<?php wp_head(); ?>
		<meta name="google-site-verification" content="4kCibqTcwZXUxoWLQyW5e9j7ulrh-09rBfUrj1Fdlrs" />
	</head>
	<body <?php body_class(); ?>>
		<?php
		$logo = get_field('logo', 'option');
		$logo_dims = $logo ? ysse_url_image_dimensions($logo, 'full', 120, 40) : null;
		?>

		<header>
			<div class="header_container d-flex justify-content-between align-items-center">
				<?php if($logo) : ?>
					<div id="logo">
						<a href="<?php echo home_url(); ?>">
							<img class="svg logo-img" src="<?php echo $logo; ?>" alt="Ysse logo" width="<?php echo esc_attr($logo_dims['width']); ?>" height="<?php echo esc_attr($logo_dims['height']); ?>">
						</a>
					</div>
				<?php endif; ?>

				<button type="button" class="mobile-menu-btn" aria-label="<?php esc_attr_e('Ava menüü', 'Ysse'); ?>" aria-expanded="false">
					<span></span>
					<span></span>
					<span></span>
				</button>
				<div class="menu_container d-flex align-items-center">
					<div id="main_nav">
						<nav>
							<?php template_nav(); ?>
						</nav>
					</div>

					<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Language_Switcher")) : ?><?php endif; ?>
					<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Language_Switcher_Mobile")) : ?><?php endif; ?>
				</div>
			</div>
		</header>

		<main id="main-content">
