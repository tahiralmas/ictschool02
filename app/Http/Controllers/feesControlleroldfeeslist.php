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
class studentfdata{


}
class feesController extends BaseController {

	public function __construct()
	{
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('getDelete','stdfeesdelete')));*/
		$this->middleware('auth');
		 $this->middleware('auth', array('only'=>array('index')));
	}
	public function getsetup()
	{

		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feesSetup',compact('classes'));
		return View('app.feesSetup',compact('classes'));
	}

	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function postSetup()
	{
		$rules=[

			'class' => 'required',
			'type' => 'required',
			'fee' => 'required|numeric',
			'title' => 'required'

		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fees/setup')->withErrors($validator);
		}
		else {

			$fee = new FeeSetup();


			$fee->class = Input::get('class');
			$fee->type = Input::get('type');
			$fee->title = Input::get('title');
			$fee->fee = Input::get('fee');
			$fee->Latefee = Input::get('Latefee');
			$fee->description = Input::get('description');
			$fee->save();
			return Redirect::to('/fees/setup')->with("success","Fee Save Succesfully.");


		}
	}




	public function getList()
	{
		$fees=array();
		$classes = ClassModel::pluck('name','code');

		$formdata = new formfoo;
		$formdata->class="";
		//return View::Make('app.feeList',compact('classes','formdata','fees'));
		return View('app.feeList',compact('classes','formdata','fees'));
	}
	/**
	* Display the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function postList()
	{
		$rules=[

			'class' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fees/list')->withErrors($validator);
		}
		else {

			$fees = FeeSetup::select("*")->where('class',Input::get('class'))->get();
			$classes = ClassModel::pluck('name','code');
			$formdata = new formfoo;
			$formdata->class=Input::get('class');
			//return View::Make('app.feeList',compact('classes','formdata','fees'));
			return View('app.feeList',compact('classes','formdata','fees'));



		}
	}


	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function getEdit($id)
	{
		$classes = ClassModel::pluck('name','code');
		$fee = FeeSetup::find($id);
		//return View::Make('app.feeEdit',compact('fee','classes'));
		return View('app.feeEdit',compact('fee','classes'));

	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function postEdit()
	{
		$rules=[

			'class' => 'required',
			'type' => 'required',
			'fee' => 'required|numeric',
			'title' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fee/edit/'.Input::get('id'))->withErrors($validator);
		}
		else {

			$fee = FeeSetup::find(Input::get('id'));
			$fee->class = Input::get('class');
			$fee->type = Input::get('type');
			$fee->title = Input::get('title');
			$fee->fee = Input::get('fee');
			$fee->Latefee = Input::get('Latefee');
			$fee->description = Input::get('description');
			$fee->save();
			return Redirect::to('/fees/list')->with("success","Fee Updated Succesfully.");


		}
	}


	/**
	* Remove the specified resource from storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function getDelete($id)
	{
		$fee = FeeSetup::find($id);
		$fee->delete();
		return Redirect::to('/fees/list')->with("success","Fee Deleted Succesfully.");
	}
	public function getCollection()
	{
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.feeCollection',compact('classes'));
		return View('app.feeCollection',compact('classes'));
	}
	public function postCollection()
	{

		$rules=[

			'class' => 'required',
			'student' => 'required',
			'date' => 'required',
			'paidamount' => 'required',
			'dueamount' => 'required',
			'ctotal' => 'required'

		];
		$validator = \Validator::make(Input::all(), $rules);

		if ($validator->fails())
		{
			return Redirect::to('/fee/collection')->withInput(Input::all())->withErrors($validator);
		}
		else {
			try {
				$feeTitles = Input::get('gridFeeTitle');
				$feeAmounts = Input::get('gridFeeAmount');
				$feeLateAmounts = Input::get('gridLateFeeAmount');
				$feeTotalAmounts = Input::get('gridTotal');
				$feeMonths = Input::get('gridMonth');
				$counter = count($feeTitles);
				if($counter>0)
				{
					$rows = FeeCol::count();
					if($rows<9)
					{
						$billId='B00'.($rows+1);
					}
					else if($rows<100)
					{
						$billId='B0'.($rows+1);
					}
					else {
						$billId='B'.($rows+1);
					}
					DB::transaction(function() use ($billId,$counter,$feeTitles,$feeAmounts,$feeLateAmounts,$feeTotalAmounts,$feeMonths)
					{
						$feeCol = new FeeCol();
						$feeCol->billNo=$billId;
						$feeCol->class=Input::get('class');
						$feeCol->regiNo=Input::get('student');
						$feeCol->payableAmount=Input::get('ctotal');
						$feeCol->paidAmount=Input::get('paidamount');
						$feeCol->dueAmount=Input::get('dueamount');
						$feeCol->payDate=Input::get('date');
						$feeCol->save();

						for ($i=0;$i<$counter;$i++) {
							$feehistory = new FeeHistory();
							$feehistory->billNo=$billId;
							$feehistory->title=$feeTitles[$i];
							$feehistory->fee=$feeAmounts[$i];
							$feehistory->lateFee=$feeLateAmounts[$i];
							$feehistory->total=$feeTotalAmounts[$i];
							$feehistory->month=$feeMonths[$i];
							$feehistory->save();

						}
					});
					return Redirect::to('/fee/collection')->with("success","Fee collection succesfull.");
				}
				else {
					$messages = $validator->errors();
					$messages->add('Validator!', 'Please add atlest one fee!!!');
					return Redirect::to('/fee/collection')->withInput(Input::all())->withErrors($messages);

				}
			}
			catch(\Exception $e)
			{

				return Redirect::to('/fee/collection')->withErrors( $e->getMessage())->withInput();
			}

		}
	}

	public function getListjson($class,$type)
	{
		$fees= FeeSetup::select('id','title')->where('class','=',$class)->where('type','=',$type)->get();
		return $fees;
	}
	public function getFeeInfo($id)
	{
		$fee= FeeSetup::select('fee','Latefee')->where('id','=',$id)->get();
		return $fee;
	}

	public function getDue($class,$stdId)
	{
		$due = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0) as dueamount'))
		->where('class',$class)
		->where('regiNo',$stdId)
		->first();
		return $due->dueamount;

	}
	public function stdfeeview()
	{
		$classes = ClassModel::pluck('name','code');
		$student = new studentfdata;
		$student->class="";
		$student->section="";
		$student->shift="";
		$student->session="";
		$student->regiNo="";
		$fees=array();
		//return View::Make('app.feeviewstd',compact('classes','student','fees'));
		return View('app.feeviewstd',compact('classes','student','fees'));
	}
	public function stdfeeviewpost()
	{
		$classes = ClassModel::pluck('name','code');
		$student = new studentfdata;
		$student->class=Input::get('class');
		$student->section=Input::get('section');
		$student->shift=Input::get('shift');
		$student->session=Input::get('session');
		$student->regiNo=Input::get('student');
		$fees=DB::Table('stdBill')
		->select(DB::RAW("billNo,payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
		->where('class',Input::get('class'))
		->where('regiNo',Input::get('student'))
		->get();
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->where('class',Input::get('class'))
		->where('regiNo',Input::get('student'))
		->first();

		//return View::Make('app.feeviewstd',compact('classes','student','fees','totals'));
		return View('app.feeviewstd',compact('classes','student','fees','totals'));
	}
	public function stdfeesdelete($billNo)
	{
		try {
			DB::transaction(function() use ($billNo)
			{
				FeeCol::where('billNo',$billNo)->delete();
				FeeHistory::where('billNo',$billNo)->delete();

			});
			return Redirect::to('/fees/view')->with("success","Fees deleted succesfull.");
		}
		catch(\Exception $e)
		{

			return Redirect::to('/fees/view')->withErrors( $e->getMessage())->withInput();
		}

	}
	public function reportstd($regiNo)
	{

		$datas=DB::Table('stdBill')
		->select(DB::RAW("payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
		->where('regiNo',$regiNo)
		->get();
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->where('regiNo',$regiNo)
		->first();
		$stdinfo=DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->select('Student.regiNo', 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName',
		'Student.section','Student.shift','Student.session','Class.Name as class')
		->where('Student.regiNo',$regiNo)
		->first();
		$institute=Institute::select('*')->first();
		$rdata =array('payTotal'=>$totals->payTotal,'paiTotal'=>$totals->paiTotal,'dueAmount'=>$totals->dueamount);
		$pdf = \PDF::loadView('app.feestdreportprint',compact('datas','rdata','stdinfo','institute'));
		return $pdf->stream('student-Payments.pdf');

	}
	public function report()
	{
		//return View::Make('app.feesreport');
		return View('app.feesreport');
	}
	public function reportprint($sDate,$eDate)
	{
		$datas= FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->whereDate('created_at', '>=', date($sDate))
		->whereDate('created_at', '<=', date($eDate))
		->first();
		$institute=Institute::select('*')->first();
		$rdata =array('sDate'=>$this->getAppdate($sDate),'eDate'=>$this->getAppdate($eDate));
		$pdf = \PDF::loadView('app.feesreportprint',compact('datas','rdata','institute'));
		return $pdf->stream('fee-collection-report.pdf');
	}

	public function billDetails($billNo)
	{
		$billDeatils = FeeHistory::select("*")
		->where('billNo',$billNo)
		->get();
		return $billDeatils;
	}
	private function  parseAppDate($datestr)
	{
		$date = explode('/', $datestr);
		return $date[2].'-'.$date[1].'-'.$date[0];
	}
	private function  getAppdate($datestr)
	{
		$date = explode('-', $datestr);
		return $date[2].'/'.$date[1].'/'.$date[0];
	}


      public function classreportindex(){
      	



$classes = ClassModel::pluck('name','code');
        $class = '';
        $section = '';
        $month = '';
        $session = '';
        $year = '';
		$student = new studentfdata;
		$student->class="";
		$student->section="";
		$student->shift="";
		$student->session="";

		$student->regiNo="";
		$fees=array();
		$paid_student = array();
		$resultArray =array();

		return View('app.feestdreportclass',compact('classes','student','fees','totals','class','section','month','session','paid_student','year','resultArray'));
	}

	public function classview(){



		$classes = ClassModel::pluck('name','code');
		$student = new studentfdata;
		$student->class=Input::get('class');
		$student->section=Input::get('section');
		$student->shift=Input::get('shift');
		$student->session=Input::get('session');
		$student->regiNo=Input::get('student');
		$feeyear = Input::get('year') ;
		/*$fees=DB::Table('stdBill')
		->select(DB::RAW("billNo,payableAmount,paidAmount,dueAmount,DATE_FORMAT(payDate,'%D %M,%Y') AS date"))
		->where('class',Input::get('class'))
		->where('regiNo',Input::get('student'))
		->get();
		$totals = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
		->where('class',Input::get('class'))
		//->where('regiNo',Input::get('student'))
		->first();*/



