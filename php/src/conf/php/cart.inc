<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998,1999 SH Online Dienst GmbH
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: cart.inc,v 1.1.1.1 2001/05/08 21:49:56 david Exp $
 *
 */ 

class Cart {
  var $classname = "Cart";
  var $persistent_slots = array("item", "currentItem");

  var $item = array();  ## The shopping cart array($item, array("art"=>..., "num"=>..., ...))
  var $currentItem = 1; ## Next item number in cart
  
  ##
  ## Return the position and number of units 
  ## of an article in the cart(or false and 0, 
  ## if it is not in there)
  ##
  function check($art) {

    reset($this->item);
    while(list($item, $attr) = each($this->item)) {
    
      if (isset($attr["art"]) 
       && ($attr["art"] == $art)) {
           return array($item, $attr["num"]);
      }
    }
    
    return array(false, 0);
  }

  ##
  ## Delete all articles from current cart
  ##
  function reset() {

    reset($this->item);
    while(list($item, $attr) = each($this->item)) {
      unset($this->item[$item]);
    }
    $this->currentItem = 1;
    
    return true;
  }

  ##
  ## Add num units of an article to the cart
  ## and return the item number (or false on error).
  ##
  function add_item($art, $num) {
  
    ## Check to see if we already have some of these
    list($item, $have) = $this->check($art);
    
    ## We already have them
    if ($item) {
      $this->item[$item]["num"] += $num;
      return $item;
    }
    
    ## New article
    $item = $this->currentItem++;
    $this->item[$item]["art"] = $art;
    $this->item[$item]["num"] = $num;

    return $item;
  }
  
  ##
  ## Take num units of an article from the cart
  ## and return the item number (or false on error)
  ##
  function remove_item($art, $num) {
  
    ## Check to see if we have some of these
    list($item, $have) = $this->check($art);
    
    ## Can't take them out
    if (!$item || ($num > $have)) {
      return false;
    }
    
    ## Drop the item
    if ($num == $have) {
      unset($this->item[$item]);
      return $item;
    }
    
    ## Take $num out...
    $this->item[$item]["num"] -= $num;
    return $item;
  }

  ##
  ## Set quantity of an article in the cart to exactly $num
  ## and return the item number
  ##
  function set_item($art, $num) {
  
    ## Check to see if we already have some of these
    list($item, $have) = $this->check($art);
    
    ## We already have them
    if ($item) {
     if ($num > 0) {
      $this->item[$item]["num"] = $num;
     } else {
      unset($this->item[$item]);
     }
     return $item;
    }
    
    if ($num > 0) {
      ## New article
      $item = $this->currentItem++;
      $this->item[$item]["art"] = $art;
      $this->item[$item]["num"] = $num;
    }

    return $item;
  }

  #
  # Return the number of articles in current cart.
  #
  function tot_arts () {
    return count($this->item);
  }
  
  function show_all() {
    reset($this->item);
    $this->show_cart_open();
    while(list($item, $attr) = each($this->item)) {
      $this->show_item($attr["art"], $attr["num"]);
    }
    $this->show_cart_close();
  }

  function start() {
    global $sess;
    
    $sess->register("cart");
  }

  ##
  ## Dummy, to be overwritten by user.
  ##

  function show_cart_open()  { 
    return; 
  }

  function show_cart_close() { 
    return; 
  }

  function show_item($art, $num) {
    printf("%s units of %s<br>\n", $num, $art);
  }
}
?>
