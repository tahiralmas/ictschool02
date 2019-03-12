@extends('layouts.master')
@section('style')
    <link href="{{url('/css/bootstrap-datepicker.css')}}" rel="stylesheet">

@stop
@section('content')
@if (Session::get('success'))
<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
    <strong>Process Success.</strong><br>{{ Session::get('success')}}<br>
</div>

@endif
@if (Session::get('error'))
    <div class="alert alert-warning">
        <button data-dismiss="alert" class="close" type="button">×</button>
        <strong> {{ Session::get('error')}}</strong>

    </div>
@endif
<div class="row">
<div class="box col-md-12">
        <div class="box-inner">
            <div data-original-title="" class="box-header well">
                <h2><i class="glyphicon glyphicon-book"></i> Family List</h2>

            </div>
            <div class="box-content">

                <div class="row">
                    <div class="col-md-12">

                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="clear: both;margin-top: 18px;" >
              <table id="studentList" class="table table-striped table-bordered" >
                                                         <thead>
                                                             <tr>
                                                                <th>Regi No</th>
                                                                 <th>Roll No</th>
                                                                 <th>Class</th>
                                                                 <th>section</th>
                                                                 <th>Name</th>
                                                                 <th>Gender</th>
                                                                  <th>Father Name</th>
                                                                   <th>Guardian's Contact</th>
                                                                 <th>Present Address</th>
                                                                  <th>Action</th>
                                                             </tr>
                                                         </thead>
                                                         <tbody>
                                                           @foreach($students as $student)
                                                             <tr>
                                                                  <td>{{$student->regiNo}}</td>
                                                                     <td>{{$student->rollNo}}</td>
                                                                     <td>{{$student->class}}</td>
                                                                     <td>{{$student->name}}</td>
                                                               <td>{{$student->firstName}} {{$student->middleName}} {{$student->lastName}}</td>
                                                               <td>{{$student->gender}}</td>
                                                                  <td>{{$student->fatherName}}</td>
                                                                  <td>   {!! "<b> Father:</b> ". $student->fatherCellNo. " <br \><b >Mother: </b>". $student->motherCellNo. $student->localGuardianCell !!}</td>
                                                                  <td>{{$student->presentAddress}}</td>
                                                       <td>
                                                  <a title='View' class='btn btn-success' href='{{url("/student/view")}}/{{$student->id}}'> <i class="glyphicon glyphicon-zoom-in icon-white"></i></a>&nbsp&nbsp<a title='Edit' class='btn btn-info' href='{{url("/student/edit")}}/{{$student->id}}'> <i class="glyphicon glyphicon-edit icon-white"></i></a>
                                                    &nbsp&nbsp<a title='Delete' class='btn btn-danger' href='{{url("/student/delete")}}/{{$student->id}}' onclick="return confirm('Are you sure you want to delete this Student?');"> <i class="glyphicon glyphicon-trash icon-white"></i></a>
                                                    &nbsp&nbsp <a title='View' class='btn btn-success' href='' onclick="window.open('{{url("/gradesheet?class=$student->class_code&section=$student->section&regiNo=$student->regiNo")}}','','width=1500','height=500'); 
                return false;"> <i class="glyphicon glyphicon-phone"></i></a>
                                                    <?php /*&nbsp&nbsp <a title='View' class='btn btn-success' href='{{url("/fee/collections?class_id=$student->class_code&section=$student->section_id&session=$student->session&type=Monthly&month=$month&fee_name=$fee_name")}}'> <i class="glyphicon glyphicon-phone"></i></a>
                                                               */ ?>
                                                               </td>
                                                           @endforeach
                                                           </tbody>
                                </table>
                        </div>
                    </div>
                                <br><br>


        </div>
    </div>
</div>
</div>
@stop
@section('script')
<script src="{{url('/js/bootstrap-datepicker.js')}}"></script>
<script type="text/javascript">
$( document ).ready(function() {
  //$('#studentList').dataTable();
    $('#studentList').DataTable( {
        //pagingType: "simple",
        //"pageLength": 5,
      //  "pagingType": "full_numbers",
        dom: 'Bfrtip',
        buttons: [
            'print'
        ],
         "sPaginationType": "bootstrap",
       
    });
  
    //console.log(data);

     
});
</script>
@stop
