</div>








<!-- end container -->



</div><!-- end main -->

<div class="parallax-pro">
    <div class="img-src" style="background-image: url('<?=base_url();?>assets/img/bg6.jpg');"></div>
    <div class="container">

        <div class="space-30"></div>
        <div class="row">
             <div class="col-md-12 text-center">
                <div class="credits">
                    &copy; <script>document.write(new Date().getFullYear())</script> Tributaries Real Estate Group
                </div>
            </div>
        </div>
    </div>

</div>

</body>

    <script src="<?=base_url();?>vendor/jquery/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="<?=base_url();?>assets/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>

	<script src="<?=base_url();?>vendor/bootstrap3/js/bootstrap.js" type="text/javascript"></script>
	<script src="<?=base_url();?>assets/js/gsdk-checkbox.js"></script>
	<script src="<?=base_url();?>assets/js/gsdk-radio.js"></script>
	<script src="<?=base_url();?>assets/js/gsdk-bootstrapswitch.js"></script>
	<script src="<?=base_url();?>assets/js/get-shit-done.js"></script>
    <script src="<?=base_url();?>assets/js/custom.js"></script>

<script type="text/javascript">

    $('.btn-tooltip').tooltip();
    $('.label-tooltip').tooltip();
    $('.pick-class-label').click(function(){
        var new_class = $(this).attr('new-class');
        var old_class = $('#display-buttons').attr('data-class');
        var display_div = $('#display-buttons');
        if(display_div.length) {
        var display_buttons = display_div.find('.btn');
        display_buttons.removeClass(old_class);
        display_buttons.addClass(new_class);
        display_div.attr('data-class', new_class);
        }
    });
    $( "#slider-range" ).slider({
		range: true,
		min: 0,
		max: 500,
		values: [ 75, 300 ],
	});
	$( "#slider-default" ).slider({
			value: 70,
			orientation: "horizontal",
			range: "min",
			animate: true
	});
	$('.carousel').carousel({
      interval: 4000
    });


</script>
</html>
