<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Subject;
use App\Attendance;
use App\Student;
use App\Message;
use App\Ictcore_attendance;
use App\Ictcore_integration;
use App\Institute;
use DB;
use Excel;
use App\SMSLog;
use App\Http\Controllers\ictcoreController;

Class formfoo{

}
use Illuminate\Support\Collection;
use Carbon\Carbon;
class attendanceController extends BaseController {
		public function __construct() {
			/*$this->beforeFilter('csrf', array('on'=>'post'));
			$this->beforeFilter('auth');
			$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
			$this->middleware('auth');
	              // $this->middleware('userAccess',array('only'=> array('delete')));
		}
		public function index()
		{
	      
			$classes=array();
			$classes2 = ClassModel::select('code','name')->orderby('code','asc')->get();
			$subjects = Subject::pluck('name','code');
			$attendance=array();
			$messages = DB::table('message')
				    ->select(DB::raw('message.id,message.name,message.description,message.recording'))
				    ->get();
				   
			//return View::Make('app.attendanceCreate',compact('classes2','classes','subjects','attendance'));
			return View('app.attendanceCreate',compact('classes2','classes','subjects','attendance','messages'));
		}
		public function index_file()
		{
			//return View::Make('app.attendanceCreateFile');
			return View('app.attendanceCreateFile');
		}
		public function create()
		{
			$rules = [
			'class' => 'required',
			'section' => 'required',
			//'shift' => 'required',
			'session' => 'required',
			'regiNo' => 'required',
			'date' => 'required',
			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails()) {
			 return Redirect::to('/attendance/create')->withInput(Input::all())->withErrors($validator);
			} else {

				$absentStudents = array();
				$students = Input::get('regiNo');
				$presents = Input::get('present');
				$all = false;
				if ($presents == null) {
				 $all = true;
				} else {
				 $ids = array_keys($presents);
				}
				 $stpresent = array();
				foreach ($students as $student) {
					$st = array();
					$st['regiNo'] = $student;
					if ($all) {
						$st['status'] = 'No';
					} else {
						$st['status'] = $this->checkPresent($student, $ids);
					}
					if ($st['status'] == "No") {
						array_push($absentStudents, $student);
					}
					else {
						array_push($stpresent, $st);
					}
				}
				$presentDate = $this->parseAppDate(Input::get('date'));
				DB::beginTransaction();
				try {
					$i=0;

                      $classc = DB::table('Class')->select('*')->where('code','=',Input::get('class'))->first();
                      $class_id =  $classc->id;
					foreach ($stpresent as $stp) {
						 $atten = DB::table('Attendance')->where('date','=',$presentDate)->where('regiNo','=',$stp['regiNo'])->first();
		                if(is_null($atten)){

							$attenData= [
							'date' => $presentDate,
							'regiNo' => $stp['regiNo'],
							'status' => "Present",
							'class_id'=>$class_id,
							'section_id'=>Input::get('section'),
							'session'=>Input::get('session'),
							'created_at' => Carbon::now()
							];
							Attendance::insert($attenData);

							$i++;
						}
					}
					foreach ($absentStudents as $absst) {
					   $atten = DB::table('Attendance')->where('date','=',$presentDate)->where('regiNo','=',$absst)->first();
	                    if(is_null($atten)){
							$attenDataabsnt= [
							'date' => $presentDate,
							'regiNo' => $absst,
							'status' => "Absent",
							'class_id'=>$class_id,
							'section_id'=>Input::get('section'),
							'session'=>Input::get('session'),
							'created_at' => Carbon::now()
							];
						    Attendance::insert($attenDataabsnt);

						    $i++;
						}
					}
					if($i==0){
                       $errorMessages = new \Illuminate\Support\MessageBag;
					$errorMessages->add('Error', 'Attendance already added by this Date Please Select Other Date');
					return Redirect::to('/attendance/create')->withErrors($errorMessages);
					}
					DB::commit();
				}catch (\Exception $e) {
					DB::rollback();
					$errorMessages = new \Illuminate\Support\MessageBag;
					$errorMessages->add('Error', 'Something went wrong!'.$e);
					return Redirect::to('/attendance/create')->withErrors($errorMessages);
				}
				
				$isSendSMS = Input::get('isSendSMS');
				if ($isSendSMS == null) {
					return Redirect::to('/attendance/create')->with("success", "Students attendance save Succesfully.");
				} else {
				if(count($absentStudents) > 0) {
					
					$student=array();
					///////////////////////////////////////////////////////////////////////////////////////////////message Create in ictcore////////////////////////////////////////////////////////////////////////////////////////////
				/*	$message = Message::find(Input::get('message'));
					$ict  = new ictcoreController();
					$result = $ict->ictcore_api('messages/recordings','GET',$data=array() );
					$array= array();
					foreach($result as $res){
						$array[]= $res->name;
						$id[] = get_object_vars($res);
					}
					if(in_array($message->name, $array)){
						$key = array_search($message->name, array_column($id, 'name'));
						$recording_id = $id[$key]['recording_id'];
					}else{
						$data = array(
						'name' => $message->name,
						'description' => $message->description,
						);
						$recording_id= $ict->ictcore_api('messages/recordings','POST',$data );
						$name = base_path() .'/public/recording/'.$message->recording;
						$finfo = new \finfo(FILEINFO_MIME_TYPE);
						$mimetype = $finfo->file($name);
						$cfile = curl_file_create($name, $mimetype, basename($name));
						$data = array( $cfile);
						$result = $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
						$recording_id = $result ;
					}
					$data = array(
					'name' => $message->name,
					'recording_id' => $recording_id,
					);
					$program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );*/

					foreach ($absentStudents as $absst) {

						$student =	DB::table('Student')
						->join('Class', 'Student.class', '=', 'Class.code')
						->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Class.Name as class')
						->where('Student.regiNo','=',$absst)
						->where('class',Input::get('class'))
						->first();
						/////////////////////////////////////////////////////////////////////////////////////////////Contact Create in ictcore//////////////////////////////////////////////////////////////////////////////////////////
						 $ictcore_attendance= Ictcore_attendance::select("*")->first();
						 $ictcore_integration = Ictcore_integration::select("*")->first();
						if($ictcore_integration->ictcore_url!='' && $ictcore_integration->ictcore_user!='' && $ictcore_integration->ictcore_password!=''){ 
							 $ict  = new ictcoreController();
	                        if($ictcore_attendance->ictcore_program_id!=''){
								$data = array(
								'first_name' => $student->firstName,
								'last_name' => $student->lastName,
								'phone'     => $student->fatherCellNo,
								'email'     => '',
								);
								$contact_id = $ict->ictcore_api('contacts','POST',$data );
								$data = array(
								'title' => 'Attendance',
								'program_id' => $ictcore_attendance->ictcore_program_id,
								'account_id'     => 1,
								'contact_id'     => $contact_id,
								'origin'     => 1,
								'direction'     => 'outbound',
								);
								$transmission_id = $ict->ictcore_api('transmissions','POST',$data );
								//echo "================================================================transmission==========================================";
								// print_r($transmission_id);
								//GET transmissions/{transmission_id}/send
								$transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );
								//echo "================================================================transmission send==========================================";
								//print_r($transmission_send);
								// exit;
								  $msg = "Dear Parents your Child (Name-".$student->firstName." ".$student->middleName." ".$student->lastName.", Class- ".$student->class." , Roll- ".$student->rollNo." ) is Absent in School today.";
								//  $fatherCellNo = Student::select('fatherCellNo','')->where('regiNo', $absst)->first();
								 if(!empty($transmission_send->error)){

	                                 	
	                                 	$status =$transmission_send->error->message;
	                                 }else{
	                                 	$status = "Completed";
	                                 }

	                                 //echo "bhutta<pre>".$status;exit;
								$msg =$ictcore_attendance->recording;
								$smsLog = new SMSLog();
								$smsLog->type      = "Attendancehello";
								$smsLog->sender    = "ictcore";
								$smsLog->message   = $msg;
								$smsLog->recipient = $student->fatherCellNo;
								$smsLog->regiNo    = $absst;
								$smsLog->status    = $status;
								$smsLog->save();
							}else{
								return Redirect::to('/attendance/create')->withErrors("Please Add Attendance Message in Setting Menu");
							}
						}else{
							return Redirect::to('/attendance/create')->withErrors("Please Add ictcore integration in Setting Menu");
						}
					}
					return Redirect::to('/attendance/create')->with("success", "Students attendance saved and " . count($absentStudents) . " sms send to father numbers.");
				}
				else
				{
				return Redirect::to('/attendance/create')->with("success", "Students attendance save Succesfully.");

				}

				}
			}
		}
		/**
		* Show the form for creating a new resource.
		*
		* @return Response
		*/
		public function create_file()
		{

			$file = Input::file('fileUpload');
			$ext = strtolower($file->getClientOriginalExtension());

			$validator = \Validator::make(array('ext' => $ext),array('ext' => 'in:xls,xlsx')
			);
			if ($validator->fails()) {
				return Redirect::to('/attendance/create-file')->withErrors($validator);
			} else {
				try {
							$toInsert = 0;
	            $data = \Excel::load(Input::file('fileUpload'), function ($reader) { })->get();

	        if(!empty($data) && $data->count()){
						DB::beginTransaction();
						try {
	            foreach ($data->toArray() as $raw) {
	            
										$attenData= [
											'date' => $raw['date_and_time'],
											'regiNo' => $raw['personnel_id'],
											'status' =>$raw['status'],
 											'created_at' => Carbon::now()
										];
										Attendance::insert($attenData);
										$toInsert++;
									//}
	                }
									DB::commit();
								} catch (Exception $e) {
									DB::rollback();
									$errorMessages = new \Illuminate\Support\MessageBag;
									 $errorMessages->add('Error', 'Something went wrong!');
									return Redirect::to('/attendance/create-file')->withErrors($errorMessages);

									// something went wrong
								}

	            }

							if($toInsert){
	                return Redirect::to('/attendance/create-file')->with("success", $toInsert.' students attendance record upload successfully.');
	            }
							$errorMessages = new \Illuminate\Support\MessageBag;
							 $errorMessages->add('Validation', 'File is empty!!!');
							return Redirect::to('/attendance/create-file')->withErrors($errorMessages);

	        } catch (Exception $e) {
						$errorMessages = new \Illuminate\Support\MessageBag;
						 $errorMessages->add('Error', 'Something went wrong!');
						return Redirect::to('/attendance/create-file')->withErrors($errorMessages);
	        }
			}

		}
		private function sendSMS($number,$sender,$msg)
		{
			return "SMS SEND";
			//need to change for production
			$phonenumber = $number;
			$phonenumber=str_replace('+','',$phonenumber);
			if (strlen($phonenumber)=="11")
			{
				$phonenumber="88".$phonenumber;
			}
			if (strlen($phonenumber)=="13")
			{
				if (preg_match ("/^88017/i", "$phonenumber") or preg_match ("/^88016/i", "$phonenumber") or preg_match ("/^88015/i", "$phonenumber") or preg_match ("/^88011/i", "$phonenumber") or preg_match ("/^88018/i", "$phonenumber") or preg_match ("/^88019/i", "$phonenumber"))
				{


					$myaccount=urlencode("shanixLab");
					$mypasswd=urlencode("1234");
					$sendBy=urlencode($sender);
					$api="http://api-link?user=".$myaccount."&password=".$mypasswd."&sender=".$sendBy."&SMSText=".$msg."&GSM=".$phonenumber."&type=longSMS";
					$client = new \Guzzle\Service\Client($api);
					//  Get your response:
					$response = $client->get()->send();
					$status=$response->getBody(true);
					if($status=="-5" || $status=="5")
					{
						return $status;
					}

					return "SMS SEND";

				}
				else
				{
					return "Invalid Number";
				}
			}
			else
			{
				return "Invalid Number";
			}
		}
		private function  parseAppDate($datestr)
		{
			$date = explode('-', $datestr);
			return $date[2].'-'.$date[1].'-'.$date[0];
		}
		private  function checkPresent($regiNo,$ids)
		{

			for($i=0;$i<count($ids);$i++)
			{

				if($regiNo==$ids[$i])
				{
					return 'Yes';
				}
			}
			return 'No';
		}

		/**
		* Display the specified resource.
		*
		* @param  int  $id
		* @return Response
		*/
		public function show()
		{
			$formdata = new formfoo;
			$formdata->class="";
			$formdata->section="";
			$formdata->shift="";
			$formdata->session=date('Y');
			$formdata->date=date('d-m-Y');
			$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
			//$attendance=array();
              $date = $this->parseAppDate(Carbon::now()->format('d-m-Y'));
			$attendance = DB::table('Student')
			->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
			->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status')
				//->where('Student.class','=',Input::get('class'))
				//->where('Student.section','=',Input::get('section'))
				//->Where('Student.shift','=','Morning')
				//->where('Student.session','=',trim(Input::get('session')))
				->where('Student.isActive', '=', 'Yes')
				->where('Attendance.date', '=', $date )
				->get();


			//$formdata["class"]="";
			//return View::Make('app.attendanceList',compact('classes','attendance','formdata'));
			return View('app.attendanceList',compact('classes','attendance','formdata'));
		}

		public function getlist()
		{

			$rules=[
				'class' => 'required',
				'section' => 'required',
				//'shift' => 'required',
				'session' => 'required',
				'date' => 'required',

			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{
				return Redirect::to('/attendance/list/')->withErrors($validator);
			}
			else {
				$date = $this->parseAppDate(Input::get('date'));


				/*$attendance = Student::with(['attendance' => function($query) use($date){

				     $query->where('date',$date);
				}])*/
	         $attendance = DB::table('Student')
			->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
			->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status')
				->where('Student.class','=',Input::get('class'))
				->where('Student.section','=',Input::get('section'))
				->Where('Student.shift','=','Morning')
				->where('Student.session','=',trim(Input::get('session')))
				->where('Student.isActive', '=', 'Yes')
				->where('Attendance.date', '=', $date)
				->get();
				$formdata = new formfoo;
				$formdata->class=Input::get('class');
				$formdata->section=Input::get('section');
				$formdata->shift=Input::get('shift');
				$formdata->session=Input::get('session');
				$formdata->date=Input::get('date');
				$classes2 = ClassModel::select('code','name')->orderby('code','asc')->pluck('name','code');

				//return View::Make('app.attendanceList',compact('classes2','attendance','formdata'));
	            // $attendance = $attendance->toArray();
				//echo "<pre>";print_r($attendance);
				//exit;
				return View('app.attendanceList',compact('classes2','attendance','formdata'));
			}
		}
		/**
		* Show the form for editing the specified resource.
		*
		* @param  int  $id
		* @return Response
		*/
		public function edit($id)
		{
			$attendance=DB::table('Attendance')
			->join('Student', 'Attendance.regiNo', '=', 'Student.regiNo')
			->select('Attendance.id','Attendance.regiNo','Student.rollNo', 'Student.firstName','Student.middleName','Student.lastName', 'Attendance.status')
			->where('Attendance.id','=',$id)
			->first();
			return View::Make('app.attendanceEdit',compact('attendance'));
		}


		/**
		* Update the specified resource in storage.
		*
		* @param  int  $id
		* @return Response
		*/
		public function update()
		{
			$attd = Attendance::find(Input::get('id'));
			$ispresent = Input::get('ispresent');
			if($ispresent==null)
			{
				$attd->status="No";

			}
			else
			{
				$attd->status="Yes";

			}
			$attd->save();
			echo '<script> alert("attendacne updated successfully.");window.close();</script>';
		}


		public  function printlist($class,$section,$shift,$session,$date)
		{
			if($class!="" && $section !="" && $shift !="" && $date) {
				$className = ClassModel::select('name')->where('code',$class)->first();
				$date = $this->parseAppDate($date);

			/*	$attendance = Student::with(['attendance' => function($query) use($date){
					  $query->where('date','=',$date);
					  $query->where ('status','=', 'Present');
				}])
				->where('class','=',$class)
				->where('section','=',$section)
				->Where('shift','=',$shift)
				->where('session','=',trim($session))
				->where('isActive', '=', 'Yes')
				->get();*/


             $attendance = DB::table('Student')
			->join('Attendance', 'Student.regiNo', '=', 'Attendance.regiNo')
			->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status','Attendance.date')
				->where('Student.class','=',Input::get('class'))
				->where('Student.section','=',Input::get('section'))
				->Where('Student.shift','=','Morning')
				->where('Student.session','=',trim(Input::get('session')))
				->where('Student.isActive', '=', 'Yes')
				->where('Attendance.date', '=', $date)
				->where ('Attendance.status','=', 'Present')
				->get();






				$date = $this->parseAppDate($date);
				$input =array($className->name,$section,$shift,$session,$date);
				$fileName=$className->name.'-'.$section.'-'.$shift.'-'.$section.'-'.$date;
				Excel::create($fileName, function($excel) use($input,$attendance) {

					$excel->sheet('New sheet', function($sheet) use ($input,$attendance) {

						$sheet->loadView('app.attendanceExcel',compact('attendance','input'));

					});

				})->download('xlsx');
				// return "true";
			}
			else
			{
				return "Please fill up form correctly!";
			}
		}

		public function report()
		{
			//return View::make('app.studentAttendance');
			$formdata = new formfoo;
			$formdata->class="";
			$formdata->section="";
			$formdata->shift="";
			$formdata->session=date('Y');
			$formdata->date=date('d-m-Y');
			$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
			$attendance = array();
			return View('app.studentAttendance',compact('classes','formdata','attendance'));
		}
		public function getReport()
		{
			/*$student= Student::where('regiNo','=',Input::get('regiNo'))
			->where('Student.isActive', '=', 'Yes')
			->first();

			//if($student->count()>0){
			if(sizeof($student)>0){
				$status = 'Present';
				$student = Student::with(['attendance' => function($query) use($status){
					  $query->where('status','=',$status);
					  //$query->where ('status','=', 'Present');
				}])
				//with('attendance')
				->where('regiNo','=',Input::get('regiNo'))
				//->where ('status','=', 'Present')
				->where('isActive', '=', 'Yes')
				->first();
				$class = ClassModel::where('code','=',$student->class)->first();
				if(count($student->attendance)>0)
				return View('app.stdattendance',compact('student','class'));
				return  Redirect::back()->with('noresult','Attendance Not Found!');

			}
			return  Redirect::back()->with('noresult','Student Not Found!');*/

			$rules=[
				'class'   => 'required',
				//'section' => 'required',
				'fdate' => 'required',
				'tdate'    => 'required',

			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{
				return Redirect::to('/attendance/report/')->withErrors($validator);
			}
			else {
				$fdate = $this->parseAppDate(Input::get('fdate'));
				$tdate = $this->parseAppDate(Input::get('tdate'));


				/*$attendance = Student::with(['attendance' => function($query) use($date){

				     $query->where('date',$date);
				}])*/
            	//echo "<pre>";print_r(Input::get('section'));exit;
               
	         $attendance = DB::table('Attendance')
			->join('Student', 'Attendance.regiNo', '=', 'Student.regiNo')
			->leftjoin('section', 'section.id', '=', 'Attendance.section_id')
			->select('Attendance.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status','Attendance.section_id','Attendance.date','section.name as section');
				if(Input::get('class')!='all'){
			     $classc = DB::table('Class')->select('*')->where('code','=',Input::get('class'))->first();
				$attendance=$attendance->where('Attendance.class_id','=',$classc->id);
				//->where('Student.section','=',Input::get('section'))
				 $attendance=$attendance->whereIn('Attendance.section_id', Input::get('section'));
				}else{
				$attendance=$attendance->where('Attendance.class_id','!=',NULL);
				//->where('Student.section','=',Input::get('section'))
				 $attendance=$attendance->where('Attendance.section_id','!=', NULL);
				}
				//$attendance=$attendance->Where('Student.shift','=','Morning');
				//->where('Student.session','=',trim(Input::get('session')))
				$attendance=$attendance->where('Student.isActive', '=', 'Yes');
				//->where('Attendance.date', '=', $date)
				$attendance=$attendance->whereBetween('Attendance.date', [$fdate, $tdate]);
				$attendance=$attendance->orderby('Attendance.id','Desc');
				$attendance=$attendance->get();
				//echo "<pre>";print_r($attendance);
				$formdata = new formfoo;
				$formdata->class=Input::get('class');
				//$formdata->section=Input::get('section');
				$formdata->shift=Input::get('shift');
				$formdata->session=Input::get('session');
				$formdata->date=Input::get('date');

				$classes2 = ClassModel::select('code','name')->orderby('code','asc')->pluck('name','code');

				//return View::Make('app.attendanceList',compact('classes2','attendance','formdata'));
	            // $attendance = $attendance->toArray();
				//echo "<pre>";print_r($attendance);
				//exit;
				return View('app.studentAttendance',compact('classes2','attendance','formdata'));


		}
	}
	public function stdatdreportindex(){

       return View('app.studentAttendancereprt',compact(''));


	}

	public function stdatdreport($b_form)
	{
		$stdinfo =  DB::table('Student')->where('Student.b_form',$b_form)->first();
		$attendances = DB::table('Attendance')
			->join('Student', 'Attendance.regiNo', '=', 'Student.regiNo')
			->leftjoin('section', 'section.id', '=', 'Attendance.section_id')
			->select('Attendance.regiNo',DB::raw('count(Attendance.regiNo) as `data`'), DB::raw("DATE_FORMAT(Attendance.date, '%m-%Y') new_date"),  DB::raw('YEAR(Attendance.date) year, MONTH(Attendance.date) month'))->where('Student.b_form',$b_form)->where('Attendance.status','Present')
             ->groupby('year')->get();
			//->select('Attendance.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName','Student.class','Attendance.status','Attendance.section_id','Attendance.date','section.name as section')->where('Student.b_form',$b_form)->get();
              foreach($attendances as $attendance){

              // 	$year = date('Y', strtotime($dateString));
              //	$this->years = Carbon::createFromDate(2018,1);

              //	$yer[] = $this->years->weeksInMonth;
              	echo date("z", mktime(0,0,0,12,31,$attendance->year)) + 1;
                 echo "<br>";


				$start = new \DateTime($attendance->year.'-01-01');
				$end = new \DateTime($attendance->year.'-12-31');
				$days = $start->diff($end, true)->days;

				echo $sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
				echo "<br>";
               }
               echo "<pre>";print_r($attendances);


               //echo "<pre>";print_r($yer);
               $institute=Institute::select('*')->first();
              $pdf = \PDF::loadView('app.attendancestdreportprint',compact('attendances','stdinfo','institute'));
		      return $pdf->stream('student-Payments.pdf');
               exit;
		$institute=Institute::select('*')->first();
		//$rdata =array('payTotal'=>$totals->payTotal,'paiTotal'=>$totals->paiTotal,'dueAmount'=>$totals->dueamount);
		//$pdf = \PDF::loadView('app.attendancestdreportprint',compact('datas','rdata','stdinfo','institute'));
		//return $pdf->stream('student-Payments.pdf');
	}
}
