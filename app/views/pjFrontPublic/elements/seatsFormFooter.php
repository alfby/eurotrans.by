<footer class="pjBsFormFoot">
    <p class="text-right pjBsTotalPrice">
        <strong id="bsRoundtripPrice_<?=$_GET['index'];?>"></strong>
    </p>

    <div class="clearfix pjBsFormActions">
        <a href="#" id="bsBtnCancel_<?=$_GET['index'];?>" class="btn btn-default pull-left"><? __('front_button_back'); ?></a>
        <?if(isset($bus_arr)){?>
            <button type="button" id="bsBtnCheckout_<?=$_GET['index'];?>" class="btn btn-primary pull-right"><? __('front_button_checkout'); ?></button>
        <?}?>
    </div>
</footer>