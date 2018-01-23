<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\ictcoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
//use App\Api_models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
//use Storage;
use File;
use App\Exam;
use App\Message;
use DB;
use Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class MessageController extends Controller
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
      public function getallmessages()
      {
         $drctry = storage_path('app/public/messages/');

          $files = File::allfiles($drctry);
          $i=0;
          $basename = array();
          foreach ($files as $file)
          {
              $filename[] = pathinfo($file);
              if(!empty($filename) && count($filename)>0){
              $basename[]=$filename[$i]['basename'];
            }else{
             return response()->json(['error'=>'Message File Not Found'], 404);
            }
              $i++;
          }
            return response()->json($basename,200);
      }
      public function getmessage($filename)
      {
           $drctry = storage_path('app/public/messages/');//asset(storage_path('app/public/messages/'));//


            return response()->download(storage_path('app/public/messages/' . $filename));

           //$contents = Storage::get($drctry.$filename);
           $response = response($drctry.$filename, 200);
            $response->header('Content-Type', ' audio/x-wav');
            $response->header('Content-Disposition', 'attachment; filename='.$filename);
            return $response;
            $headers = array(
             // 'Content-Type'=> 'audio/x-wav',
              //'Content-Disposition'=> 'attachment; filename=' . $filename,
              'Content-type', 'audio/x-wav',
              'Content-Disposition', 'attachment; filename='.$filename
              //'Location:'.$drctry.$filename
            );
             return response()->download($drctry.$filename,$headers);


        //->header('Content-type', 'audio/x-wav')
       // ->header('Content-Disposition', 'attachment; filename='.$filename);
              //$headers = array('Content-Type: application/pdf',);
            //return response()->download($drctry.$filename,$headers);


         //  return response()->download($drctry.$filename);

         // return response()->json($contents,200);
      }
    public function postmessage(Request $request)
    {
          $fil = $request->all();
          $filedata =  file_get_contents('php://input');
          $finfo = new \finfo(FILEINFO_MIME_TYPE);
          $mimetype      = $finfo->buffer($filedata);
          $filename ='notification_'.time();
        Storage::disk('public')->put('messages/'.$filename.'.wav', $filedata);
        return $filename.'.wav';
    }

    public function putmessage($notification_id)
    {
    // return response()->json(Input::all());
        $rules=[
        'name'        => 'required',
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
    public function deletemessage($filename)
    {
      $drctry = storage_path('app/public/messages/');
      if (File::exists($drctry.$filename))
      {
            //Storage::delete($drctry.$filename);
            unlink($drctry.$filename);
         return response()->json('File Deleted',200);
       }else{
         return response()->json("ERROR:File Already Deleted" );
       }

    }


}


