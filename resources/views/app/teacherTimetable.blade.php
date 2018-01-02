@extends('layouts.master')
@section('style')
    <link href="/css/bootstrap-datepicker.css" rel="stylesheet">
      <link href="/css/timetable.css" rel="stylesheet">
@stop
@section('content')
    @if (Session::get('success'))
        <div class="alert alert-success">
            <button data-dismiss="alert" class="close" type="button">×</button>
            <strong>Process Success.</strong> {{ Session::get('success')}}<br><a href="/teacher/list">View List</a><br>

        </div>
    @endif
     <form role="form" action="/teacher/create_timetable" method="post" enctype="multipart/form-data">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
        
          <div class="row">
            <div class="col-md-12">
              <h3 class="text-info"> Time Table</h3>
              <hr>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="fname">Teacher</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                    <select name="teacher" class="form-control" required>
                   <option vlaue="">---Select Teacher---</option>
                   
                    @foreach($teachers as $teacher)
                      <option value="{{$teacher->id}}">{{$teacher->firstName}} {{$teacher->lastName}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label" for="gender">Class</label>

                  <div class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                    <select name="class" class="form-control" required >

                      @foreach($classes as $class)
                      <option value="{{$class->id}}">{{$class->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label" for="section">Section</label>

                  <div class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                    <select name="section" class="form-control" required >
                      <option value="A">A</option>
                      <option value="B">B</option>
                      <option value="C">C</option>
                      <option value="D">D</option>
                      <option value="E">E</option>
                      <option value="F">F</option>
                      <option value="G">G</option>
                      <option value="H">H</option>
                      <option value="I">I</option>
                      <option value="J">J</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label" for="bloodgroup">Subject</label>

                  <div class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-info-sign blue"></i></span>
                    <select name="subject" class="form-control " required >
                      @foreach($subjects as $subject)
                      <option value="{{$subject->id}}">{{$subject->name}}</option>
                      @endforeach
                    </select>

                  </select>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="col-md-4">
              <div class="form-group">
                <label for="nationality">Start Time</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                  <input type="text" id="timepicker1" class="form-control" value="" required  name="startt">
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group ">
                <label for="dob">End Time</label>
                <div class="input-group">

                  <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i> </span>
                  <input type="text" id="timepicker2"  class="form-control datepicker" name="endt" required  data-date-format="">
                </div>


              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group ">
                <label for="photo">Day</label>
                <select name="day[]" class="form-control selectpicker" multiple data-hide-disabled="true" data-size="5">
                <option value="">---Select Day---</option>
                <option value="monday">Monday</option>
                <option value="tuesday">Tuesday</option>
                <option value="wednesday">Wednesday</option>
                <option value="thursday">Thursday</option>
                <option value="friday">Friday</option>
                <option value="saturday">Saturday</option>
                <option value="sunday">Sunday</option>
                </select>
              </div>
            </div>

          </div>
        </div>
        <div class="clearfix"></div>

        <div class="form-group">
          <button class="btn btn-primary pull-right" type="submit"><i class="glyphicon glyphicon-plus"></i>Add</button>
          <br>
        </div>
      </form>






   
@stop
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
<script>
 $('#timepicker1').timepicker();
  $('#timepicker2').timepicker();
  $('.selectpicker').selectpicker();
</script>
@stop