$student_all =	DB::table('Student')->select( '*')->where('class','=',Input::get('class'))->where('section','=',Input::get('section'))->where('session','=',$student->session)->get();

/*regiNo

$student =	DB::table('Student')
								->leftJoin('stdBill', 'Student.regiNo', '=', 'stdBill.regiNo')
								->leftJoin('billHistory' ,'Student.regiNo', '=', 'billHistory.regiNo')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Student.session','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','billHistory.month','billHistory.fee')
								// ->whereYear('stdBill.payDate', '=', 2017)
								->where('Student.class','=',Input::get('class'))->where('Student.section','=',Input::get('section'))->where('Student.session','=',$student->session)
								//->orderby('stdBill.payDate')
								->get();







echo "<pre>";print_r($student );

exit*/

//$studnet = array();
if(count($student_all)>0){
$i=0;
foreach($student_all as $stdfees){

$student =	DB::table('billHistory')->leftJoin('stdBill', 'billHistory.billNo', '=', 'stdBill.billNo')
->select( 'billHistory.billNo','billHistory.month','billHistory.fee','billHistory.lateFee','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate','stdBill.regiNo')
// ->whereYear('stdBill.payDate', '=', 2017)
->where('stdBill.regiNo','=',$stdfees->regiNo)->whereYear('stdBill.payDate', '=', Input::get('year'))->where('billHistory.month','=',Input::get('month'))->where('billHistory.month','<>','-1')
//->orderby('stdBill.payDate')
->get();
//
//echo "<pre>".$i;print_r($student);
if(count($student)>0 ){

	foreach($student as $rey){
	//echo $rey->billNo;

	//$year = date('Y', strtotime($rey->payDate));
	//if($year=='2018' && $rey->month=='1' && $rey->month!='-1'){
        	$status[] = "paid".'_'.$stdfees->regiNo."_";

	$resultArray[$i] = get_object_vars($stdfees);

array_push($resultArray[$i],'Paid',$rey->payDate,$rey->billNo,$rey->fee);

	//$arr3[] = $rest[$i] + $status[$i];
	$i++;
}
//};

}else{
				$status[$i] = "unpaid".'_'.$stdfees->regiNo."_";
				$resultArray[] = get_object_vars($stdfees);
				array_push($resultArray[$i],'unPaid');
				$i++;



}

}

}
else{
$resultArray = array();

}
$j=0;

