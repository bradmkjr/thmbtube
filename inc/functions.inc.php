<?php

function generate_cache_folders(){
	if( ! file_exists('./cache')){
		
		mkdir('./cache');
		chmod('./cache', 0777);
		$letters = range('a', 'f');
		
		for($x=0;$x<10;$x++){
			mkdir('./cache/'.$x);
			chmod('./cache/'.$x, 0777);
			
			for($y=0;$y<10;$y++){
				mkdir('./cache/'.$x.'/'.$y);
				chmod('./cache/'.$x.'/'.$y, 0777);
			}
			
			foreach($letters as $y){
				mkdir('./cache/'.$x.'/'.$y);
				chmod('./cache/'.$x.'/'.$y, 0777);
			}
			
		}
		
		foreach($letters as $x){
			mkdir('./cache/'.$x);
			chmod('./cache/'.$x, 0777);
			for($y=0;$y<10;$y++){
				mkdir('./cache/'.$x.'/'.$y);
				chmod('./cache/'.$x.'/'.$y, 0777);
			}
			
			foreach($letters as $y){
				mkdir('./cache/'.$x.'/'.$y);
				chmod('./cache/'.$x.'/'.$y, 0777);
			}
			
		}
	}
} // generate_cache_folders