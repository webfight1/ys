<?php get_header(); ?>

<?php 
$column = get_field('Column');
$show_block = get_field('show_ask_windows_and_doors_offer_block');
$show_block_wide = get_field('show_ask_windows_and_doors_offer_block_wide');
?>

<div id="content" class="default-page">
	<div class="default_page_header	d-flex justify-content-center align-items-center">
		<div class="title_container">
			<h1><?php the_title(); ?></h1>
		</div>
	</div>

<?php if(get_the_content() && get_the_content() !== ''): ?>
	<div class="page_content">
		<div class="post_content_container">
			<?php the_content(); ?>
		</div>
	</div>
<?php endif; ?>

	<?php if($column): ?>
		<div class="page_table">
			<div class="page_table_columns d-flex justify-content-center">
				<?php foreach($column as $col): ?>
					<div class="column">
						<?php if($col['title']): ?>
							<div class="column_header">
								<h2><?php echo $col['title']; ?></h2>
							</div>
						<?php endif; ?>
					
						<?php if($col['title']): ?>
							<div class="rows">
								<?php foreach($col['Row'] as $row): ?>
									<?php if($row['value']): ?>
										<div class="rows_row">
											<?php echo $row['value']; ?>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

    <?php if($show_block): ?>
		<?php get_template_part( 'partials/ask-for-offer', 'offer' ); ?>
	<?php endif; ?>

	<?php if($show_block_wide): ?>
		<?php get_template_part( 'partials/ask-for-offer-wide', 'offer' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
