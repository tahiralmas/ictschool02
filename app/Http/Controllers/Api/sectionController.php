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

class sectionController extends Controller
{

    public function __construct() 
    {

       $this->middleware('auth:api');

    }
   public $successStatus = 200;

	/**
     * student_classwise api
     *
     * @return \Illuminate\Http\Response
     */
    public function section()
    {
	  $section = DB::table('section')->get();
	  if(count($section)<1)
	  {
	     return response()->json(['error'=>'No Section Found!'], 401);
	  }
	  else {
		  return response()->json(['section' => $section]);
	  }
    }

    public function getsection($section_id)
    {
         $section = SectionModel::find($section_id);
        if(!is_null($section) && $section->count()>0){
           return response()->json(['section'=>$section]);
        }else{
        return response()->json(['error'=>'Section Not Found'], 401);
       }
    }
    public function putsection($section_id){

        $section = SectionModel::find($section_id);
        if(!is_null($section) && $section->count()>0){

            $section = SectionModel::find($section_id);
            $section->name= Input::get('name');
            $section->description=Input::get('description');
            $section->save();
           return response()->json(['section'=>$section]);
        }else{
        return response()->json(['error'=>'Section Not Found'], 401);
       }

    }
    public function update_class($class_id)
    {
        $rules=[
		'name' => 'required',
		'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		 return response()->json(['error'=>'Please Fill the Required Field'], 401);
		}
		else {
			$class = ClassModel::find($class_id);
			$class->name= Input::get('name');
			$class->description=Input::get('description');
			$class->save();
          return response()->json(['success'=>"Class Updated Succesfully."]);

		}
    }
    public function getsectionsubject($section_id){

        $section = SectionModel::find($section_id);
         $subject = DB::table('Subject')->select('code','name','type','class','stdgroup')->where('class',$section->class_code)->get();

         /*->join('Class', 'Student.class', '=', 'Class.code')
          ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
            ->where('Student.id',$student_id)->first();*/
        if(!is_null($subject) && count($subject)>0){
           return response()->json(['subjects'=>$subject]);
        }else{
        return response()->json(['error'=>'Subject Not Found'], 401);
       }
    }
    public function getsectionstudent($section_id){

        $section = SectionModel::find($section_id);
         $student = DB::table('Student')->select('*')->where('class',$section->class_code)->where('section',$section_id)->get();
         /*->join('Class', 'Student.class', '=', 'Class.code')
          ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
            ->where('Student.id',$student_id)->first();*/
        if(!is_null($student) && count($student)>0){

           return response()->json(['student'=>$student]);
        }else{
        return response()->json(['error'=>'Student Not Found'], 401);
       }
    }	

    public function getsectionteacher($section_id){

         $section = SectionModel::find($section_id);
         $student = DB::table('Student')->select('*')->where('class',$section->class_code)->where('section',$section_id)->get();

         $teacher = DB::table('teacher')
          ->join('timetable', 'teacher.id', '=', 'timetable.teacher_id')
          ->join('Subject', 'timetable.subject_id', '=', 'Subject.id')
          ->select('teacher.id', 'teacher.firstName', 'teacher.lastName', 'teacher.fatherName', 'teacher.fatherCellNo', 'teacher.fatherCellNo', 'teacher.presentAddress',
          'Subject.name as Subject')->groupby('timetable.teacher_id')
          ->where('timetable.section_id',$section_id)->get();
         /*->join('Class', 'Student.class', '=', 'Class.code')
           ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
          'Class.Name as class','Student.section' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
            ->where('Student.id',$student_id)->first();*/
        if(!is_null($teacher) && count($teacher)>0){
           return response()->json(['teacher'=>$teacher]);
        }else{
        return response()->json(['error'=>'Teacher Not Found'], 401);
       }
    }    
}


	        