/*foreach($rest as $feebill){

   foreach($student_all as $stdfees){

if($feebill['regiNo']==$stdfees->regiNo){
 $status[] = "paid".'_'.$stdfees->regiNo."_";

}else{
$status[] = "unpaid".'_'.$stdfees->regiNo."_";

}
$j++;
}
}*/
//echo "<pre>".$i;print_r($resultArray);

$class   = Input::get('class');
		$month   = Input::get('month');
		$section = Input::get('section');
		$session = Input::get('session');
		$year    = Input::get('year');
//echo "<pre>ssss";print_r($paid_student);
		return View('app.feestdreportclass',compact('resultArray','class','month','section','classes','session','year'));

exit;
$student_all =	DB::table('Student')->select( '*')->where('class','=',Input::get('class'))->where('section','=',Input::get('section'))->get();
//echo "<pre>";print_r($student_all);
		$student =	DB::table('Student')
								->leftJoin('stdBill', 'Student.regiNo', '=', 'stdBill.regiNo')
								->select( 'Student.regiNo','Student.rollNo','Student.firstName','Student.middleName','Student.lastName','Student.fatherCellNo','Student.session','stdBill.class as class1','stdBill.payableAmount','stdBill.billNo','stdBill.payDate')
								// ->whereYear('stdBill.payDate', '=', 2017)
								->where('Student.class','=',Input::get('class'))->where('Student.section','=',Input::get('section'))->where('Student.session','=',$student->session)
								//->orderby('stdBill.payDate')
								->get();
								//->where('class',Input::get('class'))
				echo "<pre>eeee";print_r($student);
				$i=0;
