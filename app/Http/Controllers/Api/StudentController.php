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
		    public function student_classwise($class_level,$section,$shift,$session)
		    {
				
					  $students = DB::table('Student')
					  ->join('Class', 'Student.class', '=', 'Class.code')
					  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
					  'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
					  //->where('isActive', '=', 'Yes')
					  ->where('class',$class_level)
					  ->where('section',$section)
					  ->where('shift',$shift)
					  ->where('session',trim($session))
					  ->get();
					  if(count($students)<1)
					  {
					     return response()->json(['error'=>'No Students Found!'], 401);

					  }
					  else {
						  //$formdata = new formfoo;
						 /* $formdata->class=Input::get('class');
						  $formdata->section=Input::get('section');
						  $formdata->shift=Input::get('shift');
						  $formdata->session=trim(Input::get('session'));*/
						  //return View::Make("app.studentList", compact('students','classes','formdata'));
						  //return View("app.studentList", compact('students','classes','formdata'));
						  return response()->json(['students' => $students]);
					  }
				  }

		    public function getstudent($student_id)
		    {
                 $student = Student::find($student_id);
                if(!is_null($student) && $student->count()>0){

                   return response()->json(['studnet'=>$student]);
		        }else{
		        return response()->json(['error'=>'Student Not Found'], 401);
	           }

		    }

		    public function update_student($student_id)
		    {
		    	 return response()->json(['student'=>$student_id]);
		    }

		    
}


	        