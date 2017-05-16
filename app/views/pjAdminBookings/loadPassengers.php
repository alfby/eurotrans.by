<?php if ($tpl['selected_seats']) { ?>
	<?php 
	$count = 0; 
	foreach ($tpl['selected_seats'] as $seat_id => $name) { ?>
		<div class="passengerItem">
			<p>
				<label class="title"><?php echo __('lblPassengerFirstName', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="p_first_name[<?php echo $seat_id;?>]" class="pj-form-field w250 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['first_name'])); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php echo __('lblPassengerLastName', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="p_last_name[<?php echo $seat_id;?>]" class="pj-form-field w250 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['last_name'])); ?>" />
				</span>
			</p>
			<?php if ($count == 0) {?>
			<p>
				<label class="title"><?php echo __('lblPassengerPhone1', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="p_phone_1[<?php echo $seat_id;?>]" class="pj-form-field w250 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['phone_1'])); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php echo __('lblPassengerPhone2', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="p_phone_2[<?php echo $seat_id;?>]" class="pj-form-field w250" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['phone_2'])); ?>" />
				</span>
			</p>
			<?php } ?>
			<p>
				<label class="title"><?php echo __('lblPassengerPassporID', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="p_passpor_id[<?php echo $seat_id;?>]" class="pj-form-field w250" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['passpor_id'])); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblPassengerCountry'); ?></label>
				<span class="inline-block">
					<select name="p_country_id[<?php echo $seat_id;?>]" class="pj-form-field w300 required">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ($tpl['country_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>"<?php echo isset($tpl['seat_passenger_arr'][$seat_id]['country_id']) && $tpl['seat_passenger_arr'][$seat_id]['country_id'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['country_title']); ?></option><?php
						}
						?>
					</select>
				</span>
			</p>
			<?php if ($count == 0) {?>
			<p>
				<label class="title"><?php echo __('lblPassengerEmail', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="p_email[<?php echo $seat_id;?>]" class="pj-form-field w250" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['email'])); ?>" />
				</span>
			</p>
			<?php } ?>
		</div>
							
	<?php } ?>
<?php $count++;} ?>


<?php if ($tpl['return_selected_seats']) { ?>
	<h2 class="passengerDetailsTitle"><?php __('lblReturnPassengersDetails');?></h2>
	<?php 
	$count = 0; 
	foreach ($tpl['return_selected_seats'] as $seat_id => $name) { ?>
		<div class="passengerItem">
			<p>
				<label class="title"><?php echo __('lblPassengerFirstName', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="r_first_name[<?php echo $seat_id;?>]" class="pj-form-field w250 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['first_name'])); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php echo __('lblPassengerLastName', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="r_last_name[<?php echo $seat_id;?>]" class="pj-form-field w250 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['last_name'])); ?>" />
				</span>
			</p>
			<?php if ($count == 0) {?>
			<p>
				<label class="title"><?php echo __('lblPassengerPhone1', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="r_phone_1[<?php echo $seat_id;?>]" class="pj-form-field w250 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['phone_1'])); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php echo __('lblPassengerPhone2', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="r_phone_2[<?php echo $seat_id;?>]" class="pj-form-field w250" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['phone_2'])); ?>" />
				</span>
			</p>
			<?php } ?>
			<p>
				<label class="title"><?php echo __('lblPassengerPassporID', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="r_passpor_id[<?php echo $seat_id;?>]" class="pj-form-field w250" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['passpor_id'])); ?>" />
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblPassengerCountry'); ?></label>
				<span class="inline-block">
					<select name="r_country_id[<?php echo $seat_id;?>]" class="pj-form-field w300 required">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ($tpl['country_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>"<?php echo isset($tpl['seat_passenger_arr'][$seat_id]['country_id']) && $tpl['seat_passenger_arr'][$seat_id]['country_id'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['country_title']); ?></option><?php
						}
						?>
					</select>
				</span>
			</p>
			<?php if ($count == 0) {?>
			<p>
				<label class="title"><?php echo __('lblPassengerEmail', true).' #'.$name; ?>:</label>
				<span class="inline-block">
					<input type="text" name="r_email[<?php echo $seat_id;?>]" class="pj-form-field w250" value="<?php echo htmlspecialchars(stripslashes(@$tpl['seat_passenger_arr'][$seat_id]['email'])); ?>" />
				</span>
			</p>
			<?php } ?>
		</div>
							
	<?php } ?>
<?php $count++;} ?>