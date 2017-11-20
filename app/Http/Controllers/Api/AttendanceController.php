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

class AttendanceController extends Controller
{

    public function __construct() 
    {

     //  $this->middleware('auth:api');

    }
   public $successStatus = 200;

			/**
			 * attendance_create api
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
						$presents = $presents;
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
					$atten = DB::table('Attendance')->where('date','=',$presentDate)->where('regiNo','=',$students)->first();
					if(is_null($atten)){
					foreach ($stpresent as $stp) 
					{
						$attenData= [
							'date' => $presentDate,
							'regiNo' => $stp['regiNo'],
							'created_at' => Carbon::now()
						];
						$attendence_id = Attendance::insertGetId($attenData);

					}
				}else{
					 return response()->json(['error'=>'Attendance already added'], 401);
				}
					DB::commit();
				}
				catch (Exception $e) 
				{
					DB::rollback();
					$errorMessages = new Illuminate\Support\MessageBag;
					 $errorMessages->add('Error', 'Something went wrong!');
					return response()->json(['error11'=>withErrors($errorMessages)], 401);

				}
                  return response()->json(['success'=>"Students attendance save Succesfully.",'id' => $attendence_id]);
			}
		}
		
		    /**
		     * attendance_view api
		     *
		     * @return \Illuminate\Http\Response
		     */
		    public function attendance_view($class_level,$section,$shift,$session,$date)
		    {
                        // return response()->json(['error'=>$class_level."========".$section], 401)
                       $date = $this->parseAppDate($date);
						//dd($date);
						$attendance = \App\Student::with(['attendance' => function($query) use($date){
						     $query->where('date','=',$date);
						}])
						->where('class','=',$class_level)
						->where('section','=',$section)
						->Where('shift','=',$shift)
						->where('session','=',trim($session))
						->where('isActive', '=', 'Yes')
						//->where('isActive', '=', 'Yes')->with('attendance')
						->get();
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

                        return response()->json(['attendance'=>$s_attendence]);

                    




				/*$validator = \Validator::make(Input::all(), $rules);
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
						/*$classes2 = ClassModel::select('code','name')->orderby('code','asc')->pluck('name','code');
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
                    select * from `Student` where `class` = 'cl1' and `section` = 'A' and `shift` = 'Day' and `session` = 2017 and `isActive` = 'Yes'
						//return View::Make('app.attendanceList',compact('classes2','attendance','formdata'));
						//dd($attendance);
						//return View('app.attendanceList',compact('classes2','attendance','formdata'));
					      return response()->json(['attendance'=>$s_attendence]);
				    }*/
		    }

		    public function get_attendance($attendance_id){
                 
	             $std_atd = DB::table('Student')
	            ->join('Attendance', 'Attendance.regiNo', '=', 'Student.regiNo')
	            ->select('Student.RegiNo','Student.rollNo','Student.firstName','Student.lastName','Student.class','Student.section', 'Attendance.date')->where('Attendance.id','=',$attendance_id)
	            ->get();
	              //return response()->json(['error'=> $std_atd], 401);

	            if($std_atd->isEmpty()) {
	              return response()->json(['error'=>'Attendance Not Found'], 401);
	            }else{

	              foreach($std_atd as $atd){
					    $att = 'Present';
					    $s_attendence[] = array('RegiNo'=>$atd->RegiNo,'RollNo'=>$atd->rollNo,'Name'=>$atd->firstName.' '.$atd->lastName,'Is Present'=>$att,'Date'=>$atd->date);

	              }
	               return response()->json(['attendance'=>$s_attendence]);
	            }
		    }
		    public function update_attendance($attendance_id){

	        return response()->json(['attendance'=>$attendance_id]);

		    }

		    public function deleted($attendance_id){
    
		    $attd = Attendance::find($attendance_id);
		    if(!is_null($attd) && $attd->count()>0){

               DB::table('Attendance')->where('Attendance.id','=',$attendance_id)->delete();
                  return response()->json(['success'=>"Students attendance deleted Succesfully."]);
		    }else{
		        return response()->json(['error'=>'Attendance Not Found'], 401);

		    }
			    }
		    private function  parseAppDate($datestr)
		    {
		      $date = explode('-', $datestr);
		      return $date[2].'-'.$date[1].'-'.$date[0];
		    } 
}


	        