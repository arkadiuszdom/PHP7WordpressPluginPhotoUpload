<form method="POST" action="#" id="plugin_configuration">
	Kategoria odbitek:
	<select name="prints_category_id">	
  		<option value="-1" <?php if($this->prints_category_id == '-1') print(' selected');?>></option>	
	<?php foreach ( $this->get_products_categories() as $products_category ): ?>
  		<option value="<?php echo $products_category['id']; ?>"<?php if($this->prints_category_id == $products_category['id']) print(' selected');?>><?php echo $products_category['name']; ?></option>		
	<?php endforeach; ?>
	</select>
	<br/>
	Nazwa folderu odbitek:
	<input type='text' name="uploaded_prints_dir" value="<?php echo $this->uploaded_prints_dir ?>">
	<br/>
	Prefiks folderów odbitek od złożonych zamówień:
	<input type='text' name="placed_order_uploaded_prints_dir_prefix" value="<?php echo $this->placed_order_uploaded_prints_dir_prefix ?>">
	<input type="hidden" name="action" value="configure_websystems_prints_plugin">
	<br/>
	<input type='submit' id="plugin_configuration_submit" value="Aktualizuj">
</form>