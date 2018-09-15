<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Student;
use File;
use App\Ictcore_integration;
use App\Ictcore_attendance;
use App\Ictcore_fees;
use App\SectionModel;
use App\ClassModel;
use App\Notification;
use DB;

class ictcoreController {

	public function __construct() {

	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{

		$ictcore_integration= Ictcore_integration::select("*")->where('type',Input::get('type'))->first();
        $type = Input::get('type');
		if(is_null($ictcore_integration)){
			$ictcore_integration = new Ictcore_integration;
			$ictcore_integration->ictcore_url = "";
			$ictcore_integration->ictcore_user = "";
			$ictcore_integration->ictcore_password = "";
		}
		return View('app.ictcore',compact('ictcore_integration','type'));
	}

	public function create()
	{
		$rules=[
		'ictcore_url' => 'required',
		'ictcore_user' => 'required',
		'ictcore_password' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()){
			return Redirect::to('ictcore')->withinput(Input::all())->withErrors($validator);
		}else {
			if(Input::get('method')==''){
				$method = 'telenor';
			}else{
				$method = 'ictcore';
			}
			
			DB::table("ictcore_integration")->where('type',Input::get('type'))->delete();
			$ictcore_integration=new Ictcore_integration;
			$ictcore_integration->ictcore_url =Input::get('ictcore_url');;
			$ictcore_integration->ictcore_user = Input::get('ictcore_user');;
			$ictcore_integration->ictcore_password = Input::get('ictcore_password');;
			$ictcore_integration->method = $method;
			$ictcore_integration->type = Input::get('type');
			$ictcore_integration->save();
			return Redirect::to('ictcore?type='.Input::get('type'))->with('success', 'Integration  Information saved.');
		}
	}

	public function attendance_index()
	{
		$ictcore_attendance= Ictcore_attendance::select("*")->first();
		if(is_null($ictcore_attendance)){
			$ictcore_attendance=new Ictcore_attendance;
			$ictcore_attendance->name = "";
			$ictcore_attendance->description = "";
			$ictcore_attendance->recording = "";
		}
		return View('app.ictcoreAttendance',compact('ictcore_attendance'));
	}

	public function post_attendance()
	{
		$rules=[
		'title' => 'required',
		'message' => 'required|mimes:wav',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()){
			return Redirect::to('/ictcore/attendance')->withErrors($validator);
		}
		else{
			$drctry = storage_path('app/public/messages/');

		    $attendance_noti = DB::table('notification_type')->where('notification','attendance')->first();

			$ictcore_integration =	DB::table('ictcore_integration')->select('*')->where('type',$attendance_noti->type)->first();
               if($ictcore_integration->method=="telenor" && $attendance_noti->type=='voice'){
                  $ictcore_attendance =	DB::table('ictcore_attendance')->select('*')->first();
					if(!empty($ictcore_attendance) && File::exists($drctry.$ictcore_attendance->recording)){
						unlink($drctry .$ictcore_attendance->recording);
					}

						$sname = Input::get('title');
						$remove_spaces =  str_replace(" ","_",Input::get('title'));
						$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();
						Input::file('message')->move($drctry ,$fileName);
						sleep(2);
					    echo exec('sox '.$drctry.'/'.$fileName .' -b 16 -r 8000 -c 1 -e signed-integer'.$drctry.'/'.$fileName);

						$name          =  $drctry .$fileName;
						$finfo         =  new \finfo(FILEINFO_MIME_TYPE);
						$mimetype      =  $finfo->file($name);
						$cfile         =  curl_file_create($name, $mimetype, basename($name));
						$data          = array('name'=>time(),'audio_file'=> $cfile);
                        $upload_audio  = $this->verification_number_telenor_voice($data,$ictcore_integration->ictcore_user,$ictcore_integration->ictcore_password);
                         echo $upload_audio;
                        // echo "<pre>";print_r($upload_audio[0] );
                         DB::table("ictcore_attendance")->delete();
							$ictcore_attendance = new Ictcore_attendance;
							$ictcore_attendance->name = Input::get('title');
							$ictcore_attendance->description = Input::get('description');
							$ictcore_attendance->recording =$fileName;
							$ictcore_attendance->ictcore_recording_id = NULL;
							$ictcore_attendance->ictcore_program_id  =NULL;
							$ictcore_attendance->telenor_file_id  =$upload_audio;
							$ictcore_attendance->save();
                         	return Redirect::to('/ictcore/attendance')->with("success", "Attendance Message Created Succesfully.");
               }
             else{
			if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){
				$ictcore_attendance =	DB::table('ictcore_attendance')->select('*')->first();
				
				if(!empty($ictcore_attendance) && File::exists($drctry.$ictcore_attendance->recording)){
					unlink($drctry .$ictcore_attendance->recording);
				}

				$sname = Input::get('title');
				$remove_spaces =  str_replace(" ","_",Input::get('title'));
				$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();
				Input::file('message')->move($drctry ,$fileName);
				sleep(2);
				$name          =  $drctry .$fileName;
				$finfo         =  new \finfo(FILEINFO_MIME_TYPE);
				$mimetype      =  $finfo->file($name);
				$cfile         =  curl_file_create($name, $mimetype, basename($name));
				$data          =  array( $cfile);
				
				$result        =  $this->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
				$recording_id  =  $result ;
				if(!empty($recording_id->error)){
					return Redirect::to('/ictcore/attendance')->withErrors("ERROR: some thing wrong in ictcore check password or user name " );
				}
				if(!is_array($recording_id )){
					$data = array(
					'name' => Input::get('title'),
					'recording_id' => $recording_id,
					);
					$program_id = $this->ictcore_api('programs/voicemessage','POST',$data );
					if(!empty($recording_id->error)){
						return Redirect::to('/ictcore/attendance')->withErrors("ERROR: some thing wrong in ictcore check password or user name " );
					}
					if(!is_array( $program_id )){
						$program_id = $program_id;
					}else{
						return Redirect::to('/ictcore/attendance')->withErrors("ERROR: Program not Created" );
					}
				}else{
					return Redirect::to('/ictcore/attendance')->withErrors("ERROR: Recording not Created" );               
				}

				DB::table("ictcore_attendance")->delete();
				$ictcore_attendance = new Ictcore_attendance;
				$ictcore_attendance->name = Input::get('title');
				$ictcore_attendance->description = Input::get('description');
				$ictcore_attendance->recording =$fileName;
				$ictcore_attendance->ictcore_recording_id =$recording_id;
				$ictcore_attendance->ictcore_program_id  =$program_id;
				$ictcore_attendance->telenor_file_id  =$file_id;
				$ictcore_attendance->save();
				return Redirect::to('/ictcore/attendance')->with("success", "Attendance Message Created Succesfully.");
			}else{
			return Redirect::to('/ictcore/attendance')->withErrors("ERROR: Please Add Ictcore integration in Setting tab" );  
			}
		}
		}
	}
	public function fee_message_index()
	{
		$ictcore_fees= Ictcore_fees::select("*")->first();
		if(is_null($ictcore_fees)){
			$ictcore_fees=new Ictcore_fees;
			$ictcore_fees->name = "";
			$ictcore_fees->description = "";
			$ictcore_fees->recording = "";
		}
		return View('app.ictcoreFees',compact('ictcore_fees'));
	}

