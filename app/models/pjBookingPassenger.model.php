<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBookingPassengerModel extends pjAppModel
{
    protected $table = 'bookings_passengers';
    public $dataArr = array();
    
    protected $schema = array(
            array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'seat_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'ticket_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'first_name', 'type' => 'varchar', 'default' => ':NULL'),
            array('name' => 'last_name', 'type' => 'varchar', 'default' => ':NULL'),
            array('name' => 'phone_1', 'type' => 'varchar', 'default' => ':NULL'),
            array('name' => 'phone_2', 'type' => 'varchar', 'default' => ':NULL'),
            array('name' => 'passpor_id', 'type' => 'varchar', 'default' => ':NULL'),
            array('name' => 'country_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL')
    );

    public static function factory($attr=array())
    {
        return new pjBookingPassengerModel($attr);
    }

    public function multiInsert(){
        foreach($this->dataArr as $data){
            $this
                ->reset()
                ->setAttributes($data)
                ->insert();
        }
        
        return $this;
    }

    public function prepareData(array $seat_id_arr, array $pickup_ticket_arr, $FORM, $booking_id, $is_return){
        $prefix = ('T' == $is_return) ? 'r_': 'p_';

        foreach ($seat_id_arr as $k => $seat_id) {
            $this->dataArr[$k]['booking_id'] = $booking_id;
            $this->dataArr[$k]['seat_id'] = $seat_id;
            $this->dataArr[$k]['ticket_id'] = isset($pickup_ticket_arr[$seat_id]) ? $pickup_ticket_arr[$seat_id]: '';
            $this->dataArr[$k]['first_name'] = $FORM[$prefix.'first_name'][$seat_id] ? $FORM[$prefix.'first_name'][$seat_id] : '';	
            $this->dataArr[$k]['last_name'] = isset($FORM[$prefix.'last_name'][$seat_id]) ? $FORM[$prefix.'last_name'][$seat_id] : '';	
            $this->dataArr[$k]['phone_1'] = isset($FORM[$prefix.'phone_1'][$seat_id]) ? $FORM[$prefix.'phone_1'][$seat_id] : '';
            $this->dataArr[$k]['phone_2'] = isset($FORM[$prefix.'phone_2'][$seat_id]) ? $FORM[$prefix.'phone_2'][$seat_id] : '';	
            $this->dataArr[$k]['passpor_id'] = isset($FORM[$prefix.'passpor_id'][$seat_id]) ? $FORM[$prefix.'passpor_id'][$seat_id] : '';
            $this->dataArr[$k]['country_id'] = isset($FORM[$prefix.'country_id'][$seat_id]) ? $FORM[$prefix.'country_id'][$seat_id] : '';
            $this->dataArr[$k]['email'] = isset($FORM[$prefix.'email'][$seat_id]) ? $FORM[$prefix.'email'][$seat_id] : '';
        }
        
        return $this;
    }
}
?>