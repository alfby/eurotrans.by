<?
$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);

$STORE = @$_SESSION[$controller->defaultStore];

$months = __('months', true);
$short_months = __('short_months', true);
ksort($months);
ksort($short_months);
$days = __('days', true);
$short_days = __('short_days', true);
?>
1111 
<div class="panel panel-default pjBsMain">
    <?include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';?>
    <div class="panel-body pjBsBody">
        <div class="pjBsForm pjBsFormAvailability">
            <div id="pjBrCalendarLocale" style="display: none;" data-months="<?=implode("_", $months);?>" data-days="<?=implode("_", $short_days);?>"></div>
            <form id="bsSearchForm_<?=$_GET['index'];?>" action="<?=PJ_INSTALL_URL; ?>index.php?controller=pjFront&amp;action=pjActionCheck" method="post" >
                <input type="hidden" id="bsIsReturn_<?=$_GET['index'];?>" name="is_return" value="<?=(isset($STORE['is_return']) && $STORE['is_return'] == 'T' && @$STORE['is_transfer'] == 0)  ? 'T' : 'F';?>" />
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 pjBsFormContent">
                        <div class="btn-group pjBsFormNav">
                                <a href="#" class="btn btn-default pjBrSwitch<?=isset($STORE['is_return']) && $STORE['is_return'] == 'T' && @$STORE['is_transfer'] == 0 ? NULL : ' active';?>" data-return="F"><? __('front_one_way');?></a>
                                <a href="#" class="btn btn-default pjBrSwitch<?=isset($STORE['is_return']) && $STORE['is_return'] == 'T' && @$STORE['is_transfer'] == 0 ? ' active' : NULL;?>" data-return="T"><? __('front_roundtrip');?></a>
                        </div><!-- /.btn-group pjBsFormNav -->
                        <div class="row">
                                <?
                                $one_way = true;
                                $class="col-lg-12 col-md-12 col-sm-12 col-xs-12"; 
                                if(isset($STORE['is_return']) && $STORE['is_return'] == 'T' && $STORE['is_transfer'] == 0)
                                {
                                        $class = "col-lg-6 col-md-6 col-sm-6 col-xs-6";
                                        $one_way = false;
                                }
                                ?>
                                <div class="<?=$class;?>">
                                        <div class="form-group">
                                                <label for=""><? __('front_departing'); ?>: </label>

                                                <div class="input-group pjBsDatePicker pjBsDatePickerFrom">
                                                        <input type="text" id="bsDate_<?=$_GET['index'];?>" name="date" class="form-control required" readonly="readonly" value="<?=isset($STORE) && isset($STORE['date']) ? htmlspecialchars($STORE['date']) : date($tpl['option_arr']['o_date_format']) ; ?>"/>
                                                        <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                                        </span>
                                                </div><!-- /.input-group pjCcDatePicker -->
                                                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                                        </div><!-- /.form-group -->
                                </div><!-- /.col-lg-6 col-md-6 col-sm-6 col-xs-6 -->
                                <?
                                $min_to = isset($STORE['date']) && !empty($STORE['date']) ? $STORE['date'] : date("Y-m-d"); 
                                ?>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="display: <?=$one_way==true? 'none' : 'block';?>">
                                        <div class="form-group">
                                                <label for=""><? __('front_returning'); ?>: </label>

                                                <div class="input-group pjBsDatePicker pjBsDatePickerTo" data-year="<?=date('Y', strtotime($min_to));?>" data-month="<?=date('n', strtotime($min_to));?>" data-day="<?=date('j', strtotime($min_to));?>">
                                                        <input type="text" id="bsReturnDate_<?=$_GET['index'];?>" name="return_date" class="form-control required" readonly="readonly" value="<?=isset($STORE) && isset($STORE['return_date']) ? htmlspecialchars($STORE['return_date']) : date($tpl['option_arr']['o_date_format']) ; ?>"/>
                                                        <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                                        </span>
                                                </div><!-- /.input-group pjCcDatePicker -->
                                                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                                        </div><!-- /.form-group -->
                                </div><!-- /.col-lg-6 col-md-6 col-sm-6 col-xs-6 -->
                        </div><!-- /.row -->

                        <div class="row pjBsSelectsRow">
                            <!--
                            <div class='col-lg-12'>
                                <div class="form-group">
                                    <div id="exchange-checkbox">	
                                        <input name='show_exchanges' type='checkbox'/>
                                        <label for='show_exchanges'>do not show exchanges</label>
                                    </div>                                                                
                                </div>
                            </div>
                            -->

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                                <div class="form-group">
                                    <label for=""><? __('front_label_from'); ?>: </label>
                                    <div id="bsPickupContainer_<?=$_GET['index'];?>">	
                                        <select id="bsPickupId_<?=$_GET['index'];?>" name="pickup_id"  class="form-control pjBsAutocomplete required">
                                            <option value="" disabled selected>-- <? __('front_choose'); ?>--</option>
                                            <?foreach($tpl['from_location_arr'] as $k => $v)
                                            {?>
                                                <option value="<?=$v['id'];?>"<?=isset($STORE['pickup_id']) && $STORE['pickup_id'] == $v['id'] ? ' selected="selected"' : null;?>><?=stripslashes($v['name']);?></option><?
                                            } 
                                            ?>
                                        </select>
                                        <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                                    <div class="form-group">
                                            <label for=""><? __('front_label_to'); ?>: </label>
                                            <div id="bsReturnContainer_<?=$_GET['index'];?>">		
                                                    <?
                                                    if(!isset($tpl['return_location_arr']))
                                                    {
                                                            ?>
                                                            <select id="bsReturnId_<?=$_GET['index'];?>" name="return_id" class="form-control pjBsAutocomplete required">
                                                                    <option value="">-- <? __('front_choose'); ?>--</option>
                                                                    <?
                                                                    foreach($tpl['to_location_arr'] as $k => $v)
                                                                    {
                                                                    ?><option value="<?=$v['id'];?>"<?=isset($STORE['return_id']) && $STORE['return_id'] == $v['id'] ? ' selected="selected"' : null;?>><?=stripslashes($v['name']);?></option><?
                                                                    }
                                                                    ?>
                                                            </select>
                                                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                                                            <?
                                                    }else{
                                                            ?>
                                                            <select id="bsReturnId_<?=$_GET['index'];?>" name="return_id" class="form-control pjBsAutocomplete">
                                                                    <option value="">-- <? __('front_choose'); ?>--</option>
                                                                    <?
                                                                    foreach($tpl['return_location_arr'] as $k => $v)
                                                                    {
                                                                            ?><option value="<?=$v['id'];?>"<?=isset($STORE['return_id']) && $STORE['return_id'] == $v['id'] ? ' selected="selected"' : null;?>><?=stripslashes($v['name']);?></option><?
                                                                    } 
                                                                    ?>
                                                            </select>
                                                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                                                            <?
                                                    } 
                                                    ?>
                                            </div>
                                    </div>
                            </div>
                        </div>

                        <p class="text-danger pjBsErrorMessage bsCheckErrorMsg" style="display: none;"><? __('front_no_bus_available');?></p><!-- /.text-danger pjBsErrorMessage -->

                        <p class="text-danger pjBsErrorMessage bsCheckReturnErrorMsg" style="display: none;"><? __('front_no_return_bus_available');?></p><!-- /.text-danger pjBsErrorMessage -->

                        <div class="form-group pjBsFormActions">
                                <button type="submit" class="btn btn-primary"><? __('front_button_check_availability'); ?></button>
                        </div>
                    </div>

                    <aside class="col-lg-6 col-md-6 col-sm-6 col-xs-12 pjBsFormAside">
                        <article class="pjBsFormArticle">
                            <?if(!empty($tpl['content_arr']['image'][0]['value']))
                            {?>
                                <div class="pjBsFormArticleImage">
                                    <img src="<?=PJ_INSTALL_URL . $tpl['content_arr']['image'][0]['value'];?>" />
                                </div>
                            <?}?>
                            <p>
                                <?=nl2br(pjSanitize::clean($tpl['content_arr']['content'][0]['content']));?>
                            </p>
                        </article>
                    </aside>
                </div>
            </form>
        </div>
    </div>
</div>