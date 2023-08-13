<?php
$username = "noora235";
$password = "GLXS_OKTaYHRs@6";
echo $currentTimestamp = time();
echo "<br />";
echo $authCode = md5($username.":".$password.":".$currentTimestamp);
//die();

$header = array(
    "keyHash: ".$authCode,
    "Content-Type: application/json"
);

 $apiUrl = "http://58.65.224.74/ccpro_dev/api/leads/request";
// $apiUrl = "http://192.168.11.38/city_leads_upload/request";
//$apiUrl = "http://127.0.0.1/city_leads_upload/request";
// $apiUrl = "http://192.168.10.64/pd_leads_upload/request";

$postData = '{
    "requestType":"uploadLeads",
    "username":"' . $username . '",
    "requestTimestamp":"' . $currentTimestamp . '",
    "leads":[
        {
            "mobileNumber":"01714016995",
            "campDay":"day-1",
            "campCategory":"ANC",
            "currentDate":"2023-08-06 12:34:56"
           
            
        }
    ]
}';
// echo $postData; die();

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_URL,$apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$result = curl_exec($ch);
echo "<pre>"; print_r($result); die();
curl_close($ch);

$data = json_decode($result,true);

echo "<pre>"; print_r($data); 

?>
