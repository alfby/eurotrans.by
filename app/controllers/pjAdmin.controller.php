<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdmin extends pjAppController
{
	public $defaultUser = 'admin_user';
	
	public $requireLogin = true;
	
	public function __construct($requireLogin=null)
	{
		$this->setLayout('pjActionAdmin');
		
		if (!is_null($requireLogin) && is_bool($requireLogin))
		{
			$this->requireLogin = $requireLogin;
		}
		
		if ($this->requireLogin)
		{
			if (!$this->isLoged() && !in_array(@$_GET['action'], array('pjActionLogin', 'pjActionForgot', 'pjActionPreview')))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin");
			}
		}
	}
	
	public function beforeRender()
	{
		
	}

	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isEditor())
		{
			$pjBookingModel = pjBookingModel::factory();
			$pjBusModel = pjBusModel::factory();
			$pjRouteModel = pjRouteModel::factory();
			
			$current_date = date('Y-m-d');
			$weekday = strtolower(date('l'));
			
			$cnt_today_bookings = $pjBookingModel->where("t1.created LIKE '%$current_date%'")->findCount()->getData();
			$this->set('cnt_today_bookings', $cnt_today_bookings);
			
			$cnt_today_departure = 0;
			$next_buses_arr = array();
			$date = $current_date;
			
			$cnt_routes = $pjRouteModel->findCount()->getData();
			$cnt_buses = $pjBusModel->findCount()->getData();
			
			$next_3_months = strtotime('+3 month', strtotime($date));
			
			if($cnt_buses > 0)
			{
				while(count($next_buses_arr) < 5 && strtotime($date) < $next_3_months)
				{
					$bus_arr = $pjBusModel
						->reset()
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->select("t1.*, t2.content AS route,
									(SELECT COUNT(TB.id) 
										FROM `".$pjBookingModel->getTable()."` AS TB 
										WHERE TB.bus_id=t1.id AND TB.booking_date='$date') AS total_bookings,
									(SELECT SUM(TBT.qty) 
										FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
										WHERE TBT.booking_id IN 
												(SELECT TB1.id 
												FROM `".$pjBookingModel->getTable()."` AS TB1
												WHERE TB1.bus_id=t1.id AND TB1.booking_date='$date')) AS total_tickets")
						->where("(t1.start_date <= '$date' AND t1.end_date >= '$date')")
						->orderBy("departure_time ASC")
						->findAll()
						->getData();
					foreach($bus_arr as $v)
					{
						if(empty($v['recurring']))
						{
							if($date == $current_date)
							{
								$cnt_today_departure++;
							}
							if(count($next_buses_arr) < 5 && strtotime(date('Y-m-d H:i:s')) <= strtotime($date . ' ' . $v['departure_time']))
							{
								$v['departure_date'] = $date;
								$next_buses_arr[] = $v;
							}
						}else{
							if(in_array($weekday, explode("|", $v['recurring'])))
							{
								if($date == $current_date)
								{
									$cnt_today_departure++;
								}
								if(count($next_buses_arr) < 5 && strtotime(date('Y-m-d H:i:s')) <= strtotime($date . ' ' . $v['departure_time']))
								{
									$v['departure_date'] = $date;
									$next_buses_arr[] = $v;
								}
							}
						}
					}
					$date = date('Y-m-d', strtotime($date . ' + 1 day'));
				}
			}
			$this->set('cnt_today_departure', $cnt_today_departure);
			
			$this->set('cnt_routes', $cnt_routes);
			$this->set('cnt_buses', $cnt_buses);
			
			$latest_bookings = $pjBookingModel
				->reset()
				->select("t1.*, (SELECT SUM(TBT.qty) 
								FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
								WHERE TBT.booking_id=t1.id)
						 	AS tickets, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location")
			 	->join('pjBus', "t2.id=t1.bus_id", 'left outer')
			 	->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			 	->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
			 	->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
				->limit(5)
				->orderBy('t1.created DESC')
				->findAll()->getData();
			
			$this->set('latest_bookings', $latest_bookings);
			$this->set('next_buses_arr', $next_buses_arr);
			
			$this->set('cnt_bookings', $pjBookingModel->reset()->findCount()->getData());
			$this->set('cnt_confirmed_bookings', $pjBookingModel->reset()->where('t1.status', 'confirmed')->findCount()->getData());
			
			$sold_tickets = pjBookingTicketModel::factory()
				->select("SUM(t1.qty) AS tickets")
				->where("t1.booking_id IN (SELECT TB.id FROM `".$pjBookingModel->getTable()."` AS TB WHERE TB.status='confirmed')")
				->findAll()->getData();
			$this->set('sold_tickets', $sold_tickets);

			$total_revenue = pjBookingModel::factory()
				->select("SUM(t1.total) AS revenue")
				->where('t1.status', 'confirmed')
				->findAll()->getData();
			$this->set('total_revenue', $total_revenue);
						
			$backup_arr = pjAppController::getBackupInfo();
			$this->set('backup_arr', $backup_arr);
			
			$overlapping_seats = pjBookingSeatModel::factory()
				->join("pjBooking", "t1.booking_id = t2.id", 'inner')
				->select("DISTINCT (GROUP_CONCAT( CONCAT_WS(':', t2.id, t2.uuid)SEPARATOR '~:~' )) AS uuid")
				->where("UNIX_TIMESTAMP(t2.booking_datetime) >= UNIX_TIMESTAMP(NOW())")
				->groupBy("t1.seat_id, t1.start_location_id, t1.end_location_id, t2.booking_date, t2.bus_id")
				->having("count(t1.booking_id) > 1")
				->findAll()
				->toArray('uuid', '~:~')
				->getData();
			$this->set('overlapping_seats', $overlapping_seats);	
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionForgot()
	{
		$this->setLayout('pjActionAdminLogin');
		
		if (isset($_POST['forgot_user']))
		{
			if (!isset($_POST['forgot_email']) || !pjValidation::pjActionNotEmpty($_POST['forgot_email']) || !pjValidation::pjActionEmail($_POST['forgot_email']))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=AA10");
			}
			$pjUserModel = pjUserModel::factory();
			$user = $pjUserModel
				->where('t1.email', $_POST['forgot_email'])
				->limit(1)
				->findAll()
				->getData();
				
			if (count($user) != 1)
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=AA10");
			} else {
				$user = $user[0];
				
				$Email = new pjEmail();
				$Email
					->setTo($user['email'])
					->setFrom($user['email'])
					->setSubject(__('emailForgotSubject', true));
				
				if ($this->option_arr['o_send_email'] == 'smtp')
				{
					$Email
						->setTransport('smtp')
						->setSmtpHost($this->option_arr['o_smtp_host'])
						->setSmtpPort($this->option_arr['o_smtp_port'])
						->setSmtpUser($this->option_arr['o_smtp_user'])
						->setSmtpPass($this->option_arr['o_smtp_pass'])
					;
				}
				
				$body = str_replace(
					array('{Name}', '{Password}'),
					array($user['name'], $user['password']),
					__('emailForgotBody', true)
				);

				if ($Email->send($body))
				{
					$err = "AA11";
				} else {
					$err = "AA12";
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=$err");
			}
		} else {
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdmin.js');
		}
	}
	
	public function pjActionMessages()
	{
		$this->setAjax(true);
		header("Content-Type: text/javascript; charset=utf-8");
	}
	
	public function pjActionLogin()
	{
		$this->setLayout('pjActionAdminLogin');
		
		if (isset($_POST['login_user']))
		{
			if (!isset($_POST['login_email']) || !isset($_POST['login_password']) ||
				!pjValidation::pjActionNotEmpty($_POST['login_email']) ||
				!pjValidation::pjActionNotEmpty($_POST['login_password']) ||
				!pjValidation::pjActionEmail($_POST['login_email']))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=4");
			}
			$pjUserModel = pjUserModel::factory();

			$user = $pjUserModel
				->where('t1.email', $_POST['login_email'])
				->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($_POST['login_password']), PJ_SALT))
				->limit(1)
				->findAll()
				->getData();

			if (count($user) != 1)
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=1");
			} else {
				$user = $user[0];
				unset($user['password']);
															
				if (!in_array($user['role_id'], array(1,2,3)))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=2");
				}
				
				if ($user['role_id'] == 3 && $user['is_active'] == 'F')
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=2");
				}
				
				if ($user['status'] != 'T')
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=3");
				}
				
				$last_login = date("Y-m-d H:i:s");
    			$_SESSION[$this->defaultUser] = $user;
    			
    			$data = array();
    			$data['last_login'] = $last_login;
    			$pjUserModel->reset()->setAttributes(array('id' => $user['id']))->modify($data);

    			if ($this->isAdmin() || $this->isEditor())
    			{
	    			pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionIndex");
    			}
			}
		} else {
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdmin.js');
		}
	}
	
	public function pjActionLogout()
	{
		if ($this->isLoged())
        {
        	unset($_SESSION[$this->defaultUser]);
        }
       	pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin");
	}
	
	public function pjActionProfile()
	{
		$this->checkLogin();
		
		if (!$this->isAdmin())
		{
			if (isset($_POST['profile_update']))
			{
				$pjUserModel = pjUserModel::factory();
				$arr = $pjUserModel->find($this->getUserId())->getData();
				$data = array();
				$data['role_id'] = $arr['role_id'];
				$data['status'] = $arr['status'];
				$post = array_merge($_POST, $data);
				if (!$pjUserModel->validates($post))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionProfile&err=AA14");
				}
				$pjUserModel->set('id', $this->getUserId())->modify($post);
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionProfile&err=AA13");
			} else {
				$this->set('arr', pjUserModel::factory()->find($this->getUserId())->getData());
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdmin.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
}
?>