@extends('layouts.master')
@section('style')
<link href="{{url('/css/bootstrap-datepicker.css')}}" rel="stylesheet">
<style>
#billItem thead th {
  color:#3986AC;
}
</style>
@stop
@section('content')
@if (Session::get('success'))
<div class="alert alert-success">
  <button data-dismiss="alert" class="close" type="button">×</button>
  <strong>Process Success.</strong> {{ Session::get('success')}}<br>

</div>
@endif
@if (count($errors) > 0)
<div class="alert alert-danger">
  <strong>Whoops!</strong> There were some problems.<br><br>
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif
<div class="row">
  <div class="box col-md-12">
    <div class="box-inner">
      <div data-original-title="" class="box-header well">
        <h2><i class="glyphicon glyphicon-list"></i> Vouchar List</h2>

      </div>
      <div class="box-content">

       
        @if($student->regiNo !="" && count($fees) < 1)
        <div class="alert alert-danger">
          <strong>Whoops!</strong> There are no fees entry for this student.<br><br>
        </div>
        @endif
        @if($fees)
        <div class="row">
          <div class="col-md-12">
          <a title='vouchar' class='btn btn-warning'  onclick="confirmed('{{$family_id}}');" href='#' style="float:right;margin-top:-30px;"> Get Voucher</a>
            <table id="feeList" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Payable Amount</th>
                  <th>Paid Amount</th>
                  <th>Due Amount</th>
                  <th>Status</th>
                  <th>Month</th>
                  <th>Pay Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($family_vouchers as $fee)
                <tr>
                  
                  <td>{{getdatainvoice($fee->bills,$fee->month)->payTotal}}</td>
                  <td>{{getdatainvoice($fee->bills,$fee->month)->paiTotal}}</td>
                  <td>{{getdatainvoice($fee->bills,$fee->month)->dueamount}}</td>
                  {{--<td>{{$fee->dueAmount}}</td>--}}
                   <td>
                   <?php
                    $paytotal  = getdatainvoice($fee->bills,$fee->month)->payTotal;
                    $paidtotal = getdatainvoice($fee->bills,$fee->month)->paiTotal;
                    $dueamount = getdatainvoice($fee->bills,$fee->month)->dueamount;
                      if($dueamount=="0.00" || $dueamount=="0"){
                          $status = 'paid';
                      }elseif($paidtotal=='0.00' ||$paidtotal=='' || $paidtotal==0){

                            $status = 'unpaid';
                      }else{
                          $status = 'partially paid';
                      }
                      ?>
                   @if($status=='unpaid')
                   <button  class="btn btn-danger" >UnPaid</button>
                   @elseif($status=='paid') 
                   <button  class="btn btn-success" >Paid</button>
                   @else
                   <button  class="btn btn-warning" >Partially Paid</button>
                   @endif
                   </td>
                  <td>{{ \DateTime::createFromFormat('!m', $fee->month)->format('F')}}</td>
                  <td>{{$fee->date}}</td>

                  <td>
                    



                   @if($fee->status=='Unpaid')
                    <a title='Paid' href="#" onclick="submitfrom('paid','{{$fee->id}}')" class='btn btn-success'> Paid</a>
                    <form  id="fee_paid{{$fee->id}}" action='{{url("/family/paid")}}/{{$fee->id}}' method="post">
                    @else
                    <a title='unPaid'  href="#" class='btn btn-danger' onclick="submitfrom('unpaid','{{$fee->id}}')"  > UnPaid</a>
                    <form id="fee_unpaid{{$fee->id}}" action='{{url("/family/paid")}}/{{$fee->id}}?s=unpaid' method="post">

                    @endif
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="bills" value="{{$fee->bills}}">

                    </form>
                  </td>
                  @endforeach
                </tbody>
              </table>
            </div>

          </div>
          <div class="row">
            <div class="col-md-12">
              <table class="table">

                <tbody>

                  <tr>
                    <td></td>
                    <td>Total Payable: <strong><i class="blue">{{$totals->payTotal }}</i></strong> rs.</td>
                    <td>Total Paid: <strong><i class="blue">{{$totals->paiTotal}}</i></strong> rs.</td>
                    <td>Total Due: <strong><i class="blue">{{$totals->dueamount}}</i></strong> rs.</td>
                    <td></td>

                    {{--<td>
                      <a title='Print' id="btnPrint" class='btn btn-info' target='_blank' href='{{url("/fees/report/std")}}/{{$student->regiNo}}'> <i class="glyphicon glyphicon-print icon-red"></i> Print</a>
                    </td>--}}
                  </tr>
                  </tbody>  
                </table>
              </div>

            </div>
            @endif
          </div>
        </div>
      </div>
      @stop
      @section('model')
          <div id="modelshow"></div>
      @stop
    <!-- Modal Goes here -->
     {{--<div id="billDetails" class="modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title">Confirmation</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="table-responsive">
                    <table id="billItem" class="table table-striped table-bordered table-hover">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Month</th>
                          <th>Fee</th>
                          <th>Late Fee</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tbody>
                        </table>
                      </div>
                    </div>

                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                </div>
              </div>
            </div>
          </div>--}}
          @section('script')
          <script src="{{url('/js/bootstrap-datepicker.js')}}"></script>
          <script type="text/javascript">

              function checkm(type){
                //alert(type);
                if(type=='multi'){

                  $("#multis").show();
                }else{
                   $("#multis").hide();
                }
              }

              function confirmed(family_id)
              {
                //alert(family_id);
                //return confirm('Are you sure you want to generate family vouchar?');
                var x = confirm('Are you sure you want to generate family vouchar?');
                              if (x){
                                 //window.location.href('{{url("/family/vouchars")}}/'+family_id);
                                //window.location = "{{url('/family/vouchars')}}/"+family_id;
                                
                                $.ajax({
                                    url: "{{url('/f_vouchar/model')}}"+'/'+family_id,
                                    data: {
                                      //format: 'json'
                                    },
                                    error: function(error) {
                                      alert("Please fill all inputs correctly!");
                                    },
                                    //dataType: 'json',
                                    success: function(data) {
                                      console.log(data);
                                     $('#modelshow').html(data);
                                      $("#myModal"+family_id).modal('show');

                                    },
                                    type: 'GET'
                                });
                                // $("#billDetails").modal('show');
                          

                               return true
                             }
                              else{
                                return false;
                              }
              }

          function submitfrom(type,id){
            if(type=='unpaid'){

              var x = confirm("Are you sure you want to Unpaid this vouchar?");
                if (x){
                   document.getElementById('fee_unpaid'+id).submit();
                 return true
               }
                else{
                  return false;
                }
              }
           /// document.getElementById('fee_unpaid').submit();
            else{
              var x = confirm("Are you sure you want to Paid this vouchar?");
                  if (x){
                      document.getElementById('fee_paid'+id).submit();
                  }else{
                    return false;
                  }
                }
                          
            }
            
          
          var stdRegiNo="{{$student->regiNo}}";
          $( document ).ready(function() {

            getsections();
            $('#class').on('change',function() {
              getsections();
            });
            $('#feeList').dataTable({
               "sPaginationType": "bootstrap",
            });
            //var session = $('#session').val().trim();
              //getstudents();
            $(".datepicker2").datepicker( {
              format: " yyyy", // Notice the Extra space at the beginning
              viewMode: "years",
              minViewMode: "years",
              autoclose:true

            }).on('changeDate', function (ev) {

              //getstudents();

            });
            $('#class').change(function () {
              //getstudents();
            });
            $('#section').change(function () {
             // getstudents();
            });
            $('#shift').change(function () {
             // getstudents();
            });
            $('#student option').filter(function() {
              return ($(this).val() == stdRegiNo); //To select Blue
            }).prop('selected', true);

            $(".btnbill").click(function(){
              var billId=$(this).text();
              $('.modal-title').html('"'+billId+'" bill details information');
              $.ajax({
                url: "{{url('/fees/details/')}}"+'/'+billId,
                data: {
                  format: 'json'
                },
                error: function(error) {
                  alert("Please fill all inputs correctly!");
                },
                dataType: 'json',
                success: function(data) {
                  //console.log(data);
                  $("#billItem").find("tr:gt(0)").remove();
                  for(var i =0;i < data.length;i++)
                  {
                    addRow(data[i],i);
                  }

                },
                type: 'GET'
              });
              $("#billDetails").modal('show');
            });
          });
          /*function getstudents()
          {
            var aclass = $('#class').val();
            var section =  $('#section').val();
            var shift = 'Morning';
            var session = $('#session').val().trim();
            $.ajax({
              url: "{{url('/student/getList')}}"+'/'+aclass+'/'+section+'/'+shift+'/'+session,
              data: {
                format: 'json'
              },
              error: function(error) {
                alert("Please fill all inputs correctly!");
              },
              dataType: 'json',
              success: function(data) {
                $('#student').empty();
                $('#student').append($('<option>').text("--Select Student--").attr('value',""));
                $.each(data, function(i, student) {
                  //console.log(student);
                  if(student.regiNo==stdRegiNo)
                  {
                    var opt="<option value='"+student.regiNo+"' selected>"+student.firstName+" "+student.middleName+" "+student.lastName+"["+student.rollNo+"] </option>"
                  }
                  else {
                    var opt="<option value='"+student.regiNo+"'>"+student.firstName+" "+student.middleName+" "+student.lastName+"["+student.rollNo+"] </option>"

                  }
                  //console.log(opt);
                  $('#student').append(opt);

                });
                //console.log(data);

              },
              type: 'GET'
            });
          };*/
          function addRow(data,index) {
            var table = document.getElementById('billItem');
            var rowCount = table.rows.length;
            var row = table.insertRow(rowCount);

            var cell2 = row.insertCell(0);
            var title = document.createElement("label");

            title.innerHTML=data['title'];
            cell2.appendChild(title);

            var cell3 = row.insertCell(1);
            var month = document.createElement("label");
            month.innerHTML=getTXTmonth(data['month']);
            cell3.appendChild(month);


            var cell4 = row.insertCell(2);
            var fee = document.createElement("label");
            fee.innerHTML=data['fee'];
            cell4.appendChild(fee);

            var cell5 = row.insertCell(3);
            var lateFee = document.createElement("label");
            lateFee.innerHTML=data['lateFee'];
            cell5.appendChild(lateFee);

            var cell6 = row.insertCell(4);
            var total = document.createElement("label");
            total.innerHTML=data['total'];
            cell6.appendChild(total);
          };


          function getTXTmonth(mindex)
          {
            if(mindex=="1")
            {
              return "January";
            }
            else if(mindex=="2")
            {
              return "February";
            }
            else if(mindex=="3")
            {
              return "March";
            }
            else if(mindex=="4")
            {
              return "April";
            }
            else if(mindex=="5")
            {
              return "May";
            }
            else if(mindex=="6")
            {
              return "June";
            }
            else if(mindex=="7")
            {
              return "July";
            }
            else if(mindex=="8")
            {
              return "August";
            }
            else if(mindex=="9")
            {
              return "September";
            }
            else if(mindex=="10")
            {
              return "October";
            }
            else if(mindex=="11")
            {
              return "November";
            }
            else if(mindex=="12")
            {
              return "December";
            }
            else {
              return "Not Monthly Fee";
            }


          };

            function getsections()
            {
                var aclass = $('#class').val();
               // alert(aclass);
                $.ajax({
                  url: "{{url('/section/getList')}}"+'/'+aclass,
                  data: {
                    format: 'json'
                  },
                  error: function(error) {
                    alert("Please fill all inputs correctly!");
                  },
                  dataType: 'json',
                  success: function(data) {
                    $('#section').empty();
                   //$('#section').append($('<option>').text("--Select Section--").attr('value',""));
                    $.each(data, function(i, section) {
                      //console.log(student);
                     
                      
                        var opt="<option value='"+section.id+"'>"+section.name + " </option>"

                    
                      //console.log(opt);
                      $('#section').append(opt);

                    });
                    //console.log(data);

                  },
                  type: 'GET'
                });
            };

          </script>

          @stop
