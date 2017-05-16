<?php
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
                $locId = $this->getLocaleId();

                $from_location_arr = $pjCityModel
                    ->reset()
                    ->select('t1.*, t2.content as name')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$locId."'", 'left outer')
                    ->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModelTable."` AS TRD)")
                    ->where("t1.id IN(SELECT TRC.city_id FROM `".$pjRouteCityModelTable."` AS TRC WHERE TRC.is_departure=1)")
                    ->orderBy("t2.content ASC")
                    ->findAll()
                    ->getData();
                
                $to_location_arr = $pjCityModel
                    ->reset()
                    ->select('t1.*, t2.content as name')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$locId."'", 'left outer')
                    ->where("t1.id IN(SELECT TRC.city_id FROM `".$pjRouteCityModelTable."` AS TRC WHERE TRC.is_arrival=1)")
                    ->orderBy("t2.content ASC")
                    ->findAll()
                    ->getData();

                if($this->_is('pickup_id'))
                {
                    $pickup_id = $this->_get('pickup_id');
                    $return_location_arr = $pjCityModel->getDestCities($pickup_id,$locId,',');
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
                    ->where('t1.locale', $locId)
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
                    $booking_period = array();
                    if($this->_is('booking_period'))
                    {
                        $booking_period = $this->_get('booking_period');
                    }
                    $booked_data = array();
                    if($this->_is('booked_data'))
                    {
                        $booked_data = $this->_get('booked_data');
                    }

                    $is_transfer = $this->_get('is_transfer');
                    
                    if($this->_is('bus_id_arr'))
                    {
                        $bus_id_arr = $this->_get('bus_id_arr');
                        $pickup_id = $this->_get('pickup_id');
                        $return_id = $this->_get('return_id');
                        $date = $this->_get('date');

                        if ($is_transfer == 1) {
                            $pickup_id = $this->_get('before_transfer_pickup_id');
                            $return_id = $this->_get('before_transfer_return_id');
                            
                            prt($is_transfer);

                        }
                        $direct_bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'F');
                        if(isset($direct_bus_list['bus_arr'][0]['seats_available'])){//of seats are available
                            //$minSeats = $bus_list['bus_arr'][0]['seats_available'];
                            $minSeats = getMinQuant($direct_bus_list['bus_arr'],'seats_available');
                            $this->set('direct_min_seats',$minSeats);
                        }
                        $booking_period = $direct_bus_list['booking_period'];
                        
                        $this->_set('booking_period', $booking_period);
                        $this->set('bus_type_arr', $direct_bus_list['bus_type_arr']);
                        $this->set('booked_seat_arr', $direct_bus_list['booked_seat_arr']);
                        $this->set('seat_arr', $direct_bus_list['seat_arr']);
                        $this->set('selected_seat_arr', $direct_bus_list['selected_seat_arr']);
                        $this->set('bus_arr', $direct_bus_list['bus_arr']);
                        $this->set('ticket_columns', $direct_bus_list['ticket_columns']);
                        $this->set('from_location', $direct_bus_list['from_location']);
                        $this->set('to_location', $direct_bus_list['to_location']);
                    }
                    
                    if($this->_is('return_bus_id_arr'))//return or transfer bus
                    {
                        $bus_id_arr = $this->_get('return_bus_id_arr');
                        $pickup_id = $this->_get('return_id');
                        $return_id = $this->_get('pickup_id');
                        $date = $this->_get('return_date');

                        if ($is_transfer == 1) {
                            $pickup_id = $this->_get('after_transfer_pickup_id');
                            $return_id = $this->_get('after_transfer_return_id');
                        }
                        
                        $field_price = $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]];	
                        $totalTicketPriceArr = getTotTicketPrice($direct_bus_list,$field_price);                        
                        $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'T');				
                        $booking_period = $bus_list['booking_period'];
                        
                        $this->set('total_ticket_price_arr', getTotTicketPrice($bus_list,$field_price,$totalTicketPriceArr));
                        $this->_set('booking_period', $booking_period);
                        $this->set('return_bus_type_arr', $bus_list['bus_type_arr']);
                        $this->set('booked_return_seat_arr', $bus_list['booked_seat_arr']);
                        $this->set('return_seat_arr', $bus_list['seat_arr']);
                        $this->set('return_selected_seat_arr', $bus_list['selected_seat_arr']);
                        $this->set('return_bus_arr', $bus_list['bus_arr']);
                        $this->set('return_ticket_columns', $bus_list['ticket_columns']);
                        $this->set('return_from_location', $bus_list['from_location']);
                        $this->set('return_to_location', $bus_list['to_location']);
                        
                        if(isset($bus_list['bus_arr'][0]['seats_available'])){//of seats are available
                            $returnMinSeats = getMinQuant($bus_list['bus_arr'],'seats_available');
                       
                            if((0<$minSeats) && (0<$returnMinSeats)){
                                $showCommonTicketsPanel = true;
                            }else{
                                $showCommonTicketsPanel = false;
                            }

                            $this->set('common_ticket_panel', $showCommonTicketsPanel);
                            $minSeats = ($returnMinSeats < $minSeats) ? $returnMinSeats : $minSeats;
                        }
                    }

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
				$bus_id = $booked_data['bus_id'];
				$departure_time = NULL;
				$_departure_time = NULL;
				$arrival_time = NULL;
				$_arrival_time = NULL;
				$duration = NULL;
				$_duration = NULL;
				
				if ($is_transfer == 1) {
					$before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
					$before_transfer_return_id = $this->_get('before_transfer_return_id');
					$after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
					$after_transfer_return_id = $this->_get('after_transfer_return_id');
				}
				
				$pjBusLocationModel = pjBusLocationModel::factory();
				$pickup_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
				if ($is_transfer == 1) {
					$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $before_transfer_return_id)->limit(1)->findAll()->getData();
				} else {
					$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
				}
	
				if(!empty($pickup_arr))
				{
					$departure_time = pjUtil::formatTime($pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($return_arr))
				{
					$arrival_time = pjUtil::formatTime($return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($pickup_arr) && !empty($return_arr))
				{
					$duration_arr = pjUtil::calDuration($pickup_arr[0]['departure_time'], $return_arr[0]['arrival_time']);
						
					$hour_str = $duration_arr['hours'] . ' ' . ($duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
					$minute_str = $duration_arr['minutes'] > 0 ? ($duration_arr['minutes'] . ' ' . ($duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
					$duration = $hour_str . ' ' . $minute_str;
				}
	
				$pjCityModel = pjCityModel::factory();
				if ($is_transfer == 1) {
					$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($before_transfer_pickup_id)->getData();
					$return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($before_transfer_return_id)->getData();
				} else {
					$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
					$return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
				}
				$from_location = $pickup_location['name'];
				$to_location = $return_location['name'];
	
				$pjBusModel= pjBusModel::factory();
				$bus_arr = $pjBusModel
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select("t1.*, t2.content as route_title")
					->find($bus_id)
					->getData();
				$bus_arr['departure_time'] = $departure_time;
				$bus_arr['arrival_time'] = $arrival_time;
				$bus_arr['duration'] = $duration;
	
				$pjPriceModel = pjPriceModel::factory();
	
				$field_price = 'price';
				if (isset($this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]])) {
					$field_price = $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]];
				}
				if ($is_transfer == 1) {
					$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $before_transfer_pickup_id, $before_transfer_return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F', $field_price, $is_transfer);
				} else {
					$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F', $field_price, $is_transfer);
				}
	
				$this->set('from_location', $from_location);
				$this->set('to_location', $to_location);
				$this->set('bus_arr', $bus_arr);
				$this->set('ticket_arr', $ticket_price_arr['ticket_arr']);
				$this->set('price_arr', $ticket_price_arr);
				if ($is_return == "T")
				{
					$return_bus_id = $booked_data['return_bus_id'];
					if ($is_transfer == 1) {
						$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $after_transfer_pickup_id, $after_transfer_return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T', $field_price, $is_transfer);
					} else {
						$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T', $field_price, $is_transfer);
					}
						
					$this->set('return_ticket_arr', $return_ticket_price_arr['ticket_arr']);
					$this->set('return_price_arr', $return_ticket_price_arr);
	
					$_bus_arr = $pjBusModel
						->reset()
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->select("t1.*, t2.content as route_title")
						->find($return_bus_id)
						->getData();
	
					if ($is_transfer == 1) {
						$_pickup_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $after_transfer_pickup_id)->limit(1)->findAll()->getData();
						$_return_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $after_transfer_return_id)->limit(1)->findAll()->getData();
					} else {
						$_pickup_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
						$_return_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
					}
	
					if(!empty($_pickup_arr))
					{
						$_departure_time = pjUtil::formatTime($_pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_return_arr))
					{
						$_arrival_time = pjUtil::formatTime($_return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_pickup_arr) && !empty($_return_arr))
					{
						$_duration_arr = pjUtil::calDuration($_pickup_arr[0]['departure_time'], $_return_arr[0]['arrival_time']);
	
						$_hour_str = $_duration_arr['hours'] . ' ' . ($_duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
						$_minute_str = $_duration_arr['minutes'] > 0 ? ($_duration_arr['minutes'] . ' ' . ($_duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
						$_duration = $_hour_str . ' ' . $_minute_str;
					}
	
					$_bus_arr['departure_time'] = $_departure_time;
					$_bus_arr['arrival_time'] = $_arrival_time;
					$_bus_arr['duration'] = $_duration;
	
					if ($is_transfer == 1) {
						$_pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($after_transfer_pickup_id)->getData();
						$_return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($after_transfer_return_id)->getData();
					} else {
						$_pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
						$_return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
					}
					$_from_location = $_pickup_location['name'];
					$_to_location = $_return_location['name'];
	
					$this->set('is_return', $is_return);
					$this->set('return_from_location', $_from_location);
					$this->set('return_to_location', $_to_location);
					$this->set('return_bus_arr', $_bus_arr);
				}
	
				$country_arr = pjCountryModel::factory()
					->select('t1.id, t2.content AS country_title')
					->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->orderBy('`country_title` ASC')->findAll()->getData();
	
				$terms_conditions = pjMultiLangModel::factory()->select('t1.*')
					->where('t1.model','pjOption')
					->where('t1.locale', $this->getLocaleId())
					->where('t1.field', 'o_terms')
					->limit(0, 1)
					->findAll()->getData();
	
				$pjSeatModel = pjSeatModel::factory();
		
				$selected_seat_arr = $pjSeatModel->whereIn('t1.id', explode("|", $booked_data['selected_seats']))->findAll()->getDataPair('id', 'name');
				$return_selected_seat_arr = (isset($booked_data['return_selected_seats']) && !empty($booked_data['return_selected_seats'])) ? $pjSeatModel->reset()->whereIn('t1.id', explode("|", $booked_data['return_selected_seats']))->findAll()->getDataPair('id', 'name') : array();
				
				$this->set('selected_seat_arr', $selected_seat_arr);
				$this->set('return_selected_seat_arr', $return_selected_seat_arr);
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
				$departure_time = NULL;
				$arrival_time = NULL;
				$duration = NULL;
				$_departure_time = NULL;
				$_arrival_time = NULL;
				$_duration = NULL;
				
				if ($is_transfer == 1) {
					$before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
					$before_transfer_return_id = $this->_get('before_transfer_return_id');
					$after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
					$after_transfer_return_id = $this->_get('after_transfer_return_id');
				}
	
				$pjBusLocationModel = pjBusLocationModel::factory();
				$pickup_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
				if ($is_transfer == 1) {
					$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $before_transfer_return_id)->limit(1)->findAll()->getData();
				} else {
					$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
				}
	
				if(!empty($pickup_arr))
				{
					$departure_time = pjUtil::formatTime($pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($return_arr))
				{
					$arrival_time = pjUtil::formatTime($return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($pickup_arr) && !empty($return_arr))
				{
					$duration_arr = pjUtil::calDuration($pickup_arr[0]['departure_time'], $return_arr[0]['arrival_time']);
						
					$hour_str = $duration_arr['hours'] . ' ' . ($duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
					$minute_str = $duration_arr['minutes'] > 0 ? ($duration_arr['minutes'] . ' ' . ($duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
					$duration = $hour_str . ' ' . $minute_str;
				}
	
				$pjCityModel = pjCityModel::factory();
				if ($is_transfer == 1) {
					$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($before_transfer_pickup_id)->getData();
					$return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($before_transfer_return_id)->getData();
				} else {
					$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
					$return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
				}
				$from_location = $pickup_location['name'];
				$to_location = $return_location['name'];
	
				$pjBusModel = pjBusModel::factory();
				$bus_arr = $pjBusModel
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select("t1.*, t2.content as route_title")
					->find($bus_id)
					->getData();
				$bus_arr['departure_time'] = $departure_time;
				$bus_arr['arrival_time'] = $arrival_time;
				$bus_arr['duration'] = $duration;
	
				$field_price = 'price';
				if (isset($this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]])) {
					$field_price = $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]];
				}
				
				$pjPriceModel = pjPriceModel::factory();
				if ($is_transfer == 1) {
					$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $before_transfer_pickup_id, $before_transfer_return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F', $field_price, $is_transfer);
				} else {
					$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F', $field_price, $is_transfer);
				}
	
				$this->set('from_location', $from_location);
				$this->set('to_location', $to_location);
				$this->set('bus_arr', $bus_arr);
				$this->set('ticket_arr', $ticket_price_arr['ticket_arr']);
				$this->set('price_arr', $ticket_price_arr);
	
				if ($is_return == "T")
				{
					$return_bus_id = $booked_data['return_bus_id'];
					if ($is_transfer == 1) {
						$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $after_transfer_pickup_id, $after_transfer_return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T', $field_price, $is_transfer);
					} else {
						$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T', $field_price, $is_transfer);
					}
						
					$this->set('return_ticket_arr', $return_ticket_price_arr['ticket_arr']);
					$this->set('return_price_arr', $return_ticket_price_arr);
	
					$_bus_arr = $pjBusModel
						->reset()
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->select("t1.*, t2.content as route_title")
						->find($return_bus_id)
						->getData();
					if ($is_transfer == 1) {
						$_pickup_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $after_transfer_pickup_id)->limit(1)->findAll()->getData();
						$_return_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $after_transfer_return_id)->limit(1)->findAll()->getData();
					} else {
						$_pickup_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
						$_return_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
					}
					if(!empty($_pickup_arr))
					{
						$_departure_time = pjUtil::formatTime($_pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_return_arr))
					{
						$_arrival_time = pjUtil::formatTime($_return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_pickup_arr) && !empty($_return_arr))
					{
						$_duration_arr = pjUtil::calDuration($_pickup_arr[0]['departure_time'], $return_arr[0]['arrival_time']);
	
						$hour_str = $_duration_arr['hours'] . ' ' . ($_duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
						$minute_str = $_duration_arr['minutes'] > 0 ? ($duration_arr['minutes'] . ' ' . ($_duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
						$_duration = $hour_str . ' ' . $minute_str;
					}
	
					$_bus_arr['departure_time'] = $_departure_time;
					$_bus_arr['arrival_time'] = $_arrival_time;
					$_bus_arr['duration'] = $_duration;
	
					if ($is_transfer == 1) {
						$_pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($after_transfer_pickup_id)->getData();
						$_return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($after_transfer_return_id)->getData();
					} else {
						$_pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
						$_return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
					}
					$_from_location = $_pickup_location['name'];
					$_to_location = $_return_location['name'];
	
					$this->set('is_return', $is_return);
					$this->set('return_from_location', $_from_location);
					$this->set('return_to_location', $_to_location);
					$this->set('return_bus_arr', $_bus_arr);
				}
	
				$country_arr = array();
				if(isset($_SESSION[$this->defaultForm]['c_country']) && !empty($_SESSION[$this->defaultForm]['c_country']))
				{
					$country_arr = pjCountryModel::factory()
						->select('t1.id, t2.content AS country_title')
						->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->find($_SESSION[$this->defaultForm]['c_country'])->getData();
				}
				$pjSeatModel = pjSeatModel::factory();
					
				$selected_seat_arr = $pjSeatModel->whereIn('t1.id', explode("|", $booked_data['selected_seats']))->findAll()->getDataPair('id', 'name');
				$return_selected_seat_arr = (isset($booked_data['return_selected_seats']) && !empty($booked_data['return_selected_seats'])) ? $pjSeatModel->reset()->whereIn('t1.id', explode("|", $booked_data['return_selected_seats']))->findAll()->getDataPair('id', 'name') : array();
				
				$this->set('selected_seat_arr', $selected_seat_arr);
				$this->set('return_selected_seat_arr', $return_selected_seat_arr);
				$this->set('country_arr', $country_arr);
				
				$all_country_arr = pjCountryModel::factory()->reset()
					->select('t1.id, t2.content AS country_title')
					->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->orderBy('`country_title` ASC')->findAll()->getData();
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
			->find($_GET['booking_id'])->getData();
	
			if (!empty($arr['back_id'])) {
				$back_arr = pjBookingModel::factory()
					->select('t1.*')
					->find($arr['back_id'])->getData();
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