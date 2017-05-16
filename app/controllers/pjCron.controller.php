<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCron extends pjAppController
{
	public function pjActionIndex()
	{
		$this->setLayout('pjActionEmpty');
		
		$option_arr = pjOptionModel::factory()->getPairs($this->getForeignId());
		$pjBookingModel = pjBookingModel::factory();
		$arr = $pjBookingModel
			->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
			->join('pjBus', "t2.id=t1.bus_id", 'left outer')
			->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
			->where('status', 'pending')
			->where('is_sent', 'F')
			->where("(UNIX_TIMESTAMP(t1.created) < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$option_arr['o_min_hour']." MINUTE)))")
			->findAll()
			->getData();
		$pjBookingTicketModel = pjBookingTicketModel::factory();
		$pjPriceModel = pjPriceModel::factory();
		$pjMultiLangModel = pjMultiLangModel::factory();
		
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
		
		$lang_message = $pjMultiLangModel->reset()->select('t1.*')
										 ->where('t1.model','pjOption')
										 ->where('t1.locale', $this->getLocaleId())
										 ->where('t1.field', 'o_email_notify_message')
										 ->limit(0, 1)
										 ->findAll()->getData();
		$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
										 ->where('t1.model','pjOption')
										 ->where('t1.locale', $this->getLocaleId())
										 ->where('t1.field', 'o_email_notify_subject')
										 ->limit(0, 1)
										 ->findAll()->getData();
		
		$admin_email = $this->getAdminEmail();			
		foreach($arr as $k => $v)
		{
			$v['tickets'] = $pjBookingTicketModel
				->reset()
				->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjTicket', "t3.id=t1.ticket_id", 'left')
				->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$pjPriceModel->getTable().'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$v['bus_id'].' AND TP.from_location_id = '.$v['pickup_id'].' AND TP.to_location_id= '.$v['return_id']. ') as price')
				->where('booking_id', $v['id'])
				->findAll()
				->getData();
			
			$tokens = pjAppController::getData($option_arr, $v, PJ_SALT, $this->getLocaleId());
			$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
		
			if ($option_arr['o_email_notify'] == 1)
			{
				if (count($lang_message) === 1 && count($lang_subject) === 1)
				{
					$Email
						->setTo($v['c_email'])
						->setFrom($admin_email)
						->setSubject($lang_subject[0]['content'])
						->send(pjUtil::textToHtml($message));
					$pjBookingModel->reset()->where('id', $v['id'])->limit(1)->modifyAll(array('is_sent' => 'T'));
				}
			}
		}
		__('lblCronJobCompleted');
		exit;
	}
}
?>