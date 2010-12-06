<?php

	require_once('json.php');

	define('GSOM_SS_SUBSCRIBED',0);
	define('GSOM_SS_CONFIRMED',1);
	define('GSOM_SS_UNSUBSCRIBED',2);
	
	global $gsom_ajresponse;
	
	$gsom_ajresponse = array();
	
	function jsonresponce($state, $mess) {
		global $gsom_ajresponse;
		global $db;
		
		header("Content-type: application/json");
		
		$gsom_ajresponse['state'] = $state;
		$gsom_ajresponse['msg'] = $mess;
		
		echo gsom_json_encode($gsom_ajresponse);
		//echo "<pre>";
		//print_r(var_dump($gsom_ajresponse),true);
		//echo "</pre>";    
		   
		if ($db) $db->close();  
		die();
	}
		
	function RequestPresent($param) {
		if (!isset($_REQUEST[$param])) {
			
		    if (func_num_args() == 2) {
		    	
		        $def = func_get_arg(1);
		  		return $def;
		  		
		    } else {
		    	jsonresponce('false','required parameter "'.$param.'" is missing in request');
		    }
		        
		} else {		    
		    return $_REQUEST[$param];
		}
	}	
	
	
	function gsom_bad_request($msg = 'Your request has wrong format.') {
		header('HTTP/1.0 400 Bad Request');
		die('<p>'.$msg.'</p>');
	}
	
	function gsom_not_authorized($msg = 'You are not authorized to access this resource.') {
		header("HTTP/1.0 401 Unauthorized");
		die('<p>'.$msg.'</p>');
		
	}	
	
	function gsom_get_request($param, $def = NULL) {
		if (!isset($_REQUEST[$param])) {
			
		    if ($def !== NULL) {
		    	
		  		return $def;		  		
		  		
		    } else {		    	
		    	gsom_bad_request('required parameter "'.$param.'" is missing in request');
		    	return NULL;
		    }
		        
		} else {		    
		    return $_REQUEST[$param];
		}
	}			
	
	
	function gsom_StatusCodeToText($code) {
	    switch ($code) {
	        case GSOM_SS_CONFIRMED:
	            return 'Confirmed';
	        	break;
	        	
	        case GSOM_SS_SUBSCRIBED:
	            return 'Unconfirmed';
	        	break;
	        	
	        case GSOM_SS_UNSUBSCRIBED:
	            return 'unsubscribed';
	        	break;
	    }
	}
	
	// export functions
	
	function gsom_toExcel($results) {
	    
	    $title = 'subscribers';
	        
	    //XML Blurb
	    
		$dataHead = "<?xml version='1.0'?>"
		."<?mso-application progid='Excel.Sheet'?>"
		."<Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>"
		."<Worksheet ss:Name='".$title."'>"
		."<Table>";
		
		$data = '';
		$dataHeadFields = '';
		
		$cf_sequence = array(); // the array of custom fields to keep the right sequence of the custom fields 
	    
	    if ((is_array($results)) && (!empty($results)))
	    foreach($results as $item)
	    {
	        $data .= '<Row>';
	        $data .= "<Cell><Data ss:Type='String'>".$item['varEmail']."</Data></Cell>";
	        $data .= "<Cell><Data ss:Type='String'>".$item['varIP']."</Data></Cell>";
	        $data .= "<Cell><Data ss:Type='String'>".$item['dtTime']."</Data></Cell>";
	        $data .= "<Cell><Data ss:Type='String'>".gsom_StatusCodeToText($item['intStatus'])."</Data></Cell>";
			$data .= "<Cell><Data ss:Type='String'>".$item['varUCode']."</Data></Cell>";
	        
	        $jdform = gsom_CustomFormToAArray($item['textCustomFields']);
	                
	        if (is_array($jdform) && !empty($jdform))
	        {
	            // if sequence is not yet defined, define it from the first form
	            if (empty($cf_sequence)) 
	            {   
	               $cf_sequence = array_keys($jdform);               
	            }
	            
	            // loop thru defined fields first
	            foreach($cf_sequence as $cItem)
	            {                
	                if (isset($jdform[$cItem]))
	                    $data .= "<Cell><Data ss:Type='String'>".$jdform[$cItem]."</Data></Cell>";
	                else
	                    $data .= "<Cell><Data ss:Type='String'></Data></Cell>";
	            }
	            
	            // loop thru the rest fields
	            foreach($jdform as $key => $value)
	            {                
	                if (!in_array($key,$cf_sequence))
	                {
	                    $data .= "<Cell><Data ss:Type='String'>".$value."</Data></Cell>";
	                    $cf_sequence[] = $key;
	                }
	            }
	            
	        }
	        $data .= "</Row>";
	    }
	    
	    
	    // Combining field names head row
	    $dataHeadFields = '<Row>';
	    $dataHeadFields .= "<Cell><Data ss:Type='String'>Email</Data></Cell>";
	    $dataHeadFields .= "<Cell><Data ss:Type='String'>IP Address</Data></Cell>";
	    $dataHeadFields .= "<Cell><Data ss:Type='String'>Time</Data></Cell>";
	    $dataHeadFields .= "<Cell><Data ss:Type='String'>Status</Data></Cell>";
		$dataHeadFields .= "<Cell><Data ss:Type='String'>varUCode</Data></Cell>";
		
	    foreach ($cf_sequence as $item)    
	        $dataHeadFields .= "<Cell><Data ss:Type='String'>".$item."</Data></Cell>";            
	    $dataHeadFields .= "</Row>";    
	     
	    //Final XML Blurb
	    $dataFooter .= "</Table></Worksheet></Workbook>";
	    
	    //header("Content-type: application/xml");
	       
	    header("Content-type: application/xls");
	    header("Content-Disposition: attachment; filename=$title.xls;");
	    header("Content-Type: application/ms-excel");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	
	    echo $dataHead.$dataHeadFields.$data.$dataFooter;
	    die();
	}
	
	function gsom_csvEscape($str, $delimiter) {
	    
	    if (preg_match('/[\r\n'.preg_quote($delimiter).']+/i',$str)) {
	    	
	    	return '"'.$str.'"';
	    	
	    } else {
	    	
	    	return $str;
	    }   
	}	
	
	function gsom_CustomFormToAArray($customForm) {   
	    $res = array(); 
	    $jdform = gsom_json_decode($customForm, true);    
	    
		foreach ($jdform as $item) {
			$x = $item['label'];
			$res[$x] = $item['value'];
		}	    
	    
	    return $res;    
	}	
	
	function gsom_toCSV($results, $delimiter) {
	    
	    $title = 'subscribers';
	       
		$data = '';
		$dataHeadFields = '';
	
		$cf_sequence = array(); // the array of custom fields to keep the right sequence of the custom fields 
	    
	    if (is_array($results) && !empty($results)) {
	    	
			foreach ($results as $item) {
				
			    $data .=    gsom_csvEscape($item['varEmail'], $delimiter).$delimiter.
			                gsom_csvEscape($item['varIP'], $delimiter).$delimiter.
			                gsom_csvEscape($item['dtTime'], $delimiter).$delimiter.
			                gsom_csvEscape(gsom_StatusCodeToText($item['intStatus']), $delimiter).$delimiter.
							gsom_csvEscape($item['varUCode'], $delimiter).$delimiter;
			    
			    $jdform = gsom_CustomFormToAArray($item['textCustomFields']);
			            
			    if (is_array($jdform) && !empty($jdform)) {
			    	
			        // if sequence is not yet defined, define it from the first form
			        if (empty($cf_sequence)) {   
			           $cf_sequence = array_keys($jdform);               
			        }
			        
			        // loop thru defined fields first
			        foreach ($cf_sequence as $cItem) {                
			        	
			            if (isset($jdform[$cItem])) {
			            	
			            	$data .= gsom_csvEscape($jdform[$cItem],$delimiter).$delimiter;
			            	
			            } else {
			            	
			            	$data .= $delimiter;
			            }
			                
			        }
			        
			        // loop thru the rest fields
			        foreach ($jdform as $key => $value) {
			            if (!in_array($key, $cf_sequence)) {
			            	
			                $data .= gsom_csvEscape($value, $delimiter).$delimiter;
			                $cf_sequence[] = $key;
			            }
			        }
			        
			    }
			    
			    $data .= "\r\n";
			}
	    	
	    }
	    
	    
	    // Combining field names head row    
	    $dataHeadFields .= gsom_csvEscape('Email',$delimiter).$delimiter;
	    $dataHeadFields .= gsom_csvEscape('IP Address',$delimiter).$delimiter;
	    $dataHeadFields .= gsom_csvEscape('Time',$delimiter).$delimiter;
	    $dataHeadFields .= gsom_csvEscape('Status',$delimiter).$delimiter;
		$dataHeadFields .= gsom_csvEscape('varUCode',$delimiter).$delimiter;
		
	    foreach ($cf_sequence as $item) {
	    	
	    	$dataHeadFields .= gsom_csvEscape($item,$delimiter).$delimiter;
	    }
	        
	    $dataHeadFields .= "\r\n";    	     
	    
	    header("Content-type: text/csv");
	    header("Content-Disposition: attachment; filename=$title.csv;");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	
	    echo $dataHeadFields.$data;
	    
	    die();
	}		
	
	
	// -----------------------------------------------------------------	AJAX functions
	
	function gsom_aj_get_subscribers() {
		
		global $wpdb;
		global $gsom_ajresponse;
		
		$pg		= RequestPresent('pg','1');
		$ipp	= RequestPresent('ipp','25');
		$email	= RequestPresent('email','');
		$show	= RequestPresent('show','all');
		$q		= RequestPresent('q',false);		
		
		$gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
		
		$pos_from = ($pg-1)*$ipp;
		$pos_to = $ipp;
		
		switch ($show) {
		    case 'all':
		        $sel = '';
		    break;
		    case 'confirmed':
		        $sel = 'WHERE intStatus = '.GSOM_SS_CONFIRMED;
		    break;
		    case 'unconfirmed':
		        $sel = 'WHERE intStatus = '.GSOM_SS_SUBSCRIBED;
		    break;    
		    case 'unsubscribed':
		        $sel = 'WHERE intStatus = '.GSOM_SS_UNSUBSCRIBED;
		    break;    
		}
		
		
		
		if ($q == false) { // if not search request
		
		    $cntres = $wpdb->get_results($wpdb->prepare('SELECT COUNT(intId) as cnt, intStatus FROM '.$gsom_table_subs.' group BY intStatus'),ARRAY_A);
		
		    $cnt_all = 0;
		
		    $gsom_ajresponse['count'] = array();
		    
		    if (($cntres) && (!empty($cntres))) {
		    	
		        foreach($cntres as $item) {
		        	
		            switch($item['intStatus']) {
		            	
		                case GSOM_SS_CONFIRMED:
		                    $gsom_ajresponse['count']['confirmed'] = $item['cnt'];
		                break;
		                case GSOM_SS_SUBSCRIBED:
		                    $gsom_ajresponse['count']['unconfirmed'] = $item['cnt'];
		                break;
		                case GSOM_SS_UNSUBSCRIBED:
		                    $gsom_ajresponse['count']['unsubscribed'] = $item['cnt'];
		                break;        
		                
		            }   
		             
		            $cnt_all += $item['cnt'];
		        }
		        
		    } else {
		    	
		        $gsom_ajresponse['count']['confirmed'] = $item['cnt'];
		        $gsom_ajresponse['count']['unconfirmed'] = $item['cnt'];
		        $gsom_ajresponse['count']['unsubscribed'] = $item['cnt'];
		        
		    }
		
		    $gsom_ajresponse['count']['all'] = $cnt_all;    
		    
		    switch ($show) {
		        case 'all':
		            $cnt = $gsom_ajresponse['count']['all'];
		        break;
		        case 'confirmed':
		            $cnt = $gsom_ajresponse['count']['confirmed'];
		        break;
		        case 'unconfirmed':
		            $cnt = $gsom_ajresponse['count']['unconfirmed'];
		        break;    
		        case 'unsubscribed':
		            $cnt = $gsom_ajresponse['count']['unsubscribed'];
		        break;    
		    }
		
		    if (($cnt < 1) || ($ipp < 1))  {
		    	$gsom_ajresponse['pageCount'] = 1;
		    } else {
		    	$gsom_ajresponse['pageCount'] = ceil($cnt / $ipp);
		    }   
		
		    $sql = $wpdb->prepare('SELECT * FROM '.$gsom_table_subs.' '.$sel.' ORDER BY intId DESC LIMIT %d, %d',$pos_from,$pos_to);
		}
		else // if search request
		{
		    $sql = $wpdb->prepare('SELECT COUNT(intId) as cnt, intStatus FROM '.$gsom_table_subs.' WHERE varEmail regexp %s group BY intStatus',preg_quote($q));
		    //echo $sql;
		    
		    $cntres = $wpdb->get_results($sql,ARRAY_A);
		
		    $cnt_all = 0;
		
		    $gsom_ajresponse['count'] = array();
			$gsom_ajresponse['count']['confirmed'] = 0;
		    $gsom_ajresponse['count']['unconfirmed'] = 0;
			$gsom_ajresponse['count']['unsubscribed'] = 0;	
			$gsom_ajresponse['count']['all'] = 0;
		
			if ($cntres && (!empty($cntres))) {
			    foreach($cntres as $item)
			    {
			        switch($item['intStatus'])
			        {
			            case GSOM_SS_CONFIRMED:
			                $gsom_ajresponse['count']['confirmed'] = $item['cnt'];
			            break;
			            case GSOM_SS_SUBSCRIBED:
			                $gsom_ajresponse['count']['unconfirmed'] = $item['cnt'];
			            break;
			            case GSOM_SS_UNSUBSCRIBED:
			                $gsom_ajresponse['count']['unsubscribed'] = $item['cnt'];
			            break;        
			        }    
			        $cnt_all += $item['cnt'];
			    }
				$gsom_ajresponse['count']['all'] = $cnt_all;    
			}
		
		    
		    
		    switch($show)
		    {
		        case 'all':
		            $cnt = $gsom_ajresponse['count']['all'];
		        break;
		        case 'confirmed':
		            $cnt = $gsom_ajresponse['count']['confirmed'];
		        break;
		        case 'unconfirmed':
		            $cnt = $gsom_ajresponse['count']['unconfirmed'];
		        break;    
		        case 'unsubscribed':
		            $cnt = $gsom_ajresponse['count']['unsubscribed'];
		        break;    
		    }
		
		    if(($cnt < 1) || ($ipp < 1))        
		        $gsom_ajresponse['pageCount'] = 1;
		    else
		        $gsom_ajresponse['pageCount'] = ceil($cnt / $ipp);
		        
		    if (trim($sel) == '')
		    {
		        $sel .= 'WHERE varEmail regexp %s';
		    }
		    else
		    {
		        $sel .= ' AND varEmail regexp %s';
		    }
		        
		    $sql = $wpdb->prepare('SELECT * FROM '.$gsom_table_subs.' '.$sel.' ORDER BY intId DESC LIMIT %d, %d',preg_quote($q),$pos_from,$pos_to);   
		    
		   // echo $sql;
		        
		}
		
		    
		$result = $wpdb->get_results($sql,ARRAY_A);
		
		$gsom_ajresponse['rows'] = array();
		
		if (($result !== false) && is_array($result) && !empty($result))
		{
		    foreach ($result as $row)
		    {
		        $gsom_ajresponse['rows'][] = array('id' => $row['intId'],
		                                    'time' => $row['dtTime'],
		                                    'ip' => $row['varIP'],
		                                    'email' => $row['varEmail'],
		                                    'customData' => utf8_encode($row['textCustomFields']),
		                                    'status' => $row['intStatus']);
		    }
		}
		
		if (function_exists('get_option'))
		{
		    $gsom_ajresponse['url'] = get_option('siteurl');
		    jsonresponce('true','success');
		}
		else    
		    jsonresponce('false','no get option');        
		
		
		
		
	}
	
	add_action('wp_ajax_gsom_aj_get_subscribers', 'gsom_aj_get_subscribers');
	
	function gsom_aj_delete_subscriber() {
		
		global $wpdb;
		global $gsom_ajresponse;
		
		$json = RequestPresent('json','[]');
		
		$gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
		
		$ejson = gsom_json_decode(gsom_mod_remslashes($json),true);
		
		if ((!is_array($ejson)) || empty($ejson))
		    jsonresponce('false','There is an error in json request.');
		    
		$first = true;
		
		$sel = '';
		    
		foreach($ejson as $item)
		{
		    if ($first)
		        $first = false;
		    else
		        $sel .= ' OR ';    
		    $sel .= ' intId = '.$item;
		}
		
		
		$sql = $wpdb->prepare('DELETE FROM '.$gsom_table_subs.' where '.$sel);
		             
		$result = $wpdb->query($sql);
		
		if ($result !== false)
		    jsonresponce('true','success');
		else
		    jsonresponce('false',$wpdb->last_error);		
	}

	add_action('wp_ajax_gsom_aj_delete_subscriber', 'gsom_aj_delete_subscriber');
	
	function gsom_aj_get_broadcast_status() {
		if (gsom_broadcastLocked()) {
			jsonresponce('true','broadcasting');
		} else {
			jsonresponce('true','done');
		}			
	}
	
	add_action('wp_ajax_gsom_aj_get_broadcast_status', 'gsom_aj_get_broadcast_status');
	
	function gsom_aj_get_options() {
		
		global $gsom_ajresponse;
		
		$gsom_ajresponse['options'] = array();
		$o = RequestPresent('o');
		
		$opts = explode(',',$o);
		
		foreach ($opts as $item){
		    $gsom_ajresponse['options'][trim($item)] = get_option(trim($item));
		}
		
		jsonresponce('true','success');		
	}

	add_action('wp_ajax_gsom_aj_get_options', 'gsom_aj_get_options');
	
	function gsom_aj_get_posts() {

		global $wpdb;
		global $post;
		global $gsom_ajresponse;
		
		$query		= RequestPresent('query');
		$limit		= RequestPresent('limit');
		$mysqldate	= RequestPresent('mysqldate');		
		
		$i = 1;                                     
		$posts = array();
		
		$df = get_option('date_format');
		$tf = get_option('time_format');       
		
		$show_full_post = get_option('gsom_show_full_posts') == '1' ? true : false;
		$exlen = get_option('gsom_rss_excerpt_length');
		
		    /*
		    	category_name=
		    	cat=22
		    	year=$current_year
		    	monthnum=$current_month
		    	order=ASC
		    	tag=bread,baking
		    	author=3
		    	caller_get_posts=1
		    	author=1
		    	post_type=page
		    	post_status=publish
		    	orderby=title
		    	order=ASC
		    	
		    *  hour= - hour (from 0 to 23)
		    * minute= - minute (from 0 to 60)
		    * second= - second (0 to 60)
		    * day= - day of the month (from 1 to 31)
		    * monthnum= - month number (from 1 to 12)
		    * year= - 4 digit year (e.g. 2009)
		    * w= - week of the year (from 0 to 53) and uses the MySQL WEEK command Mode=1.     	
		    
		    
		    */
		    
		    
		    
		    //$gsom_ajresponse['limit'] = $limit;
		    
		    //$selTime = mktime(0, 0, 0, $monthnum, $day, $year);
		    $selTime = strtotime($mysqldate);
		    
		    query_posts($query.'&post_status=publish');
		    
		    while (have_posts()) {
		        the_post();            
		        $pt = gsom_postTime();      
		        
		        
		        $content = gsom_get_fancy_excerpt(150);        
		        
		        if ('publish' == $post->post_status) {
		        	if ($pt > $selTime) {
					$posts[] =	array(	'date' => date($df.' '.$tf, $pt),
		                        		'title' => gsom_postTitle(),
		                                'description' => $content,
		                                'link' => gsom_postPermalink(),
		                                'number' => $i);
					}
					$i++;
		        }        
		        // if ($i >= $limit) {
		        // 	break;
		        // }
		        
		    }
		    
		    $gsom_ajresponse['posts'] = $posts;    
		    
		    jsonresponce('true','success');		
	}
	
	add_action('wp_ajax_gsom_aj_get_posts', 'gsom_aj_get_posts');
	
	function gsom_aj_run_broadcast() {
		
		global $gsom_ajresponse;
		
		//error_reporting(0);
		
		ignore_user_abort(true);
		
		$broadcaster_url = gsom_plugin_url.'/broadcast.php?check='. wp_hash('gsom1384695');		
		
		$gsom_ajresponse['bu'] = $broadcaster_url;
		
		//$resp = wp_remote_get($broadcaster_url, array('timeout' => 1, 'blocking' => false));
		
		// uncomment next line to force failure
		//$resp = new WP_Error('broke', "I'm broken");	
		// --
			
/*
		if ( is_wp_error( $resp ) ) {
			jsonresponce('false','Broadcast failed to start. Error: '.$resp->get_error_message());			
		} else {*/
			jsonresponce('true','Broadcast started');		
		//}
		
		
	}
	
	add_action('wp_ajax_gsom_aj_run_broadcast', 'gsom_aj_run_broadcast');
	
	function gsom_test_broadcast() {
		
		global $wpdb;
		global $gsom_table_subs;      
		global $gsom_form_vars;		
		global $gsom_ajresponse;
		
		$testemail = RequestPresent('testemail');
		
		$gsom_eml = get_option('gsom_email_from');
		$gsom_frname = get_option('gsom_name_from');
		$gsom_email_from = (trim($gsom_eml) != '') ? $gsom_eml : get_option('admin_email');
		$gsom_name_from = (trim($gsom_frname) != '') ? $gsom_frname : get_option('blogname');
		
		$email = $testemail;
		
		$newposts = gsom_getNewPostsfrom(false,10);
		
		//-----------------------------------------

		      
		        $df = get_option('date_format');
		        $tf = get_option('time_format');   
		
		        if (count($newposts) == 0){
		            jsonresponce('false','You have no posts in your blog to run this test.');
		        }
		
		        $lastPost = $newposts[0];
		
		        foreach($lastPost as $key => $value) {
		        $gsom_form_vars['last_'.$key] = $value;          
		        }
		            
		        $subj = get_option('gsom_bcst_email_subj');
		        $msg_html = get_option('gsom_bcst_email_msg');
		        $msg_plain = get_option('gsom_bcst_email_msg_plain');
		
		        // expandin rss loop template and filling rss loop vars
		        $msg_html = gsom_fillRssLoopTemplate($msg_html,$newposts);
		        $msg_plain = gsom_fillRssLoopTemplate($msg_plain,$newposts);
		
		        $gsom_form_vars['rss_channel_title'] = get_option('name');
		        $gsom_form_vars['rss_channel_description'] = get_option('blogdescription');
		        $gsom_form_vars['rss_channel_link'] = get_option('siteurl');      
		
		        // filling variables
		        $gsom_form_vars['gsom_email_field'] = $email;                   
		
		        $gsom_form_vars['manage_subscription_link'] = gsom_GetUnsubscribeLink($email);
		        $gsom_form_vars['confirmation_link'] = gsom_GetConfirmationLink($email);
		        $gsom_form_vars['resend_confirmation_link'] = gsom_GetResendConfirmationLink($email);    
		        $gsom_form_vars['encoded_email'] = $email;
		        $gsom_form_vars['decoded_email'] = gsom_ModBase64Encode($email);
		
		        $message = array('plain' => $msg_plain,
		                         'html' => $msg_html);          
		        // sending email          
		        $res = gsom_Mail($subj,$message);
		
		
		//----------------------------------------- 
		
		if ($result !== false)
		    jsonresponce('true','Test email was sent to '.$email.'.');
		else
		    jsonresponce('false',$res);		
	}
	
	add_action('wp_ajax_gsom_test_broadcast', 'gsom_test_broadcast');
	
	function gsom_test_smtp() {
		
		global $gsom_ajresponse;
		global $gsom_form_vars;
		
		$host		= RequestPresent('host');
		$username	= RequestPresent('username');
		$password	= RequestPresent('password');
		$port		= RequestPresent('port');
		$testemail	= RequestPresent('testemail');
		$auth		= RequestPresent('auth');
		$mdo		= RequestPresent('mdo');
		
		
		$mail_opts = array(
			'gsom_mail_delivery_option' => $mdo,
			'gsom_smtp_secure_conn' => $auth,	
			'gsom_smtp_hostname' => $host,
			'gsom_smtp_username' => $username,
			'gsom_smtp_password' => $password,
			'gsom_smtp_port' => $port
		);
		
		$subject = 'Your SMTP settings are correct';
		$body = 'If you read this message, your SMTP settings in G-Lock Double Opt-In plugin are correct.';
		
		// filling variables
		$email = $testemail;
		$gsom_form_vars['gsom_email_field'] = $email;                   
		
		$message = array('plain' => $body,
		                 'html' => '');      
		
		$res = gsom_Mail($subject, $message, false, $mail_opts);
		
		//----------------------------------------- 
		
		if ($result !== false)
		    jsonresponce('true','Test email was sent to '.$email.'.');
		else
		    jsonresponce('false',$res);		
	}
	
	add_action('wp_ajax_gsom_test_smtp', 'gsom_test_smtp');
	
	function gsom_aj_unsubscribe() {
				
		global $wpdb;
		global $gsom_ajresponse;
		
		$json = RequestPresent('json','[]');
		
		$gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
		
		$ejson = gsom_json_decode(gsom_mod_remslashes($json),true);
		
		if ((!is_array($ejson)) || empty($ejson))
		    jsonresponce('false','There is an error in json request.');
		    
		$first = true;
		
		$sel = '';
		    
		foreach($ejson as $item)
		{
		    if ($first)
		        $first = false;
		    else
		        $sel .= ' or ';    
		    $sel .= ' intId = '.$item;
		}
		
		
		$sql = $wpdb->prepare('UPDATE '.$gsom_table_subs.' SET intStatus = %d where '.$sel,GSOM_SS_UNSUBSCRIBED);
		                      
		$result = $wpdb->query($sql);
		
		if ($result !== false)
		    jsonresponce('true','success');
		else
		    jsonresponce('false',$wpdb->last_error);		
		    
	}
	
	add_action('wp_ajax_gsom_aj_unsubscribe', 'gsom_aj_unsubscribe');
	
	function gsom_aj_update_broadcast_settings() {
		
		global $gsom_ajresponse;
		
		$gsom_bcst_sel_cat = RequestPresent('gsom_bcst_sel_cat');
	
		$gsom_last_broadcast = RequestPresent('gsom_last_broadcast');
		$gsom_last_broadcast = mysql2date('U',$gsom_last_broadcast, false);
	
		$gsom_bcst_sel_auth			= RequestPresent('gsom_bcst_sel_auth');
		$gsom_show_full_posts		= RequestPresent('gsom_show_full_posts');	
		$gsom_bcst_when				= RequestPresent('gsom_bcst_when');	
		$gsom_bcst_number_of_posts	= RequestPresent('gsom_bcst_number_of_posts');
		$gsom_bcst_day_number		= RequestPresent('gsom_bcst_day_number');
		$gsom_bcst_send				= RequestPresent('gsom_bcst_send');
		$gsom_show_full_posts		= RequestPresent('gsom_show_full_posts');
		$gsom_rss_excerpt_length	= RequestPresent('gsom_rss_excerpt_length');
		$gsom_feed_limit			= RequestPresent('gsom_feed_limit');
		$gsom_filter_images			= RequestPresent('gsom_filter_images');
			
		$gsom_bcst_email_subj		= RequestPresent('gsom_bcst_email_subj');
		$gsom_bcst_email_msg		= RequestPresent('gsom_bcst_email_msg');
		$gsom_bcst_email_msg_plain	= RequestPresent('gsom_bcst_email_msg_plain');
	
		update_option('gsom_bcst_sel_cat', $gsom_bcst_sel_cat);
		update_option('gsom_last_broadcast', $gsom_last_broadcast);
		update_option('gsom_bcst_sel_auth', $gsom_bcst_sel_auth);
		update_option('gsom_show_full_posts', $gsom_show_full_posts);	
		update_option('gsom_bcst_when',$gsom_bcst_when);	
		update_option('gsom_bcst_number_of_posts', $gsom_bcst_number_of_posts);
		update_option('gsom_bcst_day_number', $gsom_bcst_day_number);
		gsom_log('OPTION UPDATE gsom_bcst_send: '.$gsom_bcst_send);
		update_option('gsom_bcst_send',$gsom_bcst_send);
		update_option('gsom_show_full_posts', $gsom_show_full_posts);
		update_option('gsom_rss_excerpt_length', $gsom_rss_excerpt_length);
		update_option('gsom_feed_limit', $gsom_feed_limit);
		update_option('gsom_filter_images', $gsom_filter_images);
		
		
		update_option('gsom_bcst_email_subj', stripslashes($gsom_bcst_email_subj));
		update_option('gsom_bcst_email_msg', stripslashes($gsom_bcst_email_msg));
		update_option('gsom_bcst_email_msg_plain', stripslashes($gsom_bcst_email_msg_plain));		
		
		if (gsom_broadcastLocked()) {
			$gsom_ajresponse['bcst_status'] = 'broadcasting';
		} else {
			$gsom_ajresponse['bcst_status'] = 'idle';
		}        	
	
	    jsonresponce('true','success');		
	}
	
	add_action('wp_ajax_gsom_aj_update_broadcast_settings', 'gsom_aj_update_broadcast_settings');	
	
	
	function gsom_export() {
		
		$gsom_form = get_option('gsom_form');    
		    
		$type	= gsom_get_request('type','all');
		$to		= gsom_get_request('to','xml');
		$q		= gsom_get_request('q',false);
		
		
		global $wpdb;
		$gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
		
		switch ($type) {
			
		    case 'all':
		        $sel = '';
		    	break;
		    	
		    case 'confirmed':
		        $sel = 'WHERE intStatus = '.GSOM_SS_CONFIRMED;
		    	break;
		    	
		    case 'unconfirmed':
		        $sel = 'WHERE intStatus = '.GSOM_SS_SUBSCRIBED;
		    	break;    
		    	
		    case 'unsubscribed':
		        $sel = 'WHERE intStatus = '.GSOM_SS_UNSUBSCRIBED;
		    	break;    
		    
		}
		
		if ($q == false) {
			
		    $sql = $wpdb->prepare('SELECT * FROM '.$gsom_table_subs.' '.$sel.' ORDER BY intId DESC');
		    
		} else {
			
		    if (trim($sel) == '') {
		    	
		        $sel .= 'WHERE varEmail regexp %s';
		        
		    } else {
		    	
		        $sel .= ' AND varEmail regexp %s';
		        
		    }
		        
		    $sql = $wpdb->prepare('SELECT * FROM '.$gsom_table_subs.' '.$sel.' ORDER BY intId DESC',preg_quote($q));
		}
		    
		$result = $wpdb->get_results($sql,ARRAY_A);
		
		switch ($to) {
			
		    case 'csv':
		        gsom_toCSV($result,',');
		    	break;
		    	
		    case 'xml':
		        gsom_toExcel($result);     
		    	break;
		}		
	}

	add_action('wp_ajax_gsom_export', 'gsom_export');	
	
	
?>