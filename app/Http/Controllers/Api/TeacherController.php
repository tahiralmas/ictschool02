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
use App\Teacher;
use App\SectionModel;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TeacherController extends Controller
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
	public function all_teachers()
	{
	  $teachers = DB::table('teacher')->select('id','firstName','lastName','gender','religion','bloodgroup','nationality','dob','photo','email','fatherName','fatherCellNo','presentAddress')->get();
	  
	  if(count($teachers)<1)
	  {
		 return response()->json(['error'=>'No teachers Found!'], 404);
	  }
	  else {
		  return response()->json($teachers,200);
	  }
	}

	/**
	 * student_classwise api
	 *
	 * @return \Illuminate\Http\Response
	 */
	/*public function student_classwise($class_level,$section,$shift,$session)
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
	  else 
	  {
		  return response()->json($students,200);
	  }
	}*/
	public function getteacher($teacher_id)
	{
		  $teacher = DB::table('teacher')->where('id','=',$teacher_id)->first();
		
		if(!is_null($teacher) && count($teacher)>0){
		   return response()->json($teacher,200);
		}else{
		return response()->json(['error'=>'teacher Not Found'], 404);
	   }
	}

	/*public function getstudentsubjects($student_id)
	{
		 //$student = Student::find($student_id);
		 $student = Student::find($student_id);
		  
		 $subject = DB::table('Subject')->select('code','name','type','class','stdgroup')->where('class',$student->class)->where('stdgroup',$student->group)->get();
		if(!is_null($subject) && count($subject)>0){
		  	 return response()->json(['subjects'=>$subject]);
		}else{
			return response()->json(['error'=>'Subject Not Found'], 404);
	   }
	}*/

	public function update_teacher($teacher_id)
	{
		//return response()->json(['student'=>$student_id]);
		$rules=[
		'firstname' => 'required',
		'lastname'  => 'required',
		'gender'    => 'required',
		'dob'       => 'required',
		'phone'     => 'required',
		'email'     => 'required',
		'presentAddress' => 'required',
		'fatherName'  =>'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return response()->json($validator->errors(), 422);
		}
		else{
			$teacher = Teacher::find($teacher_id);
			$teacher->firstName = Input::get('firstname');
			$teacher->lastName= Input::get('lastname');
			$teacher->gender= Input::get('gender');
			$teacher->dob= Input::get('dob');
			$teacher->phone= Input::get('phone');
			$teacher->email= Input::get('email');
			$teacher->presentAddress= Input::get('presentAddress');
			$teacher->fatherName= Input::get('fatherName');
			$teacher->save();
			return response()->json($teacher,200);
		}
	}
	public function getsectionteacher($teacher_id){

		$teacher = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.name as class', 'section.name as section')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.class_id')->get();

		if(!is_null($teacher) && count($teacher)>0){
			return response()->json($teacher,200);
		}else{
			return response()->json(['error'=>'teacher Not Found'], 404);
		}
	}  


	public function getsubjectteacher($teacher_id){

		$teacher = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('Subject', 'timetable.subject_id', '=', 'Subject.id')
		->select('Subject.name as subject', 'Class.name as class')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.class_id')->get();
		if(!is_null($teacher) && count($teacher)>0){
			return response()->json($teacher);
		}else{
			return response()->json(['error'=>'teacher Not Found'], 404);
		}
	}    
}


			