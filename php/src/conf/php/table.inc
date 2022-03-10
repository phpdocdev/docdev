<?php
/*
 * PHP Base Library
 *
 * Copyright (c) 1998,1999 SH Online Dienst GmbH
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: table.inc,v 1.1.1.1 2001/05/08 21:49:57 david Exp $
 *
 */ 

class Table {
  var $classname = "Table";  ## Persistence Support

  var $check;                ## if set, create checkboxes named $check[$key]
  var $filter = "[A-Za-z][A-Za-z0-9_]*"; ## Regexp: Field names to show
  var $fields;               ## Array of field names to show
  var $heading;              ## if set, create <th> section

  ### Initialization function, same as show.
  function start($ary, $class = "") {
    $this->show($ary, $class);
  }

  ############################################################
  ## Public functions
  
  ## Show a twodimensional array $ary as a table,
  ## tagging everything with $class.

  function show($ary, $class = "") {
    $row = 0;

    ## Check that we have at least two dimensions...
    if (!is_array($ary))
      return;
    if (!is_array(current($ary)))
      return;

    $this->table_open($class);

    if (isset($this->heading) && $this->heading) {
      reset($ary);
      list($key, $val) = each($ary);
      $this->table_heading_row($val, $class);
    }

    reset($ary);      
    while(list($key, $val) = each($ary)) {
      ## Process a single row
	  # patch by sasha@forum.swarthmore.edu
      if(!is_array($val)) continue;
      $this->table_row($row++, $key, $val, $class);
    }
    $this->table_close();
  }

  ## Show a twodimensional array $ary as a table,
  ## tagging everything with $class. Only the rows
  ## $start to $start+$num are shown.
  
  function show_page($ary, $start, $num, $class ="") {
    $row = 0;

    ## Check that we have at least two dimensions...
    if (!is_array($ary))
      return;
    if (!is_array(current($ary)))
      return;

    $max = count($ary);
    if (($start<0) || ($start>$max))
      return;
    $max = min($start+num, $max);
    
    $this->table_open($class);
    if ($this->heading) {
      reset($ary);
      list($key, $val) = each($ary);
      $this->table_heading_row($val, $class);
    }
    
    for ($row = $start; $row < $max; $row++) {
      ## Process a single row
      $this->table_row($row++, $key, $val, $class);
    }
    $this->table_close();
  }

  ## Walk a database query result $db and display it as
  ## a table, tagging everything with $class.

  function show_result($db, $class = "") {
    $row = 0;

    $this->table_open($class);

    if ($this->heading) {
      if ($db->num_rows() > 0) {
        $this->table_heading_row($db->Record, $class);
      } else {
        $this->table_close();
        return;
      }
    }
    
    while($db->next_record()) {
      ## Process a table row
      $this->table_row($row, $row, $db->Record, $class);
      $row += 1;
    }
    $this->table_close();
  }

  ## Walk a database query result $db within the limits given
  ## and display it as a table, tagging everything with $class.
  ## Only the rows $start to $start+num are shown.

  function show_result_page($db, $start, $num, $class = "") {
    $row = 0;

    if (($start > $db->num_rows()+0) || ($start < 0)) {
      return;
    }
    $row = $start;
    $fin = $start + $num;
    
    $this->table_open($class);
    if ($this->heading) {
      if ($db->num_rows() > 0) {
        $this->table_heading_row($db->Record, $class);
      } else {
        $this->table_close();
        return;
      }
    }
    
    $db->seek($start);
    while($db->next_record() && ($row < $fin)) {
      ## Process a table row
      $this->table_row($row, $row, $db->Record, $class);
      $row += 1;
    }
    $this->table_close();
  }

  ############################################################
  ## Helper functions
  
  ## Finds out which fields are on display

  function select_colnames($data) {
    if (!is_array($this->fields) && is_array($data)) {
      reset($data);
      while(list($key, $val) = each($data)) {
        if (ereg($this->filter, $key))
          $d[] = $key;
      }
    } else {
      $d = $this->fields;
    }
    
    return $d;
  }

  ## This function walks the table fields and creates
  ## a heading line.

  function table_heading_row($data, $class = "") {
    $row = 0;

    if (!is_array($data))
      return;

    $data = $this->select_colnames($data);

    $this->table_row_open($row, $data, $class);
    
    ## Checkbox handling...
    if ($this->check)
      $this->table_heading_cell(0, "&nbsp;", $class);

    ## Create regular header cells
    reset($data);
    while(list($k, $d) = each($data)) {
      $this->table_heading_cell($k, $d, $class);
    }

    $this->table_row_close(0);
  }

  ## This function walks a single row and creates
  ## cells.

  function table_row($row, $row_key, $data, $class = "") {
    $cell = 0;

    $d = $this->select_colnames($data);    

    $this->table_row_open($row, $d, $class);    

    ## Checkbox handling...
    if ($this->check)
      $this->table_checkbox_cell($row, $row_key, $data, $class);

    reset($d);
    while(list($key, $val) = each($d)) {
      $this->table_cell($row, $cell++, $val, $data[$val], $class);
    }

    $this->table_row_close($row);
  }

  ############################################################

  ## The following functions provide a very basic rendering
  ## of a HTML table with CSS class tags. Table is useable
  ## with them or the functions can be overridden for a
  ## more complex functionality.

  ## Table open and close functions.

  function table_open($class = "") {
    printf("<table%s>\n", $class?" class=$class":"");
  }

  function table_close() {
    printf("</table>\n");
  }

  ## Row open and close functions.

  function table_row_open($row, $data, $class = "") {
    printf(" <tr%s>\n", $class?" class=$class":"");
  }

  function table_row_close($row) {
    printf(" </tr>\n");
  }

  ## Renders a single table cell.

  function table_cell($row, $col, $key, $val, $class) {
    printf("  <td%s>%s</td>\n", 
      $class?" class=$class":"",
      $val);
  }

  ## Renders a single table heading cell.

  function table_heading_cell($col, $val, $class) {
    printf("  <th%s>%s</th>\n",
      $class?" class=$class":"",
      $val);
  }
  
  ## Renders a single table checkbox cell

  function table_checkbox_cell($row, $row_key, $data, $class) {
    printf("  <td><input type=checkbox name=%s[%s] value=yes></td>\n",
      $this->check, empty($data[$this->check])?$row_key:$data[$this->check]);
  }

}
?>
