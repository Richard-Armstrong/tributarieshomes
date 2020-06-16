

<div class="content">
		<!--  Floor plans if they exist     -->
		<div id="inventory">

		<?php
		if (isset($records)) {
			?>
				<div class="container">
					<h1><?=$inv_record->inv_name?></h1>
					<h4><?=$inv_record->inv_description?></h4>
					<?php
					$item_count = 1;

					foreach ($records as $row) {
						if ($item_count == 1 )
							echo '<div class="row">'
						?>
						   <div class = "col-sm-6 col-md-3">
						      <a href = "<?=base_url() . 'assets/inventory/' . $inv_record->inv_directory . '/' .$row ?>" target="_blank" class = "thumbnail">
						         <img src = "<?=base_url() . 'assets/inventory/' . $inv_record->inv_directory . '/' .$row ?>" alt = "Generic placeholder thumbnail"/>
						      </a>
						   </div>

					<?php
						$item_count += 1;
						if ($item_count == 4) {
							echo "</div>";
							$item_count  = 1;
						}
					}
					?>
				</div>
			<?php } ?>
		</div>
	</div> <!-- end container  -->
<div class="space"></div>
