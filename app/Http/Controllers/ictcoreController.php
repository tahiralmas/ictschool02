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

		$ictcore_integration= Ictcore_integration::select("*")->first();

		if(is_null($ictcore_integration)){
			$ictcore_integration=new Ictcore_integration;
			$ictcore_integration->ictcore_url = "";
			$ictcore_integration->ictcore_user = "";
			$ictcore_integration->ictcore_password = "";

		}
		return View('app.ictcore',compact('ictcore_integration'));
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
			DB::table("ictcore_integration")->delete();
			$ictcore_integration=new Ictcore_integration;
			$ictcore_integration->ictcore_url =Input::get('ictcore_url');;
			$ictcore_integration->ictcore_user = Input::get('ictcore_user');;
			$ictcore_integration->ictcore_password = Input::get('ictcore_password');;
			$ictcore_integration->save();
			return Redirect::to('ictcore')->with('success', 'Integration  Information saved.');
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
			$ictcore_integration =	DB::table('ictcore_integration')->select('*')->first();
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
				$data = array(
				'name'        => Input::get('title'),
				'description' => Input::get('description'),
				);
				$recording_id  =  $this->ictcore_api('messages/recordings','POST',$data );
				if(!empty($recording_id->error)){
					return Redirect::to('/ictcore/attendance')->withErrors("ERROR: some thing wrong in ictcore check password or user name " );
				}
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
				$ictcore_attendance->save();
				return Redirect::to('/ictcore/attendance')->with("success", "Attendance Message Created Succesfully.");
			}else{
			return Redirect::to('/ictcore/attendance')->withErrors("ERROR: Please Add Ictcore integration in Setting tab" );  
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
			$ictcore_integration =	DB::table('ictcore_integration')->select('*')->first();
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
}