@extends('layouts.master')

@section('title', 'Service Order')

@include('include.css')

@section('content')
		<div class="page animsition">
		    <div class="page-header">
		      <ol class="breadcrumb">
	          <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
	          <li><a href="{{ URL::to('service-order-approval') }}">Service Order Approval</a></li>
	           <li class="active">Details</a></li>
	          </ol>
		    </div>
		    <div class="page-content">
	        	<div class="panel">
	        		@include('include.flash')
		        	<div class="panel-body">
		        		<h3 class="text-uppercase example-title font-size-16 padding-bottom-20"><i aria-hidden="true" class="icon md-washing-machine"></i>Service Order

			        @foreach ($service_orders as $service_order)
		            <span class="padding-right-20 font-size-24 red-500 pull-right">SO {{ $service_order->id }}</span>
		            @endforeach
		               </h3>

		            <hr>
		            <div class="row font-size-16">
		                <div class="col-md-2">Supplier:</div>
			            <div class="col-md-10">{{ $so_project_site->supplier->name }}</div>
			            <div class="col-md-2">Project Name:</div>
			            <div class="col-md-9"><span class="label label-primary">{{ $so_project_site->project->nick_name }}</span> {{ $so_project_site->project->name }}</div>
			             <div class="col-md-2">Department:</div>
			            <div class="col-md-10">{{ $so_project_site->department->name }}</div>
			            <div class="col-md-2">Deliver To:</div>
			            <div class="col-md-10">{{ $so_project_site->deliver_to }}</div>
			            <div class="col-md-2">Attention To:</div>
			            <div class="col-md-10">{{ $so_project_site->attention_to }}</div>
			            <div class="col-md-2">Request Date:</div>
			            <div class="col-md-10">{{ Date::parse($so_project_site->request_date)->format('d/m/Y') }}</div>
		            </div>
		            <hr>

		        	@set('total', 0)
		        	@set('gst', 0)
		        	{!! Form::open(['method' => 'PUT', 'url' => 'service-order-approval/' . $service_order->id ]) !!}

		        	<table class="margin-top-20 table table-hover dataTable table-striped width-full" data-plugin="dataTable">
			          <thead>
			            <tr>
			              <th class="text-uppercase">#</th>
			              <th class="text-uppercase">Service</th>
			              <th class="text-uppercase">Date Required</th>
			              <th class="text-uppercase">Site Code</th>
			              <th class="text-uppercase">Quantity</th>
			              <th class="text-uppercase">Price (RM)</th>
			              <th class="text-uppercase">Amount (RM)</th>
			              <th class="text-uppercase" data-class-name="all">
			              	<div class="form-group clearfix">
				              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
				                <input type="checkbox" id="checkAll">
				                <label for="checkAll">Check All</label>
				              </div>
				            </div>
			              </th>
			            </tr>
			          </thead>
			          <tbody class="table-hover">

			          @foreach ($service_orders as $service_order)

			          	@foreach ($service_order->service_order_detail as $key => $value)


			          	<tr>
			              <td><p >{{ ++$key }}</p></td>
			              <td>
			              	<p >
				              	{{ $value->service_description }}
			              	</p>
			              </td>
			              <td>
			              	<p >
				              	{{ Date::parse($value->date_required)->format('d F Y') }}
			              	</p>
			              </td>
			              <td>
			              	<p >
				          		@foreach ($value->sites as $site)
				              	{{ $site->site_code }}
				          		@endforeach
			              	</p>
			              </td>
			              <td><p >{{ $value->quantity }}</p></td>
			              <td><p >{{ $value->unit_price }}</p></td>
			              <td><p >{{  number_format($value->amount, 2, '.', ', ') }}</p></td>

			               <td>
				               	@if($approver == 1)
				               	 	{!! Form::hidden('status', 1) !!}
				               	 	<div class="form-group clearfix">
						              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
						                <input type="checkbox" id="inputCheckbox{{ ++$key }}">
						                {{ Form::checkbox('service_order_detail_id[' . $value->id . ']' , 1, $value->status1) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@elseif($approver == 2)
				               	 	{!! Form::hidden('status', 2) !!}
				               	 	<div class="form-group clearfix">
						              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
						                <input type="checkbox" id="inputCheckbox{{ ++$key }}">
						                {{ Form::checkbox('service_order_detail_id[' . $value->id . ']' , 1, $value->status2) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@endif
			               </td>
			            </tr>

			            @set('total', $total += $value->amount)

			           	@if($so_project_site->supplier->gst_number != null)
			            	@set('gst', $gst += $value->amount * 6 / 100)
			            @else
			            	@set('gst', $gst += $value->amount * 0 / 100)
			            @endif

			            @endforeach
			          @endforeach
			          </tbody>

			        </table>

			        <div class="padding-top-20 text-center font-size-20">
			        	<div class="row">
				        	<div class="col-md-6 text-right">
				        		Total Before GST:
				        	</div>
				        	<div class="col-md-6 text-left">
								RM {{  number_format($total, 2, '.', ', ') }}
				        	</div>
						</div>
			        </div>
			        <div class="text-center font-size-20">
			        	<div class="row">
				        	<div class="col-md-6 text-right">
				        		@if($so_project_site->supplier->gst_number != null)
				        		GST (6%):
				        		@else
				        		GST (0%):
				        		@endif
				        	</div>
				        	<div class="col-md-6 text-left">
								RM {{  number_format($gst, 2, '.', ', ') }}
				        	</div>
						</div>
			        </div>
			        <div class="text-center font-size-20">
			        	<div class="row">
				        	<div class="col-md-6 text-right">
				        		Total After GST:
				        	</div>
				        	<div class="col-md-6 text-left blue-600">
								RM {{  number_format($total + $gst, 2, '.', ', ') }}
				        	</div>
						</div>
			        </div>
			        <div class="pull-right">
			         	{!! Form::submit("approve", ['name' => 'action', 'class' => 'text-uppercase btn btn-success']) !!}
			         	{!! Form::submit("reject", ['name' => 'action', 'class' => 'text-uppercase btn btn-danger']) !!}
			        </div>
			        {!! Form::close() !!}

			        @include('include.back')

		        	</div>
		        </div>
		    </div>
		</div>


@endsection
@section('script')
	{!! Html::script('global/vendor/select2/select2.min.js') !!}
	{!! Html::script('global/js/components/select2.js') !!}
	{!! Html::script('global/vendor/datatables/jquery.dataTables.js') !!}
	{!! Html::script('global/vendor/datatables-bootstrap/dataTables.bootstrap.js') !!}
	{!! Html::script('global/vendor/datatables-responsive/dataTables.responsive.js') !!}
	{!! Html::script('global/js/datatables_custom_asc.js') !!}
	<script>

		$("#checkAll").click(function () {
	     $('input:checkbox').not(this).prop('checked', this.checked);
	 	});
	</script>
@endsection

@include('include.js_asc')
