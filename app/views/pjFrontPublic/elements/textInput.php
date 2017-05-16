<?if (in_array($tpl['option_arr']['o_bf_include_'.$fieldCode], $matchArray)){?>
    <div class="form-group">
        <label class="<?=$labelClass?>"><? __('front_label_'.$fieldCode); ?> <? if($tpl['option_arr']['o_bf_include_'.$fieldCode] == 3): ?><span class="pjBsAsterisk">*</span><? endif;?>: </label>
        <div class="<?=$blockClass?>"> 
            <input type="text" name="c_<?=$fieldCode?>" class="<?=$addClass?> form-control<?=($tpl['option_arr']['o_bf_include_'.$fieldCode] == 3) ? ' required' : NULL; ?>" value="<?=isset($FORM['c_'.$fieldCode]) ? pjSanitize::clean($FORM['c_'.$fieldCode]) : null;?>"/>
            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
        </div>
    </div>
<?}?>