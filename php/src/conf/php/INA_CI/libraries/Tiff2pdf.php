<?php

class Tiff2pdf {
	protected $temp_path	 = '/';#remember backslash
	protected $tiff2pdf_path = '/usr/local/bin/tiff2pdf';
	
	function __construct($p=array()){
		$this->temp_path = $p['temp_path'];	
		
		if ($p['tiff2pdf_path'])
			$this->tiff2pdf_path = $p['tiff2pdf_path']; 
	}
	
	/**
	 * Download tiff, convert to pdf and save pdf file
	 */
	function download_tiff_and_save_pdf_from_ftp($ftp_server, $ftp_user, $ftp_pass, $remote_path, $local_path){
		
		if (!$this->download_ftp_file($ftp_server, $ftp_user, $ftp_pass, $remote_path, $local_path))
			return NULL;
			
		return $this->save_tif_as_pdf($local_path);
	}
	
	/**
	 * Download and save pdf file
	 */
	function download_and_save_pdf_from_ftp($ftp_server, $ftp_user, $ftp_pass, $remote_path, $local_path){
		
		if (!$this->download_ftp_file($ftp_server, $ftp_user, $ftp_pass, $remote_path, $local_path))
			return NULL;
			
		return $this->save_pdf($local_path);
	}	
	
	/**
	 * Download ftp file from remote server and save locally
	 */
	protected function download_ftp_file($ftp_server, $ftp_user, $ftp_pass, $remote_path, $local_path) {
	
		// set up basic connection
		if (!$conn_id = ftp_connect($ftp_server))
			return NULL;//fail
		
		ftp_pasv($conn_id, true);
		
		// login with username and password
		// try to login
		if (!@ftp_login($conn_id, $ftp_user, $ftp_pass))
		    return NULL;//fail
	
		// try to download $server_file and save to $local_file
		if (!ftp_get($conn_id, $this->temp_path.$local_path, $remote_path, FTP_BINARY))
			return NULL;//fail
		
		// close the connection
		ftp_close($conn_id);
		
		return TRUE;//success
			
	}
	
	function download_tiff_and_save_pdf($file_url, $file_name){
		/**
		 * Download image
		 */
		if ($this->download_tiff($file_url, $file_name))
			/**
			 * Output to browser (will trigger save)
			 */
			return $this->save_tif_as_pdf($file_name);
		else
			return NULL;/** failure **/
	}
	
	function download_tiff($file_url, $file_name){
		/**
		 * Get file contents
		 */
		if (!$downloaded_file = curl_file_get_contents($file_url)) return NULL;
		
		/**
		 * Write file to temp path
		 */
		file_put_contents($this->temp_path.$file_name, $downloaded_file);
        
        /**
         * If file saved successfully chmod to 644 and return file name
         */
        if (file_exists($this->temp_path.$file_name)) {
        	chmod($this->temp_path.$file_name, 0664);
        	
        	return $file_name;
        }
	}
	
	/**
	 * Launch PDF in browser
	 */
	function save_tif_as_pdf($tif_file_name){
		/**
		 * Get base of file name and change extension to PDF only if file exists
		 */
		if (file_exists($this->temp_path.$tif_file_name) && preg_match('/^(.+)\.tif$/', $tif_file_name, $matches))
			$pdf_file_name = $matches[1].'.pdf';
		else
			return NULL;
		
		header("Content-type: application/pdf");
		header("Content-Disposition: attachment; filename={$pdf_file_name}");
		header('Expires: 0');
		header('Pragma: cache');
		header('Cache-Control: private');		
		
		system($this->tiff2pdf_path." {$this->temp_path}{$tif_file_name}");
		
		return $pdf_file_name;
	}
	
	/**
	 * Launch PDF in browser
	 */
	function save_pdf($pdf_file_name){
		/**
		 * Get base of file name and change extension to PDF only if file exists
		 */
		if (!file_exists($this->temp_path.$pdf_file_name))
			return NULL;
		
		$fp = fopen($this->temp_path.$pdf_file_name, 'rb');
		
		header("Content-type: application/pdf");
		header("Content-Disposition: attachment; filename={$pdf_file_name}");
		header('Expires: 0');
		header('Pragma: cache');
		header('Cache-Control: private');		
		
		fpassthru($fp);//output pdf to screen
		
		return $pdf_file_name;
	}
}