<?php  
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Subject;
use DB;
class classController extends BaseController {
	public function __construct() 
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
		
	       $this->middleware('auth');
	       
              // $this->middleware('userAccess',array('only'=> array('delete')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$levels = DB::table('level')
		->select(DB::raw('level.id,level.name,level.description'))
		->get();
		return View('app.classCreate',compact('levels'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{
		$rules=[
			'name' => 'required',
			'code' => 'required|max:20',
			'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/class/create')->withErrors($validator);
		}
		else {
			$clcode = 'cl'.Input::get('code');
			$cexists=ClassModel::select('*')->where('code','=',$clcode)->get();
			if(count($cexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Class all ready exists!!');
				return Redirect::to('/class/create')->withErrors($errorMessages);
			}
			else {
				$class = new ClassModel;
				$class->name = Input::get('name');
				$class->code = $clcode;
				$class->description = Input::get('description');
				$class->save();
				return Redirect::to('/class/create')->with("success", "Class Created Succesfully.");
			}
		}
	}


	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function show()
	{
		//$Classes = ClassModel::orderby('code','asc')->get();
		$Classes = DB::table('Class')
		->select(DB::raw('Class.id,Class.code,Class.name,Class.description,(select count(Student.id) from Student where class=Class.code and Student.session=get_current_session()->id)as students'))
		->get();
		echo "<pre>";print_r($Classes->toArray());exit;
		//return View::Make('app.classList',compact('Classes'));
		return View('app.classList',compact('Classes'));
	}



	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$class = ClassModel::find($id);
		//return View::Make('app.classEdit',compact('class'));
		return View('app.classEdit',compact('class'));
	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function update()
	{
		$rules=[
			'name' => 'required',
			'description' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/class/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$class = ClassModel::find(Input::get('id'));
			$class->name= Input::get('name');

			$class->description=Input::get('description');
			$class->save();
			return Redirect::to('/class/list')->with("success","Class Updated Succesfully.");
		}
	}


	/**
	* Remove the specified resource from storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function delete($id)
	{
		$class = ClassModel::find($id);
		$class->delete();
		return Redirect::to('/class/list')->with("success","Class Deleted Succesfully.");
	}

	public function getSubjects($class)
	{
		$subjects = Subject::select('id','name','code')->where('class',$class)->orderby('code','asc')->get();
		return $subjects;
	}
}
