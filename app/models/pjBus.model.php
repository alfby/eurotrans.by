<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjBusModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'buses';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'route_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_type_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'start_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'end_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'departure_time', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'arrival_time', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'recurring', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'set_seats_count', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'discount', 'type' => 'decimal', 'default' => '0')
	);
	
	public static function factory($attr=array())
	{
		return new pjBusModel($attr);
	}
	
	public function getBusIds($date, $pickup_id, $return_id, $check_transfer=false)
	{
            $bus_id_arr = array();
            $day_of_week = strtolower(date('l', strtotime($date)));
            $this
                ->reset()
                ->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
                ->where("t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE TRD.from_location_id = $pickup_id AND TRD.to_location_id = $return_id)");
            
            $routeCityTable = pjRouteCityModel::factory()->getTable();
            
            if ($check_transfer) {
                $this->where("t1.route_id IN(SELECT TRC.route_id FROM `".$routeCityTable."` AS TRC WHERE TRC.city_id = $return_id AND TRC.is_arrival=1)");
            } else {
                //$this->where("t1.route_id IN(SELECT TRC.route_id FROM `".$routeCityTable."` AS TRC WHERE TRC.city_id = $return_id AND TRC.is_arrival=1)");
                $this->where("t1.route_id IN(SELECT TRC.route_id FROM `".$routeCityTable."` AS TRC WHERE TRC.city_id = $return_id AND TRC.is_arrival=1)");
            }
            
            $bus_id_arr = $this->findAll()->getDataPair(null, 'id');
            return $bus_id_arr;
	}
}
?>