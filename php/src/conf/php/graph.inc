<?php
/*
 * PHPLIB
 *
 * Copyright (c) 1998,1999 Hugh Madden (hughm@idl.net.au)
 *                    Kristian Koehntopp
 *
 * $Id: graph.inc,v 1.1.1.1 2001/05/08 21:49:56 david Exp $
 *
 */ 

 class Graph_Line {
  var $classname  = "Graph_Line";
  
  var $title      = "";    // Graph title
  var $title_col  = "red";

  var $xlabel     = "";      // Axis descriptions
  var $xlabel_col = "black";

  var $ylabel     = "";
  var $ylabel_col = "black";
    
	var $xgridlines = 0;     // Number of grid lines to draw per axis
	var $ygridlines = 0;
  var $grid_col   = "black";
  
	var $width      = 500;   // Image size (px)
	var $height     = 200;

	var $fg         = "black"; // Foreground/Background colors
	var $bg         = "white";

  /* internal */
	var $image;              // Image handle
	
	var $numx       = 0;     // Maximum number of points
	var $maxy       = 0;     // y Extrema
	var $miny       = 0;

	var $line_colors = array();  // Line colors
	var $lines       = array();  // Line y values

	var $islegend   = false;      // Do we have a legend?
	var $legendlabels = array();  // Legend label colors and texts

	var $xoffset    = 0;     // right/downshift for the left/top corner (px)
	var $yoffset    = 0;

	var $xunderset  = 50;    // left/upshift for the right/bottom corner (px)
	var $yunderset  = 0;

  var $numxlabels;
  var $xlabels    = array(); //  and tick labels
  var $ylabels    = array();
  var $numylabels;
	var $colors    = array(); // Color dictionary

  //
  // Prepare a linegraph of w by h pixels with
  // the given title and axis labels.
  //
	function start($thewidth  = 500,
	              $theheight = 200,
	              $title     = "", 
	              $ylabel    = "", 
	              $xlabel    = "") 
	{
		$this->width  = $thewidth;
		$this->height = $theheight;
		$this->title  = $title;
		$this->xlabel = $xlabel;
		$this->ylabel = $ylabel;

    // Prepare a draw buffer
		$this->image  = imagecreate($this->width,$this->height);
		
		// Set up the color library
		$this->make_colors();

    // Clear screen and set the labels
		imagefill($this->image,1,1,$this->colors[$this->bg]);
    $this->draw_title($title);
    $this->draw_xlabel($xlabel);
    $this->draw_ylabel($ylabel);
	}

  // Setting of numeric labels
	function ynum_labels($number) {
		$this->numylabels = $number;
	}

	function xnum_labels($number) {
		$this->numxlabels = $number;
	}

  // Setting of label strings
	function setylabels($thelabels) {
		$this->ylabels = $thelabels;
	}

	function setxlabels($thelabels) {
		$this->xlabels = $thelabels;
	}

  // Enable grid drawing
	function grid($xnum,$ynum) {
		$this->xgridlines = $ynum;
		$this->ygridlines = $xnum;
	}

  //
  // Color library management
  //
	function create_color($thename,$x,$y,$z) {
		$this->colors[$thename]=
			imagecolorallocate($this->image,$x,$y,$z);
		return $thename;
	}

  //
  // The plot function
  //
	function plot($array_points, $thecolor = "", $thename = "") {
		if ($thename != "") {
		  if (!$this->islegend) {
  			$this->xunderset+=80;
  			$this->islegend=true;
  		}
		
  		// Save the legend label and the matching color
  		$this->legendlabels[]=array($thename,$thecolor);
    }
    if ($thecolor == "")
      $thecolor = $this->fg;
    
		$size = sizeof($array_points);
		if ($size<=0) 
		  return 0;

    // Remember largest array
		if ($this->numx<$size) 
		  $this->numx = $size;

    // Find Extrema
		$mymaxy=0; $myminy=0;
		for ($index=0;$index<$size;$index++) {
			if ($array_points[$index]>$mymaxy) 
				$mymaxy = $array_points[$index];
      if ($array_points[$index]<$myminy)
        $myminy = $array_points[$index];
		}
		
		if ($this->maxy<$mymaxy) 
		  $this->maxy = $mymaxy;
		if ($this->miny<$myminy)
		  $this->miny = $myminy;
		
		// Save plot data and plot color
		$this->lines[]        = $array_points;
		$this->line_colors[] = $thecolor;
		return 1;
	}

	function draw() {
		Header("Content-type: image/gif");
		$this->draw_lines();
		imagegif($this->image);
	}

  //
  // Title and label drawing functions
  //
	function draw_title($title) {
    if ($title=="") 
      return 0;

		$this->yoffset = $this->height*0.15;
		imagestring($this->image,5,
		            $this->width/2-(strlen($title)*10/2),0, 
		            $title, 
		            $this->colors[$this->title_col]);

		return 1;
	}

	function draw_ylabel($label) {
    if ($label=="") 
      return 0;

		$this->yunderset=$this->height*0.15;
		imagestring($this->image, 3,
		            ($this->width/2)-(strlen($label)*10/2),$this->height-20, 
		            $label, 
		            $this->colors[$this->ylabel_col]);
		return 1;
	}

	function draw_xlabel($label) {
    if ($label=="") 
      return 0;

    $this->xoffset=$this->width*0.075;
    imagestringup($this->image,3,
                  5,($this->height/2)+(strlen($label)*6/2), 
                  $label, 
                  $this->colors[$this->xlabel_col]);
		return 1;
	}

	function make_colors() {
		$this->fg = $this->create_color("black",0,0,0);
		$this->bg = $this->create_color("white",255,255,255);

		$this->create_color("red",255,0,0);
		$this->create_color("green",0,255,0);
		$this->create_color("blue",0,0,255);

		$this->create_color("yellow",255,255,0);
    $this->create_color("magenta",255,0,255);
    $this->create_color("cyan",0,255,255);
  }

	function draw_lines() {
//  print "draw lines called<BR>";

// define our virtual window
		$virtxleft   = $this->xoffset;
		$virtxright  = $this->width-$this->xunderset;
		
		$virtytop    = $this->yoffset;
		$virtybottom = $this->height-$this->yunderset;
		
		$yunit     = ($virtybottom-$virtytop)/($this->maxy-$this->miny);
		$xunit     = ($virtxright-$virtxleft)/($this->numx-1);
		
		$lines_num = sizeof($this->lines);
		if ($lines_num <=0)
		  return 0;

// grid
// horizontal grid
    if ($this->xgridlines>0) 
      for ($x = $virtxleft;
           $x<$virtxright;
           $x = $x+($virtxright-$virtxleft)/$this->xgridlines) 
      {
        imagedashedline($this->image,
                        $x,$virtytop,
                        $x,$virtybottom,
                        $this->colors[$this->grid_col]);
		  }
// vertical grid
		if ($this->ygridlines>0) 
      for ($y = $virtybottom;
           $y>$virtytop;
           $y = $y-(($virtybottom-$virtytop)/$this->ygridlines)) 
      {
        imagedashedline($this->image,
                        $virtxleft,$y,
                        $virtxright,$y,
                        $this->colors[$this->grid_col]);
		}

// axis labels
// vertical labels
		if (sizeof($this->ylabels)>1) {
		  for ($label_index = 0;
		       $label_index<sizeof($this->ylabels);
		       $label_index++)
		  {
        imagestringup($this->image,1,
                      $virtxleft-10,$virtybottom-($label_index*($virtybottom-$virtytop)/(sizeof($this->ylabels)-1)), 
                      $this->ylabels[$label_index], 
                      $this->colors[$this->ylabel_col]);
      }
		} elseif ($this->numylabels>0) {
      for ($index=0;$index<$this->numylabels;$index++) {
  			imagestringup($this->image,1,
  			              $virtxleft-10,$virtybottom-($index*($virtybottom-$virtytop)/($this->numylabels-1)),
  			              (int)($index*($this->maxy/($this->numylabels-1))),
  			              $this->colors[$this->ylabel_col]);
  	  }
		}
// horizontal labels
		if (sizeof($this->xlabels)>1) {
		  for ($label_index=0;
		       $label_index<sizeof($this->xlabels);
		       $label_index++) 
		  {
        imagestring($this->image,1,
                    $virtxleft+($label_index*($virtxright-$virtxleft)/(sizeof($this->xlabels)-1)),$virtybottom+3, 
                    $this->xlabels[$label_index], 
                    $this->colors[$this->xlabel_col]);
      }
		} elseif ($this->numxlabels>0) {
		  for ($index=0;$index<$this->numxlabels;$index++) {
			  imagestring($this->image,1,
			              $virtxleft+($index*($virtxright-$virtxleft)/($this->numxlabels-1)),$virtybottom+3,
			              (int)($index*($this->numx/($this->numxlabels-1))),
			              $this->colors[$this->xlabel_col]);
			}
		}

// legend
		$numoflls=count($this->legendlabels);
		//imagestring($this->image,3,$virtxright+10,$virtytop,$numoflls,$this->colors["black"]);
		if (sizeof($this->legendlabels[0]) >= 2) {
		  $vx = $virtxright+20;
		  $vy = $virtytop+(($virtybottom-$virtytop)/2)-($numoflls*15/2);
		  for ($index=0;$index<$numoflls;$index++) {
        $llabel=$this->legendlabels[$index];
        imagefilledrectangle($this->image,$vx,$vy+($index*15),
                             $vx+10,$vy+($index*15)+10,
                             $this->colors[$llabel[1]]);
        imagestring($this->image,1,$vx+20,$vy+($index*15),$llabel[0],
                    $this->colors[$this->grid_col] );
		  }
		}

// for each set of points plot line
		for ($lines_index=0;$lines_index<$lines_num;$lines_index++) {

		  $array_points = $this->lines[$lines_index];
		  $size         = sizeof($array_points);

		  $index = 0;
		  $oldx  = $index*$xunit;
		  $oldy  = $array_points[$index]*$yunit;
		  for ($index=0;$index<$size;$index++) {
        $x = $index*$xunit;
        $y = $array_points[$index]*$yunit;
			  imageline($this->image,
			            $virtxleft+$oldx,($virtybottom-$oldy),
			            $virtxleft+$x,($virtybottom-$y),
			            $this->colors[$this->line_colors[$lines_index]]);
        //echo "line: ",$oldx,",",$oldy," to ",$x,",",$y," ",$this->line_colors[$lines_index],"<BR>";
        $oldx = $x;
        $oldy = $y;
		  }
		}
	}

 }
?>
