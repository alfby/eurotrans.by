<?
$STORE = @$_SESSION[$controller->defaultStore];
if(!empty($tpl['bus_type_arr'])){
    $map = PJ_INSTALL_PATH . $tpl['bus_type_arr']['seats_map'];

    if (is_file($map)){
        $size = getimagesize($map);?>
        <div class="bsMapHolder pjBsSeatsContainer" style="height: <?=$size[1]+20;?>px">
            <img id="map" src="<?=PJ_INSTALL_URL . $tpl['bus_type_arr']['seats_map']; ?>" alt="" style="margin: 0; border: none; position: absolute; top: 0; left: 0; z-index: 500" />
            <?foreach ($tpl['seat_arr'] as $seat){?>
                <span rel="hi_<?=$seat['id']; ?>" class="rect empty<?=in_array($seat['id'], $tpl['booked_seat_arr']) ? ' bs-booked' : ' bs-available';?><?=isset($tpl['selected_seat_arr']) ? ( empty($intersect) ? ( in_array($seat['id'], $tpl['selected_seat_arr']) ? ' bs-selected' : null ) : null ) : null;?>" data-id="<?=$seat['id']; ?>" data-name="<?=$seat['name']; ?>" style="width: <?=$seat['width']; ?>px; height: <?=$seat['height']; ?>px; left: <?=$seat['left']; ?>px; top: <?=$seat['top']; ?>px; line-height: <?=$seat['height']; ?>px">
                    <span class="bsInnerRect" data-name="hi_<?=$seat['id']; ?>"><?=stripslashes($seat['name']); ?></span></span>
                <?php
            }?>
        </div>
    <?} 
}else{?>
    <div class="bsSystemMessage">
        <?
        $front_messages = __('front_messages', true, false);
        $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[5]);
        $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
        echo $system_msg; 
        ?>
    </div>
<?}?>