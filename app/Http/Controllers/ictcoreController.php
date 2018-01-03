<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Student;
use App\SectionModel;
use App\ClassModel;
use DB;
class ictcoreController {

	public function __construct() {
		
	      
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/

	public function helloword(){

		return "testing";
	}
	
 /*  function executeCurl($arrOptions) 
	{
        $mixCH = curl_init();
        foreach ($arrOptions as $strCurlOpt => $mixCurlOptValue) 
		{
          curl_setopt($mixCH, $strCurlOpt, $mixCurlOptValue);
        }
        $mixResponse = curl_exec($mixCH);
        curl_close($mixCH);
        return $mixResponse;
    }

	function ictcore_api($method,$req,$data)
	{
		$username = 'admin';
		$password = 'helloAdmin';
		
		$requestType = $req; // This can be PUT or POST
		$arrPostData = $data;
		$postData = http_build_query($arrPostData); // Raw PHP array

		$postData = json_encode($arrPostData); // Only USE this when request JSON data.

		$mixResponse = $this->executeCurl(array(
			CURLOPT_URL => 'http://172.17.0.2/ictcore/api/'.$method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPGET => true,
			CURLOPT_VERBOSE => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_CUSTOMREQUEST => $requestType,
			CURLOPT_POSTFIELDS  => $postData,
			CURLOPT_HTTPHEADER  => array(
				"X-HTTP-Method-Override: " . $requestType,
				'Content-Type: application/json', // Only USE this when requesting JSON data
			),
			// If HTTP authentication is required, use the below lines.
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD  => $username. ':' . $password
		));
		$res = json_decode($mixResponse);
		return $res;
	}*/




function ictcore_api($method,$req, $arguments = array()) {
      // update following with proper access info
      $api_username = 'admin';    // <=== Username at ICTCore
      $api_password = 'helloAdmin';  // <=== Password at ICTCore
      $service_url  = 'http://172.17.0.2/ictcore/api'; // <=== URL for ICTCore REST APIs
      $requestType = $req; // This can be PUT or POST
    
      $api_url = "$service_url/$method";
      $urlaray = explode('/',$method);
      $curl = curl_init($api_url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$requestType);

      curl_setopt($curl, CURLOPT_POST, true);
      $post_data = $arguments;
      foreach($arguments as $key => $value) {
        if(is_array($value)){
          $post_data[$key] = json_encode($value);
        } else {
          $post_data[$key] = $value;
        }
      }
	 $postData = json_encode($post_data); // Only USE this when request JSON data
	  if($requestType =="PUT"  && $urlaray[3]=='media'){
		$fil = file_get_contents($post_data[0]->name);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $fil);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-HTTP-Method-Override: " . $requestType,'Content-Type: audio/x-wav'));
	}else{
		  curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		  curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-HTTP-Method-Override: " . $requestType,'Content-Type: application/json'));
		}

      curl_setopt($curl, CURLOPT_USERPWD,  $api_username.":".$api_password);

      $curl_response = curl_exec($curl);
      curl_close($curl);
      return json_decode($curl_response);  
}
    











	
}