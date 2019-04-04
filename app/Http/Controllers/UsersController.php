<?php
namespace App\Http\Controllers;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Institute;
use App\User;
use App\VerifyCode;
use Hash;
class UsersController extends BaseController {
  public function __construct() {
    /*$this->beforeFilter('csrf', array('on'=>'post'));
    $this->beforeFilter('auth', array('only'=>array('show','create','edit','update')));
    $this->beforeFilter('userAccess',array('only'=> array('show','create','edit','update','delete')));*/
    //5.5
    //$this->middleware('csrf', array('on'=>'post'));
    $this->middleware('auth', array('only'=>array('show','create','edit','update')));
  }
  /**
  * Display a listing of the resource.
  *
  * @return Response
  */
  public function postSignin() {
    if (\Auth::attempt(array('login'=>Input::get('login'), 'password'=>Input::get('password')))) {
          
          
          $login   = Auth::user()->group;
          /*if($login == "Admin"){
              $user_id = Auth::user()->id;
              $phone = Auth::user()->phone;
            \Auth::logout();
            $this->sendcode($user_id,$phone);
            return Redirect::to('/verify_code');

          }*/


      $name=Auth::user()->firstname.' '.Auth::user()->lastname;
      $login=Auth::user()->group;
      \Session::put('name', $name);
      \Session::put('userRole', $login);
      $institute=Institute::select('name')->first();
      if(!$institute)
      {
        if (Auth::user()->group != "Admin")
        {
          return Redirect::to('/')
          ->withInput(Input::all())->with('error', 'Institute Information not setup yet!Please contact administrator.');
        }
        else {
          $institute=new Institute;
          $institute->name="IctVission";
          \Session::put('inName', $institute->name);
          return Redirect::to('/institute')->with('error','Please provide institute information!');

        }
      }
      else {
        \Session::put('inName', $institute->name);
        return Redirect::to('/dashboard')->with('success','You are now logged in.');
      }

    } else {
      return Redirect::to('/')
      ->withInput(Input::all())->with('error', 'Your username/password combination was incorrect');

    }

  }

  public function verify_code()
  {
    $error = \Session::get('error');
    $institute=Institute::select('name')->first();
    if(!$institute)
    {
      $institute=new Institute;
      $institute->name="IctVission";
    }
    return View('app.users.verify',compact('error','institute'));

  }

  public function sendcode($user_id,$phone)
  {

     $verified_code = hexdec(substr(uniqid(rand(), true), 5, 5));
                $verification_code = new VerifyCode;
                $verification_code->user_id=$user_id;
                $verification_code->code=$verified_code;
                $verification_code->save();
                
               /* $ict         = new ictcoreController();
                    $contact = array(
                      'firstname' => 'admin',
                      'lastname' =>'',
                      'phone'     =>$phone,
                      'email'     => '',
                      );
                $msg = "verification code is ". $verified_code;
                 $ict_stting = DB::table('ict_settings')->first();
                 if($ict_stting->type=='ictcore'){
                $ict->verification_number($contact,$msg);*/
                  $msg = "verification code is ". $verified_code;
                  $send_msg_ictcore = sendmesssageictcore('admin','',$phone,$msg,'verified code');
  }

  public function verified()
  {
    $verification_code = VerifyCode::first();

    if(!empty($verification_code) && $verification_code->code == Input::get('code')){

      $user_id = $verification_code->user_id;
        VerifyCode::truncate();
      if(Auth::loginUsingId($user_id)){

            $name = Auth::user()->firstname.' '.Auth::user()->lastname;
            $login = Auth::user()->group;
            \Session::put('name', $name);
            \Session::put('userRole', $login);
            $institute=Institute::select('name')->first();
          if(!$institute)
          {
            if (Auth::user()->group != "Admin")
            {
              return Redirect::to('/verify_code')
              ->withInput(Input::all())->with('error', 'Institute Information not setup yet!Please contact administrator.');
            }
            else {
              $institute=new Institute;
              $institute->name="IctVission";
              \Session::put('inName', $institute->name);
              return Redirect::to('/institute')->with('error','Please provide institute information!');

            }
          }
          else {
            \Session::put('inName', $institute->name);
            return Redirect::to('/dashboard')->with('success','You are now logged in.');
          }
      }
    }else{
      return Redirect::to('/verify_code')
              ->withInput(Input::all())->with('error', 'Code Not Match please enter Correct Code');
    }
  }

