<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Subject;
use App\ClassModel;
use App\Student;
use App\Attendance;
use App\Accounting;
use App\Marks;
use App\AddBook;
use App\FeeCol;
use App\FeeSetup;
use App\Institute;
use App\FeeHistory;
use DB;

use App\Ictcore_fees;
use App\Ictcore_integration;
use App\Http\Controllers\ictcoreController;
use Carbon\Carbon;

class cronjobController extends BaseController {

	public function __construct()
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('getDelete','stdfeesdelete')));*/
		//$this->middleware('auth');
		 //$this->middleware('auth', array('only'=>array('index')));
	}
	
    public function feenotification()
    {

		$student_all =	DB::table('Student')->select( '*')->get();
		if(count($student_all)>0){
			 $ict  = new ictcoreController();
			$i=0;
			$attendance_noti     = DB::table('notification_type')->where('notification','fess')->first();
		    $ictcore_fees        = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->where('type',$attendance_noti->type)->first();
			if($ictcore_integration->method=="telenor"){
              $group_id = $ict->telenor_apis('group','','');
			}else{
				if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){ 
				     
					  $data = array(
						'name' => 'Fee Notification',
						'description' => 'fee notification using cron job',
						);

					 $group_id= $ict->ictcore_api('groups','POST',$data );

		     	}else{

		           // return Redirect::to('/fees/classreport')->withErrors("Please Add ictcore integration in Setting Menu");
                    exit();
		     	}
		     }
				foreach($student_all as $stdfees)
				{

					$student =	DB::table('billHistory')->leftJoin('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
					->select( 'billHistory.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','stdBill.regiNo')
					// ->whereYear('stdBill.payDate', '=', 2017)
					->where('stdBill.regiNo','=',$stdfees->regiNo)->whereYear('stdBill.payDate', '=', date('Y'))->where('billHistory.month','=',date('n'))->where('billHistory.month','<>','-1')
					//->orderby('stdBill.payDate')
					->get();
                    
					if(count($student)>0 ){
						$datanot[]=array($stdfees->regiNo);
					}else{
						$data= array(
				        //'registrationNumber' =>$stdfees->regiNo,
						'first_name'         => $stdfees->firstName,
						'last_name'          =>  $stdfees->lastName,
						'phone'              =>  $stdfees->fatherCellNo,
						'email'              => '',
						);
                        if($ictcore_integration->method=="telenor"){
                        	
                        	 $group_contact_id = $ict->telenor_apis('add_contact',$group_id,$stdfees->fatherCellNo);
                        }else{
					   $contact_id = $ict->ictcore_api('contacts','POST',$data );

					   $group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );
					 }
					}

				}
			}
			else{
			//$resultArray = array();
				exit();
			}
			    if($ictcore_integration->method=="telenor"){
                    $group_contact_id
                    
			    }else{
			    if(!empty($ictcore_fees) && $ictcore_fees->ictcore_program_id!=''){
			    	
	                $data = array(
						'program_id' => $ictcore_fees->ictcore_program_id,
						'group_id' => $group_id,
						'delay' => '',
						'try_allowed' => '',
						'account_id' => 1,
						'status' => '',
					);
					$campaign_id = $ict->ictcore_api('campaigns','POST',$data );
					//$campaign_id = $ict->ictcore_api('campaigns/$campaign_id/start','PUT',$data=array() );
			}
		}
    }
}
