<?php
$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
$STORE = @$_SESSION[$controller->defaultStore];

if(isset($STORE['booked_data']))
{
	$booked_data = $STORE['booked_data'];
}
?>
<div class="panel panel-default pjBsMain">
	<?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';?>
	<div class="panel-body pjBsBody">
		<div class="pjBsForm pjBsFormTickets">
			<?php
			if($tpl['status'] == 'OK')
			{
				$current_date = date('Y-m-d');
				$selected_date = pjUtil::formatDate($STORE['date'], $tpl['option_arr']['o_date_format']);
				$previous_date = pjUtil::formatDate(date('Y-m-d', strtotime($selected_date . ' -1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
				$next_date = pjUtil::formatDate(date('Y-m-d', strtotime($selected_date . ' +1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
				
				$is_return = 'F';
				$return_date = '';
				if (isset($STORE['is_return']) && $STORE['is_return'] == 'T')
				{
                                    $is_return = 'T';
                                    $return_date = $STORE['return_date'];
                                    $return_selected_date = pjUtil::formatDate($STORE['return_date'], $tpl['option_arr']['o_date_format']);
                                    $return_previous_date = pjUtil::formatDate(date('Y-m-d', strtotime($return_selected_date . ' -1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
                                    $return_next_date = pjUtil::formatDate(date('Y-m-d', strtotime($return_selected_date . ' +1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
				}
				?>
				<form id="bsSelectSeatsForm_<?=$_GET['index'];?>" action="" method="post">
                                    <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
                                        <div class="row">
                                            <?php
                                            $ticket_arr = $tpl['bus_arr'][0]['ticket_arr'];
                                            for($i = 0; $i < $tpl['ticket_columns']; $i++)
                                            {
                                                if(isset($ticket_arr[$i]))
                                                {
                                                    $ticket = $ticket_arr[$i];
                                                    $field_price = 'price';
                                                    if (isset($_SESSION[$controller->defaultFrontTicketCurrency])) {
                                                        $field_price = $controller->defaultTicketCurrencies[$_SESSION[$controller->defaultFrontTicketCurrency]];	
                                                    }
                                                    if($ticket[$field_price] != '')
                                                    {
                                                        ?>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                            <div class="form-group">
                                                                <label for=""><?=$ticket['ticket'];?></label>

                                                                <div class="input-group">
                                                                    <select name="ticket_cnt_<?=$ticket['ticket_id'];?>" class="form-control bsTicketSelect bsTicketSelect-<?=$bus['id'];?>" data-set="<?=!empty($bus['seats_map']) ? 'T' : 'F';?>" data-bus="<?=$bus['id']; ?>"  data-pickup_id="<?=$STORE['is_transfer'] == 1 ? $STORE['before_transfer_pickup_id'] : $STORE['pickup_id']; ?>" data-return_id="<?=$STORE['is_transfer'] == 1 ? $STORE['before_transfer_return_id'] : $STORE['return_id']; ?>" data-price="<?=$ticket[$field_price];?>">
                                                                        <?for($j = 0; $j <= $tpl['min_seats'];$j++){?>
                                                                        <option value="<?=$j;?>"<?=isset($booked_data) && $booked_data['ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>><?=$j;?></option>
                                                                        <?}?>
                                                                    </select>
                                                                    <span class="input-group-addon">x <?=pjUtil::formatCurrencySign($ticket[$field_price], $_SESSION[$controller->defaultFrontTicketCurrency]);?></span>
                                                                </div><!-- /.input-group -->
                                                            </div><!-- /.form-group -->
                                                        </div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-4 -->
                                                        <?php
                                                    }
                                                }
                                            } 
                                            ?>
                                        </div><!-- /.row -->
                                    </div><!-- /.col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12 -->                                    

                                    
                                    <header class="pjBsFormHead clearfix">
                                        <p class="pjBsFormTitle pull-left"><?php __('front_journey_from');?> <strong><?=$tpl['from_location']?></strong> <?php __('front_to');?> <strong><?=$tpl['to_location']?></strong></p><!-- /.pjBsFormTitle pull-left -->

                                        <dl class="dl-horizontal pjBsDepartureDate pull-right">
                                            <dt><?php __('front_date_departure');?>: </dt>

                                            <dd>
                                                <?php
                                                if($current_date != $selected_date) 
                                                { 
                                                        ?><a href="#" class="bsDateNav" data-type="pickup" data-pickup="<?=$STORE['pickup_id']?>" data-return="<?=$STORE['return_id']?>" data-date="<?=$previous_date;?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="<?=$is_return; ?>" data-return_date="<?=$return_date; ?>">&laquo;&nbsp;<?=__('front_prev');?></a><?php
                                                } 
                                                ?>
                                                <strong><?=$STORE['date'];?></strong>
                                                <a href="#" class="bsDateNav" data-type="pickup" data-pickup="<?=$STORE['pickup_id']?>" data-return="<?=$STORE['return_id']?>" data-date="<?=$next_date;?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="<?=$is_return; ?>" data-return_date="<?=$return_date; ?>"><?=__('front_next');?> &raquo;</a>
                                            </dd>
                                        </dl><!-- /.dl-horizontal pjBsDepartureDate pull-right -->
                                    </header><!-- /.pjBsFormHead clearfix -->
                                        
                                        <div class="pjBsFormBody">
						<?php
						if(isset($tpl['bus_arr']))
						{ 
							?>
							<div class="panel panel-default pjBsSeats bsBusContainer">
							
								<header class="panel-heading pjBsSeatsHead">
									<div class="row">
										<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_bus');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_available_seats');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_departure_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_arrival_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs">
											<p class="panel-title pjBsSeatsTitle"><?=__('front_duration');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs -->
									</div><!-- /.row -->
								</header><!-- /.panel-heading pjBsSeatsHead -->
								
								<ul class="list-group pjBsListBusses">
									<?php
									foreach($tpl['bus_arr'] as $bus)
									{ 
										$seats_avail = $bus['seats_available'];
										$location_arr = $bus['locations'];
										?>
										<li class="list-group-item">
											<div id="bsRow_<?=$bus['id'];?>" class="row bsRow<?=isset($booked_data) && $booked_data['bus_id'] == $bus['id'] ? ' bsFocusRow' : null;?>">
												<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
													<p class="clearfix pjBsBusTitle">
														<?=$bus['route'];?>
														<a href="#" class="pull-right pjBrDestinationTip" data-id="<?=$bus['id'];?>">
															<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
														</a>
													</p><!-- /.clearfix pjBsBusTitle -->
													
													<div id="pjBrTipClone_<?=$bus['id'];?>" style="display: none;">
														<ul class="list-unstyled pjBsListTicks">
															<?php
															foreach($location_arr as $location)
															{ 
																?><li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?=$location['content'] . " - " . (!empty($location['departure_time']) ? pjUtil::formatTime($location['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format']) : pjUtil::formatTime($location['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format']));?></li><?php
															} 
															?>
														</ul><!-- /.list-unstyled pjBsListTicks -->
													</div>
												</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusAvailableSeats"><?=$seats_avail;?></p><!-- /.pjBsBusAvailableSeats -->
													<input type="hidden" id="bs_avail_seats_<?=$bus['id'];?>" name="avail_seats" value="<?=join("~|~", $bus['seat_avail_arr']) ;?>"/>
													<input type="hidden" id="bs_number_of_seats_<?=$bus['id'];?>" value="<?=$seats_avail;?>"/>
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($bus['departure_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['departure_time']));?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['arrival_time']));?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?=$bus['duration'];?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
                                                                                                
                                                                                                
                                                                                                
                                                                                                <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
													<div class="row">
														<?php
														$ticket_arr = $bus['ticket_arr'];
														for($i = 0; $i < $tpl['ticket_columns']; $i++)
														{
															if(isset($ticket_arr[$i]))
															{
																$ticket = $ticket_arr[$i];
																$field_price = 'price';
																if (isset($controller->defaultTicketCurrencies[$_SESSION[$controller->defaultFrontTicketCurrency]])) {
																	$field_price = $controller->defaultTicketCurrencies[$_SESSION[$controller->defaultFrontTicketCurrency]];	
																}
																if($ticket[$field_price] != '')
																{
																	?>
																	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
																		<div class="form-group">
																			<label for=""><?=$ticket['ticket'];?></label>
																			
																			<div class="input-group">
																				<select name="ticket_cnt_<?=$ticket['ticket_id'];?>" class="form-control bsTicketSelect bsTicketSelect-<?=$bus['id'];?>" data-set="<?=!empty($bus['seats_map']) ? 'T' : 'F';?>" data-bus="<?=$bus['id']; ?>"  data-pickup_id="<?=$STORE['is_transfer'] == 1 ? $STORE['before_transfer_pickup_id'] : $STORE['pickup_id']; ?>" data-return_id="<?=$STORE['is_transfer'] == 1 ? $STORE['before_transfer_return_id'] : $STORE['return_id']; ?>" data-price="<?=$ticket[$field_price];?>">
																					<?php
																					for($j = 0; $j <= $seats_avail; $j++)
																					{
																						?><option value="<?=$j; ?>"<?=isset($booked_data) && $booked_data['ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>><?=$j; ?></option><?php
																					}
																					?>
																				</select>
																													
																				<span class="input-group-addon">x <?=pjUtil::formatCurrencySign( $ticket[$field_price], $_SESSION[$controller->defaultFrontTicketCurrency]);?></span>
																			</div><!-- /.input-group -->
																		</div><!-- /.form-group -->
																	</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-4 -->
																	<?php
																}
															}
														} 
														?>
													</div><!-- /.row -->
												</div><!-- /.col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12 -->
                                                                                                
                                                                                                
                                                                                                
                                                                                                
											</div><!-- /.row -->
										</li><!-- /.list-group-item -->
										<?php
									} 
									?>
								</ul><!-- /.list-group pjBsListBusses -->
								<?php
								if(isset($booked_data))
								{
									$selected_seats_arr = explode("|", $booked_data['selected_seats']);
									$intersect = array_intersect($tpl['booked_seat_arr'], $selected_seats_arr);
								}
								?>
								<div class="panel-body pjBsSeatsBody pjBsPickupSeatsBody" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
								
									<div class="text-danger bsTicketErrorMsg" style="display: none;"><?php __('front_validation_tickets');?></div>
									
									<div class="pjBsListSeats">
										<div class="pjBsChosenSeats" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
											<p><?php __('front_select');?> <strong id="bsSeats_<?=$_GET['index'];?>"><?=isset($booked_data) ? ( empty($intersect) ? ( $booked_data['selected_ticket'] > 0 ? ($booked_data['selected_ticket'] != 1 ? ($booked_data['selected_ticket'] . ' ' . pjSanitize::clean(__('front_seats', true, false))) : ($booked_data['selected_ticket'] . ' ' . pjSanitize::clean(__('front_seat', true, false))) ) :null) :null): null;?></strong></p>
	
											<dl class="dl-horizontal" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'inline-block' : 'none';?>;">
												<dt><?php __('front_selected_seats');?></dt>
												<dd id="bsSelectedSeatsLabel_<?=$_GET['index'];?>"><?=isset($booked_data) ? ( empty($intersect) ? ($booked_data['selected_seats'] != '' ? join(", ", $tpl['selected_seat_arr']) : null) : null ) : null;?></dd>
											</dl><!-- /.dl-horizontal -->
	
											<button type="button" class="btn btn-link bsReSelect" style="display:<?=isset($booked_data) ? (empty($intersect) ? ( $booked_data['has_map'] == 'T' ? 'inline-block' : 'none') :'none' ) : 'none';?>;"><?php __('front_reselect');?></button>
										</div><!-- /.pjBsChosenSeats -->
										<div id="bsMapContainer_<?=$_GET['index'];?>" class="bsMapContainer" style="display:<?=isset($booked_data) && $booked_data['has_map'] == 'T' ? 'block' : 'none';?>;">
											<?php
											if(isset($booked_data) && $booked_data['has_map'] == 'T')
											{
												include PJ_VIEWS_PATH . 'pjFrontEnd/pjActionGetSeats.php';
											} 
											?>
										</div>
									</div><!-- /.pjBsListSeats -->
									
									<div class="text-danger bsSeatErrorMsg"></div>
								</div><!-- /.panel-body pjBsSeatsBody -->
	
	
								<footer class="panel-footer pjBsSeatsFoot pjBsPickupSeatsFoot" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
									<ul class="list-inline pjBsSeatsKey">
										<li>
											<span class="pjBsSeat pjBsSeatAvailable"></span>
											<span><?php __('front_available');?></span>
										</li>
	
										<li>
											<span class="pjBsSeat pjBsSeatSelected"></span>
											<span><?php __('front_selected');?></span>
										</li>
	
										<li>
											<span class="pjBsSeat pjBsSeatBooked"></span>
											<span><?php __('front_booked');?></span>
										</li>
									</ul><!-- /.list-inline pjBsSeatsKey -->
									
								</footer><!-- /.panel-footer pjBsSeatsFoot -->
								
								<input type="hidden" id="bs_selected_tickets_<?=$_GET['index'];?>" name="selected_ticket" value="<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? $booked_data['selected_ticket'] : null;?>" data-map="<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? (!empty($tpl['bus_type_arr']['seats_map']) ? 'T' : 'F') : 'F';?>"/>
								<input type="hidden" id="bs_selected_seats_<?=$_GET['index'];?>" name="selected_seats" value="<?=isset($booked_data) && $booked_data['selected_seats'] != '' ? $booked_data['selected_seats'] : null;?>"/>
								<input type="hidden" id="bs_selected_bus_<?=$_GET['index'];?>" name="bus_id" value="<?=isset($booked_data) && $booked_data['bus_id'] != '' ? $booked_data['bus_id'] : null;?>"/>
								<input type="hidden" id="bs_has_map_<?=$_GET['index'];?>" name="has_map" value="<?=isset($booked_data) ? $booked_data['has_map'] : null;?>"/>
								
							</div><!-- /.panel panel-default pjBsSeats -->	
							<?php
						}else{
							?><div><?php __('front_no_bus_available');?><br/><br/></div><?php 
						} 
						?>
					</div><!-- /.pjBsFormBody -->

					<?php
					if(isset($tpl['return_bus_arr']) && !empty($tpl['return_bus_arr']))
					{
						?>
						<header class="pjBsFormHead clearfix">
							<p class="pjBsFormTitle pull-left"><?php __('front_journey_from');?> <strong><?=$tpl['return_from_location']?></strong> <?php __('front_to');?> <strong><?=$tpl['return_to_location']?></strong></p><!-- /.pjBsFormTitle -->
	
							<dl class="dl-horizontal pjBsDepartureDate pull-right">
								<dt><?php __('front_date_departure');?>: </dt>
	
								<dd>
									<?php
									if($selected_date != $return_selected_date || $STORE['is_transfer'] == 1)
									{ 
										?><a href="#" class="bsDateNav" data-type="return" data-pickup="<?=$STORE['pickup_id']?>" data-return="<?=$STORE['return_id']?>" data-date="<?=$STORE['date'];?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="T" data-return_date="<?=$return_previous_date;?>">&laquo;&nbsp;<?=__('front_prev');?></a><?php
									} 
									?>
									<strong><?=$STORE['return_date'];?></strong>
									<a href="#" class="bsDateNav" data-type="return" data-pickup="<?=$STORE['pickup_id']?>" data-return="<?=$STORE['return_id']?>" data-date="<?=$STORE['date'];?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="T" data-return_date="<?=$return_next_date;?>"><?=__('front_next');?> &raquo;</a>
								</dd>
							</dl><!-- /.dl-horizontal pjBsDepartureDate -->
						</header><!-- /.pjBsFormHead -->
						<div class="pjBsFormBody">
						
							<div class="panel panel-default pjBsSeats bsBusContainer">
                                                            <header class="panel-heading pjBsSeatsHead">
                                                                <div class="row">
                                                                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
                                                                            <p class="panel-title pjBsSeatsTitle"><?php __('front_bus');?></p><!-- /.panel-title pjBsSeatsTitle -->
                                                                    </div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->

                                                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                                                            <p class="panel-title pjBsSeatsTitle"><?php __('front_available_seats');?></p><!-- /.panel-title pjBsSeatsTitle -->
                                                                    </div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->

                                                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
                                                                            <p class="panel-title pjBsSeatsTitle"><?php __('front_departure_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
                                                                    </div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->

                                                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
                                                                            <p class="panel-title pjBsSeatsTitle"><?php __('front_arrival_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
                                                                    </div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->

                                                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs">
                                                                            <p class="panel-title pjBsSeatsTitle"><?=__('front_duration');?></p><!-- /.panel-title pjBsSeatsTitle -->
                                                                    </div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs -->
                                                                </div><!-- /.row -->
                                                            </header><!-- /.panel-heading pjBsSeatsHead -->
								
								<ul class="list-group pjBsListBusses">
									<?php
									foreach($tpl['return_bus_arr'] as $return_bus)
									{ 
										$seats_avail = $return_bus['seats_available'];
										$location_arr = $return_bus['locations'];
										?>
										<li class="list-group-item">
											<div id="bsReturnRow_<?=$bus['id'];?>" class="row bsReturnRow bsReturnRow_<?=$bus['id'];?><?=isset($booked_data) && $booked_data['bus_id'] == $bus['id'] ? ' bsFocusRow' : null;?>">
												<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
													<p class="clearfix pjBsBusTitle">
														<?=$return_bus['route'];?>
														<a href="#" class="pull-right pjBrDestinationTip" data-id="<?=$return_bus['id'];?>">
															<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
														</a>
													</p><!-- /.clearfix pjBsBusTitle -->
													
													<div id="pjBrTipClone_<?=$return_bus['id'];?>" style="display: none;">
														<ul class="list-unstyled pjBsListTicks">
															<?php
															foreach($location_arr as $location)
															{ 
																?><li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?=$location['content'] . " - " . (!empty($location['departure_time']) ? pjUtil::formatTime($location['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format']) : pjUtil::formatTime($location['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format']));?></li><?php
															} 
															?>
														</ul><!-- /.list-unstyled pjBsListTicks -->
													</div>
												</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusAvailableSeats"><?=$seats_avail;?></p><!-- /.pjBsBusAvailableSeats -->
													<input type="hidden" id="bs_return_avail_seats_<?=$return_bus['id'];?>" name="return_avail_seats" value="<?=join("~|~", $bus['seat_avail_arr']) ;?>"/>
													<input type="hidden" id="bs_return_number_of_seats_<?=$return_bus['id'];?>" value="<?=$seats_avail;?>"/>
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($return_bus['departure_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($return_bus['departure_time']));?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($return_bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($return_bus['arrival_time']));?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?=$return_bus['duration'];?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												
											</div><!-- /.row -->
										</li><!-- /.list-group-item -->
										<?php
									} 
									?>
								</ul><!-- /.list-group pjBsListBusses -->
								<?php
								if (isset($STORE['is_return']) && $STORE['is_return'] == 'T')
								{
									if(isset($booked_data))
									{
										$selected_seats_arr = explode("|", $booked_data['return_selected_seats']);
										$intersect = array_intersect($tpl['booked_return_seat_arr'], $selected_seats_arr);
									}
									?>
									<div class="panel-body pjBsSeatsBody pjBsReturnSeatsBody" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
									
										<div class="text-danger bsReturnTicketErrorMsg" style="display: none;"><?php __('front_validation_tickets');?></div>
										
										<div class="pjBsListSeats">
											<div class="pjBsChosenSeats" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
												<p><?php __('front_select');?> <strong id="bsReturnSeats_<?=$_GET['index'];?>"><?=isset($booked_data) ? ( empty($intersect) ? ( $booked_data['return_selected_ticket'] > 0 ? ($booked_data['return_selected_ticket'] != 1 ? ($booked_data['return_selected_ticket'] . ' ' . pjSanitize::clean(__('front_seats', true, false))) : ($booked_data['return_selected_ticket'] . ' ' . pjSanitize::clean(__('front_seat', true, false))) ) :null) :null): null;?></strong></p>
		
												<dl class="dl-horizontal" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'inline-block' : 'none';?>;">
													<dt><?php __('front_selected_seats');?></dt>
													<dd id="bsReturnSelectedSeatsLabel_<?=$_GET['index'];?>"><?=isset($booked_data) ? ( empty($intersect) ? ($booked_data['return_selected_seats'] != '' ? join(", ", $tpl['return_selected_seat_arr']) : null) : null ) : null;?></dd>
												</dl><!-- /.dl-horizontal -->
		
												<button type="button" class="btn btn-link bsReturnReSelect" style="display:<?=isset($booked_data) ? (empty($intersect) ? ( $booked_data['return_has_map'] == 'T' ? 'inline-block' : 'none') :'none' ) : 'none';?>;"><?php __('front_reselect');?></button>
											</div><!-- /.pjBsChosenSeats -->
											<div id="bsReturnMapContainer_<?=$_GET['index'];?>" class="bsReturnMapContainer" style="display:<?=isset($booked_data) && $booked_data['return_has_map'] == 'T' ? 'block' : 'none';?>;">
												<?php
												if(isset($booked_data) && $booked_data['return_has_map'] == 'T')
												{
													include PJ_VIEWS_PATH . 'pjFrontEnd/pjActionGetReturnSeats.php';
												} 
												?>
											</div>
										</div><!-- /.pjBsListSeats -->
										
										<div class="text-danger bsReturnSeatErrorMsg"></div>
									</div><!-- /.panel-body pjBsSeatsBody -->
									<?php
								} 
								?>
	
								<footer class="panel-footer pjBsSeatsFoot pjBsReturnSeatsFoot" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
									<ul class="list-inline pjBsSeatsKey">
										<li>
											<span class="pjBsSeat pjBsSeatAvailable"></span>
											<span><?php __('front_available');?></span>
										</li>
	
										<li>
											<span class="pjBsSeat pjBsSeatSelected"></span>
											<span><?php __('front_selected');?></span>
										</li>
	
										<li>
											<span class="pjBsSeat pjBsSeatBooked"></span>
											<span><?php __('front_booked');?></span>
										</li>
									</ul><!-- /.list-inline pjBsSeatsKey -->
									
								</footer><!-- /.panel-footer pjBsSeatsFoot -->
								
								<input type="hidden" id="bs_return_selected_tickets_<?=$_GET['index'];?>" name="return_selected_ticket" value="<?=isset($booked_data) && $booked_data['return_selected_ticket'] > 0 ? $booked_data['return_selected_ticket'] : null;?>" data-map="<?=isset($booked_data) && $booked_data['return_selected_ticket'] > 0 ? (!empty($tpl['bus_type_arr']['seats_map']) ? 'T' : 'F') : 'F';?>"/>
								<input type="hidden" id="bs_return_selected_seats_<?=$_GET['index'];?>" name="return_selected_seats" value="<?=isset($booked_data) && $booked_data['return_selected_seats'] != '' ? $booked_data['return_selected_seats'] : null;?>"/>
								<input type="hidden" id="bs_return_selected_bus_<?=$_GET['index'];?>" name="return_bus_id" value="<?=isset($booked_data) && $booked_data['return_bus_id'] != '' ? $booked_data['return_bus_id'] : null;?>"/>
								<input type="hidden" id="bs_return_has_map_<?=$_GET['index'];?>" name="return_has_map" value="<?=isset($booked_data) ? $booked_data['return_has_map'] : null;?>"/>
								
							</div><!-- /.panel panel-default pjBsSeats -->	
						</div>
						<?php
						
					} 
					?>
					<footer class="pjBsFormFoot">
						<p class="text-right pjBsTotalPrice">
							<strong id="bsRoundtripPrice_<?=$_GET['index'];?>"></strong>
						</p><!-- /.text-right pjBsTotalPrice -->

						<div class="clearfix pjBsFormActions">
							<a href="#" id="bsBtnCancel_<?=$_GET['index'];?>" class="btn btn-default pull-left"><?php __('front_button_back'); ?></a>
							<?php
							if(isset($tpl['bus_arr']))
							{ 
								?>
								<button type="button" id="bsBtnCheckout_<?=$_GET['index'];?>" class="btn btn-primary pull-right"><?php __('front_button_checkout'); ?></button>
								<?php
							} 
							?>
						</div><!-- /.clearfix pjBsFormActions -->
					</footer><!-- /.pjBsFormFoot -->
				</form>
				<?php 
			}else{
				?>
				<div>
					<?php
					$front_messages = __('front_messages', true, false);
					$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[5]);
					$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
					echo $system_msg; 
					?>
				</div>
				<?php
			}
			?>
		</div><!-- /.pjBsForm pjBsFormTickets -->
	</div><!-- /.panel-body pjBsBody -->
</div>
<div class="modal fade pjBsModal pjBsModalRoute" id="pjBsModalRoute" tabindex="-1" role="dialog" aria-labelledby="pjBsModalRouteLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<header class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>

				<p class="modal-title"><?php __('front_destinations');?></p><!-- /.modal-title -->
			</header><!-- /.modal-header -->

			<div class="modal-body">
				
			</div><!-- /.modal-body -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /#pjBsModalRoute.modal fade pjBsModal pjBsModalRoute -->