<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Student;
use App\User;
use App\SectionModel;
use App\ClassModel;
use Hash;
use DB;
use App\Ictcore_integration;
use App\Http\Controllers\ictcoreController;
class foobar{

}
Class formfoo{

}
class studentController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('delete')));*/
	       $this->middleware('auth');
               $this->middleware('auth',array('only'=> array('delete')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$classes = ClassModel::select('name','code')->get();
		
		$section= SectionModel::select('id','name')->where('class_code','=','cl1')->get();
		//$sections = SectionModel::select('name')->get();


		//return View::Make('app.studentCreate',compact('classes'));
		return View('app.studentCreate',compact('classes','section'));
	}

	public  function getRegi($class,$session,$section)
	{
		$ses =trim($session);
		$stdcount = Student::select(DB::raw('count(*) as total'))->where('class','=',$class)->where('session','=',$ses)->first();

		$stdseccount = Student::select(DB::raw('count(*) as total'))->where('class','=',$class)->where('session','=',$ses)->where('section','=',$section)->first();
		$r = intval($stdcount->total)+1;
		if(strlen($r)<2)
		{
			$r='0'.$r;
		}
		$c = intval($stdseccount->total)+1;
		$cl=substr($class,2);

		$foo = array();
		if(strlen($cl)<2) {
			$foo[0]= substr($ses, 2) .'0'.$cl.$r;
		}
		else
		{
			$foo[0]=  substr($ses, 2) .$cl.$r;
		}
		if(strlen($c)<2) {
			$foo[1] ='0'.$c;
		}
		else
		{
			$foo[1] =$c;
		}

		return $foo;

	}

	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create(Request $request)
	{

		$rules=['regiNo' => 'required',
		'fname' => 'required',
		'lname' => 'required',
		'gender' => 'required',
		//'religion' => 'required',
		//'bloodgroup' => 'required',
		//'nationality' => 'required',
		'dob' => 'required',
		'session' => 'required',
		'class' => 'required',
		'section' => 'required',
		'rollNo' => 'required',
		'shift' => 'required',
		'photo' => 'mimes:jpeg,jpg,png',
		'fatherName' => 'required',
		'fatherCellNo' => 'required',
		//'motherName' => 'required',
		//'motherCellNo' => 'required',
		'presentAddress' => 'required',
		//'parmanentAddress' => 'required'
	];
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('/student/create')->withErrors($validator);
	}
	else {

		if(Input::file('photo')!=''){

		$fileName=Input::get('regiNo').'.'.Input::file('photo')->getClientOriginalExtension();
		
		}else{
			$fileName='';
		}
        
		$student = new Student;
		$student->regiNo = Input::get('regiNo');
		$student->firstName = Input::get('fname');

		$student->middleName = Input::get('mname');
		if(Input::get('mname') ==''){
			$student->middleName = "";
		}
		$student->lastName = Input::get('lname');
		$student->gender= Input::get('gender');
		
		$student->religion= Input::get('religion');

		if(Input::get('religion') ==''){
			$student->religion = "";
		}
		$student->bloodgroup= Input::get('bloodgroup');

		if(Input::get('bloodgroup')==''){
		$student->bloodgroup="";

		}
		$student->dob= Input::get('dob');
		$student->session= trim(Input::get('session'));
		$student->class= Input::get('class');
		$student->section= Input::get('section');
		$student->group= Input::get('group');
		$student->rollNo= Input::get('rollNo');
		$student->shift= Input::get('shift');

		$student->photo= $fileName;
		$student->nationality= Input::get('nationality');
		if(Input::get('nationality') ==''){
			$student->nationality="";
		}
		$student->extraActivity= Input::get('extraActivity');
		if(Input::get('extraActivity') ==''){
			$student->extraActivity = "";
		}
		$student->remarks= Input::get('remarks');
       if(Input::get('remarks') ==''){
			$student->remarks = "";
		}
		$student->fatherName= Input::get('fatherName');
		$student->fatherCellNo= Input::get('fatherCellNo');
		
		$student->motherName= Input::get('motherName' );
		if(Input::get('motherName')==''){
			$student->motherName= "";
			
		}
		$student->motherCellNo= Input::get('motherCellNo');
		if(Input::get('motherCellNo')==''){
			$student->motherCellNo="";
		}
		$student->localGuardian= Input::get('localGuardian');
		if(Input::get('localGuardian')==''){
			$student->localGuardian="";
		}
		$student->localGuardianCell= Input::get('localGuardianCell');
		if(Input::get('localGuardianCell') ==''){
			$student->localGuardianCell="";
		}

		$student->presentAddress= Input::get('presentAddress');
		$student->parmanentAddress= Input::get('parmanentAddress');
		if(Input::get('parmanentAddress')==''){
			$student->parmanentAddress='';
		}
		$student->isActive= "Yes";

		$hasStudent = Student::where('regiNo','=',Input::get('regiNo'))->where('class','=',Input::get('class'))->first();
		if ($hasStudent)
		{
			$messages = $validator->errors();
			$messages->add('Duplicate!', 'Student already exits with this registration no.');
			return Redirect::to('/student/create')->withErrors($messages)->withInput();
		}
		else {
			$student->save();
			if( Input::file('photo')!=''){
             Input::file('photo')->move(base_path() .'/public/images',$fileName);
         	}
                 $user = new User;

                $user->firstname = Input::get('fname');
                $user->lastname  = Input::get('lname');
                $user->email =     Input::get('regiNo').'@gmail.com';
              	$user->login     = Input::get('regiNo');
              	$user->group     =  'Student';
                $user->password  =	Hash::make(Input::get('regiNo'));
                $user->save();

                 $ictcore_integration = Ictcore_integration::select("*")->first();
                 
			if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url !='' && $ictcore_integration->ictcore_user !='' && $ictcore_integration->ictcore_password !=''){ 

							 $ict  = new ictcoreController();
							 	$data = array(
								'first_name' => $student->firstName,
								'last_name' => $student->lastName,
								'phone'     => $student->fatherCellNo,
								'email'     => '',
								);
								$contact_id = $ict->ictcore_api('contacts','POST',$data );

                               $message = 'School name'.'<br>'.'Login Name: '. Input::get('regiNo').'Password: '.Input::get('regiNo');
                                $data = array(
								'name' => 'School Name',
								'data' => $message,
								'type'     => 'plain',
								'description'     => 'testing message',
								);

	                          $text_id = $ict->ictcore_api('messages/texts','POST',$data );

	                          $data = array(
								'name' => 'School Name',
								'text_id' => $text_id
								);

                                $program_id = $ict->ictcore_api('programs/sendsms','POST',$data );

								$data = array(
								'title' => 'User Detail',
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
								//$transmission_send = $ict->ictcore_api('transmissions/'.$transmission_id.'/send','POST',$data=array() );

             
            }





			return Redirect::to('/student/create')->with("success","Student Admited Succesfully.");
		}


	}
}


