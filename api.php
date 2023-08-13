<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

//ini_set('display_errors', '1');
//error_reporting(E_ALL); 
//error_reporting(0);


class api{
    private $maxLeads, $currency, $txnType;

    public function __construct()
    { 
        require 'db_conf.php';
		require 'constant.php';
        date_default_timezone_set('Asia/Dhaka');
        db_conn();
        $this->maxLeads = 1000;
        
    }

    private function getApiUser($username){
        global $mysqli;
        $data = [];
        if(strlen($username) <= 15 && ctype_alnum($username) && ctype_alpha(substr($username,0,1)) ){
            $sql = "SELECT * FROM api_users WHERE username = ? AND status = 'A' LIMIT 1";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $data = $result->fetch_assoc();
            }
            $stmt->close();
        }
        
        return $data;

    }

    public function uploadLeads(){
        global $mysqli;
        $currentTimestamp = time();

        
        $headerRequest =  apache_request_headers();
        $inputData = file_get_contents("php://input");
        $request = json_decode($inputData);
        
        $response = [
            'result'            => false,
            'responseTimestamp' => $currentTimestamp,
            'resultCode'        => 406,
            'request_param'     => $request,
            'message'           => ["Invalid header request!"],
        ];

        if(!empty($request) && isset($headerRequest['keyHash']) && !empty($headerRequest['keyHash']) && isset($headerRequest['Content-Type']) && $headerRequest['Content-Type'] == "application/json"){
            
            $keyHash    = trim($headerRequest['keyHash']);
            $authInfo   = $this->getApiUser($request->username);

            if(!empty($authInfo) &&  md5($authInfo['username'].":".$authInfo['password'].":".$request->requestTimestamp) == $keyHash 
            && (abs($currentTimestamp - $request->requestTimestamp) <= 1200 )) {

                $leads      = $request->leads;
                $validate   = $this->validateLeadsData($leads);

                if($validate['result'] == true) { 

                    foreach($leads as $lead) { 

                        $sql            = "INSERT INTO `leads` SET `id` = ?, `skill_id` = ?, `number_1` = ?, `customer_id` = ?, `custom_value_1` = ?, `custom_value_2` = ?, `custom_value_3` = ?";
                        $id             = $this->generateId();
                        $customerId     = substr(bin2hex(random_bytes(8)), 0, 16);
                        $customerNum    = substr(trim($lead->mobileNumber), -11);
						$campCategory   = strtoupper($lead->campCategory);

                        if (isset(skill_list[$campCategory.'-'.$lead->campDay])) {

                           $skillId = skill_list[$campCategory.'-'.$lead->campDay];

                           $stmt = $mysqli->prepare($sql);
                           $stmt->bind_param("issssss", $id, $skillId,  $customerNum, $customerId, $lead->campDay, $lead->campCategory, $lead->currentDate);
                           $stmt->execute();
                           $stmt->close();


                        } else {

                            $response = [
                                'result'            => false,
                                'responseTimestamp' => $currentTimestamp,
                                'resultCode'        => 410,
                                'message'           => ["Invalid campaign date ".$lead->campDay." or campaign category ".$lead->campCategory],
                            ]; 
                        }
						
						

                    }

                    $response = [
                        'result'            => true,
                        'responseTimestamp' => $currentTimestamp,
                        'resultCode'        => 200,
                        'message'           => ["Leads uploaded successfully!"],
                    ];

                } else {

                    $response = $validate;

                }

            } else {

                $response = [
                    'result'            => false,
                    'responseTimestamp' => $currentTimestamp,
                    'resultCode'        => 401,
                    'message'           => ["Authentication failed!"],
                ];

            }
            

        }
       
        echo json_encode( $response );
        
    }
	
	
	public function deleteLeads() {
    global $mysqli;
    $currentTimestamp = time();

    $headerRequest = apache_request_headers();
    $inputData = file_get_contents("php://input");
    $request = json_decode($inputData);

    $response = [
        'result' => false,
        'responseTimestamp' => $currentTimestamp,
        'resultCode' => 406,
        'request_param' => $request,
        'message' => ["Invalid header request!"],
    ];

    if (!empty($request) && isset($headerRequest['keyHash']) && !empty($headerRequest['keyHash']) && isset($headerRequest['Content-Type']) && $headerRequest['Content-Type'] == "application/json") {

        $keyHash = trim($headerRequest['keyHash']);
        $authInfo = $this->getApiUser($request->username);

        if (!empty($authInfo) && md5($authInfo['username'] . ":" . $authInfo['password'] . ":" . $request->requestTimestamp) == $keyHash && (abs($currentTimestamp - $request->requestTimestamp) <= 1200)) {
            $leadsToDelete = $request->leads;
			foreach ($leadsToDelete as $lead) {
			$mobileNumber = $lead->mobileNumber;
			$campDay = $lead->campDay;
			$campCategory = $lead->campCategory;
			$sql = "DELETE FROM `leads` WHERE `number_1` = ? AND `custom_value_1` = ?  AND `custom_value_2` = ?";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param("sss", $mobileNumber, $campDay,$campCategory);
			$stmt->execute();
			$stmt->close();
		}

            $response = [
                'result' => true,
                'responseTimestamp' => $currentTimestamp,
                'resultCode' => 200,
                'message' => ["Leads deleted successfully!"],
            ];

        } else {

            $response = [
                'result' => false,
                'responseTimestamp' => $currentTimestamp,
                'resultCode' => 401,
                'message' => ["Authentication failed!"],
            ];

        }
    }

    echo json_encode($response);
}


    private function validateLeadsData($leads) {

        $currentTimestamp = time();

        $result = [
            'result'            => false,
            'responseTimestamp' => $currentTimestamp,
            'resultCode'        => 400,
            'request_param2'    => $request,
            'message'           => ["Invalid request"],
        ];

        if(!empty($leads) && $leads != null) {
            
            $result = [
                'result'            => true,
                'responseTimestamp' => $currentTimestamp,
                'resultCode'        => 202,
                'message'           => ["Leads request successfull"],
            ];

            if(count($leads) > $this->maxLeads) {
                $result = [
                    'result'            => false,
                    'responseTimestamp' => $currentTimestamp,
                    'resultCode'        => 413,
                    'message'           => ["Leads upload limit exceed"],
                ];
            }

            $errorMsg = [];
            $transIds = array_column($leads, "txnId");
            
            array_filter($leads, function($lead, $key) use(&$errorMsg, $transIds) {
                
               
                if(!isset($lead->mobileNumber) || empty($lead->mobileNumber) || !is_numeric($lead->mobileNumber) 
                || strlen($lead->mobileNumber) < 11 || strlen($lead->mobileNumber) > 20 || $this->hasSpecialChar($lead->mobileNumber) == true) {
                    $errorMsg[] = "Customer Contact no {$lead->mobileNumber} is invalid";
                }
                if(!isset($lead->currentDate) || empty($lead->currentDate) || DateTime::createFromFormat('Y-m-d H:i:s', $lead->currentDate) == false) {
                    $errorMsg[] = "Invalid date time format";
                }
              
                
            }, ARRAY_FILTER_USE_BOTH);

            if(!empty($errorMsg)){
                $result = [
                    'result'            => false,
                    'responseTimestamp' => $currentTimestamp,
                    'resultCode'        => 400,
                    'message'           => $errorMsg
                ];
            }
            
        }
        
        return $result;
        
    }
    private function isTransactionExists($transId){
        global $mysqli;
        $result = false;
        if(strlen($transId) <= 20){
            $sql = "SELECT custom_value_8 FROM leads WHERE custom_value_8 = ? LIMIT 1";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $transId);
            $stmt->execute();
            $data = $stmt->get_result();
            if($data->num_rows > 0) {
                $result = true;
            }
            $stmt->close();
        }
        
        return $result;
    }
    private function hasSpecialChar($value){
        $regex = "/[`'\"~!#$^&%*(){}<>,?;:\|+=]/";
        return preg_match($regex, $value); 
    }
    private function generateId(){
        global $mysqli;
        $rand = rand(1000, 9999);
		$microtime = explode(' ', microtime());
		$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
        $tstmp = substr($primaryID, -6);
		$str = $rand.$tstmp;
		$sql = "SELECT count(*) FROM leads WHERE id = ? ";
		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param("s", $str);
		$stmt->execute();
		$data = $stmt->get_result();
		if($data->num_rows > 0) {
			$rand = rand(1000, 9999);
			$microtime = explode(' ', microtime());
			$primaryID = (int)round($microtime[0] * 1000000) + $microtime[1];
			$tstmp = substr($primaryID, -6);
			$str = $rand.$tstmp;
		}
		$stmt->close();
		return $str;
    }
    
    private function dump($data){
        echo "<pre>";
            print_r($data);
        echo "</pre>";    
    }
    private function dd($data){
        echo "<pre>";
            print_r($data);
        echo "</pre>";   
        die(); 
    }
}

$obj = new api();


$inputData = file_get_contents("php://input");
$request = json_decode($inputData);

if(isset($request->requestType) && !empty($request->requestType) && $_REQUEST['path'] == "request" && method_exists('api',$request->requestType)) {

    $obj->{$request->requestType}();

} else {

    echo json_encode(
        [
            'result'            => false,
            'responseTimestamp' => time(),
            'resultCode'        => 400,
            'request_param1'     => $request,
            'message'           => ["Invalid request"],
        ]
    );
}

?>