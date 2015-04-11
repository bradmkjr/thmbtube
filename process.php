<?php

// America/Chicago
date_default_timezone_set('America/Denver');

require('./inc/functions.inc.php');

generate_cache_folders();

$_SERVER['REQUEST_URI_PATH'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', $_SERVER['REQUEST_URI_PATH']);

// var_dump($segments);

// header('location: https://img.youtube.com/vi/'.$segments[1].'/0.jpg');

$url = 'https://img.youtube.com/vi/'.$segments[1].'/0.jpg';


// make cache folders. Due to cache folder being empty they can not be commited to source control. Also allows for easy flushing of cache folders.



// http://stackoverflow.com/questions/2742813/how-to-validate-youtube-video-ids
// [a-zA-Z0-9_-]{11}

// echo linkifyYouTubeURLs($path['0']);

/* 
Following function from:

http://stackoverflow.com/questions/5830387/how-to-find-all-youtube-video-ids-in-a-string-using-a-regex/6901180#6901180 
*/

// Linkify youtube URLs which are not already links.
function linkifyYouTubeURLs($text) {
    $text = preg_replace('~
        # Match non-linked youtube URL in the wild. (Rev:20130823)
        https?://         # Required scheme. Either http or https.
        (?:[0-9A-Z-]+\.)? # Optional subdomain.
        (?:               # Group host alternatives.
          youtu\.be/      # Either youtu.be,
        | youtube         # or youtube.com or
          (?:-nocookie)?  # youtube-nocookie.com
          \.com           # followed by
          \S*             # Allow anything up to VIDEO_ID,
          [^\w\s-]       # but char before ID is non-ID char.
        )                 # End host alternatives.
        ([\w-]{11})      # $1: VIDEO_ID is exactly 11 chars.
        (?=[^\w-]|$)     # Assert next char is non-ID or EOS.
        (?!               # Assert URL is not pre-linked.
          [?=&+%\w.-]*    # Allow URL (query) remainder.
          (?:             # Group pre-linked alternatives.
            [\'"][^<>]*>  # Either inside a start tag,
          | </a>          # or inside <a> element text contents.
          )               # End recognized pre-linked alts.
        )                 # End negative lookahead assertion.
        [?=&+%\w.-]*        # Consume any URL (query) remainder.
        ~ix', 
        '$1',
        $text);
    return $text;
}

/* 

Following function from:
http://stackoverflow.com/questions/5598480/php-parse-current-url 
*/

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}


/* http://stackoverflow.com/questions/4918799/use-curl-to-open-gd-image */

function loadimg($url) {
    $ch = curl_init();

    curl_setopt ($ch, CURLOPT_URL, $url);    
    curl_setopt ($ch, CURLOPT_BINARYTRANSFER, true);  
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt ($ch, CURLOPT_HEADER, false);  
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0); 

    $rawdata = curl_exec($ch);
    $image = imagecreatefromstring($rawdata);

    curl_close($ch);

    return imagejpeg($image);
}

function load_remote_url($url){
	/* http://stackoverflow.com/questions/5262857/5-minute-file-cache-in-php */
	
	$hash = md5($url);
	
	$cache_file =  './cache/'.substr($hash,0,1).'/'.substr($hash,1,1).'/'.$hash;
	
	if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (60 * 60 * 24) ))) {
		header('remote-cache: true');
	   // Cache file is less than five minutes old. 
	   // Don't bother refreshing, just use the file as-is.
	   $file = file_get_contents($cache_file);
	} else {
		header('remote-cache: false');
	   // Our cache is out-of-date, so load the data from our remote server,
	   // and also save it over our cache for next time.
	   $file = file_get_contents($url);
	   file_put_contents($cache_file, $file, LOCK_EX);
	}
	return $file;
}

function load_local_url($file){
	/* http://stackoverflow.com/questions/5262857/5-minute-file-cache-in-php */
	global $segments;
	
	
	$hash = md5($_SERVER['REQUEST_URI']);
	
	$cache_file =  './cache/'.substr($hash,0,1).'/'.substr($hash,1,1).'/'.$hash;
	
	if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (60 * 60 * 24) ))) {
		header('local-cache: true');
	   // Cache file is less than five minutes old. 
	   // Don't bother refreshing, just use the file as-is.
	  $local_file = file_get_contents($cache_file);
	  header('Content-Type: image/jpeg');
	  echo $local_file;
	} else {
		header('local-cache: false');
	   // Our cache is out-of-date, so load the data from our remote server,
	   // and also save it over our cache for next time.
	   
	   $image = imagecreatefromstring($file);
		
		$button = 0;
		
		if(isset($segments['3']) && '' != $segments['3']  ){
			$button = $segments['3'];	
		}
		
		
		// Load the stamp and the photo to apply the watermark to
		$stamp = imagecreatefrompng('./assets/img/play-'.$button.'.png');
		
		// echo imagejpeg($image);
		
		// Set the margins for the stamp and get the height/width of the stamp image
		$marge_right = 10;
		$marge_bottom = 10;
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);
		
		$position = 0;
		
		if(isset($segments['2']) && '' != $segments['2']  ){
			$position = $segments['2'];	
		}
		
		switch ($position) {
		    case 1: // top left
		        // Copy the stamp image onto our photo using the margin offsets and the photo 
				// width to calculate positioning of the stamp. 
				imagecopy($image, $stamp, $marge_right, $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
		
		        break;
		    case 2: // top right
		        // Copy the stamp image onto our photo using the margin offsets and the photo 
				// width to calculate positioning of the stamp. 
				imagecopy($image, $stamp, imagesx($image) - $sx - $marge_right, $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
		
		        break;
		    case 3: // bottom right
		        // Copy the stamp image onto our photo using the margin offsets and the photo 
				// width to calculate positioning of the stamp. 
				imagecopy($image, $stamp, imagesx($image) - $sx - $marge_right, imagesy($image) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
		
		        break;
		    case 4: // bottom left
		        // Copy the stamp image onto our photo using the margin offsets and the photo 
				// width to calculate positioning of the stamp. 
				imagecopy($image, $stamp, $marge_right, imagesy($image) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
		
		        break;
		
		    default: // center
		        // Copy the stamp image onto our photo using the margin offsets and the photo 
				// width to calculate positioning of the stamp. 
				imagecopy($image, $stamp, imagesx($image)/2 - $sx/2, imagesy($image)/2 - $sy/2, 0, 0, imagesx($stamp), imagesy($stamp));
		
		} 
		
		
		// Output and free memory
		// header('Content-Type: image/jpeg');
		$local_file = imagejpeg($image, $cache_file);
		
	   // file_put_contents($cache_file, $file, LOCK_EX);
	   header('Content-Type: image/jpeg');
	   imagejpeg($image);
	   imagedestroy($image);
	}
	return $local_file;
}



// loadimg($url);

$file = load_remote_url($url);

$local_file = load_local_url($file);