/**
* Display the specified resource.
*
* @param  int  $id
* @return Response
*/
public function show()
{
	$students=array();
	$classes = ClassModel::pluck('name','code');
	$formdata = new formfoo;
	$formdata->class="";
	$formdata->section="";
	$formdata->shift="";
	$formdata->session="";
	//return View::Make("app.studentList",compact('students','classes','formdata'));
	return View("app.studentList",compact('students','classes','formdata'));
}
public function getList()
{
	$rules = [
		'class' => 'required',
		'section' => 'required',
		'shift' => 'required',
		'session' => 'required'


	];
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails()) {
		return Redirect::to('/student/list')->withInput(Input::all())->withErrors($validator);
	} else {
		$students = DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		'Class.Name as class', 'Student.presentAddress', 'Student.gender', 'Student.religion')
		->where('isActive', '=', 'Yes')
		->where('class',Input::get('class'))
		->where('section',Input::get('section'))
		->where('shift',Input::get('shift'))
		->where('session',trim(Input::get('session')))
		->get();
		if(count($students)<1)
		{
			return Redirect::to('/student/list')->withInput(Input::all())->with('error','No Students Found!');

		}
		else {
			$classes = ClassModel::pluck('name','code');
			$formdata = new formfoo;
			$formdata->class=Input::get('class');
			$formdata->section=Input::get('section');
			$formdata->shift=Input::get('shift');
			$formdata->session=trim(Input::get('session'));
			//return View::Make("app.studentList", compact('students','classes','formdata'));
			return View("app.studentList", compact('students','classes','formdata'));
		}
	}

}

