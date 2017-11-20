<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\ClassModel;
use App\Subject;
use App\Attendance;
use App\Student;
use App\SectionModel;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class UserController extends Controller
{

	    public function __construct() 
	    {

	     //  $this->middleware('auth:api');
	    }
	    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
    	//dd(request('email'));
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')]))
        {

            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
    /**
     * profile api
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }
    /**
     * get_user api
     *@param  int  $user_id
     * @return \Illuminate\Http\Response
     */
    public function get_user($user_id)
    {
    	//dd($user_id);
       // $user = Auth::user();
        $user = DB::table('users')->select('id','firstname','lastname','desc','login','email','group')->where('id','=',$user_id)->first();
        if(!is_null($user)){
       	 return response()->json(['user' => $user], $this->successStatus);
        }else{

           return response()->json(['error'=>'Student not found'], 401);
        }   
    }

     /**
     * logout api
     *
     * @return \Illuminate\Http\Response
     */
		public function logout(Request $request)
		{
		    $request->user()->token()->revoke();

		    $this->guard()->logout();

		    $request->session()->flush();

		    $request->session()->regenerate();

		    $json = [
		        'success' => true,
		        'code' => 200,
		        'message' => 'You are Logged out.',
		    ];
		    return response()->json($json, '200');
		}


       /**
     * attendance api
     *
     * @return \Illuminate\Http\Response
     */
    
      public function attendance()
	  {
	    $classes=array();
	    $classes2 =ClassModel::pluck('name');
	    $section = SectionModel::pluck('name');
	    $attendance=array();
	    return response()->json(['sections' => $section,'classes'=>$classes2]);

	  }
		 /**
	     * attendance_create api
	     *
	     * @return \Illuminate\Http\Response
	     */
	    public function attendance_create()
        {
			$rules = [
				'class' => 'required',
				'section' => 'required',
				'shift' => 'required',
				'session' => 'required',
				'regiNo' => 'required',
				'date' => 'required',
			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails()) 
			{
                     return response()->json(['error'=>'Please Fill the Required Field'], 401);
			} else 
			{

					$absentStudents = array();
					$students = Input::get('regiNo');
					$presents = Input::get('present');
					$all = false;
					if ($presents == null) 
					{
						$all = true;
					} else 
					{
						//$ids = array_keys($presents);
						$presents = $present;
					}
					$stpresent = array();
					//foreach ($students as $student) {
						$st = array();
						$st['regiNo'] = $students;
						if ($all) 
						{
							$st['status'] = 'No';
						} else 
						{
							$st['status'] = $presents;
						}
						if ($st['status'] == "No") 
						{
							array_push($absentStudents, $students);
						}
						else 
						{
							array_push($stpresent, $st);
						}
					//}
					$presentDate = $this->parseAppDate(Input::get('date'));
					DB::beginTransaction();
					try {
					foreach ($stpresent as $stp) 
					{
						$attenData= [
							'date' => $presentDate,
							'regiNo' => $stp['regiNo'],
							'created_at' => Carbon::now()
						];
						Attendance::insert($attenData);

					}
					DB::commit();
				} catch (\Exception $e) 
				{
					DB::rollback();
					$errorMessages = new Illuminate\Support\MessageBag;
					 $errorMessages->add('Error', 'Something went wrong!');
					return response()->json(['error11'=>withErrors($errorMessages)], 401);

				}
					//get sms format
					//loop absent student and get father's no and send sms
					$isSendSMS = Input::get('isSendSMS');
					if ($isSendSMS == null) 
					{
						//return Redirect::to('/attendance/create')->with("success", "Students attendance save Succesfully.");
			           return response()->json(['success'=>"Students attendance save Succesfully."]);
					} else
					 {

						if(count($absentStudents) > 0) 
						{

							foreach ($absentStudents as $absst) 
							{
								$student=	DB::table('Student')
								->join('Class', 'Student.class', '=', 'Class.code')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Class.Name as class')
								->where('Student.regiNo','=',$absst)
								->where('class',Input::get('class'))
								->first();
								$msg = "Dear Parents your Child (Name-".$student->firstName." ".$student->middleName." ".$student->lastName.", Class- ".$student->class." , Roll- ".$student->rollNo." ) is Absent in School today.";
								//  $fatherCellNo = Student::select('fatherCellNo','')->where('regiNo', $absst)->first();

								$response = $this->sendSMS($student->fatherCellNo,"ShanixLab", $msg);
								$smsLog = new SMSLog();
								$smsLog->type = "Attendance";
								$smsLog->sender = "SuperSoft";
								$smsLog->message = $msg;
								$smsLog->recipient = $student->fatherCellNo;
								$smsLog->regiNo = $absst;
								$smsLog->status = $response;
								$smsLog->save();
							}
							//return Redirect::to('/attendance/create')->with("success", "Students attendance saved and " . count($absentStudents) . " sms send to father numbers.");
						}
						else
						{
							//return Redirect::to('/attendance/create')->with("success", "Students attendance save Succesfully.");
	                        return response()->json(['success'=>"Students attendance save Succesfully."]);
						}

					}
				}
	        }
			/**
		     * student_classwise api
		     *
		     * @return \Illuminate\Http\Response
		     */
		    public function student_classwise()
		    {
				  $rules = [
					  'class' => 'required',
					  'section' => 'required',
					  'shift' => 'required',
					  'session' => 'required'

				  ];
				  $validator = \Validator::make(Input::all(), $rules);
				  if ($validator->fails())
				  {
					   return response()->json(['error'=>'Please Fill the Required Field'], 401);
				  } else 
				  {
					  $students = DB::table('Student')
					  ->join('Class', 'Student.class', '=', 'Class.code')
					  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
					  'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
					  //->where('isActive', '=', 'Yes')
					  ->where('class',Input::get('class'))
					  ->where('section',Input::get('section'))
					  ->where('shift',Input::get('shift'))
					  ->where('session',trim(Input::get('session')))
					  ->get();
					  if(count($students)<1)
					  {
					     return response()->json(['error'=>'No Students Found!'], 401);

					  }
					  else {
						  $classes = ClassModel::pluck('name','code');
						  //$formdata = new formfoo;
						 /* $formdata->class=Input::get('class');
						  $formdata->section=Input::get('section');
						  $formdata->shift=Input::get('shift');
						  $formdata->session=trim(Input::get('session'));*/
						  //return View::Make("app.studentList", compact('students','classes','formdata'));
						  //return View("app.studentList", compact('students','classes','formdata'));
						  return response()->json(['students' => $students,'classes'=>$classes]);
					  }
				  }

		    }
		    private function  parseAppDate($datestr)
		    {
		      $date = explode('-', $datestr);
		      return $date[2].'-'.$date[1].'-'.$date[0];
		    }
		    /**
		     * attendance_view api
		     *
		     * @return \Illuminate\Http\Response
		     */
		    public function attendance_view()
		    {
				    $rules=[
					'class' => 'required',
					'section' => 'required',
					'shift' => 'required',
					'session' => 'required',
					'date' => 'required',

				];
				$validator = \Validator::make(Input::all(), $rules);
				if ($validator->fails())
				{
					return response()->json(['error'=>'Please Fill the Required Field'], 401);
				}
				else {
						$date = $this->parseAppDate(Input::get('date'));
						//dd($date);
						$attendance = \App\Student::with(['attendance' => function($query) use($date){
						     $query->where('date','=',$date);
						}])
						->where('class','=',Input::get('class'))
						->where('section','=',Input::get('section'))
						->Where('shift','=',Input::get('shift'))
						->where('session','=',trim(Input::get('session')))
						->where('isActive', '=', 'Yes')
						//->where('isActive', '=', 'Yes')->with('attendance')
						->get();
						
						
								/*$attendance = Student::with(['attendance' => function ($query) use($date) {
					          $query->where('date', '=',$date);

					      }])->get();*/
					      /*$attendance = App\Student::join('attendance', function ($join)use($date) {
					            $join->on('date', '=', $date);
					                // ->where('contacts.user_id', '>', 5);
					        })
				        ->get();*/
						
						/*$formdata = new formfoo;
						$formdata->class=Input::get('class');
						$formdata->section=Input::get('section');
						$formdata->shift=Input::get('shift');
						$formdata->session=Input::get('session');
						$formdata->date=Input::get('date');*/
						$classes2 = ClassModel::select('code','name')->orderby('code','asc')->pluck('name','code');
                        $s_attendence = array();
						foreach($attendance as $atd)
						{
							if(count($atd->attendance)){

                              $att = 'Present';

							}else{

								 $att = 'Absent';
							}
                          $s_attendence[] = array('RegiNo'=>$atd->regiNo,'RollNo'=>$atd->rollNo,'Name'=>$atd->firstName.' '.$atd->lastName,'Is Present'=>$att);
						}

						//return View::Make('app.attendanceList',compact('classes2','attendance','formdata'));
						//dd($attendance);
						//return View('app.attendanceList',compact('classes2','attendance','formdata'));
					      return response()->json(['attendance'=>$s_attendence]);

				    }
		    } 
              /**
		     * allstudent api
		     *
		     * @return \Illuminate\Http\Response
		     */
		     public function allstudent()
		    { 

               $student =	DB::table('Student')->get();
                if($student->count()>0){
                return response()->json(['student'=>$student]);
	            }else{

	              return response()->json(['error'=>'Student not found'], 401);
                }

		    }
      
		protected function guard()
		{
		    return Auth::guard('api');
		}
}