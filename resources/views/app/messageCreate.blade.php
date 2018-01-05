@extends('layouts.master')
@section('content')
@if (Session::get('success'))

<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
    <strong>Process Success.</strong> {{ Session::get('success')}}<br><br>

</div>
@endif
<div class="row">
<div class="box col-md-12">
        <div class="box-inner">
            <div data-original-title="" class="box-header well">
                <h2><i class="glyphicon glyphicon-th"></i> Message Create</h2>

            </div>
            <div class="box-content">
                <ul class="nav nav-tabs" id="myTab">
                    <li class="active"><a href="#email">Voice</a></li>
                    <li><a href="#sms">SMS</a></li>
                </ul>

                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane active" id="email">
                        <form role="form" action="/message" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <br >
                                <div class="form-group col-md-12 row">
                                    <label for="name"  class="col-sm-2 col-form-label">Role</label>
                                    <div class="input-group col-md-6">
                                        <select name="role" id="role" class="form-control selectpicker" tabindex="-1">
                                            <option value="">Select Users Type</option>
                                            <option value="student">Student</option>
                                            <option value="teacher">Teacher</option>
                                             <option value="parent">Parent</option>
                                        </select>
                                    </div>
                                </div>
                             <div id="studen" >
                                <div class="form-group row" id="class" >
                                    <label for="name"  class="col-sm-2 col-form-label">Class</label>
                                    <div class="input-group col-md-6">
                                        <select  name="class[]" class="form-control selectpicker" multiple="" data-hide-disabled="true"  data-actions-box="true" data-size="5" tabindex="-98">
                                            <option value="">Select Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{$class->code}}">{{$class->name }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row" id="class" >
                                    <label for="name"  class="col-sm-2 col-form-label">Section</label>
                                    <div class="input-group col-md-6">
                                        <select  name="section[]" class="form-control selectpicker" multiple="" data-hide-disabled="true" data-actions-box="true" data-size="5" tabindex="-99">
                                             <option value="">Select Sections</option>
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



                              <div class="form-group row" id="class" >
                                <label for="name"  class="col-sm-2 col-form-label">Message Title</label>
                                <div class="input-group col-md-6">
                                    <input  name="mess_name" required class="form-control">

                                </div>

                            </div>



                                <div class="form-group row">
                                    <label for="name" class="col-sm-2 col-form-label">Message</label>
                                    <div class="input-group col-md-6">

                                     <select  name="message" class="form-control selectpicker"  data-hide-disabled="true" data-actions-box="true" data-size="5" tabindex="-99">
                                            <option value="">Select Message</option>
                                    @foreach($messages as $message)
                                        <option value="{{$message->id}}">{{$message->name}}</option>
                                    @endforeach


                                            
                                    </select>                                   
                                 </div>
                                </div>

                                
                                <div class="clearfix"></div>
                                @if (count($errors) > 0)
                                      <div class="alert alert-danger">
                                          <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                          <ul>
                                              @foreach ($errors->all() as $error)
                                                  <li>{{ $error }}</li>
                                              @endforeach
                                          </ul>
                                      </div>
                                @endif
                                    <div class="form-group">
                                        <button class="btn btn-primary pull-right" type="submit"><i class="glyphicon glyphicon-plus"></i>Add</button>
                                        <br>
                                    </div>
                         </form>
                    </div>




                    <div class="tab-pane" id="sms">
                        <h3>Custom
                            <small>small text</small>
                        </h3>
                        <p>Sample paragraph.</p>

                        <p>Your custom text.</p>
                    </div>

                </div>
            </div>
        </div>

</div>
</div>
<script>

$(document).ready(function()
{
    $("#role").change(function()
    {
        var id=$(this).val();
        //var dataString = 'id='+ id;
       // alert(id);
         if(id=='teacher'){
            $("#studen").hide();
        }else{
         $("#studen").show();
        }

    });
});
</script>
@stop

