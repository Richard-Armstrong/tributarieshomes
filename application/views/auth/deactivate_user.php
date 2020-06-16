
<div class="content">
	<div class="container-fluid">
		<h1>Deactivate User</h1>

		<p><?= sprintf(lang('deactivate_subheading'), "{$user->first_name} {$user->last_name}") ?></p>

		<?= form_open("auth/deactivate/{$user->id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Yes</label>
					<input type="radio" name="confirm" value="yes" checked="checked"/>
					<label class="form-control-label">No</label>
					<input type="radio" name="confirm" value="no"/>
				</div>

				<?= form_hidden($csrf); ?>
				<?= form_hidden(array('id' => $user->id)) ?>

				<button type="submit" class="btn btn-primary">Submit</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
