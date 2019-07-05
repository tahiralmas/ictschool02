@extends('layouts.master')
@section("style")
<link href="{{ URL::asset('/css/custom.min.css')}}" rel='stylesheet'>
<link href="{{ URL::asset('/font-awesome/css/font-awesome.min.css')}}" rel='stylesheet'>
<link href="{{ URL::asset('/css/theme1.css')}}" rel="stylesheet" media="all">
<link href="{{url('/css/bootstrap-datepicker.css')}}" rel="stylesheet">
<style>
.fc-today{
  background-color: #2AA2E6;
  color:#fff;


}
.fc-button-today
{
  display: none;
}
.green{
  color: #1ABB9C;
}
.homepage-box {
    height: auto !important;
}

</style>
@stop
@section('content')
@if (Session::get('accessdined'))
<div class="alert alert-danger">
  <button data-dismiss="alert" class="close" type="button">×</button>
  <strong>Process Faild.</strong> {{ Session::get('accessdined')}}

</div>
@endif
@if (Session::get('success'))
<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
  <strong>Process Success.</strong> {{ Session::get('success')}}<br>

</div>
@endif

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">


 @if(Auth::user()->group!='Director')

<!----------------------- MOnth OR year wise filter --------------------- -->

        <form role="form" id="defulter" name="defulter"  method="get" >

          <div class="row">
            <div class="col-md-12">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="control-label" for="month">Month</label>

                    <div class="input-group">
                      <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                      <?php  $data=[
                      '1'=>'January',
                      '2'=>'February',
                      '3'=>'March',
                      '4'=>'April',
                      '5'=>'May',
                      '6'=>'June',
                      '7'=>'July',
                      '8'=>'August',
                      '9'=>'September',
                      '10'=>'October',
                      '11'=>'November',
                      '12'=>'December'
                      ];?>
                      {{ Form::select('month',$data,$month,['class'=>'form-control','id'=>'month','required'=>'true'])}}
                    </div>
                  </div>
                </div>
              {{--<div class="col-md-4">
                <div class="form-group ">
                  <label for="session">session</label>
                  <div class="input-group">

                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
                    <input  value="{{date('Y')}}" type="text" id="session" required="true" class="form-control datepicker2" name="session"   data-date-format="yyyy" value="{{$session}}">
                  </div>
                </div>
              </div>--}}
               <div class="col-md-4">
                <div class="form-group ">
                  <label for="session">Year</label>
                  <div class="input-group">

                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i> </span>
                    <input  type="text" value="" id="yeard" required="true" class="form-control datepicker2" name="year"   data-date-format="yyyy" >
                  </div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label class="control-label" for="">&nbsp;</label>

                  <div class="input-group">
                    <button class="btn btn-primary pull-right" id="btnsave" type="submit"><i class="glyphicon glyphicon-th"></i> Get List</button>

                  </div>
                </div>
              </div>



            </div>
          </div>
          </form>

<!----------------------- End ------------------------------------------- -->


<div class="row m-t-25">
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/class/list')}}">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['class']}}</h2>
                                                <span>Classes</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart1"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/student/list')}}">
                                <div class="overview-item overview-item--c2">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-shopping-cart"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['student']}}</h2>
                                                <span>Students</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart2"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                               <a href="{{url('/attendance_detail?action=absent')}}">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-calendar-note"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['totalabsent']}}</h2>
                                                <span>Absent Students</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart3"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{url('/attendance_detail?action=late')}}">
                                <div class="overview-item overview-item--c4">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-money"></i>
                                            </div>
                                            <div class="text">
                                                <h2>{{$total['totallate']}}</h2>
                                                <span>Late Students</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                            <canvas id="widgetChart4"></canvas>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                             @if(Auth::user()->group=='Admin')
                            
                            @endif
                        </div>
    </div>

    </div>

   
     <div class="row">
            <div class="col-md-6">
                <div class="au-card recent-report">
                <div class="box box-primary">
                    <div class="box-body">
                        <!-- THE CALENDAR -->
                        <div id="calendar"></div>
                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
            </div>
            <div class="col-md-6">
            <div class="au-card recent-report">
            @if(request()->getHttpHost()=='localhost' || request()->getHttpHost()=='school.ictcore.org')
            <a href='{{url("attendance/today_delete")}}' class="btn btn-danger">Clear today attendance</a>
            @endif
                <div class="box box-info">
                    <div class="box-body" style="max-height: 342px;">
                        <canvas id="attendanceChart" style="width: 400px; height: 150px;"></canvas>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            </div>
        </div>
       
       
 
     <?php /* <div class="col-md-6 col-sm-6 col-xs-6">
         <h2>Attendance Detail  <small> today</small></h2>
         <table id="feeList" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Class</th>
                  <th>Number of Student</th>
                  <th>Total Attendance</th>
                  <th>Number of Paresnt</th>
                  <th>Number of Absent</th>
                  <th>Number of Leaves</th>
                  <th>Action</th>
                
                </tr>
              </thead>
              <tbody>
              <?php $i=0; 
              //echo "<pre>".$i;print_r($scetionarray);
              //exit;
              ?>
              @foreach($attendances_b as $attendance)
               
                <tr>
                  <td>{{$attendance['class']}}</td>
                  <td>{{$attendance['total_student']}}</td>
                  <td>{{$attendance['total_attendance']}}</td>
                  <td>{{$attendance['present']}}</td>
                  <td>{{$attendance['absent']}}</td>
                  <td> @if($attendance['leaves']==''){{  0 }} @else {{ $attendance['leaves'] }} @endif </td>
                  <td></td>
                 
                </tbody>
                <?php $i++; ?>
                @endforeach
              </table>
      </div> */ ?>
      </div>
      @endif
 



    <!-- /top tiles -->
    <!-- Graph start -->
   
