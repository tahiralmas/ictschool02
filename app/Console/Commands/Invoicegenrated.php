<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use App\FeeCol;
use App\FeeSetup;
use App\FeeHistory;
use App\Voucherhistory;
class Invoicegenrated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'Invoice:genrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
    */
    public function handle()
    {
        
        DB::table('Student')->where('isActive','Yes')->orderBy('id','Asc')->chunk(100, function($users)
        {
            $i=0;
            foreach ($users as $user)
            {
                  //echo '/n';
                 //echo $i++;
                // $test[$user->id] = $user->id;
               echo  $this->createvouchour($user->regiNo,$user->class,$user->discount_id);
               //echo "/n";
            }
            //echo $i;

            //echo count($test);
        });

         $now             =  Carbon::now();
         $year1           =  $now->year;
         $month           =  $now->month;
        
    }

    public function createvouchour($regiNo,$class,$discount)
    {
        

        try {
                    $fee_setup       = FeeSetup::select('fee','Latefee')
                                        ->where('class','=',$class)
                                        ->where('type','=','Monthly');
                                        //->get();

            
            if($fee_setup->count()>0){

                    $fee_setup       =   $fee_setup->first();
                    $now             =  Carbon::now();
                    $year1           =  $now->year;
                    $month           =  $now->month;
                    $date            =  $now->addDays(5);
                    //$month           =  2;
                    //$due_date        =  $now->addDays(10);
                    if($discount==NULL || $discount==''){
                        $discount = 0;
                    }else{
                        $discount = $discount;
                    }
                    $totalfee        = $fee_setup->fee - $discount;

                    $feeTitles       = 'monthly';
                    $feeAmounts      = $totalfee;
                    $feeLateAmounts  = 0;
                    $feeTotalAmounts = $totalfee;
                    $feeMonths       = $month ;
                    $month           = $month ; 
                    //$counter         = count($feeTitles);

                    //if($counter>0)
                    //{
                      $rows = FeeCol::count();
                        if($rows < 9)
                        {
                            $billId = 'B00'.($rows+1);
                        }
                        else if($rows < 100)
                        {
                            $billId = 'B0'.($rows+1);
                        }
                        else {

                         $billId = 'B'.($rows+1);
                        }

                        DB::transaction(function() use ($billId,$feeTitles,$feeAmounts,$feeLateAmounts,$feeTotalAmounts,$feeMonths,$date,$regiNo,$class,$totalfee,$fee_setup)
                        {
                            $j=0;
                           // for ($i=0;$i<$counter;$i++) {
                           //echo $regiNo;exit;
                            $chk = DB::table('stdBill')
                            ->join('billHistory','stdBill.billNo','=','billHistory.billNo')
                            ->where('stdBill.regiNo',$regiNo)
                            ->where('stdBill.paidAmount',0)
                            ->get();

                            /*$due = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0) as dueamount'))
                                    ->where('class',$class)
                                    ->where('regiNo',$regiNo)
                                    ->first();*/



                                    $due = FeeCol::select(DB::RAW('IFNULL(sum(payableAmount),0) as payTotal,IFNULL(sum(paidAmount),0) as paiTotal,(IFNULL(sum(payableAmount),0)- IFNULL(sum(paidAmount),0)) as dueamount'))
                                                ->where('class',$class)
                                                ->where('regiNo',$regiNo)
                                                ->first();

                                   // echo  $due->paiTotal;
                             $chk = DB::table('stdBill')
                                ->join('billHistory','stdBill.billNo','=','billHistory.billNo')
                                ->where('stdBill.regiNo',$regiNo)
                                ->where('billHistory.title','monthly')
                                ->where('billHistory.month', $feeMonths );



                            if($chk->count()==0){
                                 
                                 $chk_rows = DB::table('stdBill')
                                             ->where('stdBill.regiNo',$regiNo);
                                //echo '<pre>'.print_r($due->payTotal,true);
                                // exit;
                                 if($chk_rows->count()==0){
                                    echo 'ss'.$chk_rows->count();
                                    // exit;
                                    $due1  = $totalfee;
                                 }else{
                                     $due1 = $due->payTotal + $totalfee;
                                 }

                                $feehistory          = new FeeHistory();
                                $feehistory->billNo  = $billId;
                                $feehistory->title   = $feeTitles;
                                $feehistory->fee     = $feeAmounts;
                                $feehistory->lateFee = $feeLateAmounts;
                                $feehistory->total   = $feeTotalAmounts;
                                $feehistory->month   = $feeMonths;
                                $feehistory->save();

                                $voucharhistory           = new Voucherhistory();
                                $voucharhistory->bill_id  = $billId;
                                $voucharhistory->type     = $feeTitles;
                                $voucharhistory->ref_id   = '';
                                $voucharhistory->amount   = $feeAmounts;
                                $voucharhistory->due_date = $date->format('Y-m-d');
                                $voucharhistory->rgiNo    = $regiNo;
                                $voucharhistory->status   = 'unpaid';
                                $voucharhistory->date     =   Carbon::now();
                                $voucharhistory->save();
                                //$j++;
                                //}
                               // if($j>0){
                                $feeCol                = new FeeCol();
                                $feeCol->billNo        = $billId;
                                $feeCol->class         = $class;
                                $feeCol->regiNo        = $regiNo;
                                $feeCol->payableAmount = $totalfee;
                                $feeCol->total_fee     = $fee_setup->fee;
                                $feeCol->paidAmount    = 0;
                                //$feeCol->dueAmount     = $due1  ;
                                $feeCol->dueAmount     = $totalfee  ;
                                $feeCol->payDate       = $date->format('Y-m-d');
                                //$feeCol->payDate       = Carbon::now()->format('Y-m-d');
                                echo "<pre>";print_r(Carbon::now()->format('Y-m-d'));
                                $feeCol->save();
                            }
                            //\Session::put('not_save', $j);
                           /* }else{
                            \Session::put('not_save', 0);
                            }*/

                            /*for ($i=0;$i<$counter;$i++) {

                            $feehistory          = new FeeHistory();
                            $feehistory->billNo  = $billId;
                            $feehistory->title   = $feeTitles[$i];
                            $feehistory->fee     = $feeAmounts[$i];
                            $feehistory->lateFee = $feeLateAmounts[$i];
                            $feehistory->total   = $feeTotalAmounts[$i];
                            $feehistory->month   = $feeMonths[$i];
                            $feehistory->save();

                            }*/
                        });
                    }
                }
                catch(\Exception $e)
                {
                  //  print_r($e);
                 return $e->getMessage();
                //return Redirect::to('/fee/collection?class_id='.Input::get('class').'&section='.Input::get('section').'&session='.Input::get('session').'&type='.Input::get('type').'&month='.Input::get('gridMonth')[0].'&fee_name='.Input::get('fee'))->withErrors( $e->getMessage())->withInput();
                }
    }
}
