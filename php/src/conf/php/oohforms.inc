<?php
/* OOH! Forms!
 * 
 * Object Oriented HTML Forms
 *
 * Copyright (c) 1998 by Jay Bloodworth
 * 
 * $Id: oohforms.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 */

class of_element {

  var $name;
  var $value;
  var $multiple;
  var $extrahtml;
  
  function marshal_dispatch($m,$func) {   
    $vname = $this->name;
    global $$vname;
    return $this->$func($$vname);
  }
  
  function self_show($val,$which) {
  }

  function self_show_frozen($val,$which) {
    $this->self_show($val,$which);
  }

  function self_validate($val) {
    return false;
  }

  function self_print_js($ndx_array) {
  }

  // Note that this function is generally quite simple since
  // most of the work of dealing with different types of values
  // is now done in show_self.  It still needs to be overidable,
  // however, for elements like checkbox that deal with state
  // differently
  function self_load_defaults($val) {
    $this->value = $val;
  }

  // Helper function for compatibility
  function setup_element($a) {
    $cv_tab = array("type"=>"ignore",
		    "min_l"=>"minlength",
		    "max_l"=>"maxlength",
		    "extra_html"=>"extrahtml");
    reset($a);
    while (list($k,$v) = each($a)) {
      if ($cv_tab[$k]=="ignore") continue;
      else $k = ($cv_tab[$k] ? $cv_tab[$k] : $k);
      $this->$k = $v;
    }
  }

} // end ELEMENT

class of_hidden extends of_element {

  var $hidden=1;

  function of_hidden($a) {
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    $v = (is_array($this->value) ? $this->value : array($this->value));
    $n = ($this->multiple ? $this->name . "[]" : $this->name);
    reset($v);
    while (list($k,$tv) = each($v)) {
      echo "<input type=\"hidden\" name=\"$n\" value=\"$tv\"";
      if ($this->extrahtml) echo " $this->extrahtml";
      echo ">\n";
    }
  }

} // end HIDDEN

class of_reset extends of_element {

  var $src;

  function of_reset($a) {
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    echo "<input name=\"$this->name\" type=reset value=\"$val\"";
    if ($this->extrahtml) echo " $this->extrahtml";
    echo ">\n";
  }

} // end RESET

class of_submit extends of_element {

  var $src;

  function of_submit($a) {    
    $this->setup_element($a);
  }

  function self_show($val,$which) {
    echo "<input name=\"$this->name\" value=\"$val\"";
    if ($this->src) echo "type=\"image\" src=\"$this->src\"";
    else echo " type=\"submit\"";
    if ($this->extrahtml) echo " $this->extrahtml";
    echo ">\n";
  }

} // end SUBMIT

class form {
  var $elements;
  var $hidden;
  var $jvs_name;
  var $isfile;
  var $n;

  function start($jvs_name="",$method="",$action="",$target="",$form_name="") {
    global $PHP_SELF;
    $this->jvs_name = "";
    $this->n = 0;
    if (!$method) $method = "POST";
    if (!$action) $action = $PHP_SELF;
    if (!$target) $target = "_self";
    echo "<form name=\"$form_name\" ";
    if ($this->isfile) {
	echo " enctype=\"multipart/form-data\"";
	$method = "POST";
    }
    echo " method=\"$method\"";
    echo " action=\"$action\"";
    echo " target=\"$target\"";
    if ($jvs_name) {
      $this->jvs_name = $jvs_name;
      echo " onsubmit=\"return ${jvs_name}_Validator(this)\"";
    }
    echo ">\n";
  }

  function finish($after="",$before="") {
    if ($this->hidden) {
      reset($this->hidden);
      while (list($k,$elname) = each($this->hidden)) 
	$this->show_element($elname);
    }
    echo "</form>";
    if ($this->jvs_name) {
      $jvs_name = $this->jvs_name;
      echo "<script language=\"javascript\">\n<!--\n";
      echo "function ${jvs_name}_Validator(f) {\n";
      if (strlen($before))
	echo "$before\n";
      reset($this->elements);
      while (list($k,$elrec) = each($this->elements)) {
	$el = $elrec["ob"];
	$el->self_print_js($elrec["ndx_array"]);
      }
      if (strlen($after))
	echo "$after\n";
      echo "}\n//-->\n</script>\n";
    }
  }
	   
  function add_element($el) {
    if (is_array($el)) {
      $cv_tab = array("select multiple"=>"select",
		      "image"=>"submit");
      if ($t = $cv_tab[$el["type"]]) $t = ("of_" . $t);
      else $t = ("of_" . $el["type"]);
      $el = new $t($el);
    }
    if ($el->isfile) $this->isfile = true;
    $this->elements[$el->name]["ob"] = $el;
    if ($el->hidden)
      $this->hidden[] = $el->name;
  }

  function show_element($name,$value="") {
    if (!isset($this->elements[$name]["which"]))
      $this->elements[$name]["which"] = 0;
    $el = $this->elements[$name]["ob"];
    if ($this->elements[$name]["frozen"])
      $x = $el->self_show_frozen($value,$this->elements[$name]["which"]++);
    else
      $x = $el->self_show($value,$this->elements[$name]["which"]++);
    $this->elements[$name]["ndx_array"][] = $this->n;
    $this->n += $x;
  }

  function se($name, $value="") {
	  $this->show_element($name, $value);
  }

  function ae($el) {
	  $this->add_element($el);
  }

  function validate($default=false,$vallist="") {
    if ($vallist) {
      reset($vallist);
      $elrec = $this->elements[current($vallist)];
    } else {
      reset($this->elements);
      $elrec = current($this->elements);
    }
    while ($elrec) {
      $el = $elrec["ob"];
      if ($res = $el->marshal_dispatch($this->method,"self_validate"))
	return $res; 
      if ($vallist) {
        next($vallist);
        $elrec = $this->elements[current($vallist)];
      } else {
        next($this->elements);
        $elrec = current($this->elements);
      }
    }
    return $default;
  }

  function load_defaults($deflist="") {
    if ($deflist) {
      reset($deflist);
      $elrec = $this->elements[current($deflist)];
    } else {
      reset($this->elements);
      $elrec = current($this->elements);
    }
    while ($elrec) {
      $el = $elrec["ob"];
      $el->marshal_dispatch($this->method,"self_load_defaults");
      $this->elements[$el->name]["ob"] = $el;  // no refs -> must copy back
      if ($deflist) {
        next($deflist);
        $elrec = $this->elements[current($deflist)];
      } else {
        next($this->elements);
        $elrec = current($this->elements);
      }
    }
  }

  function freeze($flist="") {
    if ($flist) {
      reset($flist);
      $elrec = $this->elements[current($flist)];
    } else {
      reset($this->elements);
      $elrec = current($this->elements);
    }
    while ($elrec) {
      $el = $elrec["ob"];
      $this->elements[$el->name]["frozen"]=1;
      if ($flist) {
        next($flist);
        $elrec = $this->elements[current($flist)];
      } else {
        next($this->elements);
        $elrec = current($this->elements);
      }
    }
  }

} /* end FORM */

include($_PHPLIB["libdir"] . "of_text.inc");
include($_PHPLIB["libdir"] . "of_select.inc");
include($_PHPLIB["libdir"] . "of_radio.inc");
include($_PHPLIB["libdir"] . "of_checkbox.inc");
include($_PHPLIB["libdir"] . "of_textarea.inc");
include($_PHPLIB["libdir"] . "of_file.inc");

?>