public function view($id)
{
	$student=	DB::table('Student')
	->join('Class', 'Student.class', '=', 'Class.code')
	->select('Student.id', 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName',
	'Student.fatherName','Student.motherName', 'Student.fatherCellNo','Student.motherCellNo','Student.localGuardianCell',
	'Class.Name as class','Student.presentAddress','Student.gender','Student.religion','Student.section','Student.shift','Student.session',
	'Student.group','Student.dob','Student.bloodgroup','Student.nationality','Student.photo','Student.extraActivity','Student.remarks',
	'Student.localGuardian','Student.parmanentAddress')
	->where('Student.id','=',$id)->first();

	//return View::Make("app.studentView",compact('student'));
	return View("app.studentView",compact('student'));
}
/**
* Show the form for editing the specified resource.
*
* @param  int  $id
* @return Response
*/
public function edit($id)
{
	$classes = ClassModel::pluck('name','code');
	$student= Student::find($id);
	
	$sections = SectionModel::select('id','name')->where('class_code','=',$student->class)->get();
	//$sections = $sections->toArray();
      // $sections = SectionModel::pluck('id', 'name')->where('class_code','=',$student->class);
	//echo "<pre>";print_r($sections);
	//dd($student);
	//$sections = SectionModel::select('name')->get();
	//return View::Make("app.studentEdit",compact('student','classes'));
	return View("app.studentEdit",compact('student','classes','sections'));
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
		'fname' => 'required',
		'lname' => 'required',
		'gender' => 'required',
		//'religion' => 'required',
		//'bloodgroup' => 'required',
		//'nationality' => 'required',
		'dob' => 'required',
		'session' => 'required',
		'class' => 'required',
		'section' => 'required',
		'rollNo' => 'required',
		'shift' => 'required',
		'fatherName' => 'required',
		'fatherCellNo' => 'required',
		//'motherName' => 'required',
		//'motherCellNo' => 'required',
		'presentAddress' => 'required',
		//'parmanentAddress' => 'required'
	];
	$validator = \Validator::make(Input::all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('/student/edit/'.Input::get('id'))->withErrors($validator);
	}
	else {

		$student = Student::find(Input::get('id'));

		if(Input::hasFile('photo'))
		{

			if(substr(Input::file('photo')->getMimeType(), 0, 5) != 'image')
			{
				$messages = $validator->errors();
				$messages->add('Notvalid!', 'Photo must be a image,jpeg,jpg,png!');
				return Redirect::to('/student/edit/'.Input::get('id'))->withErrors($messages);
			}
			else {

				$fileName=Input::get('regiNo').'.'.Input::file('photo')->getClientOriginalExtension();
				$student->photo = $fileName;
				Input::file('photo')->move(base_path() .'/public/images',$fileName);
			}

		}
		else {
			$student->photo= Input::get('oldphoto');

		}
		//$student->regiNo=Input::get('regiNo');
		//$student->rollNo=Input::get('rollNo');
		/*$student->firstName= Input::get('fname');
		$student->middleName= Input::get('mname');
		$student->lastName= Input::get('lname');
		$student->gender= Input::get('gender');
		$student->religion= Input::get('religion');
		$student->bloodgroup= Input::get('bloodgroup');
		$student->nationality= Input::get('nationality');
		$student->dob= Input::get('dob');
		$student->session= trim(Input::get('session'));
		$student->class= Input::get('class');
		$student->section= Input::get('section');
		$student->group= Input::get('group');
		$student->nationality= Input::get('nationality');
		$student->extraActivity= Input::get('extraActivity');
		$student->remarks= Input::get('remarks');

		$student->fatherName= Input::get('fatherName');
		$student->fatherCellNo= Input::get('fatherCellNo');
		$student->motherName= Input::get('motherName');
		$student->motherCellNo= Input::get('motherCellNo');
		$student->localGuardian= Input::get('localGuardian');
		$student->localGuardianCell= Input::get('localGuardianCell');
		$student->shift= Input::get('shift');

		$student->presentAddress= Input::get('presentAddress');
		$student->parmanentAddress= Input::get('parmanentAddress');*/

		$student->firstName = Input::get('fname');

		$student->middleName = Input::get('mname');
		if(Input::get('mname') ==''){
			$student->middleName = "";
		}
		$student->lastName = Input::get('lname');
		$student->gender= Input::get('gender');
		
		$student->religion= Input::get('religion');

		if(Input::get('religion') ==''){
			$student->religion = "";
		}
		$student->bloodgroup= Input::get('bloodgroup');

		if(Input::get('bloodgroup')==''){
		$student->bloodgroup="";

		}
		$student->dob= Input::get('dob');
		$student->session= trim(Input::get('session'));
		$student->class= Input::get('class');
		$student->section= Input::get('section');
		$student->group= Input::get('group');
		//$student->rollNo= Input::get('rollNo');
		$student->shift= Input::get('shift');

		//$student->photo= $fileName;
		$student->nationality= Input::get('nationality');
		if(Input::get('nationality') ==''){
			$student->nationality="";
		}
		$student->extraActivity= Input::get('extraActivity');
		if(Input::get('extraActivity') ==''){
			$student->extraActivity = "";
		}
		$student->remarks= Input::get('remarks');
       if(Input::get('remarks') ==''){
			$student->remarks = "";
		}
		$student->fatherName= Input::get('fatherName');
		$student->fatherCellNo= Input::get('fatherCellNo');
		
		$student->motherName= Input::get('motherName' );
		if(Input::get('motherName')==''){
			$student->motherName= "";
			
		}
		$student->motherCellNo= Input::get('motherCellNo');
		if(Input::get('motherCellNo')==''){
			$student->motherCellNo="";
		}
		$student->localGuardian= Input::get('localGuardian');
		if(Input::get('localGuardian')==''){
			$student->localGuardian="";
		}
		$student->localGuardianCell= Input::get('localGuardianCell');
		if(Input::get('localGuardianCell') ==''){
			$student->localGuardianCell="";
		}

		$student->presentAddress= Input::get('presentAddress');
		$student->parmanentAddress= Input::get('parmanentAddress');
		if(Input::get('parmanentAddress')==''){
			$student->parmanentAddress='';
		}

		$student->save();

		return Redirect::to('/student/list')->with("success","Student Updated Succesfully.");
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
	$student = Student::find($id);
	$student->isActive= "No";
	$student->save();

	return Redirect::to('/student/list')->with("success","Student Deleted Succesfully.");
}

