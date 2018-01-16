<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ictcoreController;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\ClassModel;
use App\Message;
use App\Subject;
use App\Attendance;
use App\Student;
use App\SectionModel;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class StudentController extends Controller
{

    public function __construct() 
    {

     //  $this->middleware('auth:api');

    }
   public $successStatus = 200;



   /**
	 * student_classwise api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function all_students()
	{
		 $students = DB::table('Student')
		  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->get();
		  if(count($students)<1)
		  {
		     return response()->json(['error'=>'No Students Found!'], 401);
		  }
		  else {
			  return response()->json(['students' => $students]);
		  }
	}


       
	/**
	 * student_classwise api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function student_classwise($class_level,$section,$shift,$session)
	{
		  $students = DB::table('Student')
		  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->where('class',$class_level)
		  ->where('section',$section)
		  ->where('shift',$shift)
		  ->where('session',trim($session))
		  ->get();
		  if(count($students)<1)
		  {
		     return response()->json(['error'=>'No Students Found!'], 404);
		  }
		  else {
			  return response()->json(['students' => $students]);
		  }
	}
    public function getstudent($student_id)
    {
         //$student = Student::find($student_id);
    	  $student = DB::table('Student')
    	 ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group','Student.presentAddress', 'Student.gender', 'Student.religion')
		    ->where('Student.id',$student_id)->first();

        if(!is_null($student) && count($student)>0){
           return response()->json(['studnet'=>$student]);
        }else{
        return response()->json(['error'=>'Student Not Found'], 404);
       }
    }

    public function getstudentsubjects($student_id)
    {
         //$student = Student::find($student_id);
    	 $student = Student::find($student_id);
          
       $subject = DB::table('Subject')->select('code','name','type','class','stdgroup')->where('class',$student->class)->where('stdgroup',$student->group)->get();

    	
    	 /*->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		    ->where('Student.id',$student_id)->first();*/

        if(!is_null($subject) && count($subject)>0){
           return response()->json(['subjects'=>$subject]);
        }else{
        return response()->json(['error'=>'Subject Not Found'], 404);
       }
    }

	public function update_student($student_id)
	{
		//return response()->json(['student'=>$student_id]);
		$rules=[
		'fname' => 'required',
		'lname' => 'required',
		'gender' => 'required',
		'session' => 'required',
		'class' => 'required',
		'section' => 'required',
		'presentAddress' => 'required',
		'parmanentAddress' => 'required',
		'fatherCellNo'  =>'required',
		'fatherName'  =>'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
            return response()->json($validator->errors(), 422);
		}
		else{
			$student = Student::find($student_id);
			$student->firstName = Input::get('fname');
			$student->lastName= Input::get('lname');
			$student->gender= Input::get('gender');
			$student->session= trim(Input::get('session'));
			$student->class= Input::get('class');
			$student->section= Input::get('section');
			$student->group= Input::get('group');
			$student->presentAddress= Input::get('presentAddress');
			$student->parmanentAddress= Input::get('parmanentAddress');
		    $student->fatherCellNo= Input::get('fatherCellNo');
			$student->fatherName= Input::get('fatherName');
			$student->save();
			return response()->json(['student' => $student]);
		}
	}

	public function studentnotification($student_id){
		 $rules=[
            'name'        => 'required',
            'recording'   =>'required'

            ];
        $validator = \Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
         return response()->json($validator->errors(), 422);
        }
        else{

                      $student = Student::find($student_id);

                      if(is_null($student)){
                         return response()->json(['error'=>'Student Not Found'], 404);
                         exit;
                      }
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimetype      = $finfo->buffer(base64_decode(Input::get('recording')));

                     if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                         $ict  = new ictcoreController();
                    // $headers = apache_request_headers();
                     //dd($headers['Authorization']);
                        $filename ='notification_class_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";

                      file_put_contents(public_path('recording/').$filename.'.wav', base64_decode(Input::get('recording')));

                         //      unlink(public_path('recording/'.$filename));

                    
                        sleep(2);
                        $data = array(
                                     'name' => Input::get('name'),
                                     'description' => Input::get('description'),
                                     );

                         $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                         $name          =  base_path() .'/public/recording/'.$filename.".wav";


                         $finfo         =  new \finfo(FILEINFO_MIME_TYPE);
                         $mimetype      =  $finfo->file($name);

                         $cfile         =  curl_file_create($name, $mimetype, basename($name));
                         $data          =  array( $cfile);
                         $result        =  $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                         $recording_id  =  $result ;
                        if(!is_array($recording_id )){

                          $data = array(
                                     'name' => Input::get('title'),
                                     'recording_id' => $recording_id,
                                     );
                         $program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );
                         if(!is_array( $program_id )){
                          $program_id = $program_id;
                         }else{
                            return response()->json("ERROR: Program not Created" );
                         
                         }
                        }else{
                            return response()->json("ERROR: Recording not Created" );
                                          
                        }

                    $notificationData= [
                                    'name' => Input::get('name'),
                                    'description' =>Input::get('description'),
                                    'recording' => $filename.".wav",
                                    'ictcore_program_id' => $program_id,
                                    'ictcore_recording_id' => $recording_id,
                                ];

                              $notification_id = Message::insertGetId($notificationData);
                                
                    $student=   DB::table('Student')
                        ->select('*')
                        ->where('isActive','Yes')
                        ->where('id', $student_id)
                        ->first();
                        
                            $data = array(
                            'first_name' => $student->firstName,
                            'last_name' => $student->lastName,
                            'phone'     => $student->fatherCellNo,
                            'email'     => '',
                            );


                            $contact_id = $ict->ictcore_api('contacts','POST',$data );

                       $data = array(
								'title' => 'Attendance',
								'program_id' => $program_id,
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

                return response()->json(['success'=>"Nofication Sended Succesfully."],200);

            }else{
                    return response()->json("ERROR:Please Upload Correct file",415 );

            }
        }
	}   
}


	        