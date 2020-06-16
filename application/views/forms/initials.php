
<div style="text-align:center">
	<?php if ($form) : ?>
		<img src="data:image/jpeg;base64,<?= base64_encode($form->signature) ?>"/>
	<?php else : ?>
		<p>Signature not found.</p>
	<?php endif ?>
</div>
