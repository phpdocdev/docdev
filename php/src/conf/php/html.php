<?

function html_text($name, $size, $value){
  return '<input type="text" name="'.$name.'" value="'.$value.'" size="'.$size.'">';
}

function html_textarea($name, $cols, $rows, $wrap, $value){
  return '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" wrap="'.$wrap.'">'.$value.'</textarea>';
}

function html_checkbox(){

}

function html_radio(){

}

function html_select(){

}

?>