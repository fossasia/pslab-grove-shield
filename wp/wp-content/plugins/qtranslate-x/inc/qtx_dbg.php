<?php
if ( !defined( 'ABSPATH' ) ) exit;

if(WP_DEBUG){
	if(!function_exists('qtranxf_dbg_log')){
		function qtranxf_dbg_log($msg,$var='novar',$bt=false,$exit=false){
			global $pagenow, $wp_current_filter;
			$h = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : '';
			if(!empty($pagenow)) $h = $h.'('.$pagenow.')';
			if(!empty($wp_current_filter)){
				$cf = end($wp_current_filter);
				if(!empty($cf)){
					$h = $h.'['.$cf;
					$cf = prev($wp_current_filter);
					if(!empty($cf)) $h = $h.','.$cf;
					$h .= ']';
				}
			}
			if(!empty($h)) $msg = $h.': '.$msg;
			if( $var !== 'novar' ){
				$msg .= var_export($var,true);
				//$msg .= print_r($var,true);
			}
			if($bt){
				//$msg .= PHP_EOL.'backtrace:'.PHP_EOL.var_export(debug_backtrace(),true);
				$msg .= PHP_EOL.'backtrace:'.PHP_EOL.print_r(debug_backtrace(),true);
			}
			//$d=ABSPATH.'/wp-logs';
			//if(!file_exists($d)) mkdir($d);
			//$f=$d.'/qtranslate.log';
			$f=WP_CONTENT_DIR.'/debug-qtranslate.log';
			error_log($msg.PHP_EOL,3,$f);
			if($exit) exit();
		}

		function qtranxf_dbg_echo($msg,$var='novar',$bt=false,$exit=false){
			if( $var !== 'novar' )
				$msg .= var_export($var,true);
			echo $msg."<br/>\n";
			if($bt){
				debug_print_backtrace();
			}
			if($exit) exit();
		}

		function qtranxf_dbg_log_if($condition,$msg,$var='novar',$bt=false,$exit=false){
			if($condition)qtranxf_dbg_log($msg,$var,$bt,$exit);
		}

		function qtranxf_dbg_echo_if($condition,$msg,$var='novar',$bt=false,$exit=false){
			if($condition)qtranxf_dbg_echo($msg,$var,$bt,$exit);
		}
	}
	assert_options(ASSERT_BAIL,true);

	/*
	function qtranxf_do_tests(){
		//qtranxf_dbg
		if(file_exists(dirname(__FILE__).'/dev/qtx-tests.php'))
			require_once(dirname(__FILE__).'/dev/qtx-tests.php');
	}
	add_action('qtranslate_init_language','qtranxf_do_tests');
	// */
}
