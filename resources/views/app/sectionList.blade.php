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
        <h2><i class="glyphicon glyphicon-home"></i> Section List</h2>
      </div>
      <div class="box-content">
        <table id="classList" class="table table-striped table-bordered table-hover">
          <thead>
            <tr>
              <th style="width:30%">Name</th>
               <th style="width:30%">Class</th>
              <th style="width:30%">Description</th>
              <th style="width:30%">Students</th>
              <th style="width:30%">Teacher</th>
             
              <th style="width:15%">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sections as $section)

            <tr>
              <td>{{$section->name}}</td>
              <td>{{$section->class_code}}</td>
              <td>{{$section->description}}</td>
              <td>{{count_student($section->id,$section->class_code)}}</td>
              {{--<td>{{$section->students}}</td>--}}
              <td>{{$section->firstName}} {{$section->lastName}}</td>

              <td>
                <a title='Edit' class='btn btn-info' href='{{url("/section/edit")}}/{{$section->id}}'> <i class="glyphicon glyphicon-edit icon-white"></i></a>&nbsp&nbsp
                <a title='Delete' class='btn btn-danger' onclick="confirmed('{{$section->id}}')" href='#' > <i class="glyphicon glyphicon-trash icon-white"></i></a>&nbsp&nbsp
                <a title='view timetable' class='btn btn-success' href='{{url("/section/view-timetable")}}/{{$section->id}}'> <i class="glyphicon glyphicon-eye-open icon-white"></i></a>
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

function confirmed(section_id)
{
  //alert(family_id);
  //return confirm('Are you sure you want to generate family vouchar?');
  var x = confirm('Are you sure you want to delete this section');
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
  text: "If you delete this section students marks and timetable of this section disturb",
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

      window.location = "{{url('/section/delete')}}/"+section_id;
                              
    });
  } else if (
    // Read more about handling dismissals
    result.dismiss === Swal.DismissReason.cancel
  ) {
    swalWithBootstrapButtons.fire(
      'Cancelled',
      'Section Not Deleted :)',
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
