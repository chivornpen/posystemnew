@extends('layouts.admin')
@section('content')
    <div>
        @include('nav.message')
    </div>
    <div class="container-fluid">
        <br>
        <div class="panel panel-default">
            <div class="panel-heading">
                Customer List
            </div>
            <div class="panel panel-body">
                <div class="container-fluid">
                    <div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" name="txtsearch" id="txtsearch" placeholder="Search..." class="form-control" onkeyup="search()">
                                </div>
                            </div>
                            @if(\Illuminate\Support\Facades\Auth::user()->position->name!="SD" )
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <select name="brand" id="brand" class="form-control" onchange="brand()">
                                            <option value="0">Branch</option>
                                            @foreach($brand as $b)
                                                <option value="{{$b->id}}">{!! $b->brandName !!}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="overflow-x:scroll;">
                        @if($customer->count())
                            <div style="clear: both;"></div>
                            <br>
                            <div id="showReportContent">
                                <img src="{{asset('/images/Logo.jpg')}}" style="height: 15px; width: 110px; margin: 10px 0 10px 0"><br>
                                <p style="font-family: 'Times New Roman',Serif;color: #cf3d54; font-size:12px;"><b>CUSTOMER LIST</b></p>
                                <table border="1px" cellpadding="5px" id="customer" style=" width: 1600px; border-collapse: collapse; border:1px solid #7a7a7a;">
                                    <thead>
                                    <tr>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; text-align: center; padding:2px 8px;">No</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; text-align: center;padding:2px 8px;">Customer Number</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px;padding:2px 8px;">Customer Name</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; text-align: center;padding:2px 8px;">Customer Channel</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; text-align: center;padding:2px 8px;">Customer Contact</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; padding:2px 8px;">Territory</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; text-align: center;padding:2px 8px;">Home Number</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px; text-align: center;padding:2px 8px;">Street</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px;padding:2px 8px;">Commune</td>
                                        <td style="font-family:'Arial Black',Serif;font-size: 12px;padding:2px 8px;">Location</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i=1; ?>
                                    @foreach($customer as $c)
                                        <tr>
                                            <td style="text-align: center; font-family: 'Times New Roman',Serif;font-size: 12px;padding:2px 8px;">{{$i++}}</td>
                                            <td style="text-align: center; font-family: 'Times New Roman'; font-size: 12px;padding:2px 8px;">{{"CAM-CU-".sprintf('%06d',$c->id)}}</td>
                                            <td style="font-family: 'Khmer OS System',Serif;font-size: 12px;padding:2px 8px;">{!! $c->name !!}</td>
                                            <td style="text-align: center; font-family: 'Times New Roman',Serif;font-size: 12px;padding:2px 8px;">{!! $c->channel->description !!}</td>
                                            <td style="text-align: center; font-family: 'Times New Roman',Serif;font-size: 12px;padding:2px 8px;">{!! $c->contactNo !!}</td>
                                            <td style=" font-family: 'Khmer OS System',Serif;font-size: 12px;padding:2px 8px;">{!! \App\Province::where('id',$c->province_id)->value('name') ?  \App\Province::where('id',$c->province_id)->value('name') : "N/A" !!}</td>
                                            <td style="text-align: center; font-family: 'Times New Roman',Serif;font-size: 12px;padding:2px 8px;">{!! $c->homeNo ? $c->homeNo : "N/A" !!}</td>
                                            <td style="text-align: center; font-family: 'Times New Roman',Serif;font-size: 12px;padding:2px 8px;">{!! $c->streetNo ? $c->streetNo : "N/A" !!}</td>
                                            <td style=" font-family: 'Khmer OS System',Serif;font-size: 12px;padding:2px 8px;">{!! $c->commune_id ? $c->village->commune->name : "N/A" !!}</td>
                                            <td style=" font-family: 'Khmer OS System',Serif;font-size: 12px;padding:2px 8px;">{!! $c->location ? $c->location : "N/A" !!}</td>
                                        </tr>
                                @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <a style="text-decoration:none;" href="#" class="btn-primary btn-sm" title="Print" id="btnPrintReport"><i class="fa fa-print" aria-hidden="true"></i> Print</a>
                            <a style="text-decoration:none;" href="#" class="btn-success btn-sm" title="Excel" id="btnExportExcel"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel</a>
                            <br><br>
                        @else
                            <h5>No found result</h5>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script src="{{asset('js/js.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('js/printThis.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
//        $(document).ready(function () {
//           $('#customer').DataTable({});
//        });
            $("[id$=btnExportExcel]").click(function(e) {
                window.open('data:application/vnd.ms-excel,' + encodeURIComponent( $('div[id$=showReportContent]').html()));
                e.preventDefault();
            });
//            $("#btnExportExcel").click(function (e) {
//                window.open('data:application/vnd.ms-excel,' + $('#showReportContent').html());
//                e.preventDefault();
//
//            });
            $("#btnPrintReport").click(function () {
                $("#showReportContent").printThis({
                    loadCSS: "border,1px solid black",
                });
            });
            function search() {
                var data = $("#txtsearch").val();
                var brand = $("#brand").val();
                if(data){
                    $.ajax({
                        type: 'get',
                        url: "{{url('/report/search/customer')}}"+"/"+data+"/"+brand,
                        dataType: 'html',
                        success:function (data) {
                            $('#showReportContent').html(data);
                        },
                        error:function (error) {
                            console.log(error);
                        }
                    });
                }else {
                    $.ajax({
                        type: 'get',
                        url: "{{url('/report/search/customer')}}"+"/"+0+"/"+brand,
                        dataType: 'html',
                        success:function (data) {
                            $('#showReportContent').html(data);
                        },
                        error:function (error) {
                            console.log(error);
                        }
                    });
                }
            }
            function brand() {
                search();
            }
    </script>
@stop
