<?php
$front_messages = __('front_messages', true, false);

switch ($tpl['arr']['payment_method'])
{
	case 'paypal':
		?><div class="bsSystemMessage"><?php echo $front_messages[1]; ?></div><?php
		if (pjObject::getPlugin('pjPaypal') !== NULL)
		{
			$controller->requestAction(array('controller' => 'pjPaypal', 'action' => 'pjActionForm', 'params' => $tpl['params']));
		}
		break;
	case 'authorize':
		?><div class="bsSystemMessage"><?php echo $front_messages[2]; ?></div><?php
		if (pjObject::getPlugin('pjAuthorize') !== NULL)
		{
			$controller->requestAction(array('controller' => 'pjAuthorize', 'action' => 'pjActionForm', 'params' => $tpl['params']));
		}
		break;
	case 'bank':
		?>
		<div class="bsSystemMessage">
			<?php
			$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
			$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
			echo $system_msg; 
			?>
			<br /><br />
			<?php echo pjSanitize::html(nl2br($tpl['option_arr']['o_bank_account'])); ?>
		</div>
		<?php
		break;
	case 'creditcard':
	case 'cash':
	default:
		?>
		<div class="bsSystemMessage">
			<?php
			$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
			$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
			echo $system_msg; 
			?>
		</div>
		<?php
}

?>