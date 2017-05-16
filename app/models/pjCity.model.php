<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCityModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'cities';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'dest_cities_list_id', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new pjCityModel($attr);
	}
        
        public function getCityById($city_id)
	{
            $arr = $this
                ->reset()
                ->where('id', $city_id)
                ->findAll()
                ->getData();
            return !empty($arr) ? $arr[0] : null;
	}
        
        public function getDestCities($cityId, $locId, $delimeter){
            $location_arr = array();
            $depCity = $this->getCityById($cityId);
            if('' != $depCity['dest_cities_list_id']){
                $arDepCityId = explode($delimeter,$depCity['dest_cities_list_id']);
                $location_arr = $this
                    ->reset()
                    ->select('t1.*, t2.content as name,t2.locale')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$locId."'", 'left outer')
                    ->whereIn('t1.id',$arDepCityId)
                    ->where('status','T')
                    ->orderBy('t2.content ASC')
                    ->findAll()
                    ->getData();

                usort($location_arr,'sortCallBack');//use custom sorting
            }
            return $location_arr;
        }
}
?>