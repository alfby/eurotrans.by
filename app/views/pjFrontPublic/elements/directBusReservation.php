<?if(0 !== $minSeats){?>
    <?if(isset($bus_list['bus_arr'])){?>
    <?$bus_arr = $bus_list['bus_arr']?>
    <?$strIds = $dataPickupId.$dataReturnId?>
        <header class="pjBsFormHead clearfix">
            <p class="pjBsFormTitle pull-left">
                <?=__('front_journey_from');?> 
                <strong><?=$bus_list['from_location']?></strong> 
                <?=__('front_to');?> 
                <strong><?=$bus_list['to_location']?></strong>
            </p>

            <dl class="dl-horizontal pjBsDepartureDate pull-right">
                <dt><?=__('front_date_departure');?>: </dt>

                <dd>
                    <?if($date['current_date'] != $date['selected_date']){?>
                        <a href="#" class="bsDateNav" data-type="pickup" data-pickup="<?=$pickup_id?>" data-return="<?=$return_id?>" data-date="<?=$date['previous_date'];?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="<?=$is_return;?>" data-return_date="<?=$return_date['current_date'];?>">
                            &laquo;&nbsp;<?=__('front_prev');?>
                        </a>
                    <?}?>
                    <strong><?=$date['current_date']?></strong>
                    <a href="#" class="bsDateNav" data-type="pickup" data-pickup="<?=$pickup_id?>" data-return="<?=$return_id?>" data-date="<?=$date['next_date'];?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="<?=$is_return; ?>" data-return_date="<?=$return_date['current_date'];?>"><?=__('front_next');?>
                        &raquo;
                    </a>
                </dd>
            </dl><!-- /.pull-right -->
        </header>

        <div class="pjBsFormBody">
            <?if(isset($bus_arr)){?>
                <div class="panel panel-default pjBsSeats bsBusContainer" data-pickup_id="<?=$dataPickupId?>" data-return_id="<?=$dataReturnId?>">
                    <?include $pathToViewElements.'journeyHeaders.php'?>
                    
                    <ul class="list-group pjBsListBusses">
                        <?foreach($bus_arr as $bus){
                            $busId = $bus['id'];
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
                                        </p>

                                        <div id="pjBrTipClone_<?=$bus['id'];?>" style="display: none;">
                                            <ul class="list-unstyled pjBsListTicks">
                                                <?foreach($location_arr as $location)
                                                {?>
                                                    <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                                                        <?=$location['content'] . " - " . (!empty($location['departure_time']) ? pjUtil::formatTime($location['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format']) : pjUtil::formatTime($location['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format']));?></li>
                                                <?}?>
                                            </ul><!-- /.list-unstyled pjBsListTicks -->
                                        </div>
                                    </div>

                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                        <p class="pjBsBusAvailableSeats"><?=$seats_avail;?></p><!-- /.pjBsBusAvailableSeats -->
                                        <input type="hidden" id="bs_avail_seats_<?=$bus['id'];?>" name="avail_seats" value="<?=join("~|~", $bus['seat_avail_arr']) ;?>"/>
                                        <input type="hidden" id="bs_number_of_seats_<?=$bus['id'];?>" class='bs_number_of_seats' value="<?=$seats_avail;?>"/>
                                    </div>

                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                        <p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($bus['departure_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['departure_time']));?></p>
                                    </div>

                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                       <p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['arrival_time']));?></p>
                                    </div>

                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                        <p class="pjBsBusDate"><?=$bus['duration'];?></p>
                                    </div>

                                    <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12 <?=($showCommonPanel)?'hide':'';?>">
                                        <div class="row">
                                            <?
                                            $ticket_arr = $bus['ticket_arr'];
                                            for($i = 0; $i < $bus_list['ticket_columns']; $i++)
                                            {
                                                if(isset($ticket_arr[$i]))
                                                {
                                                    $ticket = $ticket_arr[$i];
                                                    $field_price = 'price';
                                                    if (isset($controller->defaultTicketCurrencies[$_SESSION[$controller->defaultFrontTicketCurrency]])) {
                                                        $field_price = $controller->defaultTicketCurrencies[$_SESSION[$controller->defaultFrontTicketCurrency]];	
                                                    }

                                                    if($ticket[$field_price] != '') {?>
                                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                                                            <div class="form-group">
                                                                <label for=""><?=$ticket['ticket'];?></label>
                                                                <div class="input-group">
                                                                    <select 
                                                                        name="ticket_cnt_<?=$ticket['ticket_id'];?>"
                                                                        class="form-control bsDirectTicketSelect bsTicketSelect bsTicketSelect-<?=$bus['id'];?>" 
                                                                        data-ticket-type="<?=$ticket['ticket'];?>"
                                                                        data-set="<?=!empty($bus['seats_map']) ? 'T' : 'F';?>" 
                                                                        data-bus="<?=$bus['id']; ?>"
                                                                        data-pickup_id="<?=$dataPickupId?>" 
                                                                        data-return_id="<?=$dataReturnId?>" 
                                                                        data-price="<?=$ticket[$field_price];?>"
                                                                    >
                                                                        <?for($j = 0; $j <= $seats_avail;$j++){?>
                                                                        <option value="<?=$j;?>"<?=isset($booked_data) && $booked_data['ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>>
                                                                            <?=$j;?>
                                                                        </option>
                                                                        <?}?>
                                                                    </select>

                                                                    <span class="input-group-addon">x <?=pjUtil::formatCurrencySign( $ticket[$field_price], $_SESSION[$controller->defaultFrontTicketCurrency]);?></span>
                                                                </div><!-- /.input-group -->
                                                            </div><!-- /.form-group -->
                                                        </div>
                                                        <?
                                                    }
                                                }
                                            }?>
                                        </div><!-- /.row -->
                                    </div>
                                </div><!-- /.row -->
                            </li><!-- /.list-group-item -->
                        <?}?>
                    </ul>
                    <?if(isset($booked_data)){
                        $selected_seats_arr = (is_array($booked_data['selected_seats'])) ? $booked_data['selected_seats'] : explode("|", $booked_data['selected_seats']);
                        $intersect = array_intersect($bus_list['booked_seat_arr'], $selected_seats_arr);
                    }?>
                    
                    <div class="panel-body pjBsSeatsBody pjBsPickupSeatsBody" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
                        <div class="text-danger bsTicketErrorMsg" style="display: none;"><? __('front_validation_tickets');?></div>
                        <div class="pjBsListSeats">
                            <div class="pjBsChosenSeats" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
                                <p><? __('front_select');?> <strong id="bsSeats_<?=$_GET['index'].$strIds;?>"><?=isset($booked_data) ? ( empty($intersect) ? ( $booked_data['selected_ticket'] > 0 ? ($booked_data['selected_ticket'] != 1 ? ($booked_data['selected_ticket'] . ' ' . pjSanitize::clean(__('front_seats', true, false))) : ($booked_data['selected_ticket'] . ' ' . pjSanitize::clean(__('front_seat', true, false))) ) :null) :null): null;?></strong></p>
                                <dl class="dl-horizontal" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'inline-block' : 'none';?>;">
                                    <dt><? __('front_selected_seats');?></dt>
                                    <dd id="bsSelectedSeatsLabel_<?=$_GET['index'].$strIds;?>" class="bsSelectedSeatsLabel_<?=$_GET['index'].$strIds?> bsSelectedSeatsLabel"><?=isset($booked_data) ? ( empty($intersect) ? ($booked_data['selected_seats'] != '' ? join(", ", $bus_list['selected_seat_arr']) : null) : null ) : null;?></dd>
                                </dl>

                                <button type="button" id="bsReSelect<?=$dataPickupId?><?=$dataReturnId?>" class="btn btn-link bsReSelect" data-pickup_id=<?=$dataPickupId?> data-return_id=<?=$dataReturnId?> style="display:<?=isset($booked_data) ? (empty($intersect) ? ($booked_data['has_map'] == 'T' ? 'inline-block' : 'none') :'none' ) : 'none';?>;"><? __('front_reselect');?></button>
                            </div>
                            <div id="bsMapContainer_<?=$_GET['index'].$strIds;?>" class="bsMapContainer bsMapContainer_<?=$_GET['index'].$strIds?>" data-pickup_id=<?=$dataPickupId?>  data-return_id=<?=$dataReturnId?> class="bsMapContainer" style="display:<?=isset($booked_data) && $booked_data['has_map'] == 'T' ? 'block' : 'none';?>;">
                            <?if(isset($booked_data) && $booked_data['has_map'] == 'T'){
                                include PJ_VIEWS_PATH . 'pjFrontEnd/pjActionGetSeats.php';
                            }?>
                            </div>
                        </div>
                        <div class="text-danger bsSeatErrorMsg"></div>
                    </div>

                    <footer class="panel-footer pjBsSeatsFoot pjBsPickupSeatsFoot" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
                        <?include $pathToViewElements.'listOfSeatTypes.php'?>
                    </footer> 

                    <input type="hidden" id="bs_selected_tickets_<?=$_GET['index'].$strIds?>" class="bs_selected_tickets" name="selected_ticket" value="<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? $booked_data['selected_ticket'] : null;?>" data-map="<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? (!empty($bus_list['bus_type_arr']['seats_map']) ? 'T' : 'F') : 'F';?>"/>
                    <input type="hidden" id="bs_selected_seats_<?=$_GET['index'].$strIds?>" class="bs_selected_seats_<?=$_GET['index'].$strIds;?> bs_selected_seats" name="selected_seats[<?=$strIds?>]" value="<?=isset($booked_data) && $booked_data['selected_seats'] != '' ? $booked_data['selected_seats'] : null;?>"/>
                    <input type="hidden" id="bs_selected_bus_<?=$_GET['index'].$strIds?>" class="bs_selected_bus" name="bus_id[<?=$strIds?>]" value="<?=isset($booked_data) && $booked_data['bus_id'][$strIds] != '' ? $booked_data['bus_id'][$strIds] : null;?>"/>
                    <input type="hidden" id="bs_has_map_<?=$_GET['index'].$strIds?>" name="has_map" value="<?=isset($booked_data) ? $booked_data['has_map'] : null;?>"/>
                </div>	
            <?}else{?>
                <div>
                <?=__('front_no_bus_available');?>
                    <br/><br/>
                </div>
            <?}?>
        </div><!-- /.pjBsFormBody -->

    <?}else{
        echo "bus_list is not set";
    }?>
<?}else{?>        
    <?include $pathToViewElements.'ticketsNotAvailable.php'?>
<?}?>