<?php

function date_format_ci($string, $format="%m-%d-%y", $default_date=null) {
    if (substr(PHP_OS,0,3) == 'WIN') {
           $_win_from = array ('%e',  '%T',       '%D');
           $_win_to   = array ('%#d', '%H:%M:%S', '%m/%d/%y');
           $format = str_replace($_win_from, $_win_to, $format);
    }
    if($string != '') {
        return h(strftime($format, strtotime($string)));
    } elseif (isset($default_date) && $default_date != '') {
        return h(strftime($format, strtotime($default_date)));
    } else {
        return;
    }
}

?>