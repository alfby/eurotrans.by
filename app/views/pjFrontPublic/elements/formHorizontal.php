<?
$labelClass='col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label';
$blockClass='col-lg-9 col-md-9 col-sm-8 col-xs-12';
$matchArray = array(2,3);
?>

<p class="pjBsFormTitle"><? __('front_personal_details');?></p><!-- /.pjBsFormTitle -->

<div class="form-horizontal">
    <? if (in_array($tpl['option_arr']['o_bf_include_title'], $matchArray)){?>
        <div class="form-group">
            <label class="<?=$labelClass?>"><? __('front_label_title'); ?> <? if($tpl['option_arr']['o_bf_include_title'] == 3): ?><span class="pjBsAsterisk">*</span><? endif;?>: </label>
            <div class="<?=$blockClass?>">
                <select name="c_title" class="form-control pjBsFieldInline<?=($tpl['option_arr']['o_bf_include_title'] == 3) ? ' required' : NULL; ?>">
                    <option value="">----</option>
                    <?$title_arr = pjUtil::getTitles();
                    $name_titles = __('personal_titles', true, false);
                    foreach ($title_arr as $v){?>
                        <option value="<?=$v; ?>"<?=isset($FORM['c_title']) && $FORM['c_title'] == $v ? ' selected="selected"' : NULL; ?>><?=$name_titles[$v]; ?></option><?
                    }?>
                </select>
                <div class="help-block with-errors"></div>
            </div>
        </div>
    <?}
    $fieldCode = 'fname'; $addClass = '';
    include $pathToViewElements.'textInput.php'; 

    $fieldCode = 'lname';
    include $pathToViewElements.'textInput.php'; 

    $fieldCode = 'phone';
    include $pathToViewElements.'textInput.php'; 
    
    $fieldCode = 'email'; $addClass = 'email';
    include $pathToViewElements.'textInput.php'; 
    
    $fieldCode = 'company'; $addClass = '';
    include $pathToViewElements.'textInput.php'; 
    
    
    if (in_array($tpl['option_arr']['o_bf_include_notes'], $matchArray)){?>
        <div class="form-group">
            <label class="<?=$labelClass?>"><? __('front_label_notes'); ?> <? if($tpl['option_arr']['o_bf_include_notes'] == 3): ?><span class="pjBsAsterisk">*</span><? endif;?>: </label>
            <div class="<?=$blockClass?>">
                <textarea name="c_notes" style="height: 100px;" class="form-control<?=($tpl['option_arr']['o_bf_include_notes'] == 3) ? ' required' : NULL; ?>"><?=isset($FORM['c_notes']) ? pjSanitize::clean($FORM['c_notes']) : null;?></textarea>
                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
            </div>
        </div>
    <?}?>
    
    <?$fieldCode = 'address'; $addClass = '';
    include $pathToViewElements.'textInput.php';
    
    $fieldCode = 'city'; $addClass = '';
    include $pathToViewElements.'textInput.php';
    
    $fieldCode = 'state'; $addClass = '';
    include $pathToViewElements.'textInput.php';
    
    $fieldCode = 'zip'; $addClass = '';
    include $pathToViewElements.'textInput.php';
    

    if (in_array($tpl['option_arr']['o_bf_include_country'], $matchArray)){?>
        <div class="form-group">
            <label class="<?=$labelClass?>"><? __('front_label_country'); ?> <? if($tpl['option_arr']['o_bf_include_country'] == 3): ?><span class="pjBsAsterisk">*</span><? endif;?>: </label>
            <div class="<?=$blockClass?>">
                <select name="c_country" class="form-control<?=($tpl['option_arr']['o_bf_include_country'] == 3) ? ' required' : NULL; ?>">
                    <option value="">----</option>
                    <?foreach ($tpl['country_arr'] as $v){?>
                        <option value="<?=$v['id']; ?>"<?=isset($FORM['c_country']) && $FORM['c_country'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?=$v['country_title']; ?></option><?
                    }?>
                </select>
                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
            </div>
        </div>
    <?}
    if($tpl['option_arr']['o_payment_disable'] == 'No'){?>
        <div class="form-group">
            <label class="<?=$labelClass?>"><? __('front_label_payment_medthod'); ?> <span class="pjBsAsterisk">*</span>: </label>
            <div class="<?=$blockClass?>">
                <select id="bsPaymentMethod_<?=$_GET['index'];?>" name="payment_method" class="form-control required">
                    <option value="">----</option>
                    <?foreach (__('payment_methods', true, false) as $k => $v){
                        if($tpl['option_arr']['o_allow_' . $k] == 'Yes'){?>
                            <option value="<?=$k; ?>"<?=isset($FORM['payment_method']) && $FORM['payment_method'] == $k ? ' selected="selected"' : NULL; ?>><?=$v; ?></option><?
                        }
                    }?>
                </select>
                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
            </div>
        </div>
        <div id="bsCCData_<?=$_GET['index'];?>" style="display: <?=isset($FORM['payment_method']) && $FORM['payment_method'] == 'creditcard' ? 'block' : 'none'; ?>">
            <div class="form-group">
                <label class="<?=$labelClass?>"><? __('front_label_cc_type'); ?> <span class="pjBsAsterisk">*</span>: </label>
                <div class="<?=$blockClass?>">
                    <select name="cc_type" class="form-control required">
                        <option value="">----</option>
                        <?foreach (__('cc_types', true, false) as $k => $v){?>
                            <option value="<?=$k; ?>"<?=isset($FORM['cc_type']) && $FORM['cc_type'] == $k ? ' selected="selected"' : NULL; ?>><?=$v; ?></option><?
                        }?>
                    </select>
                    <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                </div>
            </div>
            <div class="form-group">
                <label class="<?=$labelClass?>"><? __('front_label_cc_num'); ?> <span class="pjBsAsterisk">*</span>: </label>
                <div class="<?=$blockClass?>">
                    <input type="text" name="cc_num" class="form-control required" value="<?=isset($FORM['cc_num']) ? pjSanitize::clean($FORM['cc_num']) : null;?>"/>
                    <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                </div>
            </div>
            <div class="form-group">
                <label class="<?=$labelClass?>"><? __('front_label_cc_exp'); ?> <span class="pjBsAsterisk">*</span>: </label>
                <div class="col-lg-4 col-md-5 col-sm-4 col-xs-12">
                    <select id="bsExpMonth_<?=$_GET['index'];?>" name="cc_exp_month" class="form-control required">
                    <?$month_arr = __('months', true, false);
                    ksort($month_arr);
                    foreach ($month_arr as $key => $val)
                    {?>
                        <option value="<?=$key;?>"<?=(int) @$FORM['cc_exp_month'] == $key ? ' selected="selected"' : NULL; ?>><?=$val;?></option>
                    <?}?>
                    </select>
                    <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <select id="bsExpYear_<?=$_GET['index'];?>" name="cc_exp_year" class="form-control required">
                    <?$y = (int) date('Y');
                    for ($i = $y; $i <= $y + 10; $i++)
                    {?>
                        <option value="<?=$i; ?>"<?=@$FORM['cc_exp_year'] == $i ? ' selected="selected"' : NULL; ?>><?=$i; ?></option>
                    <?}?>
                    </select>
                    <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                </div>
            </div>
            <div class="form-group">
                <label class="<?=$labelClass?>"><? __('front_label_cc_code'); ?> <span class="pjBsAsterisk">*</span>: </label>
                <div class="<?=$blockClass?>">
                    <input type="text" name="cc_code" class="form-control required" value="<?=isset($FORM['cc_code']) ? pjSanitize::clean($FORM['cc_code']) : null;?>"/>
                    <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                </div>
            </div>
        </div>
    <?}
    
    if (in_array($tpl['option_arr']['o_bf_include_captcha'], $matchArray)){?>
        <div class="form-group">
            <label class="<?=$labelClass?>"><? __('front_label_captcha'); ?> <? if($tpl['option_arr']['o_bf_include_captcha'] == 3): ?><span class="pjBsAsterisk">*</span><? endif;?>: </label>
            <div class="<?=$blockClass?>">
                <div class="pjBsCaptcha">
                    <input type="text" name="captcha" class="form-control<?=($tpl['option_arr']['o_bf_include_captcha'] == 3) ? ' required' : NULL; ?>" maxlength="6" autocomplete="off"/>
                    <img src="<?=PJ_INSTALL_URL; ?>index.php?controller=pjFrontEnd&action=pjActionCaptcha&rand=<?=rand(1, 9999); ?>" alt="Captcha" style="border: solid 1px #E0E3E8;"/>
                </div><!-- /.pjBsCaptcha -->
                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
            </div>
        </div>
    <?}?>
</div><!-- /.form-horizontal -->