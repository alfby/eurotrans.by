<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_date');?>: </dt>
    <dd><?=$bus_arr['date'];?> <a href="#" class="btn btn-link bsChangeDate"><? __('front_link_change_date');?></a></dd>
</dl>

<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_departure_from');?>: </dt>
    <dd><?=$bus_arr['from_location']?> <? __('front_at');?> <?=$bus_arr['departure_time'];?></dd>
</dl>

<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_arrive_to');?>: </dt>
    <dd><?=$bus_arr['to_location']?> <? __('front_at');?> <?=$bus_arr['arrival_time'];?></dd>
</dl>

<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_bus');?>: </dt>
    <dd><?=$bus_arr['route_title'];?></dd>
</dl>

