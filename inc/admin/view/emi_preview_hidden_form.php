<div class="emi-edit" id="emi-edit-<?php echo $r['event_id']; ?>" style="display:none">
	<fieldset class="inline-edit-left">
		<label>
			<span class="label-text"><?php _e("Title", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<input name="emi[<?php echo $r['event_id']; ?>][event][db_event_name]" id="emi-event_name-<?php echo $r['event_id']; ?>" value="<?php echo $r['event_name']; ?>" />
			</span>
		</label>
		<br class="clear" />
	</fieldset>
	<fieldset class="inline-edit-center">
		<label>
			<span class="label-text"><?php _e("Start Date", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<input name="emi[<?php echo $r['event_id']; ?>][event][db_event_start_date]" class="emi-event_start_date" id="emi-event_start_date-<?php echo $r['event_id']; ?>"
					value="<?php echo $r['event_start_date']; ?>" default="<?php echo $r['event_start_date']; ?>" parent="<?php echo $r['event_id']; ?>">
			</span>
		</label>
		<label>
			<span class="label-text"><?php _e("End Date", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<input name="emi[<?php echo $r['event_id']; ?>][event][db_event_end_date]" class="emi-event_end_date" id="emi-event_end_date-<?php echo $r['event_id']; ?>"
					value="<?php echo $r['event_end_date']; ?>" default="<?php echo $r['event_end_date']; ?>" parent="<?php echo $r['event_id']; ?>">
			</span>
		</label>
		<label>
			<span class="label-text"><?php _e("Start hour", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<input name="emi[<?php echo $r['event_id']; ?>][event][db_event_start_time]" class="emi-event_start_time" id="emi-event_start_time-<?php echo $r['event_id']; ?>"
					value="<?php echo $r['event_start_time']; ?>" default="<?php echo $r['event_start_time']; ?>"/>
			</span>
		</label>
		<label>
			<span class="label-text"><?php _e("End hour", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<input name="emi[<?php echo $r['event_id']; ?>][event][db_event_end_time]" class="emi-event_end_time" id="emi-event_end_time-<?php echo $r['event_id']; ?>"
					value="<?php echo $r['event_end_time']; ?>" default="<?php echo $r['event_start_time']; ?>"/>
			</span>
		</label>
	</fieldset>
	<fieldset class="inline-edit-right">
		<label>
			<span class="label-text"><?php _e("Content", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<textarea name="emi[<?php echo $r['event_id']; ?>][event][db_post_content]" id="emi-post_content-<?php echo $r['event_id']; ?>"><?php echo $r['post_content']; ?></textarea>
			</span>
		</label>
		<label>
			<span class="label-text"><?php _e("Category", "emi"); ?> : </span>
			<span class="input-text-wrap">
				<input readonly name="emi[<?php echo $r['event_id']; ?>][event][db_post_category]" id="emi-event_category_id-<?php echo $r['event_id']; ?>" value="<?php echo $r['event_category_id']; ?>" />
			</span>
		</label>
	</fieldset>

	<div class="clear"></div>
	<fieldset class="inline-edit-left">

		<?php foreach ($r['meta_est'] as $key => $value): ?>

			<label>
				<span class="label-text"><?php _e($key, "emi"); ?> : </span>
				<span class="input-text-wrap">
					<input name="emi[<?php echo $r['event_id']; ?>][event][meta_est][<?php $key ?>]" id="emi-<?php $key ?>-<?php echo $r['event_id']; ?>" value="<?php echo $value; ?>" />
				</span>
			</label>
			<div class="clear"></div>

		<?php endforeach; ?>

	</fieldset>
	<fieldset class="inline-edit-center">

		<?php foreach ($r['meta_rus'] as $key => $value): ?>

			<label>
				<span class="label-text"><?php _e($key, "emi"); ?> : </span>
				<span class="input-text-wrap">
					<input name="emi[<?php echo $r['event_id']; ?>][event][meta_rus][<?php $key ?>]" id="emi-<?php $key ?>-<?php echo $r['event_id']; ?>" value="<?php echo $value; ?>" />
				</span>
			</label>
			<div class="clear"></div>

		<?php endforeach; ?>
	</fieldset>

	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][post_status]" id="emi-event_all_day-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['post_status']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_event_all_day]" id="emi-event_all_day-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['event_all_day']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_event_rsvp]" id="emi-event_rsvp-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['event_rsvp']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_event_rsvp_date]" id="emi-event_rsvp_date-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['event_rsvp_date']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_event_rsvp_time]" id="emi-event_rsvp_time-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['event_rsvp_time']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_event_spaces]" id="emi-event_spaces-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['event_spaces']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_recurrence]" id="emi-recurrence-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['recurrence']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][event][db_event_id]" id="emi-event_id-<?php echo $r['event_id']; ?>"
		value="<?php echo $r['event_id']; ?>"/>
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][location][db_location_id]" id="emi-location_id-<?php echo $r['event_id']; ?>"
		value="<?php echo $location[$i]['location_id']; ?>">
	<input type="hidden" name="emi[<?php echo $r['event_id']; ?>][location][post_status]" id="emi-location_status-<?php echo $r['event_id']; ?>"
		value="<?php echo $location[$i]['location_status']; ?>">


	<br class="clear">
	<p class="submit inline-edit-save">
		<button type="button" title="<?php _e("Cancel", "emi"); ?>" class="button-secondary cancel alignleft emi-cancel" parent="<?php echo $r['event_id']; ?>" ><?php _e("Cancel", "emi"); ?></button>
		<button type="button" title="<?php _e("Save", "emi"); ?>" class="button-primary save alignright emi-save" parent="<?php echo $r['event_id']; ?>" ><?php _e("Save", "emi"); ?></button>
	</p>
</div>
