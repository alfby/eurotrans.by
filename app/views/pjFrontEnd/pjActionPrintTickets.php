<?php
if(isset($tpl['template_arr']))
{ 
	?>
	<table class="table" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td><?php echo $tpl['template_arr'];?></td>
			</tr>
		</tbody>
	</table>
	<?php
} else{
	if(@$tpl['status'] == 'ERRO1')
	{
		__('front_booking_not_found');
	}else if(@$tpl['status'] == 'ERRO2'){
		__('front_hash_not_match');
	}else{
		__('lblBookingNotConfirmed');
	}
}
?>