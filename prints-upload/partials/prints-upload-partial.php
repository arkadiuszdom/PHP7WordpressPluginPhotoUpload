<?php
$uploaded_prints_thumbnails_urls = $this->get_uploaded_prints_thumbnails_urls();
foreach ($this->get_print_cart_items() as $print_cart_item):?>
	<div class="file_upload_container" data-product_id="<?php echo $print_cart_item['product_id'] ?>">
		<h3 class="file_upload_header">Wgraj zdjęcia - <?php echo $print_cart_item['product_name']?></h3>
		<div>
			<form method="POST" action="#" class="files_upload_form" max_quantity="<?php echo $print_cart_item['quantity']; ?>" product_id="<?php echo $print_cart_item['product_id']; ?>">
				<input id='prints_upload_input' accept="image/jpeg" type='file' multiple>
				<input type='submit' value="Wgraj pliki">
			</form>

			<div class="uploaded_files" >
				
				<?php if( isset( $uploaded_prints_thumbnails_urls[ $print_cart_item['product_id'] ] ) ):
					$filenames = scandir( wp_upload_dir()['basedir'] .  $uploaded_prints_thumbnails_urls[ $print_cart_item['product_id'] ] );
					foreach ( $filenames as $filename):
						if( $filename != '.' && $filename != '..' ): ?>
							<img src="<?php echo wp_upload_dir()['baseurl'] . $uploaded_prints_thumbnails_urls[ $print_cart_item['product_id'] ] . '/' . $filename;?>"/>
						<?php endif;?>
					<?php endforeach;?>
				<?php endif;?>
			</div>
		</div>
	</div>
<?php endforeach; ?>