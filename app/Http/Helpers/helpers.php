<?php
use App\ClassModel;
use App\Subject;
use App\AcadamicYear;
use App\Student;
use App\Http\Controllers\ictcoreController;
//use Storage;
class emtysession{

}
if (! function_exists('FixData')) {
	function FixData(){
		
			$classes        = array();
			$section_array  = array(); 
			$section_array1 = array(); 
			$subject_array  = array(); 
			$result         = array();
			$result1        = array();

	    if(Auth::user()->group == 'Teacher'){

			   $teacher_id   = Auth::user()->group_id;
			    $sections    = DB::table('section')->where('teacher_id',$teacher_id)->get();
	            $timetable   = DB::table('timetable')->where('teacher_id',$teacher_id)->groupBy('section_id')->get();
	            $timetable   = DB::table('timetable')->where('teacher_id',$teacher_id)->groupBy('section_id')->get();
		      
		        $class_array  = array(); 
		        $class_array1 = array(); 
		        foreach($sections as $section){
		          $section_array[] = $section->id;
		          $class_array[]   = $section->class_code;
		        }
		        //$subject_array = array(); 
		        foreach($timetable as $tmtable){
		          $class_array1[]   = $tmtable->class_id;
		          $section_array1[] = $tmtable->section_id;
		          $subject_array[]  = $tmtable->subject_id;
		        }
		         $result1 = array_merge($class_array, $class_array1);
		      // $classes = DB::table('Class')->select('name','code')->whereIn('code',$result1)->get();
		       //$classes = ClassModel::whereIn('code',$result1)->pluck('name','code');
		       $result = array_merge($section_array, $section_array1);
		      
		}elseif(Auth::user()->group == 'Student'){
			
		}
	    return array('classes'=>$result1,'sections'=>$result,'subjects'=>$subject_array);
	}
	
}
if(! function_exists('accounting_check')) {
	function accounting_check()
	{
		if(Storage::disk('local')->exists('/public/accounting.txt')){
          $ac = Storage::get('/public/accounting.txt');
          $ac_data = explode('<br>',$ac );

			//echo "<pre>";print_r($data);
			$accounting = $ac_data[0]; 
		}else{
	      $accounting ='';
		}
		return $accounting;
	}
}
if(! function_exists('php_curl')) {
	function php_curl($method,$req, $arguments = array())
	{
		
		$accunting    =	DB::table('accounting_settings')->select('*')->first();
		$company_id   = $accunting->company_id; 
		$api_username = $accunting->username;    
		$api_password = $accunting->password; 
		$service_url  = $accunting->api_link; 
		$requestType  = $req; // This can be PUT or POST
		 $api_url      = "$service_url/$method?company_id=".$company_id;
		$urlaray      = explode('/',$method);
		$curl         = curl_init($api_url);

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$requestType);
			curl_setopt($curl, CURLOPT_POST, true);
			$post_data   = $arguments;

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
		//echo "<pre>";print_r($curl_response);exit;
		return json_decode($curl_response);  
	}
if (! function_exists('getsubjecclass')) {
	function getsubjecclass($class){
		$subjects = Subject::select('id','name')->where('class','=',$class)->get();
		
		$output ='';
		$name ='';
		$suject_aray = array();
		foreach($subjects as $subject){
			$suject_aray[] = array("id"=>$subject->id,"name"=>$subject->name);
			$output .='&nbsp;  ';
			$url = url('/').'/create/marks?sub_id='.$subject->id.'&class='.$class;
			$link = "'".$url."','enter marks','width=1500','height=500'";
			$output .='<a href="'.$url.'" onclick="window.open('."$link".'); 
              return false;">'.$subject->name.'</a>';
              //$output .=$url. $subject->name;
              $name .= '&nbsp;  '.$subject->name;
		}

		return array('url'=>$output,'sub_name'=>$suject_aray);
	}
}

}

