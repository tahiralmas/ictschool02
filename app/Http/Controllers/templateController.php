<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Ictcore_integration;
use App\Message;
use DB;
use App\Http\Controllers\ictcoreController;

class templateController extends BaseController {

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
	         
		return View('app.templateCreate');
	}
	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function create()
	{
		$rules=[
			'title' => 'required',
			//'message' => 'required'
			//'message' => 'required|mimes:audio/wav',
			'message' => 'required|mimes:wav',

		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/template/create')->withErrors($validator);
		}
		else {

            // echo "<pre>";print_r(Input::file('message'));exit;

			$sname = Input::get('title');
			$sexists=message::select('*')->where('name','=',$sname)->get();
			if(count($sexists)>0){

				$errorMessages = new \Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Title all ready exists!!');
				return Redirect::to('/template/create')->withErrors($errorMessages);
			}
			else {
               /* $remove_spaces =  str_replace(" ","_",Input::get('title'));

				$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();

				$class = new Message;
				$class->name = Input::get('title');
				$class->description = Input::get('description');
			    $class->recording =$fileName;

				$class->save();
				Input::file('message')->move(base_path() .'/public/recording',$fileName);

				return Redirect::to('/template/create')->with("success", "Message Created Succesfully.");*/

                   $ictcore_integration = Ictcore_integration::select("*")->first();
                   
		    if($ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){
				$ictcore_api  = new ictcoreController();
				$sname = Input::get('title');
                $remove_spaces =  str_replace(" ","_",Input::get('title'));
				$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();
                Input::file('message')->move(base_path() .'/public/recording',$fileName);
                sleep(3);

                $data = array(
                             'name' => Input::get('title'),
				             'description' => Input::get('description'),
							 );

                 $recording_id  =  $ictcore_api->ictcore_api('messages/recordings','POST',$data );
                 $name          =  base_path() .'/public/recording/'.$fileName;
                 $finfo         =  new \finfo(FILEINFO_MIME_TYPE);
                 $mimetype      =  $finfo->file($name);
                 $cfile         =  curl_file_create($name, $mimetype, basename($name));
                 $data          =  array( $cfile);
				 $result        =  $ictcore_api->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                 $recording_id  =  $result ;
                if(!is_array($recording_id )){

                  $data = array(
                             'name' => Input::get('title'),
				             'recording_id' => $recording_id,
							 );
                 $program_id = $ictcore_api->ictcore_api('programs/voicemessage','POST',$data );
                 if(!is_array( $program_id )){
                  $program_id = $program_id;
                 }else{
                 	return Redirect::to('/template/create')->withErrors("ERROR: Program not Created" );
                 }
                }else{
                     return Redirect::to('/template/create')->withErrors("ERROR: Recording not Created" );               
                }
				$ictcore_message = new Message;
				$ictcore_message->name = Input::get('title');
				$ictcore_message->description = Input::get('description');
			    $ictcore_message->recording =$fileName;
			    $ictcore_message->ictcore_recording_id =$recording_id;
                $ictcore_message->ictcore_program_id  =$program_id;
				$ictcore_message->save();
				return Redirect::to('/template/create')->with("success", "Message Created Succesfully.");
			

          }else{
          	  return Redirect::to('/template/create')->withErrors("Please Add ictcore integration in Setting Menu");
          }
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
		$messages = DB::table('message')
		->select(DB::raw('message.id,message.name,message.description,message.recording'))
		->get();
		//dd($sections);
		//return View::Make('app.classList',compact('Classes'));
		return View('app.messageList',compact('messages'));
	}



	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function edit($id)
	{
		$message = Message::find($id);
		//return View::Make('app.classEdit',compact('class'));
		return View('app.messageEdit',compact('message'));
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
			'title' => 'required',
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/message/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {


        if(Input::hasFile('message'))
		{

			
   
			if(substr(Input::file('message')->getMimeType(), 0, 5) != 'audio')
			{
				$messages = $validator->errors();
				$messages->add('Notvalid!', 'Audio must be a audio wav!');
				return Redirect::to('/message/edit/'.Input::get('id'))->withErrors($messages);
			}
			else {

				$remove_spaces =  str_replace(" ","_",Input::get('title'));
				$fileName= $remove_spaces.'.'.Input::file('message')->getClientOriginalExtension();

				//$student->photo = $fileName;
				Input::file('message')->move(base_path() .'/public/recording',$fileName);
			}

		}
		else {
			   $fileName = Input::get('recording');

		}





			$message = Message::find(Input::get('id'));
			$message->name= Input::get('title');

			$message->description=Input::get('description');
			$message->recording=$fileName;

			$message->save();
			return Redirect::to('/template/list')->with("success","Message Updated Succesfully.");

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

		$message = Message::find($id);
		$message->delete();
		unlink(base_path().'/public/recording/'.$message->recording);
		return Redirect::to('/template/list')->with("success","Message Deleted Succesfully.");
	}

}
