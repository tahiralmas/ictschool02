<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Level;
use App\Message;
use App\Student;
use App\Teacher;
use DB;
class messageController extends BaseController {

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
		 $classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		 $messages = DB::table('message')
				    ->select(DB::raw('message.id,message.name,message.description,message.recording'))
				    ->get();
		return View('app.messageCreate',compact('classes','messages'));
		//echo "this is section controller";
	}


/**
* Show the form for creating a new resource.
*
* @return Response
*/
public function create()
{
		$rules=[
		'role' => 'required',
		'message' => 'required',
		'mess_name' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		  return Redirect::to('/message')->withErrors($validator);
		}else {

			$ict  = new ictcoreController();
			$role = Input::get('role');
			$mess_name = Input::get('mess_name');

			$remove_spaces_m =  str_replace(" ","_",$mess_name );

			$message = Message::find(Input::get('message'));

			$data = array(
				'name' => $remove_spaces_m,
				'description' => $mess_name,
				);
			$group_id= $ict->ictcore_api('groups','POST',$data );

			if($role =='student' || $role =='parent'){

				$section = Input::get('section');
				$class = Input::get('class');
				$student=	DB::table('Student')
				->select('*')
				->where('isActive','Yes')
				->whereIn('section', $section)
				->whereIn('class', $class)
				->get();
				foreach($student as $std){

					$data = array(
					'first_name' => $std->firstName,
					'last_name' => $std->lastName,
					'phone'     => $std->fatherCellNo,
					'email'     => '',
					);


					$contact_id = $ict->ictcore_api('contacts','POST',$data );

					$group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );

				}
			}else{
              $teacher=	DB::table('teacher')
				->select('*')
				->get();
				foreach($teacher as $techrd){
                 $data = array(
					'first_name' => $techrd->firstName,
					'last_name' => $techrd->lastName,
					'phone'     => $techrd->phone,
					'email'     => $techrd->email
					);


					$contact_id = $ict->ictcore_api('contacts','POST',$data );

					$group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );

				}

			}
				$data = array(
				'name' => $message->name,
				'description' => $message->description,
				);

				$recording_id= $ict->ictcore_api('messages/recordings','POST',$data );
				$name = base_path() .'/public/recording/'.$message->recording;
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$mimetype = $finfo->file($name);
				$cfile = curl_file_create($name, $mimetype, basename($name));
				$data = array( $cfile);
				$result = $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
				$recording_id = $result ;
				$data = array(
				'name' => $remove_spaces_m,
				'recording_id' => $recording_id,
				);
				$program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );


				$data = array(
					'program_id' => $program_id,
					'group_id' => $group_id,
					'delay' => '',
					'try_allowed' => '',
					'account_id' => 1,
					'status' => '',
				);
				$campaign_id = $ict->ictcore_api('campaigns','POST',$data );

				print_r($campaign_id);

				//exit;

			

		//$class->save();
		return Redirect::to('/message')->with("success", "Voice campaign Created Succesfully.");
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
		$levels = DB::table('level')
		->select(DB::raw('level.id,level.name,level.description'))
		->get();
		//dd($sections);
		//return View::Make('app.classList',compact('Classes'));
		return View('app.levelList',compact('levels'));
	}



	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$level = Level::find($id);
		//return View::Make('app.classEdit',compact('class'));
		return View('app.levelEdit',compact('level'));
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
			return Redirect::to('/level/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {
			$section = Level::find(Input::get('id'));
			$section->name= Input::get('name');

			$section->description=Input::get('description');
			$section->save();
			return Redirect::to('/level/list')->with("success","Level Updated Succesfully.");

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
		$class = Level::find($id);
		$class->delete();
		return Redirect::to('/level/list')->with("success","Level Deleted Succesfully.");
	}

}
