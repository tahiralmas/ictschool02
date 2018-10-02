<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Level;
use App\Message;
use App\Student;
use App\Teacher;
use App\Ictcore_integration;
use DB;
use App\Http\Controllers\ictcoreController;

class messageController extends BaseController {

	public function __construct() 
	{
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
			'mess_name' => 'required',
			'class'   => 'required',
			'section' => 'required'
			];
			$validator = \Validator::make(Input::all(), $rules);
			if ($validator->fails())
			{
			  return Redirect::to('/message')->withErrors($validator);
			}else {

                   $file_id='';

                  /*   $section = Input::get('section');
							$class = Input::get('class');
							$student=	DB::table('Student')
							->select('*')
							->where('isActive','Yes')
							->whereIn('section1', $section)
							->where('class', $class)
							->get();

							echo "<pre>";print_r($student->toArray());exit; */

					$type = Input::get('type');
					
					$ictcore_integration = Ictcore_integration::select("*")->where('type',$type)->first();
					//echo "<pre>";print_r($ictcore_integration);

					//exit;
					if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){

	                  $ict  = new ictcoreController();
	                  $role = Input::get('role');
	                  $mess_name = Input::get('mess_name');
					  $remove_spaces_m =  str_replace(" ","_",$mess_name );
                       
						if($type=='voice'){
							$message = Message::find(Input::get('message'));
							$program_id =  $message->ictcore_program_id;
							$file_id =  $message->telenor_file_id;
						}elseif($type=='sms' || $type=='Sms' || $type=='SMS'){
							$data = array(
								'name' => Input::get('mess_name'),
								'data' => Input::get('message'),
								'type' => 'plain',
								'description' =>'',
							);
							if($ictcore_integration->method == 'telenor'){

							}else{
							$text_id  =  $ict->ictcore_api('messages/texts','POST',$data );
							$data     = array(
								'name' => Input::get('mess_name'),
								'text_id' =>$text_id,
							);
							$program_id  =  $ict->ictcore_api('programs/sendsms','POST',$data );
						}
						}
						if($ictcore_integration->method == 'telenor'){

                            $group_id = $ict->telenor_apis('group','','','','','');

						}else{
						$data = array(
								'name' => $remove_spaces_m,
								'description' => $mess_name,
								);
		                $group_id= $ict->ictcore_api('groups','POST',$data );
                         }
						if($role =='student' || $role =='parent'){

							$section = Input::get('section');
							$class = Input::get('class');
							$student=	DB::table('Student')
							->select('*')
							->where('isActive','Yes')
							->whereIn('section', $section)
							->where('class', $class)
							->get();
							


							foreach($student as $std){
								$data = array(
								'first_name' => $std->firstName,
								'last_name' => $std->lastName,
								'phone'     => $std->fatherCellNo,
								'email'     => '',
								);
                                 
                                if($ictcore_integration->method == 'telenor'){
                                  
                                $group_contact_id = $ict->telenor_apis('add_contact',$group_id,$std->fatherCellNo,'','','');

                                }else{

									$contact_id = $ict->ictcore_api('contacts','POST',$data );
									$group      = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );
							    }
							}
						}else{
							$teacher=	DB::table('teacher')
							->select('*')
							->get();
							foreach($teacher as $techrd){
								$data = array(
								'first_name' => $techrd->firstName,
								'last_name'  => $techrd->lastName,
								'phone'      => $techrd->phone,
								'email'      => $techrd->email
								);
                                 if($ictcore_integration->method == 'telenor'){
                                  
                                $group_contact_id = $ict->telenor_apis('add_contact',$group_id,$std->fatherCellNo,'','','');

                                }else{
								$contact_id = $ict->ictcore_api('contacts','POST',$data );
								$group      = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );

							    }
							}
						}
						if($ictcore_integration->method == 'telenor'){
                                  
                        echo  $campaign    = $ict->telenor_apis('campaign_create',$group_id,'',Input::get('message'),$file_id,$type);
                          // echo $campaign;
                         // $this->info('Notification sended successfully'.$campaign);
                        echo "<pre>";print_r($campaign);
                            // exit;
                            $send_campaign = $ict->telenor_apis('send_msg','','','','',$campaign);
                             echo "<pre>";print_r($send_campaign);
                             exit;
                        }else{
						$data = array(
						'program_id' => $program_id,
						'group_id' => $group_id,
						'delay' => '',
						'try_allowed' => '',
						'account_id' => 1,
						'status' => '',
						);
						$campaign_id = $ict->ictcore_api('campaigns','POST',$data );

						}
						exit;
						return Redirect::to('/message')->with("success", "campaign Created Succesfully.");
					}else{
						return Redirect::to('/message')->withErrors("Please Add ictcore integration in Setting Menu");
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
