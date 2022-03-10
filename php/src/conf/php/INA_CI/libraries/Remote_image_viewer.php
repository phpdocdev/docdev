<?php

#dependencies: curl_file_get_contents

class Remote_image_viewer {
	protected $temp_path = 'tmp';
	protected $file_url;
	protected $remote_ext = '.pdf';
	protected $session_name = 'remote_image_viewer';
	protected $convert_path = '/usr/local/bin/convert';
	protected $multi_file_naming_convention = '%1$s-%2$s%3$d'; #quirky that on some machines the images are naming differently than on other machines

	#set configuration
	function __construct($param) {
		foreach ($param as $key => $val) 
			if ($val && property_exists(get_class($this), $key))
				$this->$key = $val;
	}
	
	#returns a string file name path or an array with the image name requested and the total count of images
	function get_image($file_name, $return_image_ext = '.jpg', $page_num=NULL) {
		#search for cached image
		$cached_image = $this->get_cached_image($file_name, $return_image_ext, $page_num);
		
		#return cached image if found
		if ($cached_image) return $cached_image;
		
		#otherwise fetch new image from remote server
		$remote_file = curl_file_get_contents($this->file_url . $file_name . $this->remote_ext);
		
		if (!$remote_file) return NULL; #couldn't fetch remote file
		
		#name of image that will be saved from remote
		$local_file_name = $this->temp_path . $file_name . $this->remote_ext;
	
		#save file locally
        file_put_contents($local_file_name, $remote_file);
        chmod($local_file_name, 0664);#change file permissions        
        
		$new_file_name = $this->temp_path . $file_name . $return_image_ext;
 		
        exec("{$this->convert_path} {$local_file_name} {$new_file_name}");#convert save remote file to new file name
 		
    	if (file_exists($new_file_name)) {#if one file created 
    		chmod($new_file_name, 0664);
    		return ($new_file_name);#all done
    	}
    	else {#if multiple images created
        	$images = array ();
        	for ($c = 0; $c < 50; $c++) {#iterate through pages, stop when no more pages found
    			$image_name = sprintf($this->multi_file_naming_convention, $file_name, $return_image_ext, $c);
        		
        		if (file_exists($this->temp_path . $image_name)) {
        			$images [] = $image_name;#if image exists, add to array
        			chmod($this->temp_path . $image_name, 0664);
        		}
     			else {#when no image is found you are done 
     				$_SESSION[$this->session_name][$file_name] = $images;
     				
     				#return array('file_name' => $images[(int) $page_num], 'count' => count($images));#return whichever image was requested in an array
     				return $images;
     			}
        	}
    	}
	}
	
	### This block of code will look for cached images
	protected function get_cached_image($file_name, $return_image_ext = '.jpg', $page_num=0) {
		$file_path = $file_name . $return_image_ext;
		$file_full_path = $this->temp_path . $file_path;
		
		if ($_SESSION[$this->session_name]) {
			if (file_exists($this->temp_path . $_SESSION[$this->session_name][$file_name][(int) $page_num])) {
				$file_full_path = $this->temp_path . $_SESSION[$this->session_name][$file_name][(int) $page_num];
				
				if (file_exists($file_full_path))
					return $_SESSION[$this->session_name][$file_name];#return array of images
			}
			elseif ($this->temp_path . $_SESSION[$this->session_name][$file_name])
				$file_full_path = $this->temp_path . $_SESSION[$this->session_name][$file_name];
		}
		
		if (file_exists($file_full_path))
			return $file_name . $return_image_ext;
		
		return NULL;
	}
	
	# display raw image bytes
	static function show_image_bytes($file_path, $content_type = 'image/jpeg'){
        $fp = fopen($file_path, 'rb');
		
		# Display iamge in browser
		header("Content-type: {$content_type}");
		header("Content-Length: " . filesize($file_path));

		$fp = fopen($file_path, 'rb');
		fpassthru($fp);
	}
}

?>
