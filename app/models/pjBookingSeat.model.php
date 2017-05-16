<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBookingSeatModel extends pjAppModel
{
		
	protected $table = 'bookings_seats';
	
	protected $schema = array(
            array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'seat_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'ticket_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'start_location_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'end_location_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'is_return', 'type' => 'enum', 'default' => 'F')
	);
	
	public static function factory($attr=array())
	{
            return new pjBookingSeatModel($attr);
	}
        
        public function multiInsert(array $ticket_arr,array $booked_data,$booking_id,$is_return,array $seat_id_arr, array $location_pair){
            $prefix = ('T' == $is_return) ? 'return_' : '';
            $pickup_ticket_arr = array();
            foreach ($location_pair as $pair) {
		$_arr = explode ("-", $pair);
                $k = 0;
                foreach($ticket_arr as $v) {
                    if(isset($booked_data[$prefix.'ticket_cnt_' . $v['ticket_id']]) && $booked_data[$prefix.'ticket_cnt_' . $v['ticket_id']] > 0) {
                        $qty = $booked_data[$prefix.'ticket_cnt_' . $v['ticket_id']];
                        if ($qty > 0) {
                            for($i=1; $i <= $qty; $i++) {
                                $data = array ();
                                $data['booking_id'] = $booking_id;
                                $data['seat_id'] = $seat_id_arr[$k];
                                $data['ticket_id'] = $v['ticket_id'];
                                $data['start_location_id'] = $_arr[0];
                                $data['end_location_id'] = $_arr[1];
                                $data['is_return'] = $is_return;
                                $this
                                    ->reset()
                                    ->setAttributes($data)
                                    ->insert();
                                $pickup_ticket_arr[$seat_id_arr[$k]] = $v['ticket_id'];
                                $k ++;
                            }
                        }
                    }
                }
            }
            
            return $pickup_ticket_arr;
        }
}
?>