/*$student = get_object_vars($student);

$result = array_diff($student, array(''))
          + array_intersect($student, array(''));
$result = array_values($result);

echo "<pre>eeee";print_r($result);

exit;*/
       if(count($student)>0){
		foreach($student as $bilhistory){
			$tst[] = $bilhistory;
			$year = date('Y', strtotime($bilhistory->payDate));
$date = '';
          if($year==$feeyear){
			$bill =	DB::table('billHistory')->select('*')->where('billNo','=', $bilhistory->billNo )->where('month','=',Input::get('month') )->first();
		}else{

			$bill = array();
			$date= $feeyear;
		}
//&& $bill[$i]->month !='-1'
			//echo "<pre>bill";print_r($bill );
			
        if(count($bill)>0 && $bill->month !='-1'){
          
        	

        	$status[] = "paid".'_'.$bilhistory->regiNo."_".$bilhistory->billNo ."date=". $year;
			$paid_student[] = $bilhistory;

		
			/*foreach($bill  as $chkmnth){
			//	echo $chkmnth->month;
			if($chkmnth->month !='-1' && $chkmnth->month == Input::get('month')){
			//$bill[] =	DB::table('billHistory')->select('*')->where('month','!=','-1')->where('billNo','=', $bilhistory->billNo )->where('month','=',Input::get('month') )->get();
			$status[] = "paid".'_'.$bilhistory->regiNo."_".$bilhistory->billNo;
			$paid_student[] = $bilhistory;
	}else{

		$paid_student1[] = $bilhistory;
	}
		}*/
		}else{

			if($bilhistory->billNo!='' ){
              $bilhistory->billNo='';
              $bilhistory->payDate='';
              
			}
			$status[] = "unpaid".'_'.$bilhistory->regiNo."_".$bilhistory->billNo;


        

			 $paid_student[] = $bilhistory;

		
		 }
            $i++;
		}
      
foreach($paid_student as $test){

	$chk[] = get_object_vars($test);
	$regno = array();
	// if (!in_array($val[$key], $key_array) && $val['payDate']=='') {



	
}


  //$candidate = Candidate::create($data);
        //dd($data);
 

		//$details = $this->unique_multidim_array($paid_student,'regiNo'); 
//array_values(array_unique($array));


echo "<pre>";print_r($chk);

	$resultArray = $this->uniqueAssocArray($chk, 'regiNo');

	echo "<pre>";print_r($resultArray);

}else{
	$resultArray = array();
}
		//echo "<pre>";print_r($paid_student1);
		$class   = Input::get('class');
		$month   = Input::get('month');
		$section = Input::get('section');
		$session = Input::get('session');
		$year    = Input::get('year');
//echo "<pre>ssss";print_r($paid_student);
		//return View('app.feestdreportclass',compact('resultArray','class','month','section','classes','session','year'));
		//return View::Make('app.feeviewstd',compact('classes','student','fees','totals'));
		//return View('app.feestdreportclass',compact('classes','student','fees','totals'));
	}





function uniqueAssocArray($array, $uniqueKey) {
    if (!is_array($array)) {
        return array();
    }
    $uniqueKeys = array();
    foreach ($array as $key => $item) {
        $groupBy=$item[$uniqueKey];

        if (isset( $uniqueKeys[$groupBy]))
        {
            //compare $item with $uniqueKeys[$groupBy] and decide if you 
            //want to use the new item
         //   if($uniqueKeys[$groupBy]=='payDate'){
            	//$replace=true;
           // }
           // $replace= ... 
        }
        else
        {
            $replace=true;
        }
        if ($replace) $uniqueKeys[$groupBy] = $item;   
    }
    return $uniqueKeys;
}
	function unique_multidim_array($array, $key) {
    $temp_array = array();
    $i = 0;
    $key_array = array();
  
    foreach($array as $val1) {
    	$val =  get_object_vars($val1);


 
        if (!in_array($val[$key], $key_array) ) {

        	
            $key_array[$i] = $val[$key];

            $temp_array[$i] = $val;
        }
 $i++;
         
       
 
    }
    return $temp_array;
} 
}
