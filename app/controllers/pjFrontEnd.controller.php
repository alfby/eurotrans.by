<?php
if (!defined("ROOT_PATH"))
{
    header("HTTP/1.1 403 Forbidden");
    exit;
}
class pjFrontEnd extends pjFront
{
    public $locId,
           $formFromToLoc = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->locId = $this->getLocaleId();
        $this->setAjax(true);
        $this->setLayout('pjActionEmpty');
    }

    public function pjActionLoad()
    {
        $this->setAjax(false);
        $this->setLayout('pjActionFront');
        ob_start();
        header("Content-Type: text/javascript; charset=utf-8");
    }

    public function pjActionLoadCss()
    {
        $dm = new pjDependencyManager(PJ_THIRD_PARTY_PATH);
        $dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();

        $theme = $this->option_arr['o_theme'];
        $fonts = $this->option_arr['o_layout'];
        if(isset($_GET['theme']) && in_array($_GET['theme'], array('theme1','theme2','theme3','theme4','theme5','theme6','theme7','theme8','theme9','theme10')))
        {
            $theme = $_GET['theme'];
            $fonts = $_GET['theme'];
        }

        $arr = array(
            array('file' => "$fonts.css", 'path' => PJ_CSS_PATH . "fonts/"),
            array('file' => 'font-awesome.min.css', 'path' => $dm->getPath('font_awesome')),
            array('file' => 'perfect-scrollbar.min.css', 'path' => $dm->getPath('pj_perfect_scrollbar')),
            array('file' => 'select2.min.css', 'path' => $dm->getPath('pj_select2')),
            array('file' => 'bootstrap-datetimepicker.min.css', 'path' => $dm->getPath('pj_bootstrap_datetimepicker')),
            array('file' => "intlTelInput.css", 'path' => PJ_CSS_PATH),
            array('file' => "style.css", 'path' => PJ_CSS_PATH),
            array('file' => "$theme.css", 'path' => PJ_CSS_PATH . "themes/"),
            array('file' => 'transitions.css', 'path' => PJ_CSS_PATH)
        );

        header("Content-Type: text/css; charset=utf-8");
        foreach ($arr as $item)
        {
                $string = FALSE;
                if ($stream = fopen($item['path'] . $item['file'], 'rb'))
                {
                        $string = stream_get_contents($stream);
                        fclose($stream);
                }

                if ($string !== FALSE)
                {
                        echo str_replace(
                                array('../fonts/fontawesome', 'pjWrapper', '[URL]'),
                                array(
                                        PJ_INSTALL_URL . $dm->getPath('font_awesome') . 'fonts/fontawesome',
                                        "pjWrapperBusReservation_" . $theme, PJ_INSTALL_URL),
                                $string
                        ) . "\n";
                }
        }
        exit;
    }

    public function pjActionCaptcha()
    {
        $this->setAjax(true);
        $Captcha = new pjCaptcha('app/web/obj/Anorexia.ttf', $this->defaultCaptcha, 6);
        $Captcha->setImage('app/web/img/button.png')->init(isset($_GET['rand']) ? $_GET['rand'] : null);
    }

    public function pjActionCheckCaptcha()
    {
        $this->setAjax(true);
        if (!isset($_GET['captcha']) || empty($_GET['captcha']) || strtoupper($_GET['captcha']) != $_SESSION[$this->defaultCaptcha]){
            echo 'false';
        }else{
            echo 'true';
        }
        exit;
    }

    public function pjActionCheck()
    {
        if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
        {
            $resp = array();
            $return_bus_id_arr = array();

            if($_GET['pickup_id'] !== $_GET['return_id'])
            {
                $resp['code'] = 200;
                $this->_set('not_available_bus',false);
                $this->_set('return_not_available_bus',false);
                $pjBusModel = pjBusModel::factory();
                $pickup_id = $_GET['pickup_id'];
                $return_id = $_GET['return_id'];
                $this->_set('direct_before_transfer_avsilable',true);
                $is_return = (isset($_GET['is_return'])) ? $_GET['is_return'] : false;

                $is_transfer = $before_transfer_pickup_id = $before_transfer_return_id = $after_transfer_pickup_id = $after_transfer_return_id = 0;
                $return_before_transfer_pickup_id = $return_before_transfer_return_id = $rerurn_after_transfer_pickup_id = $rerurn_after_transfer_return_id = 0;
                $bus_id_before_transfer_arr = $bus_id_after_transfer_arr = $return_bus_id_before_transfer_arr = $return_bus_id_after_transfer_arr = array();
                $booking_period = ($this->_is('booking_period')) ? $this->_get('booking_period') : array();
                $booked_data = ($this->_is('booked_data')) ? $this->_get('booked_data') : array();
                $date = isset($_GET['date']) ? pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']) : false;
                $return_date = isset($_GET['return_date']) ? pjUtil::formatDate($_GET['return_date'], $this->option_arr['o_date_format']) : false;
                $pjCityModel = pjCityModel::factory();
                $pjRouteDetailModel = pjRouteDetailModel::factory();
                $pjRouteCityModel = pjRouteCityModel::factory();

                if(isset($_GET['final_check'])) {
                    $date = pjUtil::formatDate($this->_get('date'), $this->option_arr['o_date_format']);
                }

                $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id, false);

                //check if a direct bus is available
                $isDirectBusAvailable = $this->checkIsBusAvailable($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $_GET['date'], 'F');
                if(false === $isDirectBusAvailable)//has no direct bus
                {
                    $location_arr = $pjCityModel
                        ->select('t1.*, t2.content as name')
                        ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                        ->where("t1.id IN(
                            SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD 
                            INNER JOIN `".$pjRouteCityModel->getTable()."` AS TRC 
                            ON TRC.route_id=TRD.route_id 
                            WHERE TRD.from_location_id=".(int)$pickup_id." AND TRC.is_arrival=1 AND TRC.city_id=TRD.to_location_id)")
                        ->orderBy("t2.content ASC")
                        ->findAll()
                        ->getData();

                    $bus_before_transfer = $bus_after_transfer = array();
                    if ($location_arr) {//if has transfer
                        $is_transfer = 1;
                        $before_transfer_pickup_id = $pickup_id;
                        foreach ($location_arr as $location) {
                            $before_transfer_return_id = $location['id'];
                            $bus_id_before_transfer_arr = $pjBusModel->getBusIds($date, $before_transfer_pickup_id, $before_transfer_return_id, true);
                            if ($bus_id_before_transfer_arr) {
                                $after_transfer_pickup_id = $before_transfer_return_id;
                                $after_transfer_return_id = $return_id;
                                $bus_id_after_transfer_arr = $pjBusModel->getBusIds($date, $after_transfer_pickup_id, $after_transfer_return_id, true);

                                $isAvailable = $this->checkIsBusAvailable($before_transfer_pickup_id, $before_transfer_return_id, $bus_id_before_transfer_arr, $booking_period, $booked_data, $_GET['date'], 'F');
                                if(false === $isAvailable){
                                    if('T' == $is_return){
                                        $resp['code'] = 100;
                                        pjAppController::jsonResponse($resp);
                                    }else{
                                        $this->_set('direct_before_transfer_avsilable',false);
                                        $is_transfer=0;
                                        $this->_set('not_available_bus', $this->formFromToLoc($before_transfer_pickup_id,$before_transfer_return_id)->getFromToLoc());
                                        $_GET['pickup_id'] = $before_transfer_return_id;
                                        $_GET['return_id'] = $return_id;
                                        $bus_id_arr = $bus_id_after_transfer_arr;    
                                    }
                                }

                                $isAvailable = $this->checkIsBusAvailable($after_transfer_pickup_id, $after_transfer_return_id, $bus_id_after_transfer_arr, $booking_period, $booked_data, $_GET['date'], 'F');
                                if(false === $isAvailable){//there is no available after transfer bus
                                    $resp['code'] = 100;
                                    pjAppController::jsonResponse($resp);
                                }
                            }
                        }
                    }else{
                        $resp['code'] = 100;
                        pjAppController::jsonResponse($resp);
                    }

                    if ((1 == $is_transfer) || (!isset($_GET['is_return']) || (isset($_GET['is_return']) && $_GET['is_return'] == 'F'))) {
                        if (!$bus_id_before_transfer_arr || !$bus_id_after_transfer_arr) {
                            $resp['code'] = 100;
                            if(!isset($_GET['final_check']))
                            {
                                if($this->_is('bus_id_arr'))
                                {
                                    unset($_SESSION[$this->defaultStore]['bus_id_arr']);
                                }

                                if($this->_is('return_bus_id_arr'))
                                {
                                    unset($_SESSION[$this->defaultStore]['return_bus_id_arr']);
                                }
                            }
                            $this->_set('date', $_GET['date']);
                            pjAppController::jsonResponse($resp);
                        } else {
                            //$bus_id_arr = $bus_id_before_transfer_arr;
                        }
                    } else {
                        $resp['dest_cities'] = $dest_cities;
                        $resp['code'] = 100;
                        if(!isset($_GET['final_check']))
                        {
                            if($this->_is('bus_id_arr'))
                            {
                                unset($_SESSION[$this->defaultStore]['bus_id_arr']);
                            }
                        }
                        pjAppController::jsonResponse($resp);
                    }
                }

                if ($is_transfer == 1 && $bus_id_after_transfer_arr) {
                    $return_bus_id_arr = $bus_id_after_transfer_arr;
                }

                if (('T' == $is_return) && (0 == $is_transfer))
                {
                    $pickup_id = $_GET['return_id'];
                    $return_id = $_GET['pickup_id'];

                    $this->_set('return_before_transfer_pickup_id',$pickup_id);
                    $this->_set('return_before_transfer_return_id',$return_id);

                    $date = pjUtil::formatDate($_GET['return_date'], $this->option_arr['o_date_format']);
                    $return_bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
                    if(empty($return_bus_id_arr)){
                        $resp['code'] = 101;

                        if(!isset($_GET['final_check']))
                        {
                            if($this->_is('return_bus_id_arr'))
                            {
                                unset($_SESSION[$this->defaultStore]['return_bus_id_arr']);
                            }
                        }
                        pjAppController::jsonResponse($resp);
                    }
                }else{
                    if(('T' == $is_return) && (1 == $is_transfer)){
                        $return_before_transfer_pickup_id = $after_transfer_return_id;
                        $return_before_transfer_return_id = $after_transfer_pickup_id;

                        $bus_id_return_before_transfer_arr = $pjBusModel->getBusIds($return_date, $return_before_transfer_pickup_id, $return_before_transfer_return_id, true);
                        if($bus_id_return_before_transfer_arr){
                            $isAvailable = $this->checkIsBusAvailable($return_before_transfer_pickup_id, $return_before_transfer_return_id, $bus_id_return_before_transfer_arr, $booking_period, $booked_data, $_GET['return_date'], 'F');
                            if($isAvailable){
                                $this->_set('return_before_transfer_pickup_id',$return_before_transfer_pickup_id);
                                $this->_set('return_before_transfer_return_id',$return_before_transfer_return_id);
                                $this->_set('bus_id_return_before_transfer_arr', $bus_id_return_before_transfer_arr);
                            }
                        }

                        $return_after_transfer_pickup_id = $before_transfer_return_id;
                        $return_after_transfer_return_id = $before_transfer_pickup_id;
                        $bus_id_return_after_transfer_arr = $pjBusModel->getBusIds($return_date, $return_after_transfer_pickup_id, $return_after_transfer_return_id, true);
                        if($bus_id_return_after_transfer_arr){
                            $isAvailable = $this->checkIsBusAvailable($return_after_transfer_pickup_id, $return_after_transfer_return_id, $bus_id_return_after_transfer_arr, $booking_period,  $booked_data, $_GET['return_date'], 'F');
                            if($isAvailable){
                                $this->_set('return_after_transfer_pickup_id',$return_after_transfer_pickup_id);
                                $this->_set('return_after_transfer_return_id',$return_after_transfer_return_id);
                                $this->_set('bus_id_return_after_transfer_arr', $bus_id_return_after_transfer_arr);
                            }
                        }
                        $this->_set('transfer_return_date', $_GET['return_date']);
                    }
                    if(!isset($_GET['final_check']))
                    {
                        if($this->_is('return_bus_id_arr'))
                        {
                            unset($_SESSION[$this->defaultStore]['return_bus_id_arr']);
                        }
                        if($this->_is('return_date'))
                        {
                            unset($_SESSION[$this->defaultStore]['return_date']);
                        }
                    }
                }

                if(!isset($_GET['final_check']))
                {
                    $this->_set('pickup_id', $_GET['pickup_id']);
                    $this->_set('return_id', $_GET['return_id']);
                    $this->_set('bus_id_arr', $bus_id_arr);
                    $this->_set('is_return', $_GET['is_return']);
                    $this->_set('date', $_GET['date']);
                    $this->_set('is_transfer', $is_transfer);
                    $this->_set('before_transfer_pickup_id', $before_transfer_pickup_id);
                    $this->_set('before_transfer_return_id', $before_transfer_return_id);
                    $this->_set('after_transfer_pickup_id', $after_transfer_pickup_id);
                    $this->_set('after_transfer_return_id', $after_transfer_return_id);
                    $this->_set('bus_id_before_transfer_arr', $bus_id_before_transfer_arr);
                    $this->_set('bus_id_after_transfer_arr', $bus_id_after_transfer_arr);

                    if('T' == $is_return){
                        $this->_set('return_date', $_GET['return_date']);
                    }
                    if (1 == $is_transfer) {
                        $this->_set('return_bus_id_arr', $return_bus_id_arr);
                    } else {
                        if ('T' == $is_return)
                        {
                            $this->_set('return_bus_id_arr', $return_bus_id_arr);
                            
                            $isAvailable = $this->checkIsBusAvailable($pickup_id, $return_id, $return_bus_id_arr, $booking_period, $booked_data, $_GET['return_date'], 'F');
                            if(false === $isAvailable){
                                $this->_set('is_return','F');
                                $this->_set('return_not_available_bus', $this->formFromToLoc($pickup_id,$return_id)->getFromToLoc());
                            }
                        }
                    }
                    if($this->_is('booked_data'))
                    {
                        unset($_SESSION[$this->defaultStore]['booked_data']);
                    }
                    if($this->_is('bus_id'))
                    {
                        unset($_SESSION[$this->defaultStore]['bus_id']);
                    }
                    $resp['code'] = 200;
                    pjAppController::jsonResponse($resp);
                }else{
                    $STORE = @$_SESSION[$this->defaultStore];
                    if (1 == $is_transfer) {
                        $is_valid = true;
                        $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                        $before_transfer_return_id = $this->_get('before_transfer_return_id');
                        $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                        $after_transfer_return_id = $this->_get('after_transfer_return_id');

                        $STORE['pickup_id'] = $before_transfer_pickup_id;
                        $STORE['return_id'] = $before_transfer_return_id;
                        $avail_arr = $this->getBusAvailability($STORE['booked_data']['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id], $STORE, $this->option_arr);
                        $booked_seat_arr = $avail_arr['booked_seat_arr'];
                        $seat_id_arr = explode("|", $STORE['booked_data']['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                        $intersect = array_intersect($booked_seat_arr, $seat_id_arr);
                        if(!empty($intersect))
                        {
                            $is_valid = false;
                        }

                        if (1 == $is_return) {

                        }else {
                            if ($is_valid) {
                                $STORE['pickup_id'] = $after_transfer_pickup_id;
                                $STORE['return_id'] = $after_transfer_return_id;
                                $avail_arr = $this->getBusAvailability($STORE['booked_data']['bus_id'][$after_transfer_pickup_id.$after_transfer_return_id], $STORE, $this->option_arr);
                                $booked_seat_arr = $avail_arr['booked_seat_arr'];
                                $seat_id_arr = explode("|", $STORE['booked_data']['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                                $intersect = array_intersect($booked_seat_arr, $seat_id_arr);
                                if(!empty($intersect))
                                {
                                    $is_valid = false;
                                }
                            }

                        }

                        $resp['code'] = ($is_valid) ? 200 : 100;
                    } else {
                        $avail_arr = $this->getBusAvailability($STORE['booked_data']['bus_id'][$pickup_id.$return_id], $STORE, $this->option_arr);
                        $booked_seat_arr = $avail_arr['booked_seat_arr'];
                        $seat_id_arr = explode("|", $STORE['booked_data']['selected_seats'][$pickup_id.$return_id]);
                        $intersect = array_intersect($booked_seat_arr, $seat_id_arr);
                        $resp['code'] = (!empty($intersect))? 100 : 200;
                    }
                    pjAppController::jsonResponse($resp);
                }
            }
            pjAppController::jsonResponse($resp);
        }
    }

    public function pjActionSaveTickets()
    {
        $this->setAjax(true);
        $resp['code'] = 200;
        $this->_set('booked_data', $_POST);
        $resp['POST'] = $_POST;
        pjAppController::jsonResponse($resp);
    }

    public function pjActionSaveForm()
    {
        $this->setAjax(true);
        if ($this->isXHR())
        {
            if (!isset($_SESSION[$this->defaultForm]) || count($_SESSION[$this->defaultForm]) === 0)
            {
                $_SESSION[$this->defaultForm] = array();
            }
            if(isset($_POST['step_checkout'])){
                $_SESSION[$this->defaultForm] = $_POST;
            }

            $resp = array('code' => 200);
            pjAppController::jsonResponse($resp);
        }
    }

    public function pjActionSaveBooking()
    {
        $this->setAjax(true);

        if ($this->isXHR ()) {
            $STORE = @$_SESSION [$this->defaultStore];
            $FORM = @$_SESSION [$this->defaultForm];
            $booked_data = @$STORE ['booked_data'];
            $pickup_id = $this->_get('pickup_id');
            $return_id = $this->_get('return_id');
            $is_return = $data['is_return'] = $this->_get('is_return');
            $is_transfer = $data['is_transfer'] = $this->_get('is_transfer');
            $field_price = isset($this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]]) 
                ? $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]] 
                : 'price';

            $data = array();
            $payment = 'none';

            $pjPriceModel = pjPriceModel::factory();
            $pjBookingModel = pjBookingModel::factory();
            $pjRouteCityModel = pjRouteCityModel::factory();
            $pjBookingSeatModel = pjBookingSeatModel::factory();
            $pjBookingTicketModel = pjBookingTicketModel::factory();
            $pjBookingPaymentModel = pjBookingPaymentModel::factory ();
            $pjBookingPassengerModel = pjBookingPassengerModel::factory();

            if('T' == $is_return) {
                if(1 == $is_transfer) {
                    $_is_return = 'F';
                    $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                    $before_transfer_return_id = $this->_get('before_transfer_return_id');
                    $before_transfer_bus_id = $booked_data['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id];
                    $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                    $after_transfer_return_id = $this->_get('after_transfer_return_id');
                    $after_transfer_bus_id = $booked_data['bus_id'][$after_transfer_pickup_id.$after_transfer_return_id];

                    $before_transfer_bus_arr = pjBusModel::factory()
                        ->reset()
                        ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                        ->join('pjBusType','t3.id=t1.bus_type_id','left')
                        ->select('t1.*, t3.seats_map, t2.content as route')
                        ->limit(1)
                        ->find($before_transfer_bus_id)
                        ->getData();

                    $before_transfer_fromToLocArr = $this->formFromToLoc($before_transfer_pickup_id,$before_transfer_return_id)->getFromToLoc();
                    $data = $this->getPassangerData($before_transfer_bus_id,$before_transfer_bus_arr,$this->_get('date'),time(),$before_transfer_pickup_id,$before_transfer_return_id,$before_transfer_fromToLocArr);

                    $booking_id = $pjBookingModel
                        ->reset()
                        ->setAttributes(array_merge($FORM,$data))
                        ->insert()
                        ->getInsertId();
            
                    if($booking_id !== false && (int)$booking_id > 0) {
                        $before_transfer_ticket_arr = $pjPriceModel->getTicketArr($before_transfer_bus_id,$before_transfer_pickup_id,$before_transfer_return_id,$_is_return);
                        $before_transfer_location_arr = $pjRouteCityModel->getLocations($before_transfer_bus_arr['route_id'],$before_transfer_pickup_id,$before_transfer_return_id);
                        $before_transfer_location_pair = pjUtil::getLocPairArr($before_transfer_location_arr);

                        $pjBookingTicketModel->multiInsert($before_transfer_ticket_arr,$booked_data,$booking_id,$field_price,$_is_return);
                        $before_transfer_seat_id_arr = explode("|", $booked_data['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                        $before_transfer_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($before_transfer_ticket_arr,$booked_data,$booking_id,$_is_return,$before_transfer_seat_id_arr,$before_transfer_location_pair);

                        $pjBookingPassengerModel
                            ->prepareData($before_transfer_seat_id_arr, $before_transfer_pickup_ticket_arr, @$FORM, $booking_id,$_is_return)
                            ->multiInsert();
                        
                        //payment
                        $before_transfer_arr = $this->getBookingInfo($booking_id);
                        $payment_data = $this->getPaymentData($before_transfer_arr,$payment);
                        $pjBookingPaymentModel
                            ->setAttributes($payment_data)
                            ->insert();
                        $this->pjActionConfirmSend($this->option_arr, $before_transfer_arr, PJ_SALT, 'confirm');

                       
                        // === transfer === //
                        $transfer_date = pjUtil::formatTime2Date($before_transfer_arr['stop_datetime'], 'Y-m-d H:i:s',$this->option_arr['o_date_format']);
                        $after_transfer_bus_arr = pjBusModel::factory()
                            ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                            ->join('pjBusType', "t3.id=t1.bus_type_id", 'left')
                            ->select('t1.*, t3.seats_map, t2.content as route')
                            ->find($after_transfer_bus_id)
                            ->getData();

                        $after_transfer_fromToLocArr = $this->formFromToLoc($after_transfer_pickup_id,$after_transfer_return_id)->getFromToLoc();
                        $data = $this->getPassangerData($after_transfer_bus_id,$after_transfer_bus_arr,$transfer_date,time()+1,$after_transfer_pickup_id,$after_transfer_return_id,$after_transfer_fromToLocArr);

                        $after_transfer_booking_id = $pjBookingModel
                            ->reset()
                            ->setAttributes(array_merge($FORM,$data))
                            ->insert()
                            ->getInsertId(); 

                        if($after_transfer_booking_id !== false && (int)$after_transfer_booking_id > 0) {
                            $pjBookingModel->linkBooking($booking_id,$after_transfer_booking_id);
                            $after_transfer_ticket_arr = $pjPriceModel->getTicketArr($after_transfer_bus_id,$after_transfer_pickup_id,$after_transfer_return_id,$_is_return);
                            $after_transfer_location_arr = $pjRouteCityModel->getLocations($after_transfer_bus_arr['route_id'],$after_transfer_pickup_id,$after_transfer_return_id);
                            $after_transfer_location_pair = pjUtil::getLocPairArr($after_transfer_location_arr);

                            $pjBookingTicketModel->multiInsert($after_transfer_ticket_arr,$booked_data,$after_transfer_booking_id,$field_price,$_is_return);
                            $after_transfer_seat_id_arr = explode("|", $booked_data['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                            $after_transfer_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($after_transfer_ticket_arr,$booked_data,$after_transfer_booking_id,$_is_return,$after_transfer_seat_id_arr,$after_transfer_location_pair);

                            $pjBookingPassengerModel
                                ->prepareData($after_transfer_seat_id_arr,$after_transfer_pickup_ticket_arr, @$FORM, $after_transfer_booking_id,$_is_return)
                                ->multiInsert();

                            //payment
                            $after_transfer_arr = $this->getBookingInfo($after_transfer_booking_id);
                            $payment_data = $this->getPaymentData($before_transfer_arr,$payment);
                            $pjBookingPaymentModel
                                ->setAttributes($payment_data)
                                ->insert();
                            
                            $this->pjActionConfirmSend($this->option_arr, $after_transfer_arr, PJ_SALT, 'confirm');
                        }

                        
                        
                        // === return === //
                        $_is_return = 'T';
                        $return_before_transfer_pickup_id = $this->_get('return_before_transfer_pickup_id');
                        $return_before_transfer_return_id = $this->_get('return_before_transfer_return_id');
                        $return_before_transfer_bus_id = $booked_data['return_bus_id'][$return_before_transfer_pickup_id.$return_before_transfer_return_id];
                        $return_after_transfer_pickup_id = $this->_get('return_after_transfer_pickup_id');
                        $return_after_transfer_return_id = $this->_get('return_after_transfer_return_id');
                        $return_after_transfer_bus_id = $booked_data['return_bus_id'][$return_after_transfer_pickup_id.$return_after_transfer_return_id];

                        $return_before_transfer_bus_arr = pjBusModel::factory()
                            ->reset()
                            ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                            ->join('pjBusType','t3.id=t1.bus_type_id','left')
                            ->select('t1.*, t3.seats_map, t2.content as route')
                            ->limit(1)
                            ->find($return_before_transfer_bus_id)
                            ->getData();

                        $return_before_transfer_fromToLocArr = $this->formFromToLoc($before_transfer_pickup_id,$before_transfer_return_id)->getFromToLoc();
                        $data = $this->getPassangerData($return_before_transfer_bus_id,$return_before_transfer_bus_arr,$this->_get('return_date'),time()+2,$return_before_transfer_pickup_id,$return_before_transfer_return_id,$return_before_transfer_fromToLocArr);

                        $return_before_transfer_booking_id = $pjBookingModel
                            ->reset()
                            ->setAttributes(array_merge($FORM,$data))
                            ->insert()
                            ->getInsertId();

                        if($return_before_transfer_booking_id !== false && (int)$return_before_transfer_booking_id > 0) {
                            $return_before_transfer_ticket_arr = $pjPriceModel->getTicketArr($return_before_transfer_bus_id,$return_before_transfer_pickup_id,$return_before_transfer_return_id,$_is_return);
                            $return_before_transfer_location_arr = $pjRouteCityModel->getLocations($return_before_transfer_bus_arr['route_id'],$return_before_transfer_pickup_id,$return_before_transfer_return_id);
                            $return_before_transfer_location_pair = pjUtil::getLocPairArr($return_before_transfer_location_arr);

                            $pjBookingTicketModel->multiInsert($return_before_transfer_ticket_arr,$booked_data,$return_before_transfer_booking_id,$field_price,$_is_return);
                            $return_before_transfer_seat_id_arr = explode("|", $booked_data['return_selected_seats'][$return_before_transfer_pickup_id.$return_before_transfer_return_id]);
                            $return_before_transfer_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($return_before_transfer_ticket_arr,$booked_data,$return_before_transfer_booking_id,$_is_return,$return_before_transfer_seat_id_arr,$return_before_transfer_location_pair);

                            $pjBookingPassengerModel
                                ->prepareData($return_before_transfer_seat_id_arr, $return_before_transfer_pickup_ticket_arr, @$FORM, $return_before_transfer_booking_id,$_is_return)
                                ->multiInsert();

                            //payment
                            $return_before_transfer_arr = $this->getBookingInfo($return_before_transfer_booking_id);
                            $payment_data = $this->getPaymentData($return_before_transfer_arr,$payment);
                            $pjBookingPaymentModel
                                ->setAttributes($payment_data)
                                ->insert();
                            $this->pjActionConfirmSend($this->option_arr, $before_transfer_arr, PJ_SALT, 'confirm');
                        }
                        
                        // === transfer === //
                        $transfer_date = pjUtil::formatTime2Date($return_before_transfer_arr['stop_datetime'], 'Y-m-d H:i:s',$this->option_arr['o_date_format']);
                        $return_after_transfer_bus_arr = pjBusModel::factory()
                            ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                            ->join('pjBusType', "t3.id=t1.bus_type_id", 'left')
                            ->select('t1.*, t3.seats_map, t2.content as route')
                            ->find($return_after_transfer_bus_id)
                            ->getData();

                        $return_after_transfer_fromToLocArr = $this->formFromToLoc($return_after_transfer_pickup_id,$return_after_transfer_return_id)->getFromToLoc();
                        $data = $this->getPassangerData($return_after_transfer_bus_id,$return_after_transfer_bus_arr,$transfer_date,time()+3,$return_after_transfer_pickup_id,$return_after_transfer_return_id,$return_after_transfer_fromToLocArr);

                        $return_after_transfer_booking_id = $pjBookingModel
                            ->reset()
                            ->setAttributes(array_merge($FORM,$data))
                            ->insert()
                            ->getInsertId(); 

                        if($return_after_transfer_booking_id !== false && (int)$return_after_transfer_booking_id > 0) {
                            $pjBookingModel->linkBooking($return_before_transfer_booking_id,$return_after_transfer_booking_id);
                            $return_after_transfer_ticket_arr = $pjPriceModel->getTicketArr($return_after_transfer_bus_id,$return_after_transfer_pickup_id,$return_after_transfer_return_id,$_is_return);
                            $return_after_transfer_location_arr = $pjRouteCityModel->getLocations($return_after_transfer_bus_arr['route_id'],$return_after_transfer_pickup_id,$return_after_transfer_return_id);
                            $return_after_transfer_location_pair = pjUtil::getLocPairArr($return_after_transfer_location_arr);

                            $pjBookingTicketModel->multiInsert($return_after_transfer_ticket_arr,$booked_data,$return_after_transfer_booking_id,$field_price,$_is_return);
                            $return_after_transfer_seat_id_arr = explode("|", $booked_data['return_selected_seats'][$return_after_transfer_pickup_id.$return_after_transfer_return_id]);
                            $return_after_transfer_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($return_after_transfer_ticket_arr,$booked_data,$return_after_transfer_booking_id,$_is_return,$return_after_transfer_seat_id_arr,$return_after_transfer_location_pair);
                            $pjBookingPassengerModel
                                ->prepareData($return_after_transfer_seat_id_arr,$return_after_transfer_pickup_ticket_arr, @$FORM, $return_after_transfer_booking_id,$_is_return)
                                ->multiInsert();

                            //payment
                            $return_after_transfer_arr = $this->getBookingInfo($return_after_transfer_booking_id);
                            $payment_data = $this->getPaymentData($return_after_transfer_arr,$payment);
                            $pjBookingPaymentModel
                                ->setAttributes($payment_data)
                                ->insert();
                            $this->pjActionConfirmSend($this->option_arr, $return_after_transfer_arr, PJ_SALT, 'confirm');
                        }                        
                    }
                }else{
                    $_is_return = 'F';
                    $bus_id = $booked_data['bus_id'][$pickup_id.$return_id];
                    $bus_arr = pjBusModel::factory()
                        ->reset()
                        ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                        ->join('pjBusType','t3.id=t1.bus_type_id','left')
                        ->select('t1.*, t3.seats_map, t2.content as route')
                        ->limit(1)
                        ->find($bus_id)
                        ->getData();

                    $fromToLocArr = $this->formFromToLoc($pickup_id,$return_id)->getFromToLoc();
                    $data = $this->getPassangerData($bus_id,$bus_arr,$this->_get('date'),time(),$pickup_id,$return_id,$fromToLocArr);
                    
                    $booking_id = $pjBookingModel
                        ->reset()
                        ->setAttributes(array_merge($FORM,$data))
                        ->insert()
                        ->getInsertId();

                    if($booking_id !== false && (int)$booking_id > 0) {
                        $ticket_arr = $pjPriceModel->getTicketArr($bus_id,$pickup_id,$return_id,$_is_return);
                        $location_arr = $pjRouteCityModel->getLocations($bus_arr['route_id'],$pickup_id,$return_id);
                        $location_pair = pjUtil::getLocPairArr($location_arr);

                        $pjBookingTicketModel->multiInsert($ticket_arr,$booked_data,$booking_id,$field_price,$_is_return);
                        $seat_id_arr = explode("|", $booked_data['selected_seats'][$pickup_id.$return_id]);
                        $pickup_ticket_arr = $pjBookingSeatModel->multiInsert($ticket_arr,$booked_data,$booking_id,$_is_return,$seat_id_arr,$location_pair);

                        $pjBookingPassengerModel = pjBookingPassengerModel::factory();
                        $pjBookingPassengerModel
                            ->prepareData($seat_id_arr, $pickup_ticket_arr, @$FORM, $booking_id,$_is_return)
                            ->multiInsert();

                        //payment
                        $arr = $this->getBookingInfo($booking_id);
                        $payment_data = $this->getPaymentData($arr,$payment);
                        $pjBookingPaymentModel
                            ->setAttributes($payment_data)
                            ->insert();
                       $this->pjActionConfirmSend($this->option_arr,$arr,PJ_SALT,'confirm');
                        
                      
                        // === return === //
                        $_is_return = 'T';
                        $return_bus_id = $booked_data['return_bus_id'][$return_id.$pickup_id];
                        $return_bus_arr = pjBusModel::factory()
                            ->reset()
                            ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                            ->join('pjBusType','t3.id=t1.bus_type_id','left')
                            ->select('t1.*, t3.seats_map, t2.content as route')
                            ->limit(1)
                            ->find($return_bus_id)
                            ->getData();
                    
                        $return_fromToLocArr = $this->formFromToLoc($return_id,$pickup_id)->getFromToLoc();
                        $data = $this->getPassangerData($return_bus_id,$return_bus_arr,$this->_get('return_date'),time()+1,$return_id,$pickup_id,$return_fromToLocArr);
                        
                        $return_booking_id = $pjBookingModel
                            ->reset()
                            ->setAttributes(array_merge($FORM,$data))
                            ->insert()
                            ->getInsertId();

                        if($return_booking_id !== false && (int)$return_booking_id > 0) {
                            $return_ticket_arr = $pjPriceModel->getTicketArr($return_bus_id,$return_id,$pickup_id,$_is_return);
                            $return_location_arr = $pjRouteCityModel->getLocations($return_bus_arr['route_id'],$return_id,$pickup_id);
                            $return_location_pair = pjUtil::getLocPairArr($return_location_arr);

                            $pjBookingTicketModel->multiInsert($return_ticket_arr,$booked_data,$return_booking_id,$field_price,$_is_return);
                            $return_seat_id_arr = explode("|", $booked_data['return_selected_seats'][$return_id.$pickup_id]);
                            $return_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($return_ticket_arr,$booked_data,$return_booking_id,$_is_return,$return_seat_id_arr,$return_location_pair);
                            
                            $pjBookingPassengerModel = pjBookingPassengerModel::factory();
                            $pjBookingPassengerModel
                                ->prepareData($return_seat_id_arr, $return_pickup_ticket_arr, @$FORM, $return_booking_id,$_is_return)
                                ->multiInsert();
                            
                            //payment
                            $return_arr = $this->getBookingInfo($return_booking_id);
                            $return_payment_data = $this->getPaymentData($return_arr,$payment);
                            $pjBookingPaymentModel
                                ->setAttributes($return_payment_data)
                                ->insert();
                            $this->pjActionConfirmSend($this->option_arr,$return_arr,PJ_SALT,'confirm');
                        }
                    }
                }
            }
            else{
                if(1 == $is_transfer) {//direct way with trasfer
                    $before_transfer_pickup_id = $this->_get('before_transfer_pickup_id');
                    $before_transfer_return_id = $this->_get('before_transfer_return_id');
                    $before_transfer_bus_id = $booked_data['bus_id'][$before_transfer_pickup_id.$before_transfer_return_id];
                    $after_transfer_pickup_id = $this->_get('after_transfer_pickup_id');
                    $after_transfer_return_id = $this->_get('after_transfer_return_id');
                    $after_transfer_bus_id = $booked_data['bus_id'][$after_transfer_pickup_id.$after_transfer_return_id];

                    $before_transfer_bus_arr = pjBusModel::factory()
                        ->reset()
                        ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                        ->join('pjBusType','t3.id=t1.bus_type_id','left')
                        ->select('t1.*, t3.seats_map, t2.content as route')
                        ->limit(1)
                        ->find($before_transfer_bus_id)
                        ->getData();

                    $before_transfer_fromToLocArr = $this->formFromToLoc($before_transfer_pickup_id,$before_transfer_return_id)->getFromToLoc();
                    $data = $this->getPassangerData($before_transfer_bus_id,$before_transfer_bus_arr,$this->_get('date'),time(),$before_transfer_pickup_id,$before_transfer_return_id,$before_transfer_fromToLocArr);

                    $booking_id = $pjBookingModel
                        ->reset()
                        ->setAttributes(array_merge($FORM,$data))
                        ->insert()
                        ->getInsertId();

                    if($booking_id !== false && (int)$booking_id > 0) {
                        $before_transfer_ticket_arr = $pjPriceModel->getTicketArr($before_transfer_bus_id,$before_transfer_pickup_id,$before_transfer_return_id,$is_return);
                        $before_transfer_location_arr = $pjRouteCityModel->getLocations($before_transfer_bus_arr['route_id'],$before_transfer_pickup_id,$before_transfer_return_id);
                        $before_transfer_location_pair = pjUtil::getLocPairArr($before_transfer_location_arr);

                        $pjBookingTicketModel->multiInsert($before_transfer_ticket_arr,$booked_data,$booking_id,$field_price,$is_return);
                        $before_transfer_seat_id_arr = explode("|", $booked_data['selected_seats'][$before_transfer_pickup_id.$before_transfer_return_id]);
                        $before_transfer_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($before_transfer_ticket_arr,$booked_data,$booking_id,$is_return,$before_transfer_seat_id_arr,$before_transfer_location_pair);

                        $pjBookingPassengerModel
                            ->prepareData($before_transfer_seat_id_arr, $before_transfer_pickup_ticket_arr, @$FORM, $booking_id,'F')
                            ->multiInsert();

                        //payment
                        $before_transfer_arr = $this->getBookingInfo($booking_id);
                        $payment_data = $this->getPaymentData($before_transfer_arr,$payment);
                        $pjBookingPaymentModel
                            ->setAttributes($payment_data)
                            ->insert();
                        $this->pjActionConfirmSend($this->option_arr, $before_transfer_arr, PJ_SALT, 'confirm');



                        //=== transfer ===//
                        $transfer_date = pjUtil::formatTime2Date($before_transfer_arr['stop_datetime'], 'Y-m-d H:i:s',$this->option_arr['o_date_format']);
                        $after_transfer_bus_arr = pjBusModel::factory()
                            ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                            ->join('pjBusType', "t3.id=t1.bus_type_id", 'left')
                            ->select('t1.*, t3.seats_map, t2.content as route')
                            ->find($after_transfer_bus_id)
                            ->getData();

                        $after_transfer_fromToLocArr = $this->formFromToLoc($after_transfer_pickup_id,$after_transfer_return_id)->getFromToLoc();
                        $data = $this->getPassangerData($after_transfer_bus_id,$after_transfer_bus_arr,$transfer_date,time()+1,$after_transfer_pickup_id,$after_transfer_return_id,$after_transfer_fromToLocArr);

                        $after_transfer_booking_id = $pjBookingModel
                            ->reset()
                            ->setAttributes(array_merge($FORM,$data))
                            ->insert()
                            ->getInsertId(); 

                        if($after_transfer_booking_id !== false && (int)$after_transfer_booking_id > 0) {
                            $pjBookingModel->linkBooking($booking_id,$after_transfer_booking_id);
                            $after_transfer_ticket_arr = $pjPriceModel->getTicketArr($after_transfer_bus_id,$after_transfer_pickup_id,$after_transfer_return_id,$is_return);
                            $after_transfer_location_arr = $pjRouteCityModel->getLocations($after_transfer_bus_arr['route_id'],$after_transfer_pickup_id,$after_transfer_return_id);
                            $after_transfer_location_pair = pjUtil::getLocPairArr($after_transfer_location_arr);

                            $pjBookingTicketModel->multiInsert($after_transfer_ticket_arr,$booked_data,$after_transfer_booking_id,$field_price,$is_return);
                            $after_transfer_seat_id_arr = explode("|", $booked_data['selected_seats'][$after_transfer_pickup_id.$after_transfer_return_id]);
                            $after_transfer_pickup_ticket_arr = $pjBookingSeatModel->multiInsert($after_transfer_ticket_arr,$booked_data,$after_transfer_booking_id,$is_return,$after_transfer_seat_id_arr,$after_transfer_location_pair);
                            $pjBookingPassengerModel
                                ->prepareData($after_transfer_seat_id_arr,$after_transfer_pickup_ticket_arr, @$FORM, $after_transfer_booking_id,'F')
                                ->multiInsert();

                            //payment
                            $after_transfer_arr = $this->getBookingInfo($after_transfer_booking_id);
                            $payment_data = $this->getPaymentData($before_transfer_arr,$payment);
                            $pjBookingPaymentModel
                                ->setAttributes($payment_data)
                                ->insert();
                            $this->pjActionConfirmSend($this->option_arr, $after_transfer_arr, PJ_SALT, 'confirm');
                        }
                    }
                }
                else{//direct way without transfer                
                    $bus_id = $booked_data['bus_id'][$pickup_id.$return_id];
                    $bus_arr = pjBusModel::factory ()
                        ->reset()
                        ->join('pjMultiLang',"t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer')
                        ->join('pjBusType','t3.id=t1.bus_type_id','left')
                        ->select('t1.*, t3.seats_map, t2.content as route')
                        ->limit(1)
                        ->find($bus_id)
                        ->getData();

                    $fromToLocArr = $this->formFromToLoc($pickup_id,$return_id)->getFromToLoc();
                    $data = $this->getPassangerData($bus_id,$bus_arr,$this->_get('date'),time(),$pickup_id,$return_id,$fromToLocArr);

                    $booking_id = $pjBookingModel
                        ->setAttributes(array_merge($FORM,$data))
                        ->insert()
                        ->getInsertId();

                    if($booking_id !== false && (int)$booking_id > 0) {
                        $ticket_arr = $pjPriceModel->getTicketArr($bus_id,$pickup_id,$return_id,$is_return);
                        $location_arr = $pjRouteCityModel->getLocations($bus_arr['route_id'],$pickup_id,$return_id);
                        $location_pair = pjUtil::getLocPairArr($location_arr);

                        $pjBookingTicketModel->multiInsert($ticket_arr,$booked_data,$booking_id,$field_price,$is_return);
                        $seat_id_arr = explode("|", $booked_data['selected_seats'][$pickup_id.$return_id]);
                        $pickup_ticket_arr = $pjBookingSeatModel->multiInsert($ticket_arr,$booked_data,$booking_id,$is_return,$seat_id_arr,$location_pair);

                        $pjBookingPassengerModel = pjBookingPassengerModel::factory();
                        $pjBookingPassengerModel
                            ->prepareData($seat_id_arr, $pickup_ticket_arr, @$FORM, $booking_id,'F')
                            ->multiInsert();

                        //payment
                        $arr = $this->getBookingInfo($booking_id);
                        $payment_data = $this->getPaymentData($arr,$payment);
                        $pjBookingPaymentModel
                            ->setAttributes($payment_data)
                            ->insert();
                        $this->pjActionConfirmSend($this->option_arr,$arr,PJ_SALT,'confirm');
                    }
                }

            }


            if($booking_id !== false && (int)$booking_id > 0) {
                unset($_SESSION[$this->defaultStore]);
                unset($_SESSION[$this->defaultForm]);
                unset($_SESSION[$this->defaultStep]);

                $json = array(
                    'code' => 200,
                    'text' => '',
                    'booking_id' => $booking_id,
                    'payment' => $payment 
                );    
            }else{
                $json = array (
                    'code' => 100,
                    'text' => '' 
                );
            }

            pjAppController::jsonResponse($json);
        }
    }

    public function pjActionGetLocations()
    {
        $this->setAjax(true);
        $location_arr = array();

        $pjCityModel = pjCityModel::factory();
        $pjRouteCityModel = pjRouteCityModel::factory();


        if(isset($_GET['pickup_id']))
        {
            $location_arr = $pjCityModel->getDestCities($_GET['pickup_id'],$this->locId,',');
        }
        if(isset($_GET['return_id']))
        {
            $where = '';
            $rcmTable = $pjRouteCityModel->getTable();
            if(!empty($_GET['return_id']))
            {
                $where = "WHERE TRD.to_location_id=" . $_GET['return_id'];
            }
            $location_arr = $pjCityModel
                ->reset()
                ->select('t1.*, t2.content as name')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->locId."'", 'left outer')
                //->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
                ->where("t1.id IN(SELECT TRC.city_id FROM `".$rcmTable."` AS TRC WHERE TRC.is_arrival=1)")
                ->where("t1.id NOT IN(SELECT TRC.city_id FROM `".$rcmTable."` AS TRC WHERE ((TRC.is_arrival=1 AND TRC.is_departure=1)))")
                ->orderBy("t2.content ASC")
                ->findAll()
                ->getData();
        }

        $this->set('location_arr', $location_arr);
    }

    public function pjActionGetRoundtripPrice()
    {
        $this->setAjax(true);

        if ($this->isXHR())
        {
            if (isset($_SESSION[$this->defaultStore]) && count($_SESSION[$this->defaultStore]) > 0 && $this->isBusReady() == true)
            {
                $is_return = isset($_GET['is_return']) ? $_GET['is_return']: null;
                $is_transfer = $this->_get('is_transfer');
                $bus_id = (isset($_GET['bus_id']) && (int) $_GET['bus_id']) > 0 ? $_GET['bus_id'] : 0;
                $return_bus_id = (isset($_GET['return_bus_id']) && (int) $_GET['return_bus_id'] > 0) ? $_GET['return_bus_id'] : 0;

                $field_price = 'price';
                if (isset($this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]])) {
                    $field_price = $this->defaultTicketCurrencies[$_SESSION[$this->defaultFrontTicketCurrency]];
                }

                $pickup_id = (isset($_GET['pickup_id'])) ? $_GET['pickup_id'] : null;
                $return_id = (isset($_GET['return_id'])) ? $_GET['return_id'] : null;

                $pjPriceModel = pjPriceModel::factory();
                if($bus_id > 0)
                {
                    $ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), $is_return, $field_price, $is_transfer);
                    $this->set('price_arr', $ticket_price_arr);
                }

                $this->set('status', 'OK');
            }else{
                $this->set('status', 'ERR');
            }
        }
    }

    public function pjActionGetSeats()
    {
        $this->setAjax(true);
        $bus_id = $_GET['bus_id'];
        $STORE = @$_SESSION[$this->defaultStore];

        if (isset($_GET['pickup_id']) && (int)$_GET['pickup_id'] > 0) {
            $STORE['pickup_id'] = $_GET['pickup_id'];
        }
        if (isset($_GET['return_id']) && (int)$_GET['return_id'] > 0) {
            $STORE['return_id'] = $_GET['return_id'];
        }

        $avail_arr = $this->getBusAvailability($bus_id, $STORE, $this->option_arr);
        $this->set('bus_arr', pjBusModel::factory()->find($bus_id)->getData());
        $this->set('bus_type_arr', $avail_arr['bus_type_arr']);
        $this->set('booked_seat_arr', $avail_arr['booked_seat_arr']);

        if(!empty($avail_arr['bus_type_arr']))
        {
            $this->set('seat_arr', pjSeatModel::factory()->where('bus_type_id', $avail_arr['bus_type_arr']['id'])->findAll()->getData());
        }else{
            $this->set('seat_arr', array());
        }
    }

    public function pjActionGetReturnSeats()
    {
        $this->setAjax(true);
        $bus_id = $_GET['bus_id'];
        $STORE = @$_SESSION[$this->defaultStore];

        if (isset($_GET['pickup_id']) && (int)$_GET['pickup_id'] > 0) {
            $STORE['return_id'] = $_GET['pickup_id'];
        }
        if (isset($_GET['return_id']) && (int)$_GET['return_id'] > 0) {
            $STORE['pickup_id'] = $_GET['return_id'];
        }
        $avail_arr = $this->getReturnBusAvailability($bus_id, $STORE, $this->option_arr);
        $this->set('bus_arr', pjBusModel::factory()->find($bus_id)->getData());
        $this->set('return_bus_type_arr', $avail_arr['bus_type_arr']);
        $this->set('booked_return_seat_arr', $avail_arr['booked_seat_arr']);

        if(!empty($avail_arr['bus_type_arr']))
        {
            $this->set('return_seat_arr', pjSeatModel::factory()->where('bus_type_id', $avail_arr['bus_type_arr']['id'])->findAll()->getData());
        }else{
            $this->set('return_seat_arr', array());
        }
    }

    public function pjActionConfirmAuthorize()
    {
        $this->setAjax(true);

        if (pjObject::getPlugin('pjAuthorize') === NULL)
        {
            $this->log('Authorize.NET plugin not installed');
            exit;
        }

        $pjBookingModel = pjBookingModel::factory();
        $booking_arr = $pjBookingModel
            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
            ->find($_POST['x_invoice_num'])
            ->getData();

        $booking_arr['tickets'] = pjBookingTicketModel::factory()
            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
            ->select('t1.*, t2.content as title')
            ->where('booking_id', $booking_arr['id'])
            ->findAll()
            ->getData();

        if (count($booking_arr) == 0)
        {
            $this->log('No such booking');
            pjUtil::redirect($this->option_arr['o_thank_you_page']);
        }					
        if (count($booking_arr) > 0)
        {
            $params = array(
                'transkey' => $this->option_arr['o_authorize_transkey'],
                'x_login' => $this->option_arr['o_authorize_merchant_id'],
                'md5_setting' => $this->option_arr['o_authorize_md5_hash'],
                'key' => md5($this->option_arr['private_key'] . PJ_SALT)
            );

            $response = $this->requestAction(array('controller' => 'pjAuthorize', 'action' => 'pjActionConfirm', 'params' => $params), array('return'));

            if ($response !== FALSE && $response['status'] === 'OK')
            {
                    $pjBookingModel->reset()
                            ->setAttributes(array('id' => $response['transaction_id']))
                            ->modify(array('status' => $this->option_arr['o_payment_status'], 'processed_on' => ':NOW()'));

                    if (!empty($booking_arr['back_id'])) 
                    {
                            $pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['back_id']))->modify(array(
                                    'status' => $this->option_arr['o_payment_status'],
                                    'txn_id' => $response['transaction_id'],
                                    'processed_on' => ':NOW()'
                            ));
                    }
                    if ($booking_arr['is_transfer'] == 1) 
                    {
                            $pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['transfer_booking_id']))->modify(array(
                                    'status' => $this->option_arr['o_payment_status'],
                                    'txn_id' => $response['transaction_id'],
                                    'processed_on' => ':NOW()'
                            ));
                    }
                    pjBookingPaymentModel::factory()
                            ->where('booking_id', $booking_arr['id'])
                            ->where('payment_type', 'online')
                            ->modifyAll(array('status' => 'paid'));

                    pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'payment');

            } elseif (!$response) {
                    $this->log('Authorization failed');
            } else {
                    $this->log('Booking not confirmed. ' . $response['response_reason_text']);
            }
            pjUtil::redirect($this->option_arr['o_thank_you_page']);
        }
    }

    public function pjActionConfirmPaypal()
    {
        $this->setAjax(true);

        if (pjObject::getPlugin('pjPaypal') === NULL)
        {
                $this->log('Paypal plugin not installed');
                exit;
        }
        $pjBookingModel = pjBookingModel::factory();
        $booking_arr = $pjBookingModel
            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
            ->find($_POST['custom'])
            ->getData();

        $booking_arr['tickets'] = pjBookingTicketModel::factory()
            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
            ->select('t1.*, t2.content as title')
            ->where('booking_id', $booking_arr['id'])
            ->findAll()
            ->getData();

        if (count($booking_arr) == 0)
        {
            $this->log('No such booking');
            pjUtil::redirect($this->option_arr['o_thank_you_page']);
        }					
        if (!empty($booking_arr['back_id'])) {
                $back_arr = pjBookingModel::factory()
                        ->select('t1.*')
                        ->find($booking_arr['back_id'])->getData();
                $booking_arr['deposit'] += $back_arr['deposit'];
        }
        if ($booking_arr['is_transfer'] == 1) {
                $back_arr = pjBookingModel::factory()
                        ->select('t1.*')
                        ->find($booking_arr['transfer_booking_id'])->getData();
                $booking_arr['deposit'] += $back_arr['deposit'];
        }
        $params = array(
                'txn_id' => @$booking_arr['txn_id'],
                'paypal_address' => $this->option_arr['o_paypal_address'],
                'deposit' => @$booking_arr['deposit'],
                'currency' => $this->option_arr['o_currency'],
                'key' => md5($this->option_arr['private_key'] . PJ_SALT)
        );
        $response = $this->requestAction(array('controller' => 'pjPaypal', 'action' => 'pjActionConfirm', 'params' => $params), array('return'));

        if ($response !== FALSE && $response['status'] === 'OK')
        {
                $this->log('Booking confirmed');
                $pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['id']))->modify(array(
                        'status' => $this->option_arr['o_payment_status'],
                        'txn_id' => $response['transaction_id'],
                        'processed_on' => ':NOW()'
                ));
                if (!empty($booking_arr['back_id'])) 
                {
                        $pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['back_id']))->modify(array(
                                        'status' => $this->option_arr['o_payment_status'],
                                        'txn_id' => $response['transaction_id'],
                                        'processed_on' => ':NOW()'
                        ));
                }
                if ($booking_arr['is_transfer'] == 1) 
                {
                        $pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['transfer_booking_id']))->modify(array(
                                        'status' => $this->option_arr['o_payment_status'],
                                        'txn_id' => $response['transaction_id'],
                                        'processed_on' => ':NOW()'
                        ));
                }
            pjBookingPaymentModel::factory()
                ->where('booking_id', $booking_arr['id'])
                ->where('payment_type', 'online')
                ->modifyAll(array('status' => 'paid'));

            pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'payment');

        } elseif (!$response) {
            $this->log('Authorization failed');
        } else {
            $this->log('Booking not confirmed');
        }
        pjUtil::redirect($this->option_arr['o_thank_you_page']);
    }

    public function pjActionCancel()
    {
            $this->setLayout('pjActionCancel');

            $pjBookingModel = pjBookingModel::factory();

            if (isset($_POST['booking_cancel']))
            {
                    $booking_arr = pjBookingModel::factory()
                            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                            ->find($_POST['id'])
                            ->getData();
                    if (count($booking_arr) > 0)
                    {
                            $sql = "UPDATE `".$pjBookingModel->getTable()."` SET status = 'cancelled' WHERE SHA1(CONCAT(`id`, `created`, '".PJ_SALT."')) = '" . $_POST['hash'] . "'";

                            $pjBookingModel->reset()->execute($sql);

                            $booking_arr['tickets'] = pjBookingTicketModel::factory()
                                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                                ->select('t1.*, t2.content as title')
                                ->where('booking_id', $booking_arr['id'])
                                ->findAll()
                                ->getData();

                            pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'cancel');

                            pjUtil::redirect($_SERVER['PHP_SELF'] . '?controller=pjFrontEnd&action=pjActionCancel&err=200');
                    }
            }else{
                    if (isset($_GET['hash']) && isset($_GET['id']))
                    {
                            $arr = $pjBookingModel
                                    ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location, t6.content as country_title')
                                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                                    ->join('pjMultiLang', "t6.model='pjCountry' AND t6.foreign_id=t1.c_country AND t6.field='name' AND t6.locale='".$this->getLocaleId()."'", 'left outer')
                                    ->find($_GET['id'])->getData();

                            if (count($arr) == 0)
                            {
                                    $this->set('status', 2);
                            }else{
                                    if ($arr['status'] == 'cancelled')
                                    {
                                            $this->set('status', 4);
                                    }else{
                                            $hash = sha1($arr['id'] . $arr['created'] . PJ_SALT);
                                            if ($_GET['hash'] != $hash)
                                            {
                                                    $this->set('status', 3);
                                            }else{
                                                    if($arr['booking_datetime'] > date('Y-m-d H:i:s'))
                                                    {
                                                            $arr['tickets'] = pjBookingTicketModel::factory()
                                                                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                                                                ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                                                                ->select('t1.*, t2.content as title')
                                                                ->where('booking_id', $arr['id'])
                                                                ->findAll()
                                                                ->getData();

                                                            $this->set('arr', $arr);
                                                    }else{
                                                            $this->set('status', 5);
                                                    }
                                            }
                                    }
                            }
                    }elseif (!isset($_GET['err'])) {
                            $this->set('status', 1);
                    }
            }
    }

    public function pjActionPrintTickets()
    {
            $this->setLayout('pjActionPrint');

            $pjBookingModel = pjBookingModel::factory();

            $arr = $pjBookingModel
                ->select('t1.*, t2.content as from_location, t3.content as to_location')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->find($_GET['id'])
                ->getData();

            if(!empty($arr))
            {
                if ($arr['is_return'] == 'T')
                {
                        $arr['return_arr'] = $pjBookingModel
                                ->reset()
                                ->select('t1.*, t2.content as from_location, t3.content as to_location')
                                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                                ->find($arr['back_id'])->getData();
                }

                $hash = sha1($arr['id'].$arr['created'].PJ_SALT);
                if($hash == $_GET['hash'])
                {
                    if($arr['status'] == 'confirmed')
                    {
                        $arr['tickets'] = pjBookingTicketModel::factory()
                            ->reset()
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.pjPriceModel::factory()->getTable().'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$arr['bus_id'].' AND TP.from_location_id = '.$arr['pickup_id'].' AND TP.to_location_id= '.$arr['return_id']. ' AND is_return = "F") as price')
                            ->where('booking_id', $arr['id'])
                            ->findAll()->getData();

                        $pjCityModel = pjCityModel::factory();
                        $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['pickup_id'])->getData();
                        $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['return_id'])->getData();
                        $arr['from_location'] = $pickup_location['name'];
                        $arr['to_location'] = $to_location['name'];

                        $pjMultiLangModel = pjMultiLangModel::factory();
                        $lang_template = $pjMultiLangModel
                            ->reset()
                            ->select('t1.*')
                            ->where('t1.model','pjOption')
                            ->where('t1.locale', $this->getLocaleId())
                            ->where('t1.field', 'o_ticket_template')
                            ->limit(0, 1)
                            ->findAll()
                            ->getData();
                        $template = '';
                        if (count($lang_template) === 1)
                        {
                            $template = $lang_template[0]['content'];
                        }

                        $data = pjAppController::getTemplate($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());
                        $template_arr = str_replace($data['search'], $data['replace'], $template);
                        $this->set('template_arr', $template_arr);
                    }
                }else{
                    $this->set('status', 'ERR02');
                }
            }else{
                $this->set('status', 'ERR01');
            }
    }

    public function pjActionConfirmSend($option_arr, $booking_arr, $salt, $opt)
    {
        $locale_id = (isset($booking_arr['locale_id']) && (int)$booking_arr['locale_id'] > 0) ? (int)$booking_arr['locale_id'] : $this->getLocaleId();
        $Email = new pjEmail();
        if ($option_arr['o_send_email'] == 'smtp')
        {
            $Email
                ->setTransport('smtp')
                ->setSmtpHost($option_arr['o_smtp_host'])
                ->setSmtpPort($option_arr['o_smtp_port'])
                ->setSmtpUser($option_arr['o_smtp_user'])
                ->setSmtpPass($option_arr['o_smtp_pass'])
            ;
        }
        $Email->setContentType('text/html');
        $tokens = pjAppController::getData($option_arr, $booking_arr, PJ_SALT,$locale_id);
        $pjMultiLangModel = pjMultiLangModel::factory();
        $pjBookingPassengerModel = pjBookingPassengerModel::factory();
        $main_passenger_arr = $pjBookingPassengerModel
            ->where('t1.booking_id', $booking_arr['id'])
            ->where('t1.email IS NOT NULL')
            ->limit(1)
            ->findAll()
            ->getData();
        $main_passenger_email = '';
        if ($main_passenger_arr) {
            $main_passenger_email = $main_passenger_arr[0]['email'];
        }


        $admin_email = $this->getAdminEmail();
        $admin_emails = $this->getAllEmails();
        $admin_phones = $this->getAllPhones();

        $from_email = $admin_email;
        if(!empty($option_arr['o_sender_email']))
        {
            $from_email = $option_arr['o_sender_email'];
        }

        if ($option_arr['o_email_payment'] == 1 && $opt == 'payment')
        {
            $lang_message = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_email_payment_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            $lang_subject = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_email_payment_subject')
                ->limit(0, 1)
                ->findAll()
                ->getData();

            if (count($lang_message) === 1 && count($lang_subject) === 1)
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                $Email
                    ->setTo($booking_arr['c_email'])
                    ->setFrom($from_email)
                    ->setSubject($lang_subject[0]['content'])
                    ->send(pjUtil::textToHtml($message));

                if (!empty($main_passenger_email)) {
                    $Email
                        ->setTo($main_passenger_email)
                        ->setFrom($from_email)
                        ->setSubject($lang_subject[0]['content'])
                        ->send(pjUtil::textToHtml($message));
                }
            }
        }
        if ($option_arr['o_admin_email_payment'] == 1 && $opt == 'payment')
        {	
                $lang_message = $pjMultiLangModel
                    ->reset()
                    ->select('t1.*')
                    ->where('t1.model','pjOption')
                    ->where('t1.locale', $locale_id)
                    ->where('t1.field', 'o_admin_email_payment_message')
                    ->limit(0, 1)
                    ->findAll()
                    ->getData();
                $lang_subject = $pjMultiLangModel
                    ->reset()
                    ->select('t1.*')
                    ->where('t1.model','pjOption')
                    ->where('t1.locale', $locale_id)
                    ->where('t1.field', 'o_admin_email_payment_subject')
                    ->limit(0, 1)
                    ->findAll()
                    ->getData();

                if (count($lang_message) === 1 && count($lang_subject) === 1 && !empty($admin_emails))
                {
                    $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                    $message = pjUtil::textToHtml($message);
                    $subject = $lang_subject[0]['content'];

                    foreach($admin_emails as $email)
                    {
                        $Email
                            ->setTo($email)
                            ->setFrom($from_email)
                            ->setSubject($subject)
                            ->send($message);
                    }
                }
        }
        if(!empty($admin_phones))
        {
            $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_admin_sms_payment_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            if (count($lang_message) === 1 && !empty($admin_phones))
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                $params = array(
                    'text' => $message,
                    'type' => 'unicode',
                    'key' => md5($option_arr['private_key'] . PJ_SALT)
                );
                foreach($admin_phones as $phone)
                {
                    $params['number'] = $phone;
                    $this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                }
            }
        }

        if ($option_arr['o_email_confirmation'] == 1 && $opt == 'confirm')
        {
            $lang_message = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_email_confirmation_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            $lang_subject = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_email_confirmation_subject')
                ->limit(0, 1)
                ->findAll()
                ->getData();

            if (count($lang_message) === 1 && count($lang_subject) === 1)
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                $Email
                    ->setTo($booking_arr['c_email'])
                    ->setFrom($from_email)
                    ->setSubject($lang_subject[0]['content'])
                    ->send(pjUtil::textToHtml($message));

                if (!empty($main_passenger_email)) {
                    $Email
                        ->setTo($main_passenger_email)
                        ->setFrom($from_email)
                        ->setSubject($lang_subject[0]['content'])
                        ->send(pjUtil::textToHtml($message));
                }
            }
        }
        if ($option_arr['o_admin_email_confirmation'] == 1 && $opt == 'confirm' && !empty($admin_emails))
        {	
            $lang_message = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_admin_email_confirmation_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            $lang_subject = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_admin_email_confirmation_subject')
                ->limit(0, 1)
                ->findAll()
                ->getData();

            if (count($lang_message) === 1 && count($lang_subject) === 1)
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                foreach($admin_emails as $email)
                {
                    $Email
                        ->setTo($email)
                        ->setFrom($from_email)
                        ->setSubject($lang_subject[0]['content'])
                        ->send(pjUtil::textToHtml($message));
                }
            }
        }
        if(!empty($admin_phones))
        {
            $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_admin_sms_confirmation_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            if (count($lang_message) === 1)
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                $params = array(
                    'text' => $message,
                    'type' => 'unicode',						
                    'key' => md5($option_arr['private_key'] . PJ_SALT)
                );
                foreach($admin_phones as $phone)
                {
                    $params['number'] = $phone;
                    $this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                }
            }
        }

        if ($option_arr['o_email_cancel'] == 1 && $opt == 'cancel')
        {
            $lang_message = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_email_cancel_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            $lang_subject = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_email_cancel_subject')
                ->limit(0, 1)
                ->findAll()
                ->getData();

            if (count($lang_message) === 1 && count($lang_subject) === 1)
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                $Email
                    ->setTo($booking_arr['c_email'])
                    ->setFrom($from_email)
                    ->setSubject($lang_subject[0]['content'])
                    ->send(pjUtil::textToHtml($message));

                if (!empty($main_passenger_email)) {
                    $Email
                        ->setTo($main_passenger_email)
                        ->setFrom($from_email)
                        ->setSubject($lang_subject[0]['content'])
                        ->send(pjUtil::textToHtml($message));
                }
            }
        }
        if ($option_arr['o_admin_email_cancel'] == 1 && $opt == 'cancel' && !empty($admin_emails))
        {	
            $lang_message = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_admin_email_cancel_message')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            $lang_subject = $pjMultiLangModel
                ->reset()
                ->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $locale_id)
                ->where('t1.field', 'o_admin_email_cancel_subject')
                ->limit(0, 1)
                ->findAll()
                ->getData();

            if (count($lang_message) === 1 && count($lang_subject) === 1)
            {
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                foreach($admin_emails as $email)
                {
                    $Email
                        ->setTo($email)
                        ->setFrom($from_email)
                        ->setSubject($lang_subject[0]['content'])
                        ->send(pjUtil::textToHtml($message));
                }
            }
        }
    }

    public function pjActionCetCurrency()
    {
        $this->setAjax(true);
        if (isset($_GET['currency']) && array_key_exists($_GET['currency'], $this->defaultTicketCurrencies)) {
            $_SESSION[$this->defaultFrontTicketCurrency] = $_GET['currency'];
        }
        exit;
    }
        
    public function checkIsBusAvailable($pickup_id,$return_id,array $bus_id_arr,$booking_period,$booked_data, $date,$is_return)
    {
        if(!empty($bus_id_arr)){
            $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, $is_return);
            if(!empty($bus_list)){//a bus is available
                $minSeats = getMinQuant($bus_list['bus_arr'],'seats_available');
                return (0 === $minSeats) ? false : $minSeats;//check the bus has available seats
            }else{
                return false;
            }
        }else{
            return false;
        }
    }  
    
    public function getBookingInfo($booking_id)
    {
        $_arr = pjBookingModel::factory()
            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer' )
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->locId . "'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->locId . "'", 'left outer' )
            ->join ('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->locId . "'", 'left outer')
            ->find($booking_id)
            ->getData();

        $_arr['tickets'] = pjBookingTicketModel::factory()
            ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->locId . "'", 'left outer' )
            ->join('pjTicket', "t3.id=t1.ticket_id", 'left' )
            ->select('t1.*, t2.content as title' )
            ->where('booking_id',$_arr['id'] )
            ->findAll()
            ->getData();

        return (0 !== sizeof($_arr)) ? $_arr : false;
    }
    
    public function formFromToLoc($pickup_id,$return_id)
    {
        $pjCityModel = pjCityModel::factory();
        $pickup_location = $pjCityModel
            ->reset()
            ->select('t1.*, t2.content as name')
            ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->locId . "'", 'left outer')
            ->limit(1)
            ->find($pickup_id)
            ->getData();

        $return_location = $pjCityModel
            ->reset()
            ->select('t1.*, t2.content as name')
            ->join('pjMultiLang',"t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->locId . "'", 'left outer')
            ->limit(1)
            ->find($return_id)
            ->getData();

        $this->formFromToLoc['from_location'] = $pickup_location ['name'];
        $this->formFromToLoc['to_location'] = $return_location ['name'];
        
        return $this;        
    }
    
    public function getFromToLoc()
    {
        return $this->formFromToLoc;
    }
    
    public function getPassangerData($bus_id,$bus_arr,$date,$uuid,$pickup_id,$return_id,$fromToLocArr)
    {
        $STORE = @$_SESSION [$this->defaultStore];
        $pjBusLocationModel = pjBusLocationModel::factory();
        
        if (!empty($bus_arr['departure_time']) && !empty($bus_arr['arrival_time'])) {
            $depart_arrive = pjUtil::formatTime($bus_arr['departure_time'], "H:i:s", $this->option_arr ['o_time_format'] ) . ' - ' . pjUtil::formatTime ( $bus_arr ['arrival_time'], "H:i:s", $this->option_arr ['o_time_format'] );
        }

        $data['currency'] = $_SESSION[$this->defaultFrontTicketCurrency];
        $data['bus_id'] = $bus_id;
        $data['uuid'] = $uuid;
        $data['ip'] = pjUtil::getClientIp();
        $data['booking_date'] = pjUtil::formatDate($date, $this->option_arr['o_date_format']);
        $data['booking_datetime'] = $data['booking_date'];
        if(isset($STORE['booking_period'][$bus_id])) {
            $data['booking_datetime'] = $STORE['booking_period'][$bus_id]['departure_time'];
            $data['stop_datetime'] = $STORE['booking_period'][$bus_id]['arrival_time'];
        }
        $data['status'] = $this->option_arr['o_booking_status'];

        $pickup_arr = $pjBusLocationModel->getLocInfo($bus_id,$pickup_id);
        if (count($pickup_arr) > 0) {
            $bt_arr [] = pjUtil::formatTime($pickup_arr[0]['departure_time'],"H:i:s",$this->option_arr['o_time_format']);
            $data['booking_datetime'] .= ' ' . $pickup_arr[0]['departure_time'];
        }
        $return_arr = $pjBusLocationModel->getLocInfo($bus_id,$return_id);
        if (count($return_arr) > 0) {
            $bt_arr[] = pjUtil::formatTime($return_arr[0]['arrival_time'],"H:i:s",$this->option_arr['o_time_format']);
        }
        $data['booking_time'] = join(" - ",$bt_arr);
        $data['pickup_id'] = $pickup_id;
        $data['return_id'] = $return_id;
        $data['booking_route'] = $bus_arr['route'] . ', ' . $depart_arrive . '<br/>';
        $data['booking_route'] .= __ ( 'front_from', true, false ) . ' ' . $fromToLocArr['from_location'] . ' ' . __ ( 'front_to', true, false ) . ' ' . $fromToLocArr['to_location'];

        $FORM = @$_SESSION[$this->defaultForm];
        if(isset($FORM['payment_method'])) {
            if ($FORM['payment_method'] && $FORM['payment_method'] == 'creditcard') {
                $data['cc_exp'] = $FORM['cc_exp_year'] . '-' . $FORM['cc_exp_month'];
            }
        }
        
        return $data;
    }
    
    public function getPaymentData($booking_arr,$payment)
    {
        $FORM = @$_SESSION[$this->defaultForm];
        if(isset($FORM['payment_method'])) {
            if ($FORM['payment_method']) {
                $payment = $FORM['payment_method'];
            }
        }

        $payment_data = array();
        $payment_data['booking_id'] = $booking_arr['id'];
        $payment_data['payment_method'] = $payment;
        $payment_data['payment_type'] = 'online';
        $payment_data['amount'] = $booking_arr['deposit'];
        $payment_data['status'] = 'notpaid';
        
        return $payment_data;
    }
}
?>