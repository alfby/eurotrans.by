<? if ($STORE['is_transfer'] == 1) { ?>
    <dl class="dl-horizontal pjBsFormBoxData">
        <dt><? __('front_label_transfer');?>: </dt>
    </dl>
<? } else { ?>
    <dl class="dl-horizontal pjBsFormBoxData">
        <dt><? __('front_return_date');?>: </dt>
        <dd><?=$STORE['return_date'];?> <a href="#" class="btn btn-link bsChangeDate"><? __('front_link_change_date');?></a></dd>
    </dl>
<? } ?>
<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_departure_from');?>: </dt>
    <dd><?=$tpl['return_from_location']?> <? __('front_at');?> <?=$tpl['return_bus_arr']['departure_time'];?></dd>
</dl>

<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_arrive_to');?>: </dt>
    <dd><?=$tpl['return_to_location']?> <? __('front_at');?> <?=$tpl['return_bus_arr']['arrival_time'];?></dd>
</dl>

<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_bus');?>: </dt>
    <dd><?=$tpl['return_bus_arr']['route_title'];?></dd>
</dl>