/**
* Display the specified resource.
*
* @param  int  $id
* @return Response
*/
public function getForMarks($class,$section,$shift,$session)
{
	$students= Student::select('regiNo','rollNo','firstName','middleName','lastName')->where('isActive','=','Yes')->where('class','=',$class)->where('section','=',$section)->where('shift','=',$shift)->where('session','=',$session)->get();
	return $students;
}

public function index_file(){
	return View('app.studentCreateFile');


}
public function create_file(){

$file = Input::file('fileUpload');
			$ext = strtolower($file->getClientOriginalExtension());
			$validator = \Validator::make(array('ext' => $ext),array('ext' => 'in:xls,xlsx,csv'));

			if ($validator->fails()) {
				return Redirect::to('student/create-file')->withErrors($validator);
			}else {
				    try{
						$toInsert = 0;
			            $data = \Excel::load(Input::file('fileUpload'), function ($reader) { })->get();

			             

			                if(!empty($data) && $data->count()){
								DB::beginTransaction();
								try {
			                        foreach ($data->toArray() as $raw) {
                                     // echo "<pre>";print_r($raw);
									$studentData= [
											'class' => $raw['class'],
											'section' => $raw['section'],
											'session' =>    $raw['session'],
											 'regiNo' => $raw['registration'],
											  'rollNo' => $raw['nocardroll_no'],
                                               'shift' => 'Morning',
                                               'isActive'=>'Yes',
											  'group' => $raw['group'],
											'firstName' => $raw['first_name'],
											'lastName' =>    $raw['last_name'],
											 'Gender' => $raw['gender'],
											  'fatherName' => $raw['father_name'],
											  'fatherCellNo' => $raw['fathers_mobile_no']

										];
										$hasStudent = Student::where('rollNo','=',$raw['nocardroll_no'])->first();
											if ($hasStudent)
											{
												$errorMessages = new \Illuminate\Support\MessageBag;
									             $errorMessages->add('Error', 'Doublication rollNo ');
									            return Redirect::to('/student/create-file')->withErrors($errorMessages);
											}else{
												Student::insert($studentData);
												$toInsert++;
											}
			                        }
			                         
										 DB::commit();
								} catch (Exception $e) {
									DB::rollback();
									$errorMessages = new \Illuminate\Support\MessageBag;
									 $errorMessages->add('Error', 'Something went wrong!');
									return Redirect::to('/student/create-file')->withErrors($errorMessages);

									// something went wrong
								}
			            }
					   if($toInsert){
			                return Redirect::to('/student/create-file')->with("success", $toInsert.' Student data upload successfully.');
			            }
						$errorMessages = new \Illuminate\Support\MessageBag;
						 $errorMessages->add('Validation', 'File is empty!!!');
						return Redirect::to('/student/create-file')->withErrors($errorMessages);
	                }catch (Exception $e) {
						  $errorMessages = new \Illuminate\Support\MessageBag;
						  $errorMessages->add('Error', 'Something went wrong!');
						   return Redirect::to('/student/create-file')->withErrors($errorMessages);
	                }
		}

	
}

public function csvexample(){

 return response()->download(storage_path('app/public/' . 'student.csv'));

}

}