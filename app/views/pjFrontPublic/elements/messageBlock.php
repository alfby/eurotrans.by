<div>
    <?
    $front_messages = __('front_messages', true, false);
    $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[5]);
    $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
    echo $system_msg; 
    ?>
</div>