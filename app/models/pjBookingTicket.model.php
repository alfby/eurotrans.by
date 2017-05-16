<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBookingTicketModel extends pjAppModel
{
	protected $table = 'bookings_tickets';
	
	protected $schema = array(
            array('name' => 'booking_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'ticket_id', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'qty', 'type' => 'int', 'default' => ':NULL'),
            array('name' => 'amount', 'type' => 'decimal', 'default' => ':NULL'),
            array('name' => 'is_return', 'type' => 'enum', 'default' => 'F')
	);
	
	public static function factory($attr=array())
	{
            return new pjBookingTicketModel($attr);
	}
        
        public function multiInsert(array $ticket_arr,$booked_data,$booking_id,$field_price,$is_return){
            $prefix = ('T'== $is_return) ? 'return_' : '';
            
            foreach ($ticket_arr as $v ) {
                if (isset($booked_data[$prefix.'ticket_cnt_' . $v ['ticket_id']]) && $booked_data[$prefix.'ticket_cnt_' . $v['ticket_id']] > 0) {
                    $data = array ();
                    $data['booking_id'] = $booking_id;
                    $data['ticket_id'] = $v['ticket_id'];
                    $data['qty'] = $booked_data[$prefix.'ticket_cnt_' . $v['ticket_id']];
                    $data['amount'] = $data['qty'] * $v[$field_price];
                    $data['is_return'] = $is_return;
                    $this
                        ->reset()
                        ->setAttributes($data)
                        ->insert();
                }
            }

            return isset($data) ? true: false;
        }
}
?>