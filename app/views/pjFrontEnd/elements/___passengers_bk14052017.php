<? if (isset($selected_seat_arr) && $selected_seat_arr) { ?>
    <?$i = 0;
    $prefix = 'p_';
    if (isset($return_seats) && $return_seats) {
        $prefix = 'r_';
    }
    foreach ($selected_seat_arr as $key => $seatsArr) {
        foreach($seatsArr as $k => $val){?>
        <ul class="list-unstyled pjBsListPersonalData pjBsListPassengerData pjBsFormPassengerItem">
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_first_name', true).' #'.$val; ?>: </dt>
                    <dd><?=isset($FORM[$prefix.'first_name'][$k]) ? pjSanitize::clean($FORM[$prefix.'first_name'][$k]) : null;?></dd>
                </dl>
            </li>
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_last_name', true).' #'.$val; ?>: </dt>
                    <dd><?=isset($FORM[$prefix.'last_name'][$k]) ? pjSanitize::clean($FORM[$prefix.'last_name'][$k]) : null;?></dd>
                </dl>
            </li>
            <? if ($i == 0) { ?>
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_phone_1', true).' #'.$val; ?>: </dt>
                    <dd><?=isset($FORM[$prefix.'phone_1'][$k]) ? pjSanitize::clean($FORM[$prefix.'phone_1'][$k]) : null;?></dd>
                </dl>
            </li>
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_phone_2', true).' #'.$val; ?>: </dt>
                    <dd><?=isset($FORM[$prefix.'phone_2'][$k]) ? pjSanitize::clean($FORM[$prefix.'phone_2'][$k]) : null;?></dd>
                </dl>
            </li>
            <? } ?>
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_passpor_id', true).' #'.$val; ?>: </dt>
                    <dd><?=isset($FORM[$prefix.'passpor_id'][$k]) ? pjSanitize::clean($FORM[$prefix.'passpor_id'][$k]) : null;?></dd>
                </dl>
            </li>
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_country', true).' #'.$val; ?>: </dt>
                    <dd>
                        <?if (isset($FORM[$prefix.'country_id'][$k]) && (int)$FORM[$prefix.'country_id'][$k] > 0) {
                            foreach ($tpl['all_country_arr'] as $country) {
                                if ($country['id'] == $FORM[$prefix.'country_id'][$k]) {
                                    echo pjSanitize::clean($country['country_title']);
                                    break;
                                }
                            }
                        }?>
                    </dd>
                </dl>
            </li>
            <? if ($i == 0) { ?>
            <li>
                <dl class="dl-horizontal">
                    <dt><?=__('front_label_passengers_email', true).' #'.$val; ?>: </dt>
                    <dd><?=isset($FORM[$prefix.'email'][$k]) ? pjSanitize::clean($FORM[$prefix.'email'][$k]) : null;?></dd>
                </dl>
            </li>
            <? } ?>
        </ul>
        <?$i++;}
    }?>
<?}?>