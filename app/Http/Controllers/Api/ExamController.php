<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;

//use App\Api_models\User;

use Illuminate\Support\Facades\Auth;

use Validator;
use App\Exam;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ExamController extends Controller
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
	public function getallexam()
	{
		  $exams = DB::table('exam')->select('*')->get();
		/*  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->get();*/
		  if(count($exams)<1)
		  {
		     return response()->json(['error'=>'No exam Found!'], 404);
		  }
		  else {
			  return response()->json(['exams' => $exams]);
		  }
	}


    public function getexam($exam_id)
    {
         //$student = Student::find($student_id);
    	  $exam = DB::table('exam')
          ->join('Class', 'exam.class_id', '=', 'Class.id')
          ->join('section', 'exam.section_id', '=', 'section.id')
          ->select('exam.type','Class.name as class','section.name as section')
    	  ->where('exam.id','=',$exam_id)->first();
    	/* ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group','Student.presentAddress', 'Student.gender', 'Student.religion')
		    ->where('Student.id',$student_id)->first();*/

        if(!is_null($exam) && count($exam)>0){
           return response()->json(['exam'=>$exam]);
        }else{
        return response()->json(['error'=>'exam Not Found'], 404);
       }
    }

   
}


	        