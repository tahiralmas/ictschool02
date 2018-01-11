<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

//use App\Api_models\User;
//use Illuminate\Support\Facades\Auth;
use Validator;
use App\ClassModel;
use App\Subject;
use App\Attendance;
use App\Student;
use App\SectionModel;
use DB;
//use Excel;
//use Illuminate\Support\Collection;
//use Carbon\Carbon;
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

    public function getclass_section($class_id)
    {
         $classes = ClassModel::find($class_id);
       
       
         $section = DB::table('section')->select('name','description')->where('class_code',$classes->code)->get();



        if(!is_null($classes) && $classes->count()>0){
           return response()->json(['class_section'=>$section]);
        }else{
        return response()->json(['error'=>'Class Sections Not Found'], 401);
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
}


	        