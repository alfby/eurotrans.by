<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjRouteCityModel extends pjAppModel
{
	protected $table = 'routes_cities';
	
	protected $schema = array(
		array('name' => 'route_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'city_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'order', 'type' => 'tinyint', 'default' => ':NULL'),
		array('name' => 'is_departure', 'type' => 'tinyint', 'default' => ':NULL'),
		array('name' => 'is_arrival', 'type' => 'tinyint', 'default' => ':NULL'),
		//array('name' => 'exclude_from_search', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjRouteCityModel($attr);
	}
	
	public function getCity($route_id, $order)
	{
		$arr = $this
			->reset()
			->where('route_id', $route_id)
			->orderBy("`order` $order")
			->findAll()
			->getDataPair('order', 'city_id');
		return $arr;
	}
        
	public function getOrder($route_id, $city_id)
	{
		$arr = $this
			->reset()
			->where('route_id', $route_id)
			->where('city_id', $city_id)
			->findAll()
			->getData();
		return !empty($arr) ? $arr[0]['order'] : null;
	}
	
	public function getLocations($route_id, $pickup_id, $return_id)
	{
		$location_arr = array();

		$from_order = $this->getOrder($route_id, $pickup_id);
		$to_order = $this->getOrder($route_id, $return_id);
			
		if($from_order != null && $to_order != null)
		{				
			$location_arr = $this
				->reset()
				->where('route_id', $route_id)
				->where("($from_order <= t1.order AND t1.order <= $to_order)")
				->findAll()
				->getData();
		}
		return $location_arr;
	}
	
	public function getLocationIdPair($route_id, $pickup_id, $return_id)
	{
		$location_id_arr = array();
		$from_order = $this->getOrder($route_id, $pickup_id);
		$to_order = $this->getOrder($route_id, $return_id);	
		if($from_order != null && $to_order != null) {
			$location_id_arr = $this
				->reset()
				->where('route_id', $route_id)
				->where("($from_order <= t1.order AND t1.order <= $to_order)")
				->findAll()
				->getDataPair("city_id", "city_id");
		}
		return $location_id_arr;
	}
        
        public function getAvLoc2Dep($pickup_city_id){
            
        }
        /*
        public function getAvLoc2Dep($pickup_city_id,$locale_id,$recursively = false)
        {
            $avRoutesFromCity2Depart = $this
                ->reset()
                ->select('route_id,city_id,`order`')
                ->where('city_id='.$pickup_city_id)
                ->where('`is_departure`',1)
                ->orderBy('route_id ASC')
                ->findAll()
                ->getData();

            $arResult = array();
            $arCities = array();
            foreach($avRoutesFromCity2Depart as $cityDep)
            {
                $nextCities = $this
                    ->reset()
                    ->select('t1.city_id,t2.content as name')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$locale_id."'", 'left outer')
                    ->where('`is_arrival`',1)
                    ->where('route_id',$cityDep['route_id'])
                    ->where('`order`>'.$cityDep['order'])
                    ->orderBy('t2.content ASC')
                    ->findAll()
                    ->getData();

                $arCities = getUniqueCitiesArray($nextCities,'city_id',$arCities);
                $arResult = $arCities;
                if($recursively && !empty($arCities)):
                    foreach($arCities as $id => $arCity):
                        $arAddCities = $this->getAvLoc2Dep($id,$locale_id);
                        $arResult = getUniqueCitiesArray($arAddCities,'id',$arResult);
                    endforeach;
                endif;
            }
                
            if(isset($arResult[$pickup_city_id])):
                unset($arResult[$pickup_city_id]);
            endif;
            
            return $arResult;
        }
        */
}
?>