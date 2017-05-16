<?
$STORE = @$_SESSION[$controller->defaultStore];
$FORM = @$_SESSION[$controller->defaultForm];
$booked_data = $STORE['booked_data'];

$arrData = array(2,3);

$sub_total = 0;
$tax = 0;
$total = 0;
$deposit = 0;

$return_sub_total = 0;
$return_tax = 0;
$return_total = 0;
$return_deposit = 0;

$front_messages = __('front_messages', true, false);
?>
<div class="panel panel-default pjBsMain">
    <?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';?>
    <div class="panel-body pjBsBody">
        <?if($tpl['status'] == 'OK'){?>
            <div class="pjBsForm pjBsFormCheckout">
                <form id="bsPreviewForm_<?= $_GET['index'];?>" action="" method="post">
                    <input type="hidden" name="step_preview" value="1" />
                    <?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/booking_details.php';?>

                    <div class="pjBsFormBody">
                        <? if(!empty($tpl['selected_seat_arr'])) { ?>
                            <p class="pjBsFormTitle"><? __('front_label_passengers_details');?></p>
                            <?$selected_seat_arr = $tpl['selected_seat_arr'];
                            $pickup_seats = true;
                            include PJ_VIEWS_PATH . 'pjFrontEnd/elements/passengers.php';?>
                        <?}?>
                        <?// if(!empty($tpl['return_selected_seat_arr'])) { ?>
                            <!--
                            <p class="pjBsFormTitle"><? __('front_label_return_passengers_details');?></p>
                            -->
                            <?//$selected_seat_arr = $tpl['return_selected_seat_arr'];
                            //$return_seats = true;
                            //include PJ_VIEWS_PATH . 'pjFrontEnd/elements/passengers.php';?>
                        <?//}?>

                        <p class="pjBsFormTitle"><? __('front_personal_details');?></p>
                        <ul class="list-unstyled pjBsListPersonalData">
                            <?if (in_array($tpl['option_arr']['o_bf_include_title'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_title'); ?>: </dt>
                                        <dd>
                                            <?
                                            $name_titles = __('personal_titles', true, false);
                                            echo @$name_titles[$FORM['c_title']];
                                            ?>
                                        </dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_fname'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_fname'); ?>: </dt>
                                        <dd><?= isset($FORM['c_fname']) ? pjSanitize::clean($FORM['c_fname']) : null;?></dd>
                                    </dl>
                                </li>
                            <?} 
                            if (in_array($tpl['option_arr']['o_bf_include_lname'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_lname'); ?>: </dt>
                                        <dd><?= isset($FORM['c_lname']) ? pjSanitize::clean($FORM['c_lname']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_phone'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                            <dt><? __('front_label_phone'); ?>: </dt>
                                            <dd><?= isset($FORM['c_phone']) ? pjSanitize::clean($FORM['c_phone']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_email'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_email'); ?>: </dt>
                                        <dd><?= isset($FORM['c_email']) ? pjSanitize::clean($FORM['c_email']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_company'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_company'); ?>: </dt>
                                        <dd><?= isset($FORM['c_company']) ? pjSanitize::clean($FORM['c_company']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_notes'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_notes'); ?>: </dt>
                                        <dd><?= isset($FORM['c_notes']) ? nl2br(pjSanitize::clean($FORM['c_notes'])) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_address'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_address');?>: </dt>
                                        <dd><?= isset($FORM['c_address']) ? pjSanitize::clean($FORM['c_address']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_city'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_city'); ?>: </dt>
                                        <dd><?= isset($FORM['c_city']) ? pjSanitize::clean($FORM['c_city']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_state'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_state'); ?>: </dt>
                                        <dd><?= isset($FORM['c_state']) ? pjSanitize::clean($FORM['c_state']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_zip'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_zip'); ?>: </dt>
                                        <dd><?= isset($FORM['c_zip']) ? pjSanitize::clean($FORM['c_zip']) : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if (in_array($tpl['option_arr']['o_bf_include_country'], $arrData))
                            {?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_country'); ?>: </dt>
                                        <dd><?= !empty($tpl['country_arr']) ? $tpl['country_arr']['country_title'] : null;?></dd>
                                    </dl>
                                </li>
                            <?}
                            if($tpl['option_arr']['o_payment_disable'] == 'No')
                            { 
                                ?>
                                <li>
                                    <dl class="dl-horizontal">
                                        <dt><? __('front_label_payment_medthod'); ?>: </dt>
                                        <dd>
                                            <? 
                                            $payment_methods = __('payment_methods', true, false);
                                            echo $payment_methods[$FORM['payment_method']];
                                            ?>
                                        </dd>
                                    </dl>
                                </li>
                                <div id="bsCCData_<?= $_GET['index'];?>" style="display: <?= isset($FORM['payment_method']) && $FORM['payment_method'] == 'creditcard' ? 'block' : 'none'; ?>">
                                    <li>
                                        <dl class="dl-horizontal">
                                            <dt><? __('front_label_cc_type'); ?>: </dt>
                                            <dd>
                                                <? 
                                                $cc_types = __('cc_types', true, false);
                                                echo $cc_types[$FORM['cc_type']];
                                                ?>
                                            </dd>
                                        </dl>
                                    </li>
                                    <li>
                                        <dl class="dl-horizontal">
                                            <dt><? __('front_label_cc_num'); ?>: </dt>
                                            <dd><?= isset($FORM['cc_num']) ? pjSanitize::clean($FORM['cc_num']) : null;?></dd>
                                        </dl>
                                    </li>
                                    <li>
                                        <dl class="dl-horizontal">
                                            <dt><? __('front_label_cc_exp'); ?>: </dt>
                                            <dd>
                                                <?
                                                $month_arr = __('months', true, false);
                                                ksort($month_arr);
                                                echo $month_arr[$FORM['cc_exp_month']] . '-' . $FORM['cc_exp_year'];
                                                ?>
                                            </dd>
                                        </dl>
                                    </li>
                                    <li>
                                        <dl class="dl-horizontal">
                                            <dt><? __('front_label_cc_code'); ?>: </dt>
                                            <dd><?= isset($FORM['cc_code']) ? pjSanitize::clean($FORM['cc_code']) : null;?></dd>
                                        </dl>
                                    </li>
                                </div>
                            <?}?>
                        </ul>
                    </div>
                </form>

                <footer class="pjBsFormFoot">
                    <div class="clearfix pjBsFormMessages" style="display: none;">
                        <div id="bsBookingMsg_<?= $_GET['index']?>" class="text-success pjBrBookingMsg"></div>
                    </div>
                    <div class="clearfix pjBsFormActions">
                        <a href="#" id="bsBtnBack4_<?= $_GET['index'];?>" class="btn btn-default pull-left"><?__('front_button_back');?></a>
                        <button type="button" id="bsBtnConfirm_<?= $_GET['index'];?>" class="btn btn-primary pull-right"><? __('front_button_confirm'); ?></button>
                    </div>
                </footer>

                <input type="hidden" id="bsDate_<?= $_GET['index'];?>" value="<?= $STORE['date'];?>" />
                <input type="hidden" id="bsPickupId_<?= $_GET['index'];?>" value="<?=$STORE['pickup_id'];?>" />
                <input type="hidden" id="bsReturnId_<?= $_GET['index'];?>" value="<?=$STORE['return_id'];?>" />
                <?
                $failed_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[6]);
                $failed_msg = str_replace("[ETAG]", "</a>", $failed_msg);  
                ?>
                <input type="hidden" id="bsFailMessage_<?= $_GET['index'];?>" value="<?= $failed_msg;?>" />
            </div>
        <?} else {?>
            <div>
                <?$front_messages = __('front_messages', true, false);
                $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[5]);
                $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
                echo $system_msg;?>
            </div>
        <?}?>
    </div>
</div>