@stop
@section("model")

@stop
@section("script")
<script src="{{url('/js/Chart.min.js')}}"></script>
<script src="{{url('/js/bootstrap-datepicker.js')}}"></script>
<script script type="text/javascript">
 
  $(document).ready(function () {

        $(".datepicker2").datepicker( {
              format: " yyyy", // Notice the Extra space at the beginning
              viewMode: "years",
              minViewMode: "years",
              autoclose:true

            }).on('changeDate', function (ev) {

              //getstudents();

            });
        $('#calendar').fullCalendar({
        header: {
            left: 'prev,next',
            center: 'title',
        },
        today: 'true',
        height: 300,
   <?php if($json_event_data!=''){ ?>
    events: /*[
    {
      title  : 'event1',
      start  : '2018-10-01'
    },
    {
      title  : 'event2',
      start  : '2018-10-05',
      end    : '2018-10-07'
    },
    {
      title  : 'event3',
      start  : '2018-10-09T12:30:00',
      allDay : false // will make the time show
    }
  ]*/
  <?php echo $json_event_data;
   }
   ?>
 
    });

           var ctx = document.getElementById('attendanceChart').getContext('2d');
            //var attendanceChart = new Chart(ctx, config);
            var myChart = new Chart(ctx, {
    type: 'line',
    data: {
          labels: ["<?php echo join($class, '","')?>"],
        datasets: [{
                    label: 'Present',
                    data: ["<?php echo join($present, '","')?>"],
                    backgroundColor:  "rgb(54, 162, 235)",
                    borderColor:  "rgb(54, 162, 235)",
                    fill: false,
                    pointRadius: 6,
                    pointHoverRadius: 20,
                }, {
                    label: 'Absent',
                    data: ["<?php echo join($absent, '","')?>"],
                    backgroundColor: "rgb(255, 99, 132)",
                    borderColor: "rgb(255, 99, 132)",
                    fill: false,
                    pointRadius: 6,
                    pointHoverRadius: 20,

                }
                ]
            },
    options: {
      responsive: true,
       hover: {
                    mode: 'index'
                },
        scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Class'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Attendace'
                        }
                    }]
                },
                title: {
                    display: true,
                    text: 'Students Today\'s Attendance'
                }
    }
});
        });
  



Chart.defaults.global.legend = {
  enabled: false
};
// Line chart
   var ctx = document.getElementById("lineCharttest");
   var lineCharttest = new Chart(ctx, {
     type: 'line',
     data: {
       labels: ["<?php echo join($incomes['key'], '","')?>"],
       datasets: [{
         label: "Income",
         backgroundColor: "rgba(38, 185, 154, 0.31)",
         borderColor: "rgba(38, 185, 154, 0.7)",
         pointBorderColor: "rgba(38, 185, 154, 0.7)",
         pointBackgroundColor: "rgba(38, 185, 154, 0.7)",
         pointHoverBackgroundColor: "#fff",
         pointHoverBorderColor: "rgba(220,220,220,1)",
         pointBorderWidth: 1,
         data: [<?php echo join($incomes['value'], ',')?>]
       }, {
         label: "Expence",
         backgroundColor: "rgba(3, 88, 106, 0.3)",
         borderColor: "rgba(3, 88, 106, 0.70)",
         pointBorderColor: "rgba(3, 88, 106, 0.70)",
         pointBackgroundColor: "rgba(3, 88, 106, 0.70)",
         pointHoverBackgroundColor: "#fff",
         pointHoverBorderColor: "rgba(151,187,205,1)",
         pointBorderWidth: 1,
         data: [<?php echo join($expences['value'], ',')?>]
       }]
     },
     options: {
          responsive: true
          
          }
          

        
   });


  
</script>
@stop