  public function getLogout() {
    /*request()->session()->flush();
    \Auth::logout();*/

    if(request()->session()->pull('isAdmin', 0)){
      $id = request()->session()->pull('adminID', 0);
      //$url = request()->session()->pull('surl','');
      //$id = request()->session()->pull('adminID', 0);
      if(Auth::loginUsingId($id)) {
        //request()->session()->flush();
        $name  = Auth::user()->firstname.' '.Auth::user()->lastname;
        $login = Auth::user()->group;
        \Session::put('name', $name);
        \Session::put('userRole', $login);
        return redirect('/dashboard');
      }
      return redirect('/dashboard');
    }
    request()->session()->flush();
    \Auth::logout();
    return redirect('/')->with('message', 'Your are now logged out!');
  } 
  public function dologin($id,$usr_id) {
    $user = User::find($id);
    request()->session()->forget('isAdmin');
    request()->session()->forget('adminID');
    request()->session()->forget('surl');
    request()->session()->put('isAdmin', 1);
    request()->session()->put('adminID', $usr_id);
    
   // echo request()->root();
    //echo "<pre>rr".request()->session()->get('adminID')."tt";print_r($user);
    if (Auth::loginUsingId($id)) {
        
        $name  = Auth::user()->firstname.' '.Auth::user()->lastname;
        $login = Auth::user()->group;
        \Session::put('name', $name);
        \Session::put('userRole', $login);
        //echo "adeel";
        return redirect('/dashboard');
    }
  }

  public  function show()
  {
    $users= User::all();
    $user=array();
    //return View::Make('app.users',compact('users','user'));
    return View('app.users',compact('users','user'));
  }
  public  function create()
  {
    $rules=[
      'firstname' => 'required',
      'lastname' => 'required',
      'email' => 'required|email',
      'group' => 'required',
      'desc' => 'required',
      'login' => 'required',
      'password' => 'required'

    ];
    $validator = \Validator::make(Input::all(), $rules);
    if ($validator->fails())
    {
      return Redirect::to('/users')->withInput(Input::all())->withErrors($validator);
    }
    else {

      $uexits = User::select('*')->where('email','=',Input::get('email'))->where('login','=',Input::get('login'))->get();
    //  dd($uexits );
     //echo "<pre>";print_r($uexits);exit;
      if(count($uexits)>0)
      {
        $errorMessages = new \Illuminate\Support\MessageBag;
        $errorMessages->add('deplicate', 'User all ready exists with this email or login');
        return Redirect::to('/users')->withInput(Input::all())->withErrors($errorMessages);

      }
      {
        $user = new User;
        $user->firstname = Input::get('firstname');
        $user->lastname = Input::get('lastname');
        $user->login = Input::get('login');
        $user->desc = Input::get('desc');
        $user->email = Input::get('email');
        $user->group = Input::get('group');
        $user->password = Hash::make(Input::get('password'));
        $user->save();
        
        return Redirect::to('/users')->with("success","User Created Succesfully.");
      }


    }
  }
  public function edit($id)
  {
    $user = User::find($id);
    $users= User::all();
    //return View::Make('app.users',compact('users','user'));
     return View('app.users',compact('users','user'));
  }
  public  function update()
  {
    $rules=[
      'firstname' => 'required',
      'lastname' => 'required',
      'email' => 'required|email',
      'group' => 'required',
      'desc' => 'required',
      'login' => 'required',
      'password' => 'required'

    ];
    $validator = \Validator::make(Input::all(), $rules);
    if ($validator->fails())
    {
      return Redirect::to('/usersedit/'.Input::get('id'))->withErrors($validator);
    }
    else {

      $uexits = User::select('*')->orwhere('email','=',Input::get('email'))->first();
      if($uexits->count()>0) {

        if ($uexits->id != Input::get('id')) {
          $errorMessages = new \Illuminate\Support\MessageBag;
          $errorMessages->add('deplicate', 'User all ready exists with this email');
          return Redirect::to('/users')->withInput(Input::all())->withErrors($errorMessages);
        } else {
          $user = User::find(Input::get('id'));
          $user->firstname = Input::get('firstname');
          $user->lastname = Input::get('lastname');
          $user->login = Input::get('login');
          $user->desc = Input::get('desc');
          $user->email = Input::get('email');
          $user->group = Input::get('group');
          $user->password = Hash::make(Input::get('password'));
          $user->save();
          return Redirect::to('/users')->with("success", "User Updated Succesfully.");
        }
      }
      else
      {
        $user = User::find(Input::get('id'));
        $user->firstname = Input::get('firstname');
        $user->lastname = Input::get('lastname');
        $user->login = Input::get('login');
        $user->desc = Input::get('desc');
        $user->email = Input::get('email');
        $user->group = Input::get('group');
        $user->password = Hash::make(Input::get('password'));
        $user->save();
        return Redirect::to('/users')->with("success", "User Updated Succesfully.");
      }

    }
  }

  public function delete($id)
  {
    $user= User::find($id);
    $user->delete();
    return Redirect::to('/users')->with("success","User Deleted Succesfully.");

  }
}