	public function post_fees()
	{
		$rules=[
		'title' => 'required',
		'message' => 'required|mimes:wav',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()){
			return Redirect::to('/ictcore/fees')->withErrors($validator);
		}
		else {
			//$ictcore_integration =	DB::table('ictcore_integration')->select('*')->first();
			 $attendance_noti = DB::table('notification_type')->where('notification','fess')->first();

			$ictcore_integration =	DB::table('ictcore_integration')->select('*')->where('type',$attendance_noti->type)->first();
            if($ictcore_integration->method=="telenor" && $attendance_noti->type=='voice'){
              // $ictcore_fees =	DB::table('ictcore_fees')->select('*')->first();
				$drctry = storage_path('app/public/messages/');
              $ictcore_fess =	DB::table('ictcore_fees')->select('*')->first();
					if(!empty($ictcore_fess) && File::exists($drctry.$ictcore_fess->recording)){
						unlink($drctry .$ictcore_fess->recording);
					}

						$sname = Input::get('title');
						$remove_spaces =  str_replace(" ","_",Input::get('title'));
						$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();
						Input::file('message')->move($drctry ,$fileName);
						sleep(2);
						$php_dir =  exec('which php');
						//echo 'sox '."$drctry"."$fileName" .' -b 16 -r 8000 -c 1 -e signed-integer '."$drctry"."$fileName";
					     $data  = exec('sox '."$drctry"."$fileName" .' -b 16 -r 8000 -c 1 -e signed-integer '."$drctry"."fesstelenor.wav");
                        echo $data;
						//exit;
						$name          =  $drctry .$fileName;
						$nname         = $drctry .'fesstelenor.wav';
						$finfo         =  new \finfo(FILEINFO_MIME_TYPE);
						$mimetype      =  $finfo->file($nname);
						$cfile         =  curl_file_create($nname, $mimetype, basename($nname));
						$data          = array('name'=>time(),'audio_file'=> $cfile);
                        $upload_audio  = $this->verification_number_telenor_voice($data,$ictcore_integration->ictcore_user,$ictcore_integration->ictcore_password);
                         //echo $upload_audio;
                           DB::table("ictcore_fees")->delete();
						$ictcore_fees = new Ictcore_fees;
						$ictcore_fees->name = Input::get('title');
						$ictcore_fees->description = Input::get('description');
						if(Input::get('description')==''){
		                	$ictcore_fees->description ='';
						}
						$ictcore_fees->recording =$fileName;
						$ictcore_fees->ictcore_recording_id ='';
						$ictcore_fees->ictcore_program_id  ='';
						$ictcore_fees->telenor_file_id  =$upload_audio;
						$ictcore_fees->save();
						unlink($drctry .'fesstelenor.wav');
						return Redirect::to('/ictcore/fees')->with("success", "Fees Message Created Succesfully.");



            }else{

			if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){
				$ictcore_fees =	DB::table('ictcore_fees')->select('*')->first();
				$drctry = storage_path('app/public/messages/');
				if(count($ictcore_fees) > 0 && File::exists($drctry.$ictcore_fees->recording)){
					unlink($drctry.$ictcore_fees->recording);
				}
				$sname = Input::get('title');
				$remove_spaces =  str_replace(" ","_",Input::get('title'));
				$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();
				Input::file('message')->move($drctry,$fileName);
				sleep(2);
				$data = array(
				'name' => Input::get('title'),
				'description' => Input::get('description'),
				);
				$recording_id  =  $this->ictcore_api('messages/recordings','POST',$data );
				if(!empty($recording_id->error)){
					return Redirect::to('/ictcore/fees')->withErrors("ERROR: some thing wrong in ictcore check password or user name " );
				}
				$name          =   $drctry.$fileName;
				$finfo         =  new \finfo(FILEINFO_MIME_TYPE);
				$mimetype      =  $finfo->file($name);
				$cfile         =  curl_file_create($name, $mimetype, basename($name));
				$data          =  array( $cfile);
				$result        =  $this->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
				$recording_id  =  $result ;
				if(!empty($recording_id->error)){
					return Redirect::to('/ictcore/fees')->withErrors("ERROR: some thing wrong in ictcore check password or user name " );
				}
				if(!is_array($recording_id )){
					$data = array(
					'name' => Input::get('title'),
					'recording_id' => $recording_id,
					);
					$program_id = $this->ictcore_api('programs/voicemessage','POST',$data );
					if(!empty($program_id->error)){
						return Redirect::to('/ictcore/fees')->withErrors("ERROR: some thing wrong in ictcore check password or user name " );
					}
					if(!is_array( $program_id )){
						$program_id = $program_id;
					}else{
						return Redirect::to('/ictcore/fees')->withErrors("ERROR: Program not Created" );
					}
				}else{
					return Redirect::to('/ictcore/fees')->withErrors("ERROR: Recording not Created" );               
				}
				DB::table("ictcore_fees")->delete();
				$ictcore_fees = new Ictcore_fees;
				$ictcore_fees->name = Input::get('title');
				$ictcore_fees->description = Input::get('description');
				if(Input::get('description')==''){
                	$ictcore_fees->description ='';
				}
				$ictcore_fees->recording =$fileName;
				$ictcore_fees->ictcore_recording_id =$recording_id;
				$ictcore_fees->ictcore_program_id  =$program_id;
				$ictcore_fees->save();
				return Redirect::to('/ictcore/fees')->with("success", "Fees Message Created Succesfully.");

			}else{
			return Redirect::to('/ictcore/attendance')->withErrors("ERROR: Please Add Ictcore integration in Setting tab" );  
			}
		}
		}
	}
	function ictcore_api($method,$req, $arguments = array()) {

		$ictcore_integration =	DB::table('ictcore_integration')->select('*')->get();
		$api_username = $ictcore_integration[0]->ictcore_user;    // <=== Username at ICTCore
		$api_password = $ictcore_integration[0]->ictcore_password;  // <=== Password at ICTCore
		$service_url  =  $ictcore_integration[0]->ictcore_url;  //'http://172.17.0.2/ictcore/api'; // <=== URL for ICTCore REST APIs
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
		if($requestType =="PUT"  && in_array("media", $urlaray)){
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

	public function noti_index(){

		$notification_types = DB::table('notification_type')->get();
	   
	    return View('app.notifications',compact('notification_types'));
	}

	public function noti_create(){
       $rules=[
		'fess' => 'required',
		'attendance' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()){
			return Redirect::to('/notification_type')->withErrors($validator);
		}
		else {
		
		$data = array(Input::all());
		unset($data[0]['_token']);
		Notification::truncate();
		foreach($data[0] as $key=>$value){
			$add_noti = new Notification;
		     $add_noti->type = $value;
		     $add_noti->notification = $key;
		     $add_noti->save();
		     //echo $key;echo $value;
		     //echo "<br>";
		}    
			return Redirect::to('/notification_type')->with("success", "Notifications setting Created Succesfully.");

	}
	}

	public function verification_number_telenor_sms($to,$msg,$mask,$user,$pass,$type)
	{

	    $planetbeyondApiUrl="https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp?msisdn=#username#&password=#password#";
		if($type=='sms'){
		$planetbeyondApiSendSmsUrl="https://telenorcsms.com.pk:27677/corporate_sms2/api/sendsms.jsp?session_id=#session_id#&to=#to_number_csv#&text=#message_text#"; 
	    }elseif($type=='voice'){
	    	$attandace_message = DB::table("ictcore_attendance")->first();
	    	$planetbeyondApiSendSmsUrl="https://telenorcsms.com.pk:27677/corporate_sms2/api/makecall.jsp?session_id=#session_id#&to=#to_number_csv#&file_id=".$attandace_message->telenor_file_id."&max_retries=1";

	    }
	    $userName = $user;
	    $password = $pass;
	    $url      =  str_replace("#username#",$userName,$planetbeyondApiUrl);
		$url      =  str_replace("#password#",$password,$url);


	   $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_FAILONERROR,1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	    $retValue = curl_exec($ch);          
	    curl_close($ch);
			
		$xml = new \SimpleXMLElement($retValue);
	    $session_id = $xml->data;

         $url_sms = str_replace("#message_text#",urlencode($msg),$planetbeyondApiSendSmsUrl);

	     $url_sms = str_replace("#to_number_csv#",$to,$url_sms);
	   //$url=str_replace("#from_number#",$fromNumber,$url);

	    $urlWithSessionKey = str_replace("#session_id#",$session_id,$url_sms);
        if($mask!=null)
	    {
	    	if($type=='sms'){
			$urlWithSessionKey = $urlWithSessionKey . "&mask=" . urlencode($mask);
	        }
	    }
	    //return $urlWithSessionKey;
          $snd_sms = curl_init();
		    curl_setopt($snd_sms, CURLOPT_URL,$urlWithSessionKey);
		    curl_setopt($snd_sms, CURLOPT_FAILONERROR,1);
		    curl_setopt($snd_sms, CURLOPT_FOLLOWLOCATION,1);
		    curl_setopt($snd_sms, CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($snd_sms, CURLOPT_TIMEOUT, 15);
		    $sms_data = curl_exec($snd_sms);          
		    curl_close($snd_sms);
		   // echo $urlWithSessionKey;
		    //echo "<pre>";print_r($sms_data);
				
			$xml_sms = new \SimpleXMLElement($sms_data);
		    //$data    = $xml_sms->data;
		    return $xml_sms ;
	}

	public function verification_number_telenor_voice($post,$user,$pass)
	{

	    $planetbeyondApiUrl        = "https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp?msisdn=#username#&password=#password#";
		$planetbeyondApiSendSmsUrl = "https://telenorcsms.com.pk:27677/corporate_sms2/api/audio_upload.jsp?session_id=#session_id#"; 
	    $userName = $user;
	    $password = $pass;
	     $url      =  str_replace("#username#",$userName,$planetbeyondApiUrl);
		 $url      =  str_replace("#password#",$password,$url);


	   $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_FAILONERROR,1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	    $retValue = curl_exec($ch);          
	    curl_close($ch);
			
		$xml = new \SimpleXMLElement($retValue);
	    $session_id = $xml->data;

        // $url_sms = str_replace("#message_text#",urlencode($msg),$planetbeyondApiSendSmsUrl);

	    // $url_sms = str_replace("#to_number_csv#",$to,$url_sms);
	   //$url=str_replace("#from_number#",$fromNumber,$url);

	    $urlWithSessionKey = str_replace("#session_id#",$session_id,$planetbeyondApiSendSmsUrl);
       
	    //return $urlWithSessionKey;
          /*$snd_sms = curl_init();
		    curl_setopt($snd_sms, CURLOPT_URL,$urlWithSessionKey);
		    curl_setopt($snd_sms, CURLOPT_FAILONERROR,1);
		    curl_setopt($snd_sms, CURLOPT_FOLLOWLOCATION,1);
		    curl_setopt($snd_sms, CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($snd_sms, CURLOPT_TIMEOUT, 15);
		    $sms_data = curl_exec($snd_sms);          
		    curl_close($snd_sms);
				
			$xml_sms = new \SimpleXMLElement($sms_data);
		    $data = $xml_sms->data;
		    return $xml_sms ;*/
            //echo "<pre>";print_r($urlWithSessionKey);exit;
		    $headers = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
			$cha = curl_init();
			curl_setopt($cha, CURLOPT_URL,$urlWithSessionKey);
			//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($cha, CURLOPT_FAILONERROR,1);
	        curl_setopt($cha, CURLOPT_FOLLOWLOCATION,1);
	       curl_setopt($cha, CURLOPT_RETURNTRANSFER,1);
	       curl_setopt($cha, CURLOPT_TIMEOUT, 15);
			curl_setopt($cha, CURLOPT_POST,1);
			curl_setopt($cha, CURLOPT_POSTFIELDS, $post);
			$result=curl_exec ($cha);
			curl_close ($cha);
			//echo "<pre>";print_r($result);

			$xml =  new \SimpleXMLElement($result);
			    $data = $xml->data;
			   
			return $data;
	}

	public function telenor_apis($method,$group_id,$to,$sms_msg,$file_id,$type)
	{  
      $ictcore_integration = Ictcore_integration::select("*")->where('method','telenor')->first();
       if(!empty($ictcore_integration)){
        $planetbeyondApiUrl        = "https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp?msisdn=#username#&password=#password#";
		if($method == 'group'){
		$planetbeyondApi = "https://telenorcsms.com.pk:27677/corporate_sms2 /api/list.jsp?session_id=#session_id#&list_name=fee_defulter_".time(); 
	    }
	    if($method == 'add_contact'){
          $planetbeyondApi = "https://telenorcsms.com.pk:27677/corporate_sms2/api/addcontacts.jsp?session_id=#session_id#&list_id=".$group_id."&to=".$contact;
	    }
	    if($method == 'campaign_create' && $type =='sms'){
	    	$planetbeyondApi="https://telenorcsms.com.pk:27677/corporate_sms2/api/campaign.jsp?session_id=#session_id#&name=fee_defulter_".time()."&group_ids=".$group_id."&text=".$sms_msg."&time=".date("Y/m/d h:i:s");

	    }
	    if($method == 'campaign_create' && $type =='voice'){
	    	$planetbeyondApi="https://telenorcsms.com.pk:27677/corporate_sms2/api/campaign.jsp?session_id=#session_id#&name=fee_defulter_".time()."&group_ids=".$group_id."&text=".$sms_msg."&time=".date("Y/m/d h:i:s");

	    }
	    $userName  = $ictcore_integration->ictcore_user;
	    $password  = $ictcore_integration->ictcore_password;
	     $url      =  str_replace("#username#",$userName,$planetbeyondApiUrl);
		 $url      =  str_replace("#password#",$password,$url);


	   $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_FAILONERROR,1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	    $retValue = curl_exec($ch);          
	    curl_close($ch);
			
		$xml = new \SimpleXMLElement($retValue);
	    $session_id = $xml->data;
	    $urlWithSessionKey = str_replace("#session_id#",$session_id,$planetbeyondApi);
         $api = curl_init();
		    curl_setopt($api, CURLOPT_URL,$urlWithSessionKey);
		    curl_setopt($api, CURLOPT_FAILONERROR,1);
		    curl_setopt($api, CURLOPT_FOLLOWLOCATION,1);
		    curl_setopt($api, CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($api, CURLOPT_TIMEOUT, 15);
		    $api_data = curl_exec($api);          
		    curl_close($api);
				
			$xml     = new \SimpleXMLElement($api_data);
		    $data    = $xml->data;
		    return $data ;

       }else{

       }
	}
}