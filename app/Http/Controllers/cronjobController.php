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
class studentfdata{


}
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
			$i=0;
		    $ictcore_fees = Ictcore_fees::select("*")->first();
			$ictcore_integration = Ictcore_integration::select("*")->first();
				if(!empty($ictcore_integration) && $ictcore_integration->ictcore_url && $ictcore_integration->ictcore_user && $ictcore_integration->ictcore_password){ 
				      $ict  = new ictcoreController();
					  $data = array(
						'name' => 'Fee Notification',
						'description' => 'fee notification using cron job',
						);

					 $group_id= $ict->ictcore_api('groups','POST',$data );

		     	}else{

		           // return Redirect::to('/fees/classreport')->withErrors("Please Add ictcore integration in Setting Menu");
                    exit();
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

					   $contact_id = $ict->ictcore_api('contacts','POST',$data );

					    $group = $ict->ictcore_api('contacts/'.$contact_id.'/link/'.$group_id,'PUT',$data=array() );
					}

				}
			}
			else{
			//$resultArray = array();
				exit();
			}
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
