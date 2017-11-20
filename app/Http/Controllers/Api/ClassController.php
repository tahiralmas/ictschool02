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

class ClassController extends Controller
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
		    public function classes()
		    {
				
					  $class = DB::table('Class')->get();
					  if(count($class)<1)
					  {
					     return response()->json(['error'=>'No Class Found!'], 401);

					  }
					  else {
						  //$formdata = new formfoo;
						 /* $formdata->class=Input::get('class');
						  $formdata->section=Input::get('section');
						  $formdata->shift=Input::get('shift');
						  $formdata->session=trim(Input::get('session'));*/
						  //return View::Make("app.studentList", compact('students','classes','formdata'));
						  //return View("app.studentList", compact('students','classes','formdata'));
						  return response()->json(['classes' => $class]);
					  }
				  }

		    public function getclass($class_id)
		    {
                 $classes = ClassModel::find($class_id);

                if(!is_null($classes) && $classes->count()>0){

                   return response()->json(['class'=>$classes]);
		        }else{
		        return response()->json(['error'=>'Class Not Found'], 401);
	           }

		    }

		    public function update_student($class_id)
		    {
		    	 return response()->json(['class'=>$class_id]);
		    }

		    
}


	        