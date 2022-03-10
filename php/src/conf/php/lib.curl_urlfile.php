<?

function curl_file_get_contents($url) {
        if (function_exists(curl_init)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false) ;
                $res=curl_exec($ch);
                curl_close($ch);
                return $res;

        } else {
                return false;
        }
}

?>
