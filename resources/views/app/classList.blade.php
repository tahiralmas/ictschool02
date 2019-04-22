@extends('layouts.master')
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
  <strong>Whoops.</strong><br>{{ Session::get('error')}}<br>
</div>

@endif
<div class="row">
  <div class="box col-md-12">
    <div class="box-inner">
      <div data-original-title="" class="box-header well">
        <h2><i class="glyphicon glyphicon-home"></i> Class List</h2>
      </div>
      <div class="box-content">
        <table id="classList" class="table table-striped table-bordered table-hover">
          <thead>
            <tr>
              <th style="width:20%">Code</th>
              <th style="width:30%">Name</th>
              <th style="width:30%">Description</th>
              <th style="width:5%">Students</th>
              <th style="width:15%">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($Classes as $class)

            <tr>
              <td>{{$class->code}}</td>
              <td>{{$class->name}}</td>
              <td>{{$class->description}}</td>
              <td>{{count_student('',$class->code)}}</td>
              {{--<td>{{$class->students}}</td>--}}

              <td>
                <a title='Edit' class='btn btn-info' href='{{url("/class/edit")}}/{{$class->id}}'> <i class="glyphicon glyphicon-edit icon-white"></i></a>&nbsp&nbsp
                <a title='Delete' class='btn btn-danger' href='#' onclick="confirmed('{{$class->id}}')"> <i class="glyphicon glyphicon-trash icon-white"></i></a>&nbsp&nbsp
                <a title='View Diary' class='btn btn-warning' href='{{url("/class/diary/")}}/{{$class->code}}'> <i class="glyphicon glyphicon-zoom-in"></i></a>

              </td>
              @endforeach
            </tbody>
          </table>
          <br><br>


        </div>
      </div>
    </div>
  </div>
  @stop
  @section('script')
              <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

  <script type="text/javascript">
  $( document ).ready(function() {
    $('#classList').dataTable({

       "sPaginationType": "bootstrap",
    });
  });


function confirmed(class_id)
{
  //alert(family_id);
  //return confirm('Are you sure you want to generate family vouchar?');
  var x = confirm('Are you sure you want to delete this Class');
                if (x){
                   //window.location.href('{{url("/family/vouchars")}}/'+family_id);
                 // window.location = "{{url('/subject/delete')}}/"+subject_id;
                  // $("#billDetails").modal('show');
                  const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: 'btn btn-success',
    cancelButton: 'btn btn-danger'
  },
  buttonsStyling: false,
})

swalWithBootstrapButtons.fire({
  title: 'Are you sure?',
  text: "If you delete this Class students marks and timetable of this Class disturb",
  type: 'warning',
  showCancelButton: true,
  confirmButtonText: 'Yes, delete it!',
  cancelButtonText: 'No, cancel!',
  reverseButtons: true
}).then((result) => {
  if (result.value) {
    swalWithBootstrapButtons.fire(
      'Deleted!',
      'Your file has been deleted.',
      'success'
    ).then(function() {

      window.location = "{{url('/class/delete')}}/"+class_id;
                              
    });
  } else if (
    // Read more about handling dismissals
    result.dismiss === Swal.DismissReason.cancel
  ) {
    swalWithBootstrapButtons.fire(
      'Cancelled',
      'Class Not Deleted :)',
      'error'
    )
  }
})
                 return true
               }
                else{
                  return false;
                }
}
  </script>
  @stop
