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
	  $teachers = DB::table('teacher')->select('id','firstName','lastName','gender','dob','email','phone','fatherName','fatherCellNo','presentAddress')->paginate(20);
	  
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
		  $teacher = DB::table('teacher')->select('id','firstName','lastName','gender','dob','email','phone','fatherName','fatherCellNo','presentAddress')->where('id','=',$teacher_id)->first();
		
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
		'email'     => 'required',
		'phone'     => 'required',
		'presentaddress' => 'required',
		'fathername'  =>'required',
		'fathercellno'=> 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return response()->json($validator->errors(), 422);
		}
		else{
			$teacher = Teacher::select('id','firstName','lastName','gender','dob','email','phone','fatherName','fatherCellNo','presentAddress')->where('id',$teacher_id);
			$teacher->firstName = Input::get('firstname');
			$teacher->lastName= Input::get('lastname');
			$teacher->gender= Input::get('gender');
			$teacher->dob= Input::get('dob');
			$teacher->phone= Input::get('phone');
			$teacher->email= Input::get('email');
			$teacher->presentAddress= Input::get('presentaddress');
			$teacher->fatherName= Input::get('fathername');
			$teacher->fatherName= Input::get('fathercellno');
			$teacher->save();
			return response()->json($teacher,200);
		}
	}
	public function getsectionteacher($teacher_id){

		$teacher = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.section_id')->get();
           
		
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

	public function getteacherdata($teacher_id)
	{
		/*$teachers = DB::table('timetable')
		->join('Class', 'timetable.class_id', '=', 'Class.code')
		->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section')
		->where('timetable.teacher_id',$teacher_id)->groupby('timetable.section_id')->get();		
		*/

		$teachers = DB::table('section')
		->join('Class', 'section.class_code', '=', 'Class.code')
		//->join('section', 'timetable.section_id', '=', 'section.id')
		->select('Class.id as class_id','Class.name as class', 'section.id as section_id','section.name as section');
		if($teacher_id!='admin'){
	    $teachers = $teachers->where('section.teacher_id',$teacher_id);		
		}
		$teachers =$teachers->get();
		$sections  = array();
		$attendances_b  = array();
		 if($teachers->count()>0){
		 	$i=0;
		foreach($teachers as $teacher ){
          $sections[] = $teacher->section_id;
         
          $attendances_a = DB::table('Attendance')
             ->join('Class', 'Attendance.class_id', '=', 'Class.id')
		     ->join('section', 'Attendance.section_id', '=', 'section.id')
             ->select(DB::raw('COUNT(*) as total_attendance,
                           SUM(Attendance.status="Absent") as absent,
                           SUM(Attendance.status="Present" ) as present ,
                           SUM(Attendance.coments="sick_leave" OR Attendance.coments="leave") as leaves'),'section.id as section_id','section.name as section','Class.id as class_id','Class.name as class')->where('Attendance.session',2018)->where('Attendance.section_id',$teacher->section_id)->where('date',Carbon::today()->toDateString())->first();
           //$tst[] = $attendances_a[$i]->total_attendance;
           if($attendances_a->total_attendance==0){
           	 $attendances_b[] = array('total_attendance'=>0,'absent'=>0,'present'=>0,'leaves'=>0,'section_id'=>$teacher->section_id,'section'=>$teacher->section,'class_id'=>$teacher->class_id,'class'=>$teacher->class);
           }else{
           	$attendances_b[] = $attendances_a;
           }
           $i++;
		}
		//$mrge = array_merge($attendances_b,$attendances_d);
		return response()->json($attendances_b);
	      $merage = $attendances_a;
		
			if(!empty($merage)){
				return response()->json($merage,200);
			}else{
				return response()->json(['error'=>'teacher Not Found'], 404);
			}
		}else{
			 return response()->json(['error'=>'teacher Not Found'], 404);

		}
	}     
}


			