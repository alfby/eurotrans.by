<?
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFrontPublic extends pjFront
{
	public function __construct()
	{
            parent::__construct();
            $this->setAjax(true);
            $this->locId = $this->getLocaleId();
            $this->busJoinStr = "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->locId."'";
            $this->cityJoinStr = "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'";
            $this->setLayout('pjActionEmpty');
	}
	
	public function pjActionSearch()
	{
            $this->setAjax(true);

            if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
            {
                $_SESSION[$this->defaultStep]['1_passed'] = true;

                $pjCityModel = pjCityModel::factory();
                $pjRouteDetailModel = pjRouteDetailModel::factory();
                $pjRouteDetailModelTable = $pjRouteDetailModel->getTable();
                $pjRouteCityModel = pjRouteCityModel::factory();
                $pjRouteCityModelTable = $pjRouteCityModel->getTable();
                $this->locId = $this->getLocaleId();

                $from_location_arr = $pjCityModel
                    ->reset()
                    ->select('t1.*, t2.content as name')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                    ->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModelTable."` AS TRD)")
                    ->where("t1.id IN(SELECT TRC.city_id FROM `".$pjRouteCityModelTable."` AS TRC WHERE TRC.is_departure=1)")
                    ->orderBy("t2.content ASC")
                    ->findAll()
                    ->getData();
                
                $to_location_arr = $pjCityModel
                    ->reset()
                    ->select('t1.*, t2.content as name')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                    ->where("t1.id IN(SELECT TRC.city_id FROM `".$pjRouteCityModelTable."` AS TRC WHERE TRC.is_arrival=1)")
                    ->orderBy("t2.content ASC")
                    ->findAll()
                    ->getData();

                if($this->_is('pickup_id'))
                {
                    $pickup_id = $this->_get('pickup_id');
                    $return_location_arr = $pjCityModel->getDestCities($pickup_id,$this->locId,',');
                    $this->set('return_location_arr', $return_location_arr);
                }
                $image = pjOptionModel::factory()
                    ->where('t1.foreign_id', $this->getForeignId())
                    ->where('t1.key', 'o_image_path')
                    ->orderBy('t1.order ASC')
                    ->findAll()
                    ->getData();
                $content = pjMultiLangModel::factory()->select('t1.*')
                    ->where('t1.model','pjOption')
                    ->where('t1.locale', $this->locId)
                    ->where('t1.field', 'o_content')
                    ->limit(0, 1)
                    ->index("FORCE KEY (`foreign_id`)")
                    ->findAll()
                    ->getData();

                $this->set('from_location_arr', $from_location_arr);
                $this->set('to_location_arr', $to_location_arr);
                $this->set('content_arr', compact('content', 'image'));
                $this->set('status', 'OK');
            }
	}
	
	public function pjActionSeats()
	{
            if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
            {
                $_SESSION[$this->defaultStep]['2_passed'] = true;
                if (isset($_SESSION[$this->defaultStore]) && count($_SESSION[$this->defaultStore]) > 0 && $this->isBusReady() == true)
                {
                    $minSeats = 0;
                    $showCommonTicketPanel = true;
                    $totalTicketPriceArr = array();
                    $field_price = $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]];
                    $booking_period = $this->_is('booking_period') ? $this->_get('booking_period') : array();
                    $this->_set('booking_period',$booking_period);
                    $dateFormat = 'Y-m-d';
                    $date = pjUtil::getDateArray($this->_get('date'),$dateFormat,$this->option_arr['o_date_format']);
                    $this->_set('direct_date_arr',$date);
                    
                    $booked_data = ($this->_is('booked_data')) ? $this->_get('booked_data') : array();
                    $is_transfer = $this->_get('is_transfer');
                    $is_return = $this->_get('is_return');
                    
                    if('T' === $is_return){
                        $return_date_arr = pjUtil::getDateArray($this->_get('return_date'),$dateFormat,$this->option_arr['o_date_format']);
                        $this->_set('return_date_arr',$return_date_arr);
                            
                        if(1 == $is_transfer){
                            $bus_id_return_before_transfer_arr = $this->_get('bus_id_before_transfer_arr');
                            $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                            $before_transfer_return_id = $this->_get('before_transfer_return_id');
                            
                            $before_transfer_bus_list = $this->getBusList($before_transfer_pickup_id, $before_transfer_return_id, $bus_id_return_before_transfer_arr, $booking_period, $booked_data, $date['current_date'], 'T');
                            if(isset($before_transfer_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                $busMinSeats = getMinQuant($before_transfer_bus_list['bus_arr'],'seats_available');
                                $this->set('direct_min_seats',$busMinSeats);
                                if(0 === $busMinSeats){//no seats available
                                    $showCommonTicketPanel = false;
                                    $transfer_date = $date['current_date'];
                                }else{
                                    $busId = $before_transfer_bus_list['bus_arr'][0]['id'];
                                    $booking_period[$busId] = $before_transfer_bus_list['booking_period'][$busId];
                                    $this->set('before_transfer_bus_list',$before_transfer_bus_list);
                                    $this->set('ticket_arr', $before_transfer_bus_list['bus_arr'][0]['ticket_arr']);
                                    $this->set('booked_seat_arr', $before_transfer_bus_list['booked_seat_arr']);
                                    $totalTicketPriceArr = getTotTicketPrice($before_transfer_bus_list,$field_price,$totalTicketPriceArr);
                                    $minSeats = $busMinSeats;
                                    $transfer_date = pjUtil::formatTime2Date($before_transfer_bus_list['bus_arr'][0]['arrival_time'], 'Y-m-d H:i:s',$this->option_arr['o_date_format']);
                                }
                                $transfer_date_arr = pjUtil::getDateArray($transfer_date,$dateFormat,$this->option_arr['o_date_format']);
                                $this->_set('transfer_date_arr',$transfer_date_arr);
                                
                                $bus_id_after_transfer_arr = $this->_get('bus_id_after_transfer_arr');
                                $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                                $after_transfer_return_id = $this->_get('after_transfer_return_id');
                                $after_transfer_bus_list = $this->getBusList($after_transfer_pickup_id, $after_transfer_return_id, $bus_id_after_transfer_arr, $booking_period, $booked_data, $transfer_date_arr['current_date'], 'F');
                                if(isset($after_transfer_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                    $busMinSeats = getMinQuant($after_transfer_bus_list['bus_arr'],'seats_available');
                                    if(0 === $busMinSeats){//no seats available
                                        $showCommonTicketPanel = false;
                                    }else{
                                        $totalTicketPriceArr = getTotTicketPrice($after_transfer_bus_list,$field_price,$totalTicketPriceArr);
                                        $minSeats = ($busMinSeats < $minSeats) ? $busMinSeats : $minSeats;
                                        $busId = $after_transfer_bus_list['bus_arr'][0]['id'];
                                        $booking_period[$busId] = $after_transfer_bus_list['booking_period'][$busId];
                                        $this->set('after_transfer_bus_list', $after_transfer_bus_list);
                                        $this->set('after_transfer_data_pickup_id', $after_transfer_pickup_id);
                                        $this->set('after_transfer_data_return_id', $after_transfer_return_id);
                                    }
                                    $this->set('after_transfer_from_location', $after_transfer_bus_list['from_location']);
                                    $this->set('after_transfer_to_location', $after_transfer_bus_list['to_location']);
                                }
                                
                                
                                //return travelling
                                $return_before_transfer_pickup_id = $this->_get('return_before_transfer_pickup_id');
                                $return_before_transfer_return_id = $this->_get('return_before_transfer_return_id');
                                $return_before_transfer_bus_list = $this->getBusList($return_before_transfer_pickup_id, $return_before_transfer_return_id, $this->_get('bus_id_return_before_transfer_arr'), $booking_period, $booked_data, $return_date_arr['current_date'], 'F');
                                if(isset($return_before_transfer_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                    $busMinSeats = getMinQuant($return_before_transfer_bus_list['bus_arr'],'seats_available');
                                    if(0 === $busMinSeats){//no seats available
                                        $showCommonTicketPanel = false;
                                        $return_transfer_date = $return_date_arr['current_date'];
                                    }else{
                                        $totalTicketPriceArr = getTotTicketPrice($return_before_transfer_bus_list,$field_price,$totalTicketPriceArr);
                                        $minSeats = ($busMinSeats < $minSeats) ? $busMinSeats : $minSeats;

                                        $return_transfer_date = pjUtil::formatTime2Date($return_before_transfer_bus_list['bus_arr'][0]['arrival_time'], 'Y-m-d H:i:s',$this->option_arr['o_date_format']);
                                        
                                        $busId = $return_before_transfer_bus_list['bus_arr'][0]['id'];
                                        $booking_period[$busId] = $return_before_transfer_bus_list['booking_period'][$busId];
                                        $this->set('return_before_transfer_bus_list', $return_before_transfer_bus_list);
                                        $this->set('return_before_transfer_data_pickup_id', $return_before_transfer_pickup_id);
                                        $this->set('return_before_transfer_data_return_id', $return_before_transfer_return_id);
                                        $this->set('booked_return_before_transfer_seat_arr', $return_before_transfer_bus_list['booked_seat_arr']);
                                    }
                                }
                                $return_transfer_date_arr = pjUtil::getDateArray($return_transfer_date,$dateFormat,$this->option_arr['o_date_format']);
                                $this->_set('return_transfer_date_arr',$return_transfer_date_arr);

                                $return_after_transfer_pickup_id = $this->_get('return_after_transfer_pickup_id');
                                $return_after_transfer_return_id = $this->_get('return_after_transfer_return_id');
                                $return_after_transfer_bus_list = $this->getBusList($return_after_transfer_pickup_id, $return_after_transfer_return_id, $this->_get('bus_id_return_after_transfer_arr'), $booking_period, $booked_data, $return_transfer_date, 'F');
                                if(isset($return_after_transfer_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                    $busMinSeats = getMinQuant($return_after_transfer_bus_list['bus_arr'],'seats_available');
                                    if(0 === $busMinSeats){//no seats available
                                        $showCommonTicketPanel = false;
                                    }else{
                                        $totalTicketPriceArr = getTotTicketPrice($return_after_transfer_bus_list,$field_price,$totalTicketPriceArr);
                                        $minSeats = ($busMinSeats < $minSeats) ? $busMinSeats : $minSeats;
                                      
                                        $busId = $return_after_transfer_bus_list['bus_arr'][0]['id'];
                                        $booking_period[$busId] = $return_after_transfer_bus_list['booking_period'][$busId];
                                        
                                        $this->set('return_after_transfer_bus_list', $return_after_transfer_bus_list);
                                        $this->set('return_after_transfer_data_pickup_id', $return_after_transfer_pickup_id);
                                        $this->set('return_after_transfer_data_return_id', $return_after_transfer_return_id);
                                        $this->set('booked_return_after_transfer_seat_arr', $return_after_transfer_bus_list['booked_seat_arr']);
                                    }
                                }
                            }
                            
                            $this->_set('booking_period',$booking_period);
                            $this->set('ticket_columns', $before_transfer_bus_list['ticket_columns']);
                        }else{//return travelling without transfer
                            $pickup_id = $this->_get('pickup_id');
                            $return_id = $this->_get('return_id');
                            $bus_id_arr = $this->_get('bus_id_arr');
                           
                            $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date['current_date'], 'F');
                            if(isset($bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                $busMinSeats = getMinQuant($bus_list['bus_arr'],'seats_available');
                                $this->set('direct_min_seats',$busMinSeats);
                                if(0 === $busMinSeats){//no seats available
                                    $showCommonTicketPanel = false;
                                }else{
                                    $booking_period = $bus_list['booking_period']; 
                                    $minSeats = $busMinSeats;
                                    $this->set('bus_list', $bus_list);
                                    $this->set('direct_min_seats',$busMinSeats);
                                    $totalTicketPriceArr = getTotTicketPrice($bus_list,$field_price,$totalTicketPriceArr);
                                }
                            }
                             
                            //return
                            $return_bus_id_arr = $this->_get('return_bus_id_arr');
                            $return_bus_list = $this->getBusList($return_id, $pickup_id, $return_bus_id_arr, $booking_period, $booked_data, $return_date_arr['current_date'], 'T');
                            if(isset($return_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                $busMinSeats = getMinQuant($return_bus_list['bus_arr'],'seats_available');
                                $this->set('return_min_seats',$busMinSeats);
                                if(0 === $busMinSeats){//no seats available
                                    $showCommonTicketPanel = false;
                                }else{
                                    $booking_period = $return_bus_list['booking_period'];
                                    $minSeats = ($minSeats > $busMinSeats) ? $busMinSeats : $minSeats;
                                    $totalTicketPriceArr = getTotTicketPrice($return_bus_list,$field_price,$totalTicketPriceArr);
                                }
                                $this->set('return_bus_list',$return_bus_list);
                            }

                            $this->_set('booking_period',$booking_period);
                            $this->set('ticket_arr', $bus_list['bus_arr'][0]['ticket_arr']);
                            $this->set('ticket_columns', $bus_list['ticket_columns']);
                        }
                        
                        $this->set('total_ticket_price_arr',$totalTicketPriceArr);
                    }else{//one way trawelling
                        if(1 == $is_transfer){//transfer
                            if($this->_is('bus_id_arr')){
                                $pickup_id = $this->_get('before_transfer_pickup_id');
                                $return_id = $this->_get('before_transfer_return_id');
                                $before_transfer_date = $date['current_date'];

                                $direct_before_transfer_bus_list = $this->getBusList($pickup_id, $return_id, $this->_get('bus_id_before_transfer_arr'), $booking_period, $booked_data, $before_transfer_date, 'F');
                                $totalTicketPriceArr = getTotTicketPrice($direct_before_transfer_bus_list,$field_price);
                                if(isset($direct_before_transfer_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                    $busMinSeats = getMinQuant($direct_before_transfer_bus_list['bus_arr'],'seats_available');
                                    $this->set('data_pickup_id',$pickup_id);
                                    $this->set('data_return_id',$return_id);
                                    $this->set('before_transfer_bus_list', $direct_before_transfer_bus_list);
                                    $minSeats = $busMinSeats;
                                    $after_transfer_date = pjUtil::formatTime2Date($direct_before_transfer_bus_list['bus_arr'][0]['arrival_time'], 'Y-m-d H:i:s',$this->option_arr['o_date_format']);
                                }else{
                                    $after_transfer_date = $before_transfer_date;
                                    $busMinSeats = 0;
                                }

                                $after_transfer_date_arr = pjUtil::getDateArray($after_transfer_date,$dateFormat,$this->option_arr['o_date_format']);
                                $this->_set('after_transfer_date_arr',$after_transfer_date_arr);
                                $this->set('direct_min_seats',$busMinSeats);
                                $booking_period = $direct_before_transfer_bus_list['booking_period'];
                                $direct_after_transfer_bus_list = $this->getBusList($this->_get('after_transfer_pickup_id'), $this->_get('after_transfer_return_id'), $this->_get('bus_id_after_transfer_arr'), $booking_period, $booked_data, $after_transfer_date_arr['current_date'], 'F');
                                $totalTicketPriceArr = getTotTicketPrice($direct_after_transfer_bus_list,$field_price,$totalTicketPriceArr);
                                if(isset($direct_after_transfer_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                    $busMinSeats = getMinQuant($direct_after_transfer_bus_list['bus_arr'],'seats_available');
                                    $this->set('after_transfer_bus_list', $direct_after_transfer_bus_list);
                                    $minSeats = ($minSeats>$busMinSeats) ? $busMinSeats : $minSeats;
                                }else{
                                    $busMinSeats = 0;
                                }
                                
                                if(0 !== $minSeats){
                                    $this->set('ticket_arr', $direct_after_transfer_bus_list['bus_arr'][0]['ticket_arr']);
                                    $this->set('ticket_columns', $direct_after_transfer_bus_list['ticket_columns']);
                                }
                                
                                $showCommonTicketPanel = (0 === $minSeats) ? false : true;
                                $this->set('show_common_ticket_panel',$showCommonTicketPanel);
                                $this->set('total_ticket_price_arr',$totalTicketPriceArr);
                                $this->set('direct_after_transfer_min_seats',$busMinSeats);
                                $booking_period = $direct_after_transfer_bus_list['booking_period'];
                                $this->_set('booking_period', $booking_period);
                            }
                        }else{//direct way
                            $showCommonTicketPanel = false;
                            if($this->_is('bus_id_arr')){
                                $bus_id_arr = $this->_get('bus_id_arr');
                                $pickup_id = $this->_get('pickup_id');
                                $return_id = $this->_get('return_id');

                                $direct_bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date['current_date'], 'F');
                                $totalTicketPriceArr = getTotTicketPrice($direct_bus_list,$field_price);
                                if(isset($direct_bus_list['bus_arr'][0]['seats_available'])){// seats are available
                                    $busMinSeats = getMinQuant($direct_bus_list['bus_arr'],'seats_available');
                                    $this->set('direct_min_seats',$busMinSeats);

                                    if(0 !== $busMinSeats){//seats available
                                        $this->set('data_pickup_id',$pickup_id);
                                        $this->set('data_return_id',$return_id);
                                    }
                                    
                                    $this->_set('booking_period', $direct_bus_list['booking_period']);
                                    $this->set('bus_list', $direct_bus_list);
                                }
                            }
                        }                        
                    }
                                
                    $this->set('show_common_ticket_panel',$showCommonTicketPanel);
                    $this->set('min_seats', $minSeats);
                    $this->set('status', 'OK');
                }else{
                    $this->set('status', 'ERR');
                }
            }
	}
	
	public function pjActionCheckout()
	{
            if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
            {
                $_SESSION[$this->defaultStep]['3_passed'] = true;

                if (isset($_SESSION[$this->defaultStore]) && count($_SESSION[$this->defaultStore]) > 0 && $this->isBusReady() == true)
                {
                    $booked_data = $this->_get('booked_data');
                    $pickup_id = $this->_get('pickup_id');
                    $return_id = $this->_get('return_id');
                    $is_return = $this->_get('is_return');
                    $is_transfer = $this->_get('is_transfer');
                    $selected_seat_arr = array();
                    $timeFormat = 'H:i:s';
                    $field_price = (isset($this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]])) 
                        ? $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]]
                        : 'price';
                    $this->locId = $this->getLocaleId();
                    
                    $pjCityModel = pjCityModel::factory();
                    $pjBusModel= pjBusModel::factory();
                    $pjBusLocationModel = pjBusLocationModel::factory();
                    $pjPriceModel = pjPriceModel::factory();
                    $pjSeatModel = pjSeatModel::factory();

                    if ('T' == $is_return){
                        if (1 == $is_transfer)//return way with transfer
                        {
                            $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                            $before_transfer_return_id = $this->_get('before_transfer_return_id');
                            $before_transfer_bus_id = $booked_data['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id];
                            $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                            $after_transfer_return_id = $this->_get('after_transfer_return_id');
                            $after_transfer_bus_id = $booked_data['bus_id'][$after_transfer_pickup_id.$after_transfer_return_id];
                            
                            $return_before_transfer_pickup_id = $this->_get('return_before_transfer_pickup_id');
                            $return_before_transfer_return_id = $this->_get('return_before_transfer_return_id');
                            $return_before_transfer_bus_id = $booked_data['return_bus_id'][$return_before_transfer_pickup_id.$return_before_transfer_return_id];
                            $return_after_transfer_pickup_id = $this->_get('return_after_transfer_pickup_id');
                            $return_after_transfer_return_id = $this->_get('return_after_transfer_return_id');
                            $return_after_transfer_bus_id = $booked_data['return_bus_id'][$return_after_transfer_pickup_id.$return_after_transfer_return_id];

                            //direct bus before transfer
                            $before_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$pickup_id);
                            $before_transfer_return_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$return_id);
                            $before_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($before_transfer_bus_id)
                                ->getData();
                            $before_transfer_bus_arr = pjUtil::formBusArray($before_transfer_bus_arr,$before_transfer_pickup_arr,$before_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $before_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_pickup_id)
                                ->getData();
                            $before_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_return_id)
                                ->getData();
                            $before_transfer_bus_arr['from_location'] = $before_transfer_pickup_location['name'];
                            $before_transfer_bus_arr['to_location'] = $before_transfer_return_location['name'];
                            $before_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('before_transfer_bus_arr', $before_transfer_bus_arr);
                                               
                            //direct bus after transfer
                            $after_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_pickup_id);
                            $after_transfer_return_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_return_id);
                            $after_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($after_transfer_bus_id)
                                ->getData();
                            $after_transfer_bus_arr = pjUtil::formBusArray($after_transfer_bus_arr,$after_transfer_pickup_arr,$after_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $after_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_pickup_id)
                                ->getData();
                            $after_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_return_id)
                                ->getData();
                            $after_transfer_bus_arr['from_location'] = $after_transfer_pickup_location['name'];
                            $after_transfer_bus_arr['to_location'] = $after_transfer_return_location['name'];
                            $after_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('after_transfer_bus_arr', $after_transfer_bus_arr);
                             
                            //return bus before transfer
                            $return_before_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($return_before_transfer_bus_id,$return_before_transfer_pickup_id);
                            $return_before_transfer_return_arr = $pjBusLocationModel->getLocInfo($return_before_transfer_bus_id,$return_before_transfer_return_id);
                            $return_before_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($return_before_transfer_bus_id)
                                ->getData();
                            $return_before_transfer_bus_arr = pjUtil::formBusArray($return_before_transfer_bus_arr,$return_before_transfer_pickup_arr,$return_before_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $return_before_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_before_transfer_pickup_id)
                                ->getData();
                            $return_before_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_before_transfer_return_id)
                                ->getData();
                            $return_before_transfer_bus_arr['from_location'] = $return_before_transfer_pickup_location['name'];
                            $return_before_transfer_bus_arr['to_location'] = $return_before_transfer_return_location['name'];
                            $return_before_transfer_bus_arr['date'] = $this->_get('transfer_return_date');
                            $this->set('return_before_transfer_bus_arr', $return_before_transfer_bus_arr);
                            
                            //return after transfer bus
                            $return_after_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($return_after_transfer_bus_id,$return_after_transfer_pickup_id);
                            $return_after_transfer_return_arr = $pjBusLocationModel->getLocInfo($return_after_transfer_bus_id,$return_after_transfer_return_id);
                            $return_after_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($return_after_transfer_bus_id)
                                ->getData();
                            $return_after_transfer_bus_arr = pjUtil::formBusArray($return_after_transfer_bus_arr,$return_after_transfer_pickup_arr,$return_after_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $return_after_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_after_transfer_pickup_id)
                                ->getData();
                            $return_after_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_after_transfer_return_id)
                                ->getData();
                            $return_after_transfer_bus_arr['from_location'] = $return_after_transfer_pickup_location['name'];
                            $return_after_transfer_bus_arr['to_location'] = $return_after_transfer_return_location['name'];
                            $return_after_transfer_bus_arr['date'] = $this->_get('transfer_return_date');
                            $this->set('return_after_transfer_bus_arr', $return_after_transfer_bus_arr);
                            
                            
                            //direct price 
                            $direct_before_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($before_transfer_bus_id, $before_transfer_pickup_id, $before_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'F', $field_price, $is_transfer);
                            if(!empty($direct_before_transfer_ticket_price_arr)){
                                $ticket_price_arr[0] = $direct_before_transfer_ticket_price_arr;
                            }
                            $direct_after_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($after_transfer_bus_id, $after_transfer_pickup_id, $after_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'F', $field_price, $is_transfer);
                            if(!empty($direct_after_transfer_ticket_price_arr)){
                                $ticket_price_arr[1] = $direct_after_transfer_ticket_price_arr;
                            }
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);
                            $this->set('ticket_price_arr', $ticket_price_arr);

                            //return price 
                            $return_before_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($return_before_transfer_bus_id, $return_before_transfer_pickup_id, $return_before_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'T', $field_price, $is_transfer);
                            if(!empty($return_before_transfer_ticket_price_arr)){
                                $return_ticket_price_arr[0] = $return_before_transfer_ticket_price_arr ;
                            }
                            $return_after_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($return_after_transfer_bus_id, $return_after_transfer_pickup_id, $return_after_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'T', $field_price, $is_transfer);
                            if(!empty($return_after_transfer_ticket_price_arr)){
                                $return_ticket_price_arr[1] = $return_after_transfer_ticket_price_arr;
                            }
                            $return_ticket_price_total = pjUtil::formPriceArray($return_ticket_price_arr);
                            $this->set('return_price_arr', $return_ticket_price_total);
                            $this->set('return_ticket_price_arr', $return_ticket_price_arr);

                            //direct selected seats
                            $direct_before_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                            if(!empty($direct_before_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $direct_before_transfer_selected_seats_arr;
                            }
                            $direct_after_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                            if(!empty($direct_before_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $direct_after_transfer_selected_seats_arr;
                            }
                            $this->set('selected_seat_arr', $selected_seat_arr);

                            //return selected seats
                            $return_before_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['return_selected_seats'][$return_before_transfer_pickup_id.$return_before_transfer_return_id]);
                            if(!empty($return_before_transfer_selected_seats_arr)){
                                $return_selected_seat_arr[] = $return_before_transfer_selected_seats_arr;
                            }
                            $return_after_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['return_selected_seats'][$return_after_transfer_pickup_id.$return_after_transfer_return_id]);
                            if(!empty($return_before_transfer_selected_seats_arr)){
                                $return_selected_seat_arr[] = $return_after_transfer_selected_seats_arr;
                            }
                            $this->set('return_selected_seat_arr', $return_selected_seat_arr);
                        }else{//return way without transfer
                            $bus_id = $booked_data['bus_id'][$pickup_id.$return_id];
                            //direct bus
                            $pickup_arr = $pjBusLocationModel->getLocInfo($bus_id,$pickup_id);
                            $return_arr = $pjBusLocationModel->getLocInfo($bus_id,$return_id);
                            $bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($bus_id)
                                ->getData();
                            $bus_arr = pjUtil::formBusArray($bus_arr,$pickup_arr,$return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($pickup_id)
                                ->getData();
                            $return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_id)
                                ->getData();
                            $bus_arr['from_location'] = $pickup_location['name'];
                            $bus_arr['to_location'] = $return_location['name'];
                            $bus_arr['date'] = $this->_get('date');
                            $this->set('bus_arr', $bus_arr);
                            
                            //return bus
                            $return_bus_id = $booked_data['return_bus_id'][$return_id.$pickup_id];
                            $return_pickup_arr = $pjBusLocationModel->getLocInfo($return_bus_id,$return_id);
                            $return_return_arr = $pjBusLocationModel->getLocInfo($return_bus_id,$pickup_id);
                            $return_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($bus_id)
                                ->getData();
                            $return_bus_arr = pjUtil::formBusArray($return_bus_arr,$return_pickup_arr,$return_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $return_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_id)
                                ->getData();
                            $return_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($pickup_id)
                                ->getData();
                            $return_bus_arr['from_location'] = $return_pickup_location['name'];
                            $return_bus_arr['to_location'] = $return_return_location['name'];
                            $return_bus_arr['date'] = $this->_get('return_date');
                            $this->set('return_bus_arr', $return_bus_arr);                    
                            
                            //price
                            $direct_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->locId, 'F', $field_price, $is_transfer);
                            if(!empty($direct_ticket_price_arr)){
                                $ticket_price_arr[] = $direct_ticket_price_arr;
                                $this->set('ticket_price_arr', array(0 => $direct_ticket_price_arr));
                            }
                            $return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->locId, 'T', $field_price, $is_transfer);
                            if(!empty($return_ticket_price_arr)){
                                $ticket_price_arr[] = $return_ticket_price_arr;
                                $this->set('return_ticket_price_arr', array(0 => $return_ticket_price_arr));
                            }
                            
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);
                            
                            //selected seats
                            $direct_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$pickup_id.$return_id]);
                            if(!empty($direct_selected_seats_arr)){
                                $selected_seat_arr[] = $direct_selected_seats_arr;
                                $this->set('selected_seat_arr', array(0 => $direct_selected_seats_arr));
                            }
                            $return_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['return_selected_seats'][$return_id.$pickup_id]);
                            if(!empty($return_selected_seats_arr)){
                                $selected_seats_arr[] = $return_selected_seats_arr;
                                $this->set('return_selected_seat_arr', array(0 => $return_selected_seats_arr));
                            }
                        }
                    }else{
                        if (1 == $is_transfer)//direct way with transfer
                        {
                            $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                            $before_transfer_return_id = $this->_get('before_transfer_return_id');
                            $before_transfer_bus_id = $booked_data['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id];
                            $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                            $after_transfer_return_id = $this->_get('after_transfer_return_id');
                            $after_transfer_bus_id = $booked_data['bus_id'][$before_transfer_return_id.$return_id];
                            
                            //direct bus before transfer
                            $before_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$before_transfer_pickup_id);
                            $before_transfer_return_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$before_transfer_return_id);
                            $before_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($before_transfer_bus_id)
                                ->getData();

                            $before_transfer_bus_arr = pjUtil::formBusArray($before_transfer_bus_arr,$before_transfer_pickup_arr,$before_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $before_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_pickup_id)
                                ->getData();
                            
                            $before_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_return_id)
                                ->getData();
                            $before_transfer_bus_arr['from_location'] = $before_transfer_pickup_location['name'];
                            $before_transfer_bus_arr['to_location'] = $before_transfer_return_location['name'];
                            $before_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('before_transfer_bus_arr', $before_transfer_bus_arr);
                            
                            //direct bus after transfer
                            $after_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_pickup_id);
                            $after_transfer_return_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_return_id);
                            $after_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($after_transfer_bus_id)
                                ->getData();
                            
                            $after_transfer_bus_arr = pjUtil::formBusArray($after_transfer_bus_arr,$after_transfer_pickup_arr,$after_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $after_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_pickup_id)
                                ->getData();
                            
                            $after_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_return_id)
                                ->getData();
                            $after_transfer_bus_arr['from_location'] = $after_transfer_pickup_location['name'];
                            $after_transfer_bus_arr['to_location'] = $after_transfer_return_location['name'];
                            $after_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('after_transfer_bus_arr', $after_transfer_bus_arr);
                            
                            //price
                            $before_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($before_transfer_bus_id, $before_transfer_pickup_id, $before_transfer_return_id, $booked_data, $this->option_arr, $this->locId, $is_return, $field_price, $is_transfer);
                            $after_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($after_transfer_bus_id, $after_transfer_pickup_id, $after_transfer_return_id, $booked_data, $this->option_arr, $this->locId, $is_return, $field_price, $is_transfer);
                            if(!empty($before_transfer_ticket_price_arr)){
                                $ticket_price_arr[] = $before_transfer_ticket_price_arr;
                            }
                            if(!empty($after_transfer_ticket_price_arr)){
                                $ticket_price_arr[] = $after_transfer_ticket_price_arr;
                            }
                            $this->set('ticket_price_arr', $ticket_price_arr);
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);

                            //selected seats
                            $before_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                            if(!empty($before_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $before_transfer_selected_seats_arr;
                            }
                            $after_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                            if(!empty($after_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $after_transfer_selected_seats_arr;
                            }
                            $this->set('selected_seat_arr', $selected_seat_arr);
                        }else{//direct way
                            $bus_id = $booked_data['bus_id'][$pickup_id.$return_id];
                            $pickup_arr = $pjBusLocationModel->getLocInfo($bus_id,$pickup_id);
                            $return_arr = $pjBusLocationModel->getLocInfo($bus_id,$return_id);
                            $bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($bus_id)
                                ->getData();
                            
                            $bus_arr = pjUtil::formBusArray($bus_arr,$pickup_arr,$return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            
                            $pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($pickup_id)
                                ->getData();
                            
                            $return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_id)
                                ->getData();
                            $bus_arr['from_location'] = $pickup_location['name'];
                            $bus_arr['to_location'] = $return_location['name'];
                            $bus_arr['date'] = $this->_get('date');
                            $this->set('bus_arr', $bus_arr);
                            
                            //ticket price
                            $price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->locId, $is_return, $field_price, $is_transfer);
                            if(!empty($price_arr)){
                                $ticket_price_arr[1] = $price_arr;
                            }
                            $this->set('ticket_price_arr', $ticket_price_arr);

                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);

                            //selected seats
                            $selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$pickup_id.$return_id]);
                            if(!empty($selected_seats_arr)){
                                $selected_seat_arr[] = $selected_seats_arr;
                                $this->set('selected_seat_arr', $selected_seat_arr);
                            }
                        }                        
                    }
                    
                               
                    $country_arr = pjCountryModel::factory()
                        ->select('t1.id, t2.content AS country_title')
                        ->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                        ->orderBy('`country_title` ASC')
                        ->findAll()
                        ->getData();
                    
                    $terms_conditions = pjMultiLangModel::factory()
                        ->select('t1.*')
                        ->where('t1.model','pjOption')
                        ->where('t1.locale', $this->locId)
                        ->where('t1.field', 'o_terms')
                        ->limit(0, 1)
                        ->findAll()
                        ->getData();
                    
                    $this->set('ticket_price_total', $ticket_price_total);
                    $this->set('country_arr', $country_arr);
                    $this->set('terms_conditions', $terms_conditions[0]['content']);
                    $this->set('status', 'OK');
                }else{
                    $this->set('status', 'ERR');
                }
            }
	}
	
	public function pjActionPreview()
	{
            if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
            {
                $_SESSION[$this->defaultStep]['4_passed'] = true;

                if (isset($_SESSION[$this->defaultForm]) && count($_SESSION[$this->defaultForm]) > 0 && $this->isBusReady() == true)
                {
                    $booked_data = $this->_get('booked_data');
                    $pickup_id = $this->_get('pickup_id');
                    $return_id = $this->_get('return_id');
                    $bus_id = $booked_data['bus_id'];
                    $is_return = $this->_get('is_return');
                    $is_transfer = $this->_get('is_transfer');
                    $selected_seat_arr = array();
                    $timeFormat = 'H:i:s';
                    $field_price = (isset($this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]])) 
                        ? $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]]
                        : 'price';
                    
                    $pjCityModel = pjCityModel::factory();
                    $pjBusModel= pjBusModel::factory();
                    $pjBusLocationModel = pjBusLocationModel::factory();
                    $pjPriceModel = pjPriceModel::factory();
                    $pjSeatModel = pjSeatModel::factory();
                    
                    if('T' === $is_return){
                        if(1 == $is_transfer){//return way with transfer 
                            $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                            $before_transfer_return_id = $this->_get('before_transfer_return_id');
                            $before_transfer_bus_id = $booked_data['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id];
                            $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                            $after_transfer_return_id = $this->_get('after_transfer_return_id');
                            $after_transfer_bus_id = $booked_data['bus_id'][$after_transfer_pickup_id.$after_transfer_return_id];
                            
                            $return_before_transfer_pickup_id = $this->_get('return_before_transfer_pickup_id');
                            $return_before_transfer_return_id = $this->_get('return_before_transfer_return_id');
                            $return_before_transfer_bus_id = $booked_data['return_bus_id'][$return_before_transfer_pickup_id.$return_before_transfer_return_id];
                            $return_after_transfer_pickup_id = $this->_get('return_after_transfer_pickup_id');
                            $return_after_transfer_return_id = $this->_get('return_after_transfer_return_id');
                            $return_after_transfer_bus_id = $booked_data['return_bus_id'][$return_after_transfer_pickup_id.$return_after_transfer_return_id];

                            //direct bus before transfer
                            $before_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$pickup_id);
                            $before_transfer_return_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$return_id);
                            $before_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($before_transfer_bus_id)
                                ->getData();
                            $before_transfer_bus_arr = pjUtil::formBusArray($before_transfer_bus_arr,$before_transfer_pickup_arr,$before_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $before_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_pickup_id)
                                ->getData();
                            $before_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_return_id)
                                ->getData();
                            $before_transfer_bus_arr['from_location'] = $before_transfer_pickup_location['name'];
                            $before_transfer_bus_arr['to_location'] = $before_transfer_return_location['name'];
                            $before_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('before_transfer_bus_arr', $before_transfer_bus_arr);
                                               
                            //direct bus after transfer
                            $after_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_pickup_id);
                            $after_transfer_return_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_return_id);
                            $after_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($after_transfer_bus_id)
                                ->getData();
                            $after_transfer_bus_arr = pjUtil::formBusArray($after_transfer_bus_arr,$after_transfer_pickup_arr,$after_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $after_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_pickup_id)
                                ->getData();
                            $after_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_return_id)
                                ->getData();
                            $after_transfer_bus_arr['from_location'] = $after_transfer_pickup_location['name'];
                            $after_transfer_bus_arr['to_location'] = $after_transfer_return_location['name'];
                            $after_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('after_transfer_bus_arr', $after_transfer_bus_arr);
                             
                            //return bus before transfer
                            $return_before_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($return_before_transfer_bus_id,$return_before_transfer_pickup_id);
                            $return_before_transfer_return_arr = $pjBusLocationModel->getLocInfo($return_before_transfer_bus_id,$return_before_transfer_return_id);
                            $return_before_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($return_before_transfer_bus_id)
                                ->getData();
                            $return_before_transfer_bus_arr = pjUtil::formBusArray($return_before_transfer_bus_arr,$return_before_transfer_pickup_arr,$return_before_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $return_before_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_before_transfer_pickup_id)
                                ->getData();
                            $return_before_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_before_transfer_return_id)
                                ->getData();
                            $return_before_transfer_bus_arr['from_location'] = $return_before_transfer_pickup_location['name'];
                            $return_before_transfer_bus_arr['to_location'] = $return_before_transfer_return_location['name'];
                            $return_before_transfer_bus_arr['date'] = $this->_get('transfer_return_date');
                            $this->set('return_before_transfer_bus_arr', $return_before_transfer_bus_arr);
                            
                            //return after transfer bus
                            $return_after_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($return_after_transfer_bus_id,$return_after_transfer_pickup_id);
                            $return_after_transfer_return_arr = $pjBusLocationModel->getLocInfo($return_after_transfer_bus_id,$return_after_transfer_return_id);
                            $return_after_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($return_after_transfer_bus_id)
                                ->getData();
                            $return_after_transfer_bus_arr = pjUtil::formBusArray($return_after_transfer_bus_arr,$return_after_transfer_pickup_arr,$return_after_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $return_after_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_after_transfer_pickup_id)
                                ->getData();
                            $return_after_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_after_transfer_return_id)
                                ->getData();
                            $return_after_transfer_bus_arr['from_location'] = $return_after_transfer_pickup_location['name'];
                            $return_after_transfer_bus_arr['to_location'] = $return_after_transfer_return_location['name'];
                            $return_after_transfer_bus_arr['date'] = $this->_get('transfer_return_date');
                            $this->set('return_after_transfer_bus_arr', $return_after_transfer_bus_arr);
                            
                            //direct price 
                            $direct_before_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($before_transfer_bus_id, $before_transfer_pickup_id, $before_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'F', $field_price, $is_transfer);
                            if(!empty($direct_before_transfer_ticket_price_arr)){
                                $ticket_price_arr[0] = $direct_before_transfer_ticket_price_arr;
                            }
                            $direct_after_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($after_transfer_bus_id, $after_transfer_pickup_id, $after_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'F', $field_price, $is_transfer);
                            if(!empty($direct_after_transfer_ticket_price_arr)){
                                $ticket_price_arr[1] = $direct_after_transfer_ticket_price_arr;
                            }
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);
                            $this->set('ticket_price_arr', $ticket_price_arr);

                            //return price 
                            $return_before_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($return_before_transfer_bus_id, $return_before_transfer_pickup_id, $return_before_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'T', $field_price, $is_transfer);
                            if(!empty($return_before_transfer_ticket_price_arr)){
                                $return_ticket_price_arr[0] = $return_before_transfer_ticket_price_arr ;
                            }
                            $return_after_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($return_after_transfer_bus_id, $return_after_transfer_pickup_id, $return_after_transfer_return_id, $booked_data, $this->option_arr, $this->locId, 'T', $field_price, $is_transfer);
                            if(!empty($return_after_transfer_ticket_price_arr)){
                                $return_ticket_price_arr[1] = $return_after_transfer_ticket_price_arr;
                            }
                            $return_ticket_price_total = pjUtil::formPriceArray($return_ticket_price_arr);
                            $this->set('return_price_arr', $return_ticket_price_total);
                            $this->set('return_ticket_price_arr', $return_ticket_price_arr);

                            //direct selected seats
                            $direct_before_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                            if(!empty($direct_before_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $direct_before_transfer_selected_seats_arr;
                            }
                            $direct_after_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                            if(!empty($direct_before_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $direct_after_transfer_selected_seats_arr;
                            }
                            $this->set('selected_seat_arr', $selected_seat_arr);

                            //return selected seats
                            $return_before_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['return_selected_seats'][$return_before_transfer_pickup_id.$return_before_transfer_return_id]);
                            if(!empty($return_before_transfer_selected_seats_arr)){
                                $return_selected_seat_arr[] = $return_before_transfer_selected_seats_arr;
                            }
                            $return_after_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['return_selected_seats'][$return_after_transfer_pickup_id.$return_after_transfer_return_id]);
                            if(!empty($return_before_transfer_selected_seats_arr)){
                                $return_selected_seat_arr[] = $return_after_transfer_selected_seats_arr;
                            }
                            $this->set('return_selected_seat_arr', $return_selected_seat_arr);                            
                        }else{//return way without transfer
                            $bus_id = $booked_data['bus_id'][$pickup_id.$return_id];
                            $pickup_arr = $pjBusLocationModel->getLocInfo($bus_id,$pickup_id);
                            $return_arr = $pjBusLocationModel->getLocInfo($bus_id,$return_id);
                            $bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($bus_id)
                                ->getData();
                            $bus_arr = pjUtil::formBusArray($bus_arr,$pickup_arr,$return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($pickup_id)
                                ->getData();
                            $return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_id)
                                ->getData();
                            $bus_arr['from_location'] = $pickup_location['name'];
                            $bus_arr['to_location'] = $return_location['name'];
                            $bus_arr['date'] = $this->_get('date');
                            $this->set('bus_arr', $bus_arr);
                            
                            //return bus
                            $return_bus_id = $booked_data['return_bus_id'][$return_id.$pickup_id];
                            $return_pickup_arr = $pjBusLocationModel->getLocInfo($return_bus_id,$return_id);
                            $return_return_arr = $pjBusLocationModel->getLocInfo($return_bus_id,$pickup_id);
                            $return_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($bus_id)
                                ->getData();
                            $return_bus_arr = pjUtil::formBusArray($return_bus_arr,$return_pickup_arr,$return_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $return_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_id)
                                ->getData();
                            $return_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($pickup_id)
                                ->getData();
                            $return_bus_arr['from_location'] = $return_pickup_location['name'];
                            $return_bus_arr['to_location'] = $return_return_location['name'];
                            $return_bus_arr['date'] = $this->_get('return_date');
                            $this->set('return_bus_arr', $return_bus_arr);                    
                            
                            //price
                            $direct_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->locId, 'F', $field_price, $is_transfer);
                            if(!empty($direct_ticket_price_arr)){
                                $ticket_price_arr[] = $direct_ticket_price_arr;
                                $this->set('ticket_price_arr', array(0 => $direct_ticket_price_arr));
                            }
                            $return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->locId, 'T', $field_price, $is_transfer);
                            if(!empty($return_ticket_price_arr)){
                                $ticket_price_arr[] = $return_ticket_price_arr;
                                $this->set('return_ticket_price_arr', array(0 => $return_ticket_price_arr));
                            }
                            
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);
                            
                            //selected seats
                            $direct_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$pickup_id.$return_id]);
                            if(!empty($direct_selected_seats_arr)){
                                $selected_seat_arr[] = $direct_selected_seats_arr;
                                $this->set('selected_seat_arr', array(0 => $direct_selected_seats_arr));
                            }
                            $return_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['return_selected_seats'][$return_id.$pickup_id]);
                            if(!empty($return_selected_seats_arr)){
                                $selected_seats_arr[] = $return_selected_seats_arr;
                                $this->set('return_selected_seat_arr', array(0 => $return_selected_seats_arr));
                            }                            
                        }
                    }else{
                        if(1 == $is_transfer){//direct bus with transfer
                            $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                            $before_transfer_return_id = $this->_get('before_transfer_return_id');
                            $before_transfer_bus_id = $booked_data['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id];
                            $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                            $after_transfer_return_id = $this->_get('after_transfer_return_id');
                            $after_transfer_bus_id = $booked_data['bus_id'][$before_transfer_return_id.$return_id];
                            
                            //direct bus before transfer
                            $before_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$before_transfer_pickup_id);
                            $before_transfer_return_arr = $pjBusLocationModel->getLocInfo($before_transfer_bus_id,$before_transfer_return_id);
                            
                            $before_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($before_transfer_bus_id)
                                ->getData();
                            $before_transfer_bus_arr = pjUtil::formBusArray($before_transfer_bus_arr,$before_transfer_pickup_arr,$before_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);

                            $before_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_pickup_id)
                                ->getData();
                            
                            $before_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($before_transfer_return_id)
                                ->getData();
                            $before_transfer_bus_arr['from_location'] = $before_transfer_pickup_location['name'];
                            $before_transfer_bus_arr['to_location'] = $before_transfer_return_location['name'];
                            $before_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('before_transfer_bus_arr', $before_transfer_bus_arr);
                            
                            //direct bus after transfer
                            $after_transfer_pickup_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_pickup_id);
                            $after_transfer_return_arr = $pjBusLocationModel->getLocInfo($after_transfer_bus_id,$after_transfer_return_id);
                            $after_transfer_bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($after_transfer_bus_id)
                                ->getData();
                            
                            $after_transfer_bus_arr = pjUtil::formBusArray($after_transfer_bus_arr,$after_transfer_pickup_arr,$after_transfer_return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            $after_transfer_pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_pickup_id)
                                ->getData();
                            
                            $after_transfer_return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($after_transfer_return_id)
                                ->getData();
                            $after_transfer_bus_arr['from_location'] = $after_transfer_pickup_location['name'];
                            $after_transfer_bus_arr['to_location'] = $after_transfer_return_location['name'];
                            $after_transfer_bus_arr['date'] = $this->_get('date');
                            $this->set('after_transfer_bus_arr', $after_transfer_bus_arr);
                            
                            //price
                            $before_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($before_transfer_bus_id, $before_transfer_pickup_id, $before_transfer_return_id, $booked_data, $this->option_arr, $this->locId, $is_return, $field_price, $is_transfer);
                            $after_transfer_ticket_price_arr = $pjPriceModel->getTicketPrice($after_transfer_bus_id, $after_transfer_pickup_id, $after_transfer_return_id, $booked_data, $this->option_arr, $this->locId, $is_return, $field_price, $is_transfer);
                            if(!empty($before_transfer_ticket_price_arr)){
                                $ticket_price_arr[] = $before_transfer_ticket_price_arr;
                            }
                            if(!empty($after_transfer_ticket_price_arr)){
                                $ticket_price_arr[] = $after_transfer_ticket_price_arr;
                            }
                            $this->set('ticket_price_arr', $ticket_price_arr);
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);

                            //selected seats
                            $before_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                            if(!empty($before_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $before_transfer_selected_seats_arr;
                            }
                            $after_transfer_selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                            if(!empty($after_transfer_selected_seats_arr)){
                                $selected_seat_arr[] = $after_transfer_selected_seats_arr;
                            }
                            $this->set('selected_seat_arr', $selected_seat_arr);
                        }else{
                            $bus_id = $booked_data['bus_id'][$pickup_id.$return_id];
                            $pickup_arr = $pjBusLocationModel->getLocInfo($bus_id,$pickup_id);
                            $return_arr = $pjBusLocationModel->getLocInfo($bus_id,$return_id);
                            $bus_arr = $pjBusModel
                                ->reset()
                                ->join('pjMultiLang', $this->busJoinStr, 'left outer')
                                ->select("t1.*, t2.content as route_title")
                                ->limit(1)
                                ->find($bus_id)
                                ->getData();
                            
                            $bus_arr = pjUtil::formBusArray($bus_arr,$pickup_arr,$return_arr,$timeFormat,$this->option_arr['o_time_format']);
                            
                            $pickup_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($pickup_id)
                                ->getData();
                            
                            $return_location = $pjCityModel
                                ->reset()
                                ->select('t1.*, t2.content as name')
                                ->join('pjMultiLang', $this->cityJoinStr, 'left outer')
                                ->limit(1)
                                ->find($return_id)
                                ->getData();
                            $bus_arr['from_location'] = $pickup_location['name'];
                            $bus_arr['to_location'] = $return_location['name'];
                            $bus_arr['date'] = $this->_get('date');
                            $this->set('bus_arr', $bus_arr);
                            
                            //ticket price
                            $price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->locId, $is_return, $field_price, $is_transfer);
                            if(!empty($price_arr)){
                                $ticket_price_arr[1] = $price_arr;
                            }
                            $this->set('ticket_price_arr', $ticket_price_arr);
                            $ticket_price_total = pjUtil::formPriceArray($ticket_price_arr);

                            //selected seats
                            $selected_seats_arr = $pjSeatModel->getSeatsInfo($booked_data['selected_seats'][$pickup_id.$return_id]);
                            if(!empty($selected_seats_arr)){
                                $selected_seat_arr[] = $selected_seats_arr;
                                $this->set('selected_seat_arr', $selected_seat_arr);
                            }
                        }
                    }

                    
                    $country_arr = array();
                    if(isset($_SESSION[$this->defaultForm]['c_country']) && !empty($_SESSION[$this->defaultForm]['c_country']))
                    {
                        $country_arr = pjCountryModel::factory()
                            ->select('t1.id, t2.content AS country_title')
                            ->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                            ->find($_SESSION[$this->defaultForm]['c_country'])
                            ->getData();
                    }
                    $all_country_arr = pjCountryModel::factory()
                        ->reset()
                        ->select('t1.id, t2.content AS country_title')
                        ->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                        ->orderBy('`country_title` ASC')
                        ->findAll()
                        ->getData();
                    
                    $this->set('ticket_price_total', $ticket_price_total);
                    $this->set('country_arr', $country_arr);
                    $this->set('all_country_arr', $all_country_arr);

                    $this->set('status', 'OK');
                }else{
                    $this->set('status', 'ERR');
                }
            }
	}
	

	public function pjActionGetPaymentForm()
	{
            if ($this->isXHR())
            {
                $arr = pjBookingModel::factory()
                ->select('t1.*')
                ->find($_GET['booking_id'])
                ->getData();

                if (!empty($arr['back_id'])) {
                    $back_arr = pjBookingModel::factory()
                        ->select('t1.*')
                        ->find($arr['back_id'])
                        ->getData();
                    $arr['deposit'] += $back_arr['deposit'];
                }
                switch ($arr['payment_method'])
                {
                    case 'paypal':
                        $this->set('params', array(
                            'name' => 'bsPaypal',
                            'id' => 'bsPaypal',
                            'business' => $this->option_arr['o_paypal_address'],
                            'item_name' => __('front_label_bus_schedule', true, false),
                            'custom' => $arr['id'],
                            'amount' => number_format($arr['deposit'], 2, '.', ''),
                            'currency_code' => $this->option_arr['o_currency'],
                            'return' => $this->option_arr['o_thank_you_page'],
                            'notify_url' => PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionConfirmPaypal',
                            'target' => '_self'
                        ));
                        break;
                    case 'authorize':
                        $this->set('params', array(
                            'name' => 'bsAuthorize',
                            'id' => 'bsAuthorize',
                            'target' => '_self',
                            'timezone' => $this->option_arr['o_timezone'],
                            'transkey' => $this->option_arr['o_authorize_transkey'],
                            'x_login' => $this->option_arr['o_authorize_merchant_id'],
                            'x_description' => __('front_label_bus_schedule', true, false),
                            'x_amount' => number_format($arr['deposit'], 2, '.', ''),
                            'x_invoice_num' => $arr['id'],
                            'x_receipt_link_url' => $this->option_arr['o_thank_you_page'],
                            'x_relay_url' => PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionConfirmAuthorize'
                        ));
                        break;
                }

                $this->set('arr', $arr);
                $this->set('get', $_GET);
            }
	}
	
}
?>