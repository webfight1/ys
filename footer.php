		<footer>
			<?php
			$cookies_allowed = $_COOKIE['ysse_cookie'];
			$logo = get_field('logo', 'option');
			$footer_text = get_field('footer_text', 'option');
			$footer_social = get_field('footer_social', 'option');
			$cookies_text = get_field('cookies_text', 'option');
			$cookies_accept_text = get_field('cookies_accept_text', 'option');
			$cookies_deny_text = get_field('cookies_deny_text', 'option');
			?>

			<div class="footer_container d-flex justify-content-between">
				<div class="footer_left">
					<div class="footer_Left_top d-flex align-items-center">
						<?php if($logo) : ?>
							<div id="footer_logo">
								<a href="<?php echo home_url(); ?>">
									<img class="svg" src="<?php echo $logo; ?>" alt="Ysse logo" class="logo-img">
								</a>
							</div>
							<p>© <?php echo date("Y"); ?> Ysse OÜ</p>
						<?php endif; ?>
					</div>
					
					<?php if($footer_text): ?>
						<div class="footer_text">
							<?php echo $footer_text; ?>
						</div>
					<?php endif; ?>

					<?php if($footer_social): ?>
						<div class="footer_social d-flex flex-wrap">
							<?php foreach($footer_social as $social): ?>
								<?php if($social['icon'] && $social['url']): ?>
									<a href="<?php echo $social['url']; ?>">
										<img src="<?php echo $social['icon']; ?>" />
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				

				<div class="footer_right">
					<?php wp_nav_menu(array('theme_location' => 'footer-menu')); ?>
				</div>
			</div>
		</footer>

		<div id="cookie_notification_container">
			<div id="cookie_notification">
				<div class="cookie_container">
					<div class="cookie_content_container">
						<div class="cookie_content">
							<?php echo $cookies_text; ?>
						</div>

						<div class="cookies_buttons">
							<div id="accept_cookies" class="cookie_button">
								<span><?php echo $cookies_accept_text; ?></span>
							</div>
							<div class="button_spacer"></div>
							<div id="deny_cookies" class="cookie_button">
								<span><?php echo $cookies_deny_text; ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php wp_footer(); ?>
	</body>
</html>