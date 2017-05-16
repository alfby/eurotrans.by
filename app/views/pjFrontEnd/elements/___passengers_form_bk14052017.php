<div class="form-horizontal">
    <?$i = 0;
    if (isset($selected_seat_arr) && $selected_seat_arr) {
        $prefix = 'p_';
        if (isset($return_seats) && $return_seats) {
            $prefix = 'r_';
        }
        foreach ($selected_seat_arr as $key => $valueArr) {
            foreach($valueArr as $k => $val){?>
                <div class="pjBsFormPassengerItem">
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_first_name', true).' #'.$val; ?> <span class="pjBsAsterisk">*</span>: </label>
                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                            <input type="text" name="<?=$prefix;?>first_name[<?=$k;?>]" class="form-control required" value="<?= isset($FORM[$prefix.'first_name'][$k]) ? pjSanitize::clean($FORM[$prefix.'first_name'][$k]) : null;?>"/>
                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_last_name', true).' #'.$val; ?> <span class="pjBsAsterisk">*</span>: </label>
                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                            <input type="text" name="<?= $prefix;?>last_name[<?= $k;?>]" class="form-control required" value="<?=isset($FORM[$prefix.'last_name'][$k]) ? pjSanitize::clean($FORM[$prefix.'last_name'][$k]) : null;?>"/>
                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                        </div>
                    </div>
                    <?if($i == 0) {?>
                        <div class="form-group">
                            <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_phone_1', true).' #'.$val; ?> <span class="pjBsAsterisk">*</span>: </label>
                            <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                <input type="text" name="<?= $prefix;?>phone_1[<?= $k;?>]" class="form-control pjBsInputPhone required" value="<?= isset($FORM[$prefix.'phone_1'][$k]) ? pjSanitize::clean($FORM[$prefix.'phone_1'][$k]) : null;?>"/>
                                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_phone_2', true).' #'.$val; ?> : </label>
                            <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                <input type="text" name="<?= $prefix;?>phone_2[<?= $k;?>]" class="form-control pjBsInputPhone" value="<?= isset($FORM[$prefix.'phone_2'][$k]) ? pjSanitize::clean($FORM[$prefix.'phone_2'][$k]) : null;?>"/>
                                <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                            </div>
                        </div>
                    <? } ?>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_passpor_id', true).' #'.$val; ?> : </label>
                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                            <input type="text" name="<?= $prefix;?>passpor_id[<?= $k;?>]" class="form-control" value="<?= isset($FORM[$prefix.'passpor_id'][$k]) ? pjSanitize::clean($FORM[$prefix.'passpor_id'][$k]) : null;?>"/>
                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_country', true).' #'.$val; ?> <span class="pjBsAsterisk">*</span>: </label>
                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                            <select name="<?= $prefix;?>country_id[<?= $k;?>]" class="form-control required">
                                <option value="">----</option>
                                <?foreach ($tpl['country_arr'] as $v){?>
                                    <option value="<?=$v['id'];?>"<?=isset($FORM[$prefix.'country_id'][$k]) && $FORM[$prefix.'country_id'][$k] == $v['id'] ? ' selected="selected"' : NULL; ?>>
                                        <?= $v['country_title']; ?>
                                    </option>
                                <?}?>
                            </select>
                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                        </div>
                    </div>
                    <? if ($i == 0) { ?>
                    <!--
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label"><?= __('front_label_passengers_email', true).' #'.$val; ?> : </label>
                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                            <input type="text" name="<?= $prefix;?>email[<?= $k;?>]" class="form-control email" value="<?= isset($FORM[$prefix.'email'][$k]) ? pjSanitize::clean($FORM[$prefix.'email'][$k]) : null;?>"/>
                            <div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
                        </div>
                    </div>
                    -->
                    <? } ?>
                </div>
            <?$i++;}
        }
    }?>
</div>