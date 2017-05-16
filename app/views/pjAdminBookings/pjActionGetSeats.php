<?
$map = $tpl['bus_type_arr']['seats_map'];
if (is_file($map))
{
	$size = getimagesize($map);
	?>
	<div class="bs-hint b10"><?php __('lblSelectSeatsHint')?></div>
	<div class="bs-seats-legend b10">
            <label><span class="bs-available-seats"></span><?php __('lblAvailableSeats');?></label>
            <label><span class="bs-selected-seats"></span><?php __('lblSelectedSeats');?></label>
            <label><span class="bs-booked-seats"></span><?php __('lblBookedSeats');?></label>
	</div>
	<div id="boxMap">
		<div id="mapHolder" style="position: relative; overflow: hidden; width: <?=$size[0]; ?>px; height: <?=$size[1]; ?>px; margin: 0 auto;">
			<img id="map" src="<?=$map; ?>" alt="" style="margin: 0; border: none; position: absolute; top: 0; left: 0; z-index: 500" />
			<?foreach ($tpl['seat_arr'] as $seat){?>
                            <span rel="hi_<?=$seat['id'];?>" class="rect empty<?=in_array($seat['id'], $tpl['booked_seat_arr']) ? ' bs-booked' : ' bs-available';?><?=isset($_POST['booking_update']) ? (in_array($seat['id'], $tpl['seat_pair_arr']) ? ' bs-selected' : null) : null;?>" data-id="<?=$seat['id']; ?>" data-name="<?=$seat['name']; ?>" style="width: <?=$seat['width']; ?>px; height: <?=$seat['height']; ?>px; left: <?=$seat['left']; ?>px; top: <?=$seat['top']; ?>px; line-height: <?=$seat['height']; ?>px"><span class="bsInnerRect" data-name="hi_<?=$seat['id']; ?>"><?=stripslashes($seat['name']); ?></span></span><?php
			}?>
		</div>
	</div>
<?}else{?>
	<p>
		<label class="title"><?php __('lblSeats'); ?>:</label>
		<span class="inline-block">
			<span class="block b5">
				<select name="assigned_seats[]" id="assigned_seats" class="pj-form-field required" multiple="multiple" size="5">
					<?php
					foreach ($tpl['seat_arr'] as $seat)
					{
						if(!in_array($seat['id'], $tpl['booked_seat_arr']))
						{
							?><option value="<?=$seat['id']; ?>" <?=in_array($seat['id'], $tpl['seat_pair_arr']) ? 'selected="selected"' : null;?>><?=stripslashes($seat['name']); ?></option><?php
						}
					}
					?>
				</select>
			</span>
			<a class="block" target="_blank" href="<?=$_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?=$_POST['bus_id'];?>&amp;date=<?=$_POST['booking_date'];?>"><?php __('lblViewSeatsList');?></a>
		</span>
	</p>
	<?php
} 
?>