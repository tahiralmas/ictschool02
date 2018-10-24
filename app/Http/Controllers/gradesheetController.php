<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\ClassModel;
use App\Subject;
use App\Student;
use App\Marks;
use App\GPA;
use App\MeritList;
use DB;
Class formfoo{

}
Class Meritdata{

}
class gradesheetController extends BaseController {

	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth',array('except' => array('searchpub','postsearchpub','printsheet')));*/
               $this->middleware('auth',array('except' => array('searchpub','postsearchpub','printsheet')));
	}
	/**
	* Display a listing of the resource.
	*
	* @return Response
	*/
	public function index()
	{
		$formdata = new formfoo;
		$formdata->class="";
		$formdata->section="";
		$formdata->shift="";
		$formdata->exam="";
		$formdata->session="";
		$students=array();
		$classes = ClassModel::pluck('name','code');

		//return View::Make('app.gradeSheet',compact('classes','formdata','students'));
		return View('app.gradeSheet',compact('classes','formdata','students'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function stdlist()
	{
		$rules=[
			'class' => 'required',
			'section' => 'required',
			'exam' => 'required',
			'session' => 'required'


		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			$formdata = new formfoo;
			$formdata->class=Input::get('class');
			$formdata->section=Input::get('section');
			$formdata->exam=Input::get('exam');
			$formdata->session=Input::get('session');

			return Redirect::to('/gradesheet')->withErrors($validator);
		}
		else {

			$ispubl  = DB::table('MeritList')
			->select('regiNo')
			->where('class','=',Input::get('class'))
			->where('session','=',trim(Input::get('session')))
			->where('exam','=',Input::get('exam'))
			->get();
			if(count($ispubl)>0) {
				$classes = ClassModel::pluck('name', 'code');
				$students = DB::table('Student')
				->join('Marks', 'Student.regiNo', '=', 'Marks.regiNo')
				->select(DB::raw('DISTINCT(Student.regiNo)'), 'Student.rollNo', 'Student.firstName', 'Student.middleName', 'Student.lastName', 'Student.group', 'Marks.shift', 'Marks.class', 'Marks.section')
				->where('Student.isActive', '=', 'Yes')
				->where('Student.class', '=', Input::get('class'))
				->where('Marks.class', '=', Input::get('class'))
				->where('Marks.section', '=', Input::get('section'))
				->where('Marks.session', '=', trim(Input::get('session')))
				->where('Marks.exam', '=', Input::get('exam'))
				->get();

				$formdata = new formfoo;
				$formdata->class = Input::get('class');
				$formdata->section = Input::get('section');
				$formdata->session = Input::get('session');
				$formdata->exam = Input::get('exam');
				$formdata->postclass = array_get($classes, Input::get('class'));

				//return View::Make('app.gradeSheet', compact('classes', 'formdata', 'students'));
				return View('app.gradeSheet', compact('classes', 'formdata', 'students'));
			}
			else
			{
				return Redirect::to('/gradesheet')->withInput()->with("noresult", "Results Not Published Yet!");
			}


		}
	}

	public  function gradeCalculator($point,$gparules)
	{
		$grade=0;
		foreach ($gparules as $gpa) {
			if ($point >= $gpa->grade){
				$grade=$gpa->gpa;
				break;
			}
		}
		return $grade;
	}
	public  function pointCalculator($marks,$gparules)
	{

		$point=0;
		foreach ($gparules as $gpa) {


			if ($marks >= $gpa->markfrom){
				$point=$gpa->grade;
				break;
			}
		}

		return $point;
	}
	public  function gpaCalculator($marks,$gparules)
	{
		$gpacal= array();
      //dd($marks);
		foreach ($gparules as $gpa) {
			
			if ($marks >= $gpa->markfrom){
				$gpacal[0]=$gpa->grade;
				$gpacal[1]=$gpa->gpa;
				break;
			}
		}
		return $gpacal;
	}
	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	/*public function printsheet($regiNo,$exam,$class)
	{
		 $student = DB::table('Student')
		->join('Class', 'Student.class', '=', 'Class.code')
		->select( 'Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity')
		->where('Student.regiNo','=',$regiNo)
		->where('Student.class','=',$class)
		->where('Student.isActive', '=', 'Yes')
		->first();
		if(!is_null($student)) {

			$merit = DB::table('MeritList')
			->select('regiNo', 'grade', 'point', 'totalNo')
			->where('exam', $exam)
			->where('class', $class)
			->where('session', trim($student->session))
			->where('regiNo',$regiNo)
			->orderBy('point', 'DESC')
			->orderBy('totalNo', 'DESC')->get();
			if (is_null($student)  || is_null($merit) ) {
				return Redirect::back()->with('noresult', 'Result Not Found!');
			} else {
				$meritdata = new Meritdata();
				$position = 0;
				foreach ($merit as $m) {
					$position++;
					if ($m->regiNo === $regiNo) {
						$meritdata->regiNo = $m->regiNo;
						$meritdata->point = $m->point;
						$meritdata->grade = $m->grade;
						$meritdata->position = $position;
						$meritdata->totalNo = $m->totalNo;
						break;
					}
				}

				//sub group need to implement
				$subjects = Subject::select('name', 'code', 'subgroup', 'totalfull')->where('class', '=', $student->classcode)->get();

				$overallSubject = array();
				$subcollection = array();

				$banglatotal = 0;
				$banglatotalhighest = 0;
				$banglaArray = array();
				$blextra = array();

				$englishtotal = 0;
				$englishtotalhighest = 0;
				$englishArray = array();
				$enextra = array();

				$totalHighest = 0;
				$isBanglaFail=false;
				$isEnglishFail=false;
				foreach ($subjects as $subject) {
					$submarks = Marks::select('written', 'mcq', 'practical', 'ca', 'total', 'point', 'grade')->where('regiNo', '=', $student->regiNo)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->where('class', '=', $class)->first();
					$maxMarks = Marks::select(DB::raw('max(total) as highest'))->where('class', '=', $class)->where('session', '=', $student->session)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->first();

					$submarks["highest"] = $maxMarks->highest;
					$submarks["subcode"] = $subject->code;

					$submarks["subname"] = $subject->name;


					if ($this->getSubGroup($subjects, $subject->code) === "Bangla") {
						if($submarks->grade=="F")
						{
							$isBanglaFail=true;
						}

						$banglatotal += $submarks->total;
						$banglatotalhighest += $submarks->highest;

						$bangla = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical);
						array_push($banglaArray, $bangla);

					} else if ($this->getSubGroup($subjects, $subject->code) === "English") {
						if($submarks->grade==="F")
						{
							$isEnglishFail=true;
						}
						$englishtotal += $submarks->total;
						$englishtotalhighest += $submarks->highest;

						$english = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical);
						array_push($englishArray, $english);

						//array_push($subcollection, $submarks);



					} else {
						$totalHighest += $maxMarks->highest;
						array_push($subcollection, $submarks);

							//print_r($submarks);

					}


				}
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
				  //dd($gparules);
				$subgrpbl = false;
				if ($banglatotal > 0) {

					$blt = floor($banglatotal / 2);
					$totalHighest += $banglatotalhighest;
					$gcal = $this->gpaCalculator($blt, $gparules);
                 
					$subgrpbl = true;
					array_push($blextra, $banglatotal);
					array_push($blextra, $banglatotalhighest);
					if($isBanglaFail)
					{
						array_push($blextra, "0.00");
						array_push($blextra, "F");
					}
					else {
						if(isset($gcal[0])){
						array_push($blextra, $gcal[0]);
						array_push($blextra, $gcal[1]);
					}
                        }


				}
				$subgrpen = false;
				if ($englishtotal > 0) {
					$ent = floor($englishtotal / 2);
					$totalHighest += $englishtotalhighest;
					$gcal = $this->gpaCalculator($ent, $gparules);
					$subgrpen = true;
					array_push($enextra, $englishtotal);
					array_push($enextra, $englishtotalhighest);
					if($isEnglishFail)
					{
						array_push($enextra, "0.00");
						array_push($enextra, "F");

					}
					else {
						if(isset($gcal[0])){
						array_push($enextra, $gcal[0]);
						array_push($enextra, $gcal[1]);
					}

					}


				}
				$extra = array($exam, $subgrpbl, $totalHighest, $subgrpen, $student->extraActivity);
				$query="select left(MONTHNAME(STR_TO_DATE(m, '%m')),3) as month, count(regiNo) AS present from ( select 01 as m union all select 02 union all select 03 union all select 04 union all select 05 union all select 06 union all select 07 union all select 08 union all select 09 union all select 10 union all select 11 union all select 12 ) as months LEFT OUTER JOIN Attendance ON MONTH(Attendance.date)=m and Attendance.regiNo ='".$regiNo."' GROUP BY m";
				$attendance=DB::select(DB::RAW($query));
				//return View::Make('app.stdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));
              //  dd($englishArray);

				
				

			print_r($banglaArray);
              return View('app.stdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));
			
			}
		}
		else
		{
			//echo "<h1 style='text-align: center;color: red'>Result Not Found</h1>";
			return  Redirect::back()->with('noresult','Result Not Found!');

		}
	}*/


	public function printsheet($regiNo,$exam,$class)
	{
        $examed  = DB::table('exam')->where('id',$exam)->first();
		$exam_name =  $examed->type;
		$student =	DB::table('Student')
		 ->join('Class', 'Student.class', '=', 'Class.code')
		 ->join('section','Student.section','=','section.id')
		 ->select( 'Student.regiNo','Student.rollNo','Student.dob', 'Student.firstName','Student.middleName','Student.lastName','Student.fatherName','Student.motherName', 'Student.group','Student.shift','Student.class as classcode','Class.Name as class','Student.section','Student.session','Student.extraActivity','section.name as section_name')
		 ->where('Student.regiNo','=',$regiNo)
		 ->where('Student.class','=',$class)
		 ->where('Student.isActive', '=', 'Yes');
		 //->first();
        //echo "<pre>";print_r($student->first());exit;
		if($student->count()>0) {
           $student = $student->first();

			$merit = DB::table('MeritList')
			->select('regiNo', 'grade', 'point', 'totalNo')
			->where('exam', $exam)
			->where('class', $class)
			->where('session', trim($student->session))
			//->where('regiNo',$regiNo)
			//->orderBy('point', 'DESC')
			//->orderBy('point')
			->orderBy('totalNo', 'DESC')->get();
			//->orderBy('totalNo', 'DESC')->get();
			//echo "<pre>";print_r($merit);exit;
			if (empty($student)  || empty($merit)) {
				return Redirect::back()->with('noresult', 'Result Not Found!');
			} else {
				$meritdata = new Meritdata();
				$position  = 0;
				foreach ($merit as $m) {
					$position++;
					if($m->regiNo === $regiNo) {
						$meritdata->regiNo = $m->regiNo;
						$meritdata->point = $m->point;
						$meritdata->grade = $m->grade;
						$meritdata->position = $position;
						$meritdata->totalNo = $m->totalNo;
						break;
					}
				}
              //echo "<pre>";print_r($merit);
              //print_r($meritdata);
             // exit;
				//sub group need to implement
				$subjects = Subject::select('name', 'code', 'subgroup', 'totalfull')->where('class', '=', $student->classcode)->get();

				$overallSubject = array();
				$subcollection = array();

				$banglatotal = 0;
				$banglatotalhighest = 0;
				$urdu = 0;
				$banglaArray = array();
				$blextra = array();

				$englishtotal = 0;
				$englishtotalhighest = 0;
				$english_total = 0;
				$englishArray = array();
				$enextra = array();

				$totalHighest = 0;
				$totalourall = 0;
				$isBanglaFail=false;
				$isEnglishFail=false;
				foreach ($subjects as $subject) {
					$submarks = Marks::select('written', 'mcq', 'practical', 'ca', 'total', 'point', 'grade')->where('regiNo', '=', $student->regiNo)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->where('class', '=', $class)->first();
					$maxMarks = Marks::select(DB::raw('max(total) as highest'))->where('class', '=', $class)->where('session', '=', $student->session)
					->where('subject', '=', $subject->code)->where('exam', '=', $exam)->first();

					$submarks["highest"] = $maxMarks->highest;
					$submarks["subcode"] = $subject->code;

					$submarks["subname"] = $subject->name;
					$submarks["outof"] = $subject->totalfull;


					if ($this->getSubGroup($subjects, $subject->code) === "Bangla") {

						if($submarks->grade=="F")
						{
							$isBanglaFail=true;
						}

						$banglatotal += $submarks->total;
						$banglatotalhighest += $submarks->highest;
                         $urdu += $subject->totalfull;
						$bangla = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical,$subject->totalfull);
						array_push($banglaArray, $bangla);

					} else if ($this->getSubGroup($subjects, $subject->code) === "English") {
						if($submarks->grade==="F")
						{
							$isEnglishFail=true;
						}
						$englishtotal += $submarks->total;
						$englishtotalhighest += $submarks->highest;
                        $english_total += $subject->totalfull;
						$english = array($submarks->subcode, $submarks->subname, $submarks->written, $submarks->mcq, $submarks->ca, $submarks->practical,$subject->totalfull);
						array_push($englishArray, $english);

					} else {
						$totalHighest += $maxMarks->highest;
						$totalourall +=$subject->totalfull;
						array_push($subcollection, $submarks);
					}
					$outof[] = $subject->totalfull;
				}
				$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
				$subgrpbl = false;

				if ($banglatotal > 0) {

					$blt = floor($banglatotal / 2);
					$totalHighest += $banglatotalhighest;
					$totalourall +=$urdu;
					$gcal = $this->gpaCalculator($blt, $gparules);

					$subgrpbl = true;
					array_push($blextra, $banglatotal);
					//array_push($blextra, $banglatotalhighest);
					array_push($blextra, $urdu);
                   // echo $gcal[1].'uuu';
					if($isBanglaFail)
					{
						array_push($blextra, "0.00");
						array_push($blextra, "F");
					}
					else {
						array_push($blextra, $gcal[0]);
						array_push($blextra, $gcal[1]);
					}
				}
				$subgrpen = false;
				if ($englishtotal > 0) {
					$ent = floor($englishtotal / 2);
					$totalHighest += $englishtotalhighest;
					$totalourall += $english_total;
					$gcal = $this->gpaCalculator($ent, $gparules);
					$subgrpen = true;
					array_push($enextra, $englishtotal);
					//array_push($enextra, $englishtotalhighest);
					array_push($enextra, $english_total);

					//echo $ent.'uuu'.print_r($gcal,true);
					//exit;
					if($isEnglishFail)
					{
						array_push($enextra, "0.00");
						array_push($enextra, "F");

					}
					else {
						array_push($enextra, $gcal[0]);
						array_push($enextra, $gcal[1]);

					}
				}


				$extra = array($exam_name, $subgrpbl, $totalHighest, $subgrpen, $student->extraActivity,$totalourall);
				$query="select left(MONTHNAME(STR_TO_DATE(m, '%m')),3) as month, count(regiNo) AS present from ( select 01 as m union all select 02 union all select 03 union all select 04 union all select 05 union all select 06 union all select 07 union all select 08 union all select 09 union all select 10 union all select 11 union all select 12 ) as months LEFT OUTER JOIN Attendance ON MONTH(Attendance.date)=m and Attendance.regiNo ='".$regiNo."' and  Attendance.status IN ('Present','present','late','Late') GROUP BY m";
				$attendance=DB::select(DB::RAW($query));
				//echo "<pre>";print_r($attendance);
				//exit;
				return View('app.stdgradesheet', compact('student', 'extra', 'meritdata', 'subcollection', 'blextra', 'banglaArray', 'enextra', 'englishArray','attendance'));

			}
		}
		else
		{
			//echo "<h1 style='text-align: center;color: red'>Result Not Found</h1>";
			return  Redirect::back()->with('noresult','Result Not Found!');

		}
	}


	public  function  getgenerate()
	{
		$classes = ClassModel::pluck('name','code');
		//return View::Make('app.resultgenerate',compact('classes'));
		return View('app.resultgenerate',compact('classes'));
	}

	public  function getSubGroup($subjects,$subject)
	{
		$group="";
		foreach($subjects as $sub)
		{
			if($sub->code===$subject)
			{
				$group=$sub->subgroup;
				break;

			}
		}
		return $group;
	}
	public  function getSubjectTotalno($subjects,$subject)
	{
		$total="";
		foreach($subjects as $sub)
		{
			if($sub->code===$subject)
			{
				$total=$sub->totalfull;
				break;
			}
		}
		return $total;
	}
	public  function  postgenerate()
	{
		$rules = [
			'class' => 'required',
			'exam' => 'required',
			'session' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			return Redirect::to('/result/generate')->withErrors($validator)->withInput();
		} else {
			$isGenerated=DB::table('MeritList')
			->select('regiNo')
			->where('class', '=', Input::get('class'))
			->where('session', '=', trim(Input::get('session')))
			->where('exam', '=', Input::get('exam'))
			->get();
			if(count($isGenerated)==0)
			{
				$subjects           = Subject::select('name', 'code', 'type', 'subgroup')->where('class', '=', Input::get('class'))->get();
				$sectionsHas        = Student::select('section')->where('class', '=', Input::get('class'))->where('session', trim(Input::get('session')))->where('isActive', '=', 'Yes')->distinct()->orderBy('section', 'asc')->get();

				$sectionMarksSubmit = Marks::select('section')->where('class', '=', Input::get('class'))->where('session', trim(Input::get('session')))->where('exam',Input::get('exam'))->distinct()->get();
				//dd($sectionsHas);

				if (count($sectionsHas)==count($sectionMarksSubmit))
				{
					$isAllSubSectionMarkSubmit =false;
					$notSubSection='';
					foreach ($sectionsHas as $section) {
						$marksubmit = Marks::select('subject')->where('class', '=', Input::get('class'))->where('section',$section->section)->where('session', trim(Input::get('session')))->where('exam',Input::get('exam'))->distinct()->get();

						if(count($subjects) == count($marksubmit))
						{
							$isAllSubSectionMarkSubmit = true;
							continue;
						}
						else{
							$notSubSection=$section->section;
							$isAllSubSectionMarkSubmit =false;
							break;
						}
					}

					if ($isAllSubSectionMarkSubmit) {
						$fourthsubjectCode = "";
						foreach ($subjects as $subject) {
							if ($subject->type === "Electives") {
								$fourthsubjectCode = $subject->code;
							}
						}


						$students = Student::select('regiNo')
						->join('section','Student.section','=','section.id')
						->select('Student.*','section.name')
						->where('Student.class', '=', Input::get('class'))
						->where('Student.session', '=', trim(Input::get('session')))
						->where('Student.isActive', '=', 'Yes')->get();
                      //  echo "<pre>";print_r($students->toArray());exit;
						if (count($students) != 0) {
							$marksSubmitStudents=Marks::select('Marks.regiNo')
							->join('Student', 'Marks.regiNo', '=', 'Student.regiNo')
							->where('Student.isActive', '=', 'Yes')
							->where('Student.class', '=', Input::get('class'))
							->where('Marks.class', '=', Input::get('class'))
							->where('Marks.session', '=', trim(Input::get('session')))
							->where('Marks.exam', '=', Input::get('exam'))
							->distinct()
							->get();

							if(count($students)==count($marksSubmitStudents))
							{
								$gparules = GPA::select('gpa', 'grade', 'markfrom')->get();
								$foobar = array();
								foreach ($students as $student) {
									$marks = Marks::select('subject', 'grade', 'point', 'total')->where('regiNo', '=', $student->regiNo)->where('exam', '=', Input::get('exam'))->get();

									$totalpoint = 0;
									$totalmarks = 0;
									$subcounter = 0;
									$banglamark = 0;
									$englishmark = 0;
									$isfail = false;
									foreach ($marks as $mark) {


										if ($this->getSubGroup($subjects, $mark->subject) === "Bangla") {
											$banglamark += $mark->total;

										} else if ($this->getSubGroup($subjects, $mark->subject) === "English") {
											$englishmark += $mark->total;
										} else {
											if ($mark->subject === $fourthsubjectCode) {
												if ($mark->point >= 2.00) {
													$totalmarks += $mark->total;
													$totalpoint += $mark->point - 2;


												} else {
													$totalmarks += $mark->total;
												}
												$subcounter--;

											} else {
												$totalmarks += $mark->total;
												$totalpoint += $mark->point;

											}

										}


										$subcounter++;

										if ($mark->subject !== $fourthsubjectCode && $mark->grade === "F") {
											$isfail = true;
										}
									}


									if ($banglamark > 0) {
										$blmarks = floor($banglamark / 2);


										$totalmarks += $banglamark;

										$totalpoint += $this->pointCalculator($blmarks, $gparules);

										$subcounter--;

									}


									if ($englishmark > 0) {
										$enmarks = floor($englishmark / 2);


										$totalmarks += $englishmark;

										$totalpoint += $this->pointCalculator($enmarks, $gparules);

										$subcounter--;

									}

									$grandPoint = ($totalpoint / $subcounter);


									if ($isfail) {
										$grandGrade = $this->gradnGradeCal(0.00, $gparules);
									} else {
										$grandGrade = $this->gradnGradeCal($grandPoint, $gparules);
									}

									$merit          = new MeritList;
									$merit->class   = Input::get('class');
									$merit->session = trim(Input::get('session'));
									$merit->exam    = Input::get('exam');
									$merit->regiNo  = $student->regiNo;
									$merit->totalNo = $totalmarks;
									$merit->point   = $grandPoint;
									$merit->grade   = $grandGrade;

                                // echo "<pre>";print_r($merit );
									$merit->save();

                                     $test[] = $merit;


								}

								 //echo "<pre>";print_r($test );
									//exit;

							}
							else {

								return Redirect::to('/result/generate')->withInput()->with("noresult", "All students examintaion marks not submited yet!!");
							}


						}
						else
						{


							return Redirect::to('/result/generate')->withInput()->with("noresult", "There is no students in this class!!");
						}

						return Redirect::to('/result/generate')->with("success", "Result Generate and Publish Successfull.");
					}
					else
					{
						return Redirect::to('/result/generate')->withInput()->with("noresult", "Section ".$notSubSection." all subjects marks not submited yet!!");

					}
				}
				else{
					return Redirect::to('/result/generate')->withInput()->with("noresult", "All sections marks not submited yet!!");
				}
			}
			else{
				return Redirect::to('/result/generate')->withInput()->with("noresult", "Result already generated for this class,session and exam!");

			}
		}
	}

	public function gradnGradeCal($grandPoint)
	{
		$grade="";
		if($grandPoint>=5.00)
		{
			$grade="A+";
			return $grade;
		}
		$lowarray   = array("0.00","1.00","2.00","3.00","3.50","4.00");
		$higharray  = array("1.00","2.00","3.00","3.50","4.00","5.00");
		$gradearray = array("F","D","C","B","A-","A");

		for($i = 0;$i < count($lowarray);$i++)
		{
			if($grandPoint >= $lowarray[$i] && $grandPoint<$higharray[$i])
			{
				$grade=$gradearray[$i];
			}
		}

		return $grade;
	}

	public function search()
	{
		$formdata = new formfoo;
		$formdata->exam="";
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.resultsearch',compact('formdata','classes'));
		return View('app.resultsearch',compact('formdata','classes'));
	}
	public function postsearch()
	{
		$rules=[

			'exam' => 'required',
			'regiNo' => 'required',
			'class' => 'required'


		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/result/search')->withErrors($validator)->withInput(Input::all());
		}
		else {


			return Redirect::to('/gradesheet/print/'.Input::get('regiNo').'/'.Input::get('exam').'/'.Input::get('class'));
		}
	}
	public function searchpub()
	{
		$formdata = new formfoo;
		$formdata->exam="";
		$classes = ClassModel::select('code','name')->orderby('code','asc')->get();
		//return View::Make('app.resultsearchpublic',compact('formdata','classes'));
		return View('app.resultsearchpublic',compact('formdata','classes'));
	}
	public function postsearchpub()
	{

		$rules=[
		 'exam' => 'required',
		 'regiNo' => 'required',
		 'class' => 'required'
		];
		$validator = \Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/results')->withErrors($validator)->withInput(Input::all());
		}
		else {


			return Redirect::to('/gradesheet/print/'.Input::get('regiNo').'/'.Input::get('exam').'/'.Input::get('class'));
		}
	}
}