<?
$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
$STORE = @$_SESSION[$controller->defaultStore];
$pathToViewElements = PJ_VIEWS_PATH . 'pjFrontPublic/elements/';

if(isset($STORE['booked_data'])){
    $booked_data = $STORE['booked_data'];
}
?>

<div class="panel panel-default pjBsMain">
    <?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';?>
    <div class="panel-body pjBsBody">
        <div class="pjBsForm pjBsFormTickets">
            <?if($tpl['status'] == 'OK'){
                $date = $STORE['direct_date_arr'];
                
                $is_return = $STORE['is_return'];
                $return_date = '';
            ?>
                <form id="bsSelectSeatsForm_<?=$_GET['index'];?>" action="" method="post">
                    <?if($tpl['show_common_ticket_panel']){
                        $ticket_arr = $tpl['ticket_arr'];
                        $showCommonPanel=true;
                        include $pathToViewElements.'commonTicketsPanel.php';
                    }else{
                        $showCommonPanel=false;
                    }
                    
                    $minSeats = (isset($tpl['direct_min_seats']) && (0 !== $tpl['direct_min_seats'])) ? $tpl['direct_min_seats'] : 0;
                    $returnMinSeats = (isset($tpl['return_min_seats']) && (0 !== $tpl['return_min_seats'])) ? $tpl['return_min_seats'] : 0;

                    if('T' === $is_return){
                        $pickup_id = $STORE['return_before_transfer_pickup_id'];
                        $return_id = $STORE['return_before_transfer_return_id'];
                        $return_date = $STORE['return_date_arr'];
                        
                        if($STORE['is_transfer']){
                            $pickup_id = $STORE['pickup_id'];
                            $return_id = $STORE['return_id'];
                            $dataPickupId = $STORE['before_transfer_pickup_id'];
                            $dataReturnId = $STORE['before_transfer_return_id'];
                            $bus_list = $tpl['before_transfer_bus_list'];
                            include $pathToViewElements.'directBusReservation.php';
                            
                            $date = $STORE['transfer_date_arr'];
                            $dataPickupId = $tpl['after_transfer_data_pickup_id'];
                            $dataReturnId = $tpl['after_transfer_data_return_id'];
                            $bus_list = $tpl['after_transfer_bus_list'];
                            include $pathToViewElements.'directBusReservation.php';
                            
                            include $pathToViewElements.'returnJourneySection.php'; 

                            $dataPickupId = $tpl['return_before_transfer_data_pickup_id'];
                            $dataReturnId = $tpl['return_before_transfer_data_return_id'];
                            $bus_list = $tpl['return_before_transfer_bus_list'];
                            include $pathToViewElements.'returnBusReservation.php';

                            $return_date = $STORE['return_transfer_date_arr'];
                            $dataPickupId = $tpl['return_after_transfer_data_pickup_id'];
                            $dataReturnId = $tpl['return_after_transfer_data_return_id'];
                            $bus_list = $tpl['return_after_transfer_bus_list'];
                            include $pathToViewElements.'returnBusReservation.php';
                        }else{//return travelling without transfer
                            $pickup_id = $dataPickupId = $STORE['pickup_id'];
                            $return_id = $dataReturnId = $STORE['return_id'];
                            $bus_list = $tpl['bus_list'];
                            
                            include $pathToViewElements.'directBusReservation.php';
                            include $pathToViewElements.'returnJourneySection.php';
                            
                            $dataPickupId = $STORE['return_id'];
                            $dataReturnId = $STORE['pickup_id'];
                            $bus_list = $tpl['return_bus_list'];
                            $minSeats = $returnMinSeats;
                            
                            if(isset($STORE['return_not_available_bus']) && (false !== $STORE['return_not_available_bus'])){
                                $bus_list = $STORE['return_not_available_bus'];
                                include $pathToViewElements.'ticketsNotAvailable.php';
                            }
                            
                            include $pathToViewElements.'returnBusReservation.php';
                        }
                    }else{//one way travelling
                        if($STORE['is_transfer']){//transfer
                            $pickup_id = $STORE['pickup_id'];
                            $return_id = $STORE['return_id'];
                            $dataPickupId = $STORE['before_transfer_pickup_id'];
                            $dataReturnId = $STORE['before_transfer_return_id'];
                            $bus_list = $tpl['before_transfer_bus_list'];
                            $minSeats = $tpl['direct_min_seats'];
                            include $pathToViewElements.'directBusReservation.php';
                                          
                            $date = $STORE['after_transfer_date_arr'];
                            $dataPickupId = $STORE['after_transfer_pickup_id'];
                            $dataReturnId = $STORE['after_transfer_return_id'];
                            $bus_list = $tpl['after_transfer_bus_list'];
                            $minSeats = $tpl['direct_after_transfer_min_seats'];
                            include $pathToViewElements.'directBusReservation.php';
                        }else{//direct way
                            if(isset($STORE['not_available_bus']) && (false !== $STORE['not_available_bus'])){
                                $bus_list = $STORE['not_available_bus'];
                                include $pathToViewElements.'ticketsNotAvailable.php';
                            }

                            if(isset($tpl['direct_before_transfer_bus_list'])){
                                $bus_list = $tpl['direct_before_transfer_bus_list'];
                                $minSeats = $tpl['direct_before_transfer_min_seats'];
                                include $pathToViewElements.'directBusReservation.php';
                            }
                            
                            $pickup_id = $dataPickupId = $STORE['pickup_id'];
                            $return_id = $dataReturnId = $STORE['return_id'];
                            $bus_list = $tpl['bus_list'];
                            $minSeats = $tpl['direct_min_seats'];
                            include $pathToViewElements.'directBusReservation.php';

                            if(isset($STORE['return_not_available_bus']) && (false !== $STORE['return_not_available_bus'])){
                                $bus_list = $STORE['return_not_available_bus'];
                                include $pathToViewElements.'ticketsNotAvailable.php';
                            }
                            
                        }
                    }
                    include $pathToViewElements.'seatsFormFooter.php';?>
                </form>
            <?}else{
                include $pathToViewElements.'messageBlock.php';
            }?>
        </div>
    </div>
</div>


<?include $pathToViewElements.'pjBsModalRoute.php';?>