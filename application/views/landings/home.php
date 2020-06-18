
<div class="space"></div>

<div class="content">
	<div class="container-fluid">

		<div id="carousel">
		    <!--
		        IMPORTANT - This carousel can have a special class for a smooth
				transition "gsdk-transition". Since javascript cannot be
				overwritten, if you want to use it, you can use the bootstrap.js
				or bootstrap.min.js from the GSDKit or you can open your
				bootstrap.js file, search for "emulateTransitionEnd(600)" and
				change it with "emulateTransitionEnd(1200)"
		    -->
			<div id="inventory-carousel" class="carousel slide" data-ride="carousel">
			  <!-- Indicators -->
			  <ol class="carousel-indicators">

				<?php
				$slides = '';
				$slide_count = 0;

				foreach ($inventory as $row) {
					// Load a variable with slides and initialize slide definitions
					echo '<li data-target="#inventory-carousel" data-slide-to="' . $slide_count . '" ';
					if ($slide_count == 0 ) {
						echo 'class="active"';
						$slides .= '<div class="item active">';
					} else {
						$slides .= '<div class="item">';

					}
					echo '></li>';

					$slides .= '  <img src="' . base_url() . 'assets/inventory/' . $row->inv_directory . '/' . $row->landing_image . '" alt="Awesome Image" >';
					$slides .= '</div>';

					$slide_count +=1;
				}
				 ?>
		      </ol>

		      <!-- Wrapper for slides -->
		      <div class="carousel-inner">
				  <?=$slides?>
		      </div>

		      <!-- Controls -->
		      <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
		        <span class="fa fa-angle-left"></span>
		      </a>
		      <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
		        <span class="fa fa-angle-right"></span>
		      </a>
		    </div>
		</div> <!-- end carousel -->

		<!--  Floor plans if they exist     -->
		<div id="inventory">

		<?php
		if (isset($inventory)) {
			?>
				<div class="container">
					<h1>Our Inventory</h1>
					<?php
					foreach ($inventory as $row) {
						?>
						<div class="row">
							<div class="col-md-3 col-sm-3">
								<div style="vertical-align: text-top;">
									<a href="<?=base_url() . 'index.php/main/view/' . $row->id?>">
								<img src="<?=base_url() . 'assets/inventory/' . $row->inv_directory . '/' . $row->landing_image?>" alt="Rounded Image" style="max-width: 100%;max-height: 100%" class="bio-image-container img-rounded">
							</a>
							</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<h4><?=$row->inv_name?></h4>
								<h5><?=$row->inv_desc_short?></h5>

							</div>

							<?php if (isset($row->flythru_link)) {?>
								<div class="col-md-6 col-sm-6">

								<embed type="text/html" src="<?=$row->flythru_link?>"  width="500" height="200">
								</div>
							<?php }  ?>

						</div>
					<?php
					}
					?>
				</div>
			<?php
		} ?>
		</div>

		<div class="space"></div>

		<!--  Bios if they exist -->
		<?php
		if (isset($bio_records)) {
		?>
		<div id="bios">
			<div class="container">
			<h1>Our people</h1>

			<?php
			foreach ($bio_records as $row) {
				?>
				<div class="row">
					<div class="col-md-3 col-sm-3">
						<div style="vertical-align: text-top;">

						<img src="<?=$row->bio_image?>" alt="Rounded Image" style="max-width: 100%;max-height: 100%" class="bio-image-container img-rounded">
					</div>
					</div>
					<div class="col-md-6 col-sm-6">
						<h4><?=$row->bio_name?></h4>
						<h5><?=$row->bio_title?></h5>
						<h5><?=$row->bio_companies?></h5>
						<?=$row->bio_description?>
					</div>

				</div>
			<?php
			}
			?>

		</div> <!-- end bios -->
	</div> <!-- end container  -->
		<?php  }  ?>
	</div>
</div>
<div class="space"></div>
