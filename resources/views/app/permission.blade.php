@extends('layouts.master')
@section('content')
   <link type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" />
@if (Session::get('success'))

<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
    <strong>Process Success.</strong> {{ Session::get('success')}}<br><br>

</div>
@endif
<?php 
/*$permission_fields = array(
  'Student view',
  'Student Update',
  'Student Delete',
  'Add Student Attendance',
  'View Student Attendance',
  'View Student Monthly Reports',
  'Add Marks',
  'View Marks',
  'Delete Marks',
  'Generate Result',
  'Search Result',
  'promote Student',
  'Add Fess',
  'View Fess',
  'Delete Fess',
  'View Fess Report',
  'View Result Reports',
  'View Attendance Reports',
  'View Sms/voice log Reports',
  //'View Student Monthly Reports',
  'Class View',
  'Class Add',
  'Class update',
  'Class delete',
  'Sections view',
  'Section add',
  'Section update',
  'Section View',
  'Teacher View',
  'Teacher Add',
  'Teacher update',
  'Teacher delete',
  'Teacher timetable add',
  'Teacher timetable view',
  'Send Sms/Voice',
  'Setting GPA Rule view',
  'GPA Rule add',
  'GPA Rule update',
  'GPA Rule delete',
  'holidays add',
  'holidays view',
  'holidays delete',
  'Class off view',
  'Class off add',
  'Class off delete',
  'Institute information add',
  'Grade system (auto,manual)',

  );*/
$permission_fields = array(
          'Student View',
          'Student Add',
          'Student Update',
          'Student Delete',
          'Student Info',
          'Student Student Portal Access',
          'Student Student Bulk Add',
          'Family',
          'Add Student Attendance',
          'View Student Attendance',
          'View Student Monthly Reports',
          'Add Marks',
          'View Marks',
          'Delete Marks',
          'Generate Result',
          'Search Result',
          'promote Student',
          'Add Fess',
          'View Fess',
          'Update Fess',
          'Delete Fess',
          'View Fess Report',
          'View Result Reports',
          'View Attendance Reports',
          'View Sms/voice log Reports',
          //'View Student Monthly Reports',
          'Class View',
          'Class Add',
          'Class update',
          'Class delete',
          'Section View',
          'Section add',
          'Section update',
          'Section Delete',
          'Section Time Table',
          'Teacher View',
          'Teacher Add',
          'Teacher Bulk Add',
          'Teacher update',
          'Teacher delete',
          'Teacher timetable add',
          'Teacher timetable view',
          'Teacher Portal Access',
          'Send Sms/Voice',
          'Setting GPA Rule view',
          'GPA Rule add',
          'GPA Rule update',
          'GPA Rule delete',
          'GPA Rule View',
          'holidays add',
          'holidays view',
          'holidays delete',
          'Class off view',
          'Class off add',
          'Class off delete',
          'Institute information add',
          'Grade system (auto,manual)',
          'Subject View',
          'Subject Add',
          'Subject update',
          'Subject delete',
          'Exam View',
          'Exam Add',
          'Exam update',
          'Exam delete',
          'Gradesheet View',
          'Gradesheet Print',
          'Send Notification',
          'Paper View',
          'Paper Add',
          'Paper update',
          'Paper delete',
        );

//echo "<pre>";print_r($permission_field);exit;
?>
<div class="row">
<div class="box col-md-12">
        <div class="box-inner">
            <div data-original-title="" class="box-header well">
                <h2><i class="glyphicon glyphicon-th"></i>Permissions Setting</h2>

            </div>
             <div class="box-content">
               <div class="container">
<div id="user-permissions">
   <form role="form" action="{{url('/permission/create')}}" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

<table style="width:100%" class="table table-bordered">
  <thead>
    <tr>
      <th>Permissions</th>
      <th>Admin</th>
      <th>Teachers</th>
      <th>Students</th>
    </tr>
  </thead>
   <tbody>
   <?php 
   $i = 0 ;
   $student = count($permission_fields);
   $teacher =  $student  + count($permission_fields);
   ?>
    @foreach($permission_fields as $permission_field)
    <?php $field_name = str_replace(" ","_",strtolower($permission_field)); 
    ?>
    @if($permissions)
    <tr>
      <td>{{$permission_field}}</td>
      @if($permissions[$i]->permission_group=='admin')
      <td>
        <div class="btn-group btn-toggle">
            <input class="chb" data-toggle="toggle" id="admin_{{$field_name}}" data-on="Yes" data-off="No" data-width="100"   name="admin[{{$field_name}}]" data-onstyle="success" data-offstyle="danger" type="checkbox"  @if($permissions[$i]->permission_type=='yes') checked @endif  >                                            
        </div>
      </td>
      @endif
       @if($permissions[$teacher]->permission_group=='teacher')
      <td>
        <div class="btn-group btn-toggle">
          <input class="chb" data-toggle="toggle" id="teacher_{{$field_name}}" data-on="Yes" data-off="No" data-width="100"   name="teacher[{{$field_name}}]" data-onstyle="success" data-offstyle="danger" type="checkbox"   @if($permissions[$teacher]->permission_type=='yes') checked @endif >                                            
        </div>

        </div>
      </td>
      @endif
       @if($permissions[$student]->permission_group=='student')
      <td>
        <div class="btn-group btn-toggle">
          <input class="chb" data-toggle="toggle" id="student_{{$field_name}}" data-on="Yes" data-off="No" data-width="100"   name="student[{{$field_name}}]" data-onstyle="success" data-offstyle="danger" type="checkbox" @if($permissions[$student]->permission_type=='yes') checked @endif >                                            
        </div>
      </td>
      @endif
    </tr>
    <?php 
   $i++ ;
   $student++ ;
   $teacher++;
   ?>
   @else

    <tr>
      <td>{{$permission_field}}</td>
      
      <td>
        <div class="btn-group btn-toggle">
            <input class="chb" data-toggle="toggle" id="admin_{{$field_name}}" data-on="Yes" data-off="No" data-width="100"   name="admin[{{$field_name}}]" data-onstyle="success" data-offstyle="danger" type="checkbox"  >                                            
        </div>
      </td>
     
      <td>
        <div class="btn-group btn-toggle">
          <input class="chb" data-toggle="toggle" id="teacher_{{$field_name}}" data-on="Yes" data-off="No" data-width="100"   name="teacher[{{$field_name}}]" data-onstyle="success" data-offstyle="danger" type="checkbox"    >                                            
        </div>

        </div>
      </td>
      
      <td>
        <div class="btn-group btn-toggle">
          <input class="chb" data-toggle="toggle" id="student_{{$field_name}}" data-on="Yes" data-off="No" data-width="100"   name="student[{{$field_name}}]" data-onstyle="success" data-offstyle="danger" type="checkbox" >                                            
        </div>
      </td>
      
    </tr>
   @endif
    @endforeach
    
  </tbody>
</table>
  </div>
</div>
<!--button save -->
        <div class="row">
         <div class="col-md-12">
           <button class="btn btn-primary pull-right" id="btnsave" type="submit"><i class="glyphicon glyphicon-plus"></i>Save</button>
             </form>

            <div id="push"></div>
        

           
        </div>
        </div>
@stop
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
<script>
$( document ).ready(function() {
   //$('#timepicker1').timepicker();
    $('#timepicker').timepicker({
        timeFormat: 'HH:mm:ss',
    });

            $('#timepicker1').timepicker();
    
});
</script>
@stop
