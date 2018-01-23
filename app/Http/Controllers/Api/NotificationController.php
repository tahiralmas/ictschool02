<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\ictcoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Exam;
use File;
use App\Message;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class NotificationController extends Controller
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
	public function getallnotification()
	{
		  $messages = DB::table('message')->select('name','description','recording','ictcore_program_id','ictcore_recording_id')->get();
		/*  ->join('Class', 'Student.class', '=', 'Class.code')
		  ->select('Student.id', 'Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.fatherName', 'Student.motherName', 'Student.fatherCellNo', 'Student.motherCellNo', 'Student.localGuardianCell',
		  'Class.Name as class','Student.section' ,'Student.group' ,'Student.presentAddress', 'Student.gender', 'Student.religion')
		  ->get();*/
		  if(count($messages)<1)
		  {
		     return response()->json(['error'=>'No message Found!'], 404);
		  }
		  else {
			  return response()->json(['messages' => $messages]);
		  }
	}


    public function getnotification($notification_id)
    {
         $message = Message::find($notification_id);
    	

        if(!is_null($message) && count($message)>0){
           return response()->json(['notification'=>$message]);
        }else{
        return response()->json(['error'=>'Notification Not Found'], 404);
       }
    }
     public function postnotification(Request $request)
    {
         
	 $rules=[
            'name'    =>'required',
			'type'    => 'required',
			'message' =>'required'

			];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		 return response()->json($validator->errors(), 422);
		}
		else {
                 
            if(Input::get('type')=='voice' || Input::get('type')=='Voice'){

                   $drctry = storage_path('app/public/messages/');

                if(File::exists($drctry.Input::get('message'))){

                   
                    $mimetype      = mime_content_type($drctry.Input::get('message'));
                     if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
                         	 $ict  = new ictcoreController();
                    		    // $headers = apache_request_headers();
                                 //dd($headers['Authorization']);
                         //	$filename ='notification_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";

                         // file_put_contents(public_path('recording/').$filename.'.wav', $drctry.Input::get('message'));

                             //      unlink(public_path('recording/'.$filename));

            		    
                            sleep(3);
                            $data = array(
                                         'name' => Input::get('name'),
            				             'description' => Input::get('description'),
            							 );

                             $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                             $name          =  $drctry.Input::get('message');

                             $mimetype      =  mime_content_type($name);

                             $cfile         =  curl_file_create($name, $mimetype, basename($name));
                             $data          =  array( $cfile);
            				 $result        =  $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                             $recording_id  =  $result ;
                            if(!is_array($recording_id )){

                              $data = array(
                                         'name' => Input::get('title'),
            				             'recording_id' => $recording_id,
            							 );
                             $program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );
                             if(!is_array( $program_id )){
                              $program_id = $program_id;
                             }else{
                             	return response()->json("ERROR: Program not Created" );
                             
                             }
                            }else{
                            	return response()->json("ERROR: Recording not Created" );
                                              
                            }

            			$notificationData= [
            							'name' => Input::get('name'),
            							'description' =>Input::get('message'),
            							'recording' =>  basename($name),
            							'ictcore_program_id' => $program_id,
            							'ictcore_recording_id' => $recording_id,
            						];

            					  $notification_id = Message::insertGetId($notificationData);


            		      return response()->json(['success'=>"Nofication save Succesfully.",'id' => $notification_id]);
            	    }else{
                        return response()->json("ERROR:Please Upload Correct file". $drctry.Input::get('message'),415 );
            	    }
                }
            }

		}
      
    }

    public function putnotification($notification_id)
    {
        // return response()->json(Input::all());


         $rules=[
		'name'        => 'required',
        'message'     =>'required',
		'recording'   =>'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
		 return response()->json($validator->errors(), 422);
		}
		else {
                 $message = Message::find($notification_id);
                  unlink(base_path().'/public/recording/'.$message->recording);

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimetype      = $finfo->buffer(base64_decode(Input::get('recording')));
             if($mimetype =='audio/x-wav' || $mimetype=='audio/wav'){ 
             	 $ict  = new ictcoreController();
		    
             	 $filename ='notification_'.time();//'recordingn5QzxE.wav';//tempnam(public_path('recording/'), 'recording'). ".wav";

              file_put_contents(public_path('recording/').$filename.'.wav', base64_decode(Input::get('recording')));

                sleep(3);
                $data = array(
                             'name' => Input::get('name'),
				             'description' => Input::get('description'),
							 );

                 $recording_id  =  $ict->ictcore_api('messages/recordings','POST',$data );
                 $name          =  base_path() .'/public/recording/'.$filename.".wav";


                 $finfo         =  new \finfo(FILEINFO_MIME_TYPE);
                 $mimetype      =  $finfo->file($name);

                 $cfile         =  curl_file_create($name, $mimetype, basename($name));
                 $data          =  array( $cfile);
				 $result        =  $ict->ictcore_api('messages/recordings/'.$recording_id.'/media','PUT',$data );
                 $recording_id  =  $result ;
                if(!is_array($recording_id )){

                  $data = array(
                             'name' => Input::get('title'),
                             'message'=>
				             'recording_id' => $recording_id,
							 );
                 $program_id = $ict->ictcore_api('programs/voicemessage','POST',$data );
                 if(!is_array( $program_id )){
                  $program_id = $program_id;
                 }else{
                 	return response()->json("ERROR: Program not Created" );
                 
                 }
                }else{
                	return response()->json("ERROR: Recording not Created" );
                                  
                }


		   
			$message->name= Input::get('name');
			$message->description=Input::get('description');
			$message->recording=$filename.'.wav';
			$message->ictcore_program_id = $program_id;
			$message->ictcore_recording_id = $recording_id;

			





        if(!is_null($message) && count($message)>0){
        	$message->save();
           return response()->json(['nofication'=>$message]);
        }else{
        return response()->json(['error'=>'Notification Not Found'], 404);
       }
    }else{
    	return response()->json("ERROR:Please Upload Correct file",415 );

    }
}
}
     public function deletenotification($notification_id)
    {
          $notification = Message::find($notification_id);
		    if(!is_null($notification) && $notification->count()>0){

               DB::table('message')->where('id','=',$notification_id)->delete();

               unlink(base_path().'/public/recording/'.$message->recording);

                  return response()->json(['success'=>"notification deleted Succesfully."],200);
		    }else{
		        return response()->json(['error'=>'notification Not Found'], 404);

		    }
    }

   
}


	        