<p id='av-cities' class='av-cities-wrapper pj-table'>
    <label class='title' for='av-cities-list'>Destination cities:</label>
    <span class='inline_block'>
        <span id='av-cities-container'>
            <?if(isset($tpl['dest_cities']) && !empty($tpl['dest_cities'])):?>
                <?foreach($tpl['dest_cities'] as $arCity){?>
                <span class='av-city-wrapper'>
                    <input type='hidden' name='av_cities[id][<?=$arCity[$tpl["locId"]]["id"]?>]' value='<?=$arCity[$tpl["locId"]]["id"]?>'>
                    <span class='av-cities-name'>
                        <?=$arCity[$tpl["locId"]]["name"]?>
                    </span>
                    <a href='javascript:void(0)' class='pj-table-icon-delete'></a>
                </span>
                <?}?>
            <?endif;?>
        </span>
        <?if(!empty($tpl['arr_cities'])):?>
            <select class='form-control pj-form-field pj-form-select pj-selector-editable' id='av-cities-list' name='av-cities-list'>
                <option value=''>-- Select city --</option>
                <?foreach($tpl['arr_cities'] as $arCity): ?>
                <option style="display:<?=($arCity['locale']==$tpl['locId']) ? 'block':'none';?>" value="<?=$arCity['id']?>" data-locid="<?=$arCity['locale']?>"><?=$arCity['name']?></option>
                <?endforeach;?>
            </select>
        <?endif;?>
    </span>
</p>