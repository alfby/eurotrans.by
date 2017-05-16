<?
$STORE = @$_SESSION[$controller->defaultStore];
$FORM = @$_SESSION[$controller->defaultForm];
$booked_data = $STORE['booked_data'];
$pathToViewElements = PJ_VIEWS_PATH . 'pjFrontPublic/elements/';
?>
<div class="panel panel-default pjBsMain">
    <?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';?>
    <div class="panel-body pjBsBody">
        <?if($tpl['status'] == 'OK'){?>
            <div class="pjBsForm pjBsFormCheckout">
                <form id="bsCheckoutForm_<?=$_GET['index'];?>" action="" method="post" class="bsCheckoutForm" data-toggle="validator" role="form">
                    <input type="hidden" name="step_checkout" value="1" />
                    <?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/booking_details.php';?>

                    <div class="pjBsFormBody">
                        <?if(!empty($tpl['selected_seat_arr'])) {?>
                            <p class="pjBsFormTitle"><? __('front_label_passengers_details');?></p>
                            <?
                            $selected_seat_arr = $tpl['selected_seat_arr'];
                            $pickup_seats = true;
                            include PJ_VIEWS_PATH . 'pjFrontEnd/elements/passengers_form.php';
                        }?>

                        <?if(!empty($tpl['return_selected_seat_arr'])) { ?>
<!--
                            <p class="pjBsFormTitle"><? __('front_label_return_passengers_details');?></p>
-->
                            <?
                            //$selected_seat_arr = $tpl['return_selected_seat_arr'];
                            //$return_seats = true;
                            //include PJ_VIEWS_PATH . 'pjFrontEnd/elements/passengers_form.php';
                        }
                        include $pathToViewElements.'formHorizontal.php';?>
                    </div>

                    <footer class="pjBsFormFoot">
                        <div class="form-group">
                            <p class="pjBsFormTitle"><?__('front_label_terms_conditions');?></p>

                            <div class="checkbox">
                                <label>
                                    <input id="bsAgree_<?=$_GET['index']?>" name="agreement" type="checkbox" checked="checked" />&nbsp;<? __('front_label_agree');?>&nbsp;
                                    <?if(!empty($tpl['terms_conditions'])) {?>
                                        <a href="#" data-toggle="modal" data-target="#pjBsModalTerms"><? __('front_label_terms_conditions');?></a>
                                    <?}else{?>
                                        <? __('front_label_agree');?>&nbsp;<? __('front_label_terms_conditions');?>
                                    <?}?>
                                </label>
                            </div>

                            <div class="help-block with-errors">
                                <ul class="list-unstyled"></ul>
                            </div>
                        </div>

                        <div class="clearfix pjBsFormActions">
                            <a href="#" id="bsBtnBack3_<?=$_GET['index'];?>" class="btn btn-default pull-left"><? __('front_button_back'); ?></a>
                            <button type="button" id="bsBtnPreview_<?=$_GET['index'];?>" class="btn btn-primary pull-right"><? __('front_button_preview'); ?></button>
                        </div>
                    </footer>
                </form>

                <?include $pathToViewElements.'checkoutModal.php';?>
            </div>
            <?} else {
                include $pathToViewElements.'messageBlock.php';
            }?>
    </div>
</div>