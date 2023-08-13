	<?php
	define('DEBUG', false);
	if (DEBUG) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	} else {
		error_reporting(0);
	}
	$test_writeable_file = "/home/api/cbs/cbs.txt";
	///PRODUCTION

	$type  = !empty($_REQUEST['type']) ? strtoupper($_REQUEST['type']) : "";

	/**********************************************Nora*********************************************/

	$result = "";
	if ($type == "INITUSER") { ////Nora
		$result = initUser();
	}else if ($type == "CONDITIONAREA"){
        $result = conditionArea();
    }
	else if ($type == "HIGHRISKPRAG"){
        $result = highRiskPregnancy();
    }else if ($type == "CONFIRMDELIVERY"){
        $result = confirmDelivery();
    }else if ($type == "UNSUBSCRIBE"){
        $result = unsubscribeUser();
    }
	echo $result;
	exit();



	function insertLeads(){
		require_once ('db_conf.php');
		db_conn ();
		//$callUrl = "http://192.168.1.32/mbm/public/api/mbm/subscription/enquiry";
		$cli = !empty($_REQUEST['CLI']) ? substr($_REQUEST['CLI'], -11) : "";
		$callid = !empty($_REQUEST['CALLID']) ? $_REQUEST['CALLID'] : "";
		$ltype = !empty($_REQUEST['ltype']) ? $_REQUEST['ltype'] : "";
		//$id = !empty($_REQUEST['refno']) ? $_REQUEST['refno'] : "";
		$rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
		$tstmp = substr($primaryID, -6);
		$id = $rand . $tstmp;
		$skill_id = !empty($_REQUEST['skill_id']) ? $_REQUEST['skill_id'] : "";
		date_default_timezone_set('Asia/Dhaka');
		$timestamp  = date("Y-m-d H:i:s");
		//$timestmOneHourAdd = date("Y-m-d H:i:s", strtotime($timestamp  . " +1 hour"));
		$timestmOneHourAdd = date("Y-m-d H:i:s", strtotime($timestamp));
		//if($name != '' && $email != '' && $conn_name != ''){
		$sql = "INSERT INTO leads SET id='$id',skill_id='$skill_id', number_1='$cli',custom_value_1='$callid',custom_value_4='$timestmOneHourAdd',type='$ltype',updated_at='$timestmOneHourAdd'";
		 //var_dump($sql);die();
		$requestResults = db_update($sql);
		 //var_dump($requestResults);die();
		 if($requestResults=='1'){
			$result = '[{"status":"true","responseCode":"100","responseMessage":"Leads Data Successfully Inserted"}]';
			return $result; 
		 }else{
			$result = '[{"status":"false","responseCode":"101","responseMessage":"Leads Data is not Successfully Inserted"}]';
			return $result;  
		 }
		
	}


	function initUser(){
		require_once ('db_conf.php');
		db_conn ();
		$curl = curl_init();
		$callUrl = "https://staging.noorahealth.org/bd-res-signup/res/ivr/initialize";
		$cli = !empty($_REQUEST['CLI']) ? substr($_REQUEST['CLI'], -11) : "";
		$callid = !empty($_REQUEST['CALLID']) ? $_REQUEST['CALLID'] : "";
		$did = !empty($_REQUEST['did']) ? $_REQUEST['did'] : "";
		$uSelection = !empty($_REQUEST['usel']) ? $_REQUEST['usel'] : "";
		date_default_timezone_set('Asia/Dhaka');
		$timestamp  = date("Y-m-d H:i:s");
		$direction='Outgoing';
		$rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
		$tstmp = substr($primaryID, -6);
		$id = $rand . $tstmp;
		$postData = array(
			"CallSid" => $callid,
			"Direction" =>$direction,
			"StartTime" => $timestamp,
			"EndTime" => "1970-01-01 05:30:00",
			"From" =>$did,
			"To" => $cli,
			"CurrentTime" => $timestamp,
			"UserSelection" => $uSelection
		);
        curl_setopt($curl, CURLOPT_URL, $callUrl);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	   //var_dump($postData);die();
	   //$data = curl_exec($curl);
	    $data = curl_exec($curl);
	    if($data == false){
		   $result['error'] = curl_error($curl);
	    }else{
		   $result = $data;
	    }
	    curl_close($curl);
		$requestData = serialize($postData);
        $responseData = serialize($result);
		$sql = "INSERT INTO log_endpoints (ref_id, callid, direction, call_start_time, call_end_time, call_from, call_to,currentDate,user_selection,request_data,response_data)
            VALUES ('$id', '$callid', '$direction', '$timestamp',' ', '$did', '$cli', '$timestamp','$uSelection','$requestData','$responseData')";
			//var_dump($sql);die();
		$requestResults = db_update($sql);
		return $result;
		
	}
	
	
	function conditionArea(){
		$curl = curl_init();
		$callUrl = "https://staging.noorahealth.org/bd-res-signup/res/ivr/select_condition_area";
		$cli = !empty($_REQUEST['CLI']) ? substr($_REQUEST['CLI'], -11) : "";
		$callid = !empty($_REQUEST['CALLID']) ? $_REQUEST['CALLID'] : "";
		$did = !empty($_REQUEST['did']) ? $_REQUEST['did'] : "";
		$uSelection = !empty($_REQUEST['usel']) ? $_REQUEST['usel'] : "";
		date_default_timezone_set('Asia/Dhaka');
		$timestamp  = date("Y-m-d H:i:s");
		$direction='Outgoing';
		$rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
		$tstmp = substr($primaryID, -6);
		$id = $rand . $tstmp;
		$postData = array(
			"CallSid" => $callid,
			"Direction" =>$direction,
			"StartTime" => $timestamp,
			"EndTime" => "1970-01-01 05:30:00",
			"From" =>$did,
			"To" => $cli,
			"CurrentTime" => $timestamp,
			"UserSelection" => $uSelection
		);
        curl_setopt($curl, CURLOPT_URL, $callUrl);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	   //var_dump($postData);die();
	   //$data = curl_exec($curl);
	    $data = curl_exec($curl);
	    if($data == false){
		   $result['error'] = curl_error($curl);
	    }else{
		   $result = $data;
	    }
	    curl_close($curl);
		$requestData = serialize($postData);
        $responseData = serialize($result);
		$sql = "INSERT INTO log_endpoints (ref_id, callid, direction, call_start_time, call_end_time, call_from, call_to,currentDate,user_selection,request_data,response_data)
            VALUES ('$id', '$callid', '$direction', '$timestamp',' ', '$did', '$cli', '$timestamp','$uSelection','$requestData','$responseData')";
			//var_dump($sql);die();
		$requestResults = db_update($sql);

	    return $result;
		
	}
	
	
	function highRiskPregnancy(){
		$curl = curl_init();
		$callUrl = "https://staging.noorahealth.org/bd-res-signup/res/ivr/select_high_risk";
		$cli = !empty($_REQUEST['CLI']) ? substr($_REQUEST['CLI'], -11) : "";
		$callid = !empty($_REQUEST['CALLID']) ? $_REQUEST['CALLID'] : "";
		$did = !empty($_REQUEST['did']) ? $_REQUEST['did'] : "";
		$uSelection = !empty($_REQUEST['usel']) ? $_REQUEST['usel'] : "";
		date_default_timezone_set('Asia/Dhaka');
		$timestamp  = date("Y-m-d H:i:s");
		$direction='Outgoing';
		$rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
		$tstmp = substr($primaryID, -6);
		$id = $rand . $tstmp;
	    $postData = array(
			"CallSid" => $callid,
			"Direction" =>$direction,
			"StartTime" => $timestamp,
			"EndTime" => "1970-01-01 05:30:00",
			"From" =>$did,
			"To" => $cli,
			"CurrentTime" => $timestamp,
			"UserSelection" => $uSelection
		);
        curl_setopt($curl, CURLOPT_URL, $callUrl);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	   //var_dump($postData);die();
	   //$data = curl_exec($curl);
	    $data = curl_exec($curl);
	    if($data == false){
		   $result['error'] = curl_error($curl);
	    }else{
		   $result = $data;
	    }
	    curl_close($curl);
		$requestData = serialize($postData);
        $responseData = serialize($result);
		$sql = "INSERT INTO log_endpoints (ref_id, callid, direction, call_start_time, call_end_time, call_from, call_to,currentDate,user_selection,request_data,response_data)
            VALUES ('$id', '$callid', '$direction', '$timestamp',' ', '$did', '$cli', '$timestamp','$uSelection','$requestData','$responseData')";
			//var_dump($sql);die();
		$requestResults = db_update($sql);

	    return $result;
		
	}
	
	
	function confirmDelivery(){
		$curl = curl_init();
		$callUrl = "https://staging.noorahealth.org/bd-res-signup/res/ivr/confirm_delivery";
		$cli = !empty($_REQUEST['CLI']) ? substr($_REQUEST['CLI'], -11) : "";
		$callid = !empty($_REQUEST['CALLID']) ? $_REQUEST['CALLID'] : "";
		$did = !empty($_REQUEST['did']) ? $_REQUEST['did'] : "";
		$uSelection = !empty($_REQUEST['usel']) ? $_REQUEST['usel'] : "";
		date_default_timezone_set('Asia/Dhaka');
		$timestamp  = date("Y-m-d H:i:s");
		$direction='Outgoing';
		$rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
		$tstmp = substr($primaryID, -6);
		$id = $rand . $tstmp;
		$postData = array(
			"CallSid" => $callid,
			"Direction" =>$direction,
			"StartTime" => $timestamp,
			"EndTime" => "1970-01-01 05:30:00",
			"From" =>$did,
			"To" => $cli,
			"CurrentTime" => $timestamp,
			"UserSelection" => $uSelection
	    );
        curl_setopt($curl, CURLOPT_URL, $callUrl);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	   //var_dump($postData);die();
	   //$data = curl_exec($curl);
	    $data = curl_exec($curl);
	    if($data == false){
		   $result['error'] = curl_error($curl);
	    }else{
		   $result = $data;
	    }
	    curl_close($curl);
		$requestData = serialize($postData);
        $responseData = serialize($result);
		$sql = "INSERT INTO log_endpoints (ref_id, callid, direction, call_start_time, call_end_time, call_from, call_to,currentDate,user_selection,request_data,response_data)
            VALUES ('$id', '$callid', '$direction', '$timestamp',' ', '$did', '$cli', '$timestamp','$uSelection','$requestData','$responseData')";
			//var_dump($sql);die();
		$requestResults = db_update($sql);

	    return $result;
		
	}
	
	
	function unsubscribeUser(){
		$curl = curl_init();
		$callUrl = "https://staging.noorahealth.org/bd-res-signup/res/ivr/unsubscribe";
		$cli = !empty($_REQUEST['CLI']) ? substr($_REQUEST['CLI'], -11) : "";
		$callid = !empty($_REQUEST['CALLID']) ? $_REQUEST['CALLID'] : "";
		$did = !empty($_REQUEST['did']) ? $_REQUEST['did'] : "";
		$uSelection = !empty($_REQUEST['usel']) ? $_REQUEST['usel'] : "";
		date_default_timezone_set('Asia/Dhaka');
		$timestamp  = date("Y-m-d H:i:s");
		$direction='Outgoing';
		$rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
		$tstmp = substr($primaryID, -6);
		$id = $rand . $tstmp;
		$postData = array(
			"CallSid" => $callid,
			"Direction" =>$direction,
			"StartTime" => $timestamp,
			"EndTime" => "1970-01-01 05:30:00",
			"From" =>$did,
			"To" => $cli,
			"CurrentTime" => $timestamp,
			"UserSelection" => $uSelection
	    );
        curl_setopt($curl, CURLOPT_URL, $callUrl);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	   //var_dump($postData);die();
	   //$data = curl_exec($curl);
	    $data = curl_exec($curl);
	    if($data == false){
		   $result['error'] = curl_error($curl);
	    }else{
		   $result = $data;
	    }
	    curl_close($curl);
		$requestData = serialize($postData);
        $responseData = serialize($result);
		$sql = "INSERT INTO log_endpoints (ref_id, callid, direction, call_start_time, call_end_time, call_from, call_to,currentDate,user_selection,request_data,response_data)
            VALUES ('$id', '$callid', '$direction', '$timestamp',' ', '$did', '$cli', '$timestamp','$uSelection','$requestData','$responseData')";
			//var_dump($sql);die();
		$requestResults = db_update($sql);

	    return $result;
		
	}
	



	function callForCurl($callUrl, $data_string, Array $header, String $method){
		$ch = curl_init($callUrl);
		$result=[];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $callUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		$data = curl_exec($ch);
		if($data == false){
			$result['error'] = curl_error($ch);
		}else{
			$result = $data;
		}
		curl_close($ch);

		return $result;
	}


	function xmlToArrya($data){
		$xmlResult = new SimpleXMLElement($data);
		return json_decode(json_encode((array)$xmlResult), TRUE);
	}

	function jsonToArray($string) {
		$r = json_decode($string, 1);
		if(json_last_error() === JSON_ERROR_NONE){// No error
			return $r;
		}
		return false;
	}

