<?php
namespace App\Http\Controllers;
use App\ClassModel;
use App\Subject;
use App\Student;
use App\Attendance;
use App\Accounting;
use App\Marks;
use App\AddBook;
use Carbon\Carbon;

class DashboardController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth', array('only'=>array('index')));*/
		 $this->middleware('auth', array('only'=>array('index')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$error = \Session::get('error');
		$success=\Session::get('success');
		$tclass = ClassModel::count();
		$tsubject = Subject::count();
		$tstudent=Student::count();
 		$totalAttendance = Attendance::groupBy('date')->get();
 		//echo Carbon::now()->format('Y-m-d');
 		$totalabsent = Attendance::where('date',Carbon::now()->format('Y-m-d'))->where('status','Absent')->count();

 		//echo "<pre>";print_r($totalabsent );exit;
 		$totalExam = Marks::groupBy('exam')->groupBy('subject')->get();
		$book = AddBook::count();
 		$total = [
 			'class' =>$tclass,
 			'student' =>$tstudent,
 			'subject' =>$tsubject,
 			'attendance' =>count($totalAttendance),
 			'exam' =>count($totalExam),
			'book' => $book,
			'totalabsent' => $totalabsent,
 		];
 	     // 	//graph data
 	  //dd($total);
 		$monthlyIncome= Accounting::selectRaw('month(date) as month, sum(amount) as amount, year(date) as year')
 		->where('type','Income')
 		->groupBy('month')
 		->get();
              
 		$monthlyExpences= Accounting::selectRaw('month(date) as month, sum(amount) as amount, year(date) as year')
 		->where('type','Expence')
 		->groupBy('month')
 		->get();
 		$incomeTotal = Accounting::where('type','Income')
 		->sum('amount');
 		$expenceTotal = Accounting::where('type','Expence')
 		->sum('amount');
 		$incomes=$this->datahelper($monthlyIncome);
 		$expences=$this->datahelper($monthlyExpences);
 		$balance = $incomeTotal - $expenceTotal;
		//return View::Make('dashboard',compact('error','success','total','incomes','expences','balance'));
		return View('dashboard',compact('error','success','total','incomes','expences','balance'));
	}
	private function datahelper($data)
 	{
 		$DataKey = [];
 		$DataVlaue =[];
 		foreach ($data as $d) {
 			array_push($DataKey,date("F", mktime(0, 0, 0, $d->month, 10)).','.$d->year);
 			array_push($DataVlaue,$d->amount);

 		}
 		return ["key"=>$DataKey,"value"=>$DataVlaue];

 	}
}