if (! function_exists('get_current_session')) {

	function get_current_session()
	{
		$years = AcadamicYear::where('status',1)->orderBy('id','desc');
		if($years->count()>0){
			$years = $years->first();
		}else{
			$years     = new emtysession;
			$years->id = date("Y");
		}

		return $years;
	}
}
if (! function_exists('count_student')) {

	function count_student($section,$class){
		
		$count_student = Student::where('isActive','Yes')->where('session',get_current_session()->id);
		if($class!=''){
		  $count_student =	$count_student->where('class',$class);
		}
		if($section!=''){
			$count_student =$count_student->where('section',$section);
		}

		$count_student =$count_student->count();
		
		return $count_student;
	}
}

if(! function_exists('family_check')) {
	function family_check()
	{
		if(Storage::disk('local')->exists('/public/family.txt')){
          $fm = Storage::get('/public/family.txt');
          $fm_data = explode('<br>',$fm );

			//echo "<pre>";print_r($data);
			$famly = $fm_data[0]; 
		}else{
	      $famly ='';
		}
		return $famly;
	}
}
if(! function_exists('branchesapi')) {
	function branchesapi($username,$password,$url,$type)
	{
			//return "as";
	   /* curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    $result = curl_exec($ch);
	    curl_close($ch);
	    return json_decode($result);*/
	    if(request()->session()->get($url)==''){
	    $ch = curl_init( $url.'/api/authenticate' );
		# Setup request to send json via POST.
		$data = array("username" =>$username,"password" =>$password );
		$payload = json_encode(  $data  );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		# Return response instead of printing.
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);
		# Print response.
		//$dataa = json_decode($result);
		$dataa = json_decode($result);
		if(!empty($dataa->success)){
			request()->session()->put($url, $dataa->success->token);
			$count = gettyperesult(request()->session()->get($url),$url,$type);
		}else{
			return 404;
		}
	}else{
		$count = gettyperesult(request()->session()->get($url),$url,$type);
	}
       //return request()->session()->get($url);
		//echo "<pre>".print_r($dataa ,true)"</pre>";
			return $count ;
			//if ($server_output == "success") { ... } else { ... }
	}
}
if(! function_exists('gettyperesult')){
	function gettyperesult($token,$url,$type){
	if(empty(request()->session()->get($url.'test'))){
		$authorization = "Authorization: Bearer ".$token;
		$ch = curl_init( $url.'/api/'.'branches/data' );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    //curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    $result = curl_exec($ch);
	    curl_close($ch);
	    $data   = json_decode($result);
	    request()->session()->put($url.'test', $data);
	}else{
		$data   = request()->session()->get($url.'test');
	}

	  return $data ;
	}
}

if(! function_exists('sendmesssageictcore')){

	function sendmesssageictcore($first_name,$last_name,$to,$message,$m_name)
	{
		//exit;
		$ict  = new ictcoreController();
		$data = array(
					'first_name' =>$first_name,
					'last_name'  =>$last_name,
					'phone'      =>$to,
					'email'      =>''
				);
		$contact_id = $ict->ictcore_api('contacts','POST',$data );
		
		$data = array(
						'name' => $m_name,
						'data' => $message,
						'type' => 'utf-8',
						'description' =>'',
				);
		$text_id  =  $ict->ictcore_api('messages/texts','POST',$data );
		$data     = array(
						'name' =>$m_name,
						'text_id' =>$text_id,
					);
		$program_id  =  $ict->ictcore_api('programs/sendsms','POST',$data );

		$data = array(
						'title' => 'Attendance',
						//$program_id,
						'program_id' =>$program_id,
						'account_id'     => 1,
						'contact_id'     => $contact_id,
						'origin'     => 1,
						'direction'     => 'outbound',
					);
		$transmission_id   = $ict->ictcore_api('transmissions','POST',$data );
		$transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );
	  	if(!is_array($transmission_send) || !is_object($transmission_send)){
	  		$transmission_send = 'sended';
	  	}
	  	return $transmission_send;
	}

}

