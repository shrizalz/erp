@extends('layouts.master')

@section('title', 'Purchase Requisition')

@include('include.css')

@section('content')
		<div class="page animsition">
		    <div class="page-header">
		      <ol class="breadcrumb">
	          <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
	          <li><a href="{{ URL::to('purchase-requisition-approval') }}">Purchase Requisition Approval</a></li>
	           <li class="active">Details</a></li>
	          </ol>
		    </div>
		    <div class="page-content">
	        	<div class="panel">
	        		@include('include.flash')
		        	<div class="panel-body">
		        		<h3 class="text-uppercase example-title font-size-16 padding-bottom-20"><i aria-hidden="true" class="icon md-assignment"></i>Purchase Requisition

			        @foreach ($purchase_requisitions as $purchase_requisition)
		            <span class="padding-right-20 font-size-24 red-500 pull-right">PRF {{ $purchase_requisition->id }}</span>
		            @endforeach
		               </h3>

		            <hr>
		            <div class="row font-size-16">
			            <div class="col-md-2">Project Name:</div>
			             <div class="col-md-10"><span class="label label-primary">{{ $prf_project_site->project->nick_name }}</span> {{ $prf_project_site->project->name }}</div>
			            <div class="col-md-2">Deliver To:</div>
			            <div class="col-md-10">{{ $prf_project_site->deliver_to }}</div>
			            <div class="col-md-2">Request Date:</div>
			            <div class="col-md-10">{{ Date::parse($prf_project_site->created_at)->format('d/m/Y') }}</div>
		            </div>
		            <hr>

		        	@set('total', 0)
					{!! Form::open(['method' => 'PUT', 'url' => 'purchase-requisition-approval/' . $purchase_requisition->id ]) !!}

		        	<table class="margin-top-20 table table-hover dataTable table-striped width-full" data-plugin="dataTable">
			          <thead>
			            <tr>
			              <th class="text-uppercase">#</th>
			              <th class="text-uppercase">Item</th>
			              <th class="text-uppercase">Description</th>
			              <th class="text-uppercase">Quantity</th>
			              <th class="text-uppercase">Unit</th>
			              <th class="text-uppercase">Remark</th>
			              <th class="text-uppercase">Status</th>
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

			          @foreach ($purchase_requisitions as $purchase_requisition)

			          	@foreach ($purchase_requisition->purchase_requisition_detail as $key => $value)


			          	<tr>
			              <td><p>{{ ++$key }}</p></td>
			              <td>
			              	<p>
				          		@foreach ($value->items as $item)
				              	{{ $item->item_code }}
				          		@endforeach
			              	</p>
			              </td>
			              <td>
			              	<p>
				          		@foreach ($value->items as $item)
				              	{{ $item->item_description }}
				          		@endforeach
			              	</p>
			              </td>
			              <td><p>{{ $value->quantity }}</p></td>
			              <td><p>{{ $value->metric->name }}</p></td>
			              <td><p>
			              @if($value->remark == NULL || $value->remark == '')
			              	N/A
			              @else
			              	{{ $value->remark }}
			              @endif
			              </p></td>
			              <td>
				              @if($approver == 1)
				                @if ($value->status1 == 1)
				                    <p class="label label-outline label-success font-size-16">Approved</p>
				                @elseif($value->status1 == 0)
				               		<p class="label label-outline label-warning font-size-16">Pending</p>
				                @elseif ($value->status1 == -1)
				               		<p class="label label-outline label-danger font-size-16">Rejected</p>
				                @endif
				              @elseif($approver == 2)
				              	@if ($value->status2 == 1)
				                    <p class="label label-outline label-success font-size-16">Approved</p>
				                @elseif($value->status2 == 0 )
				               		<p class="label label-outline label-warning font-size-16">Pending</p>
				                @elseif ($value->status2 == -1)
				               		<p class="label label-outline label-danger font-size-16">Rejected</p>
				                @endif
				              @endif
			               </td>
			               <td>
				               	@if($approver == 1)
				               	 	{!! Form::hidden('status', 1) !!}
				               	 	<div class="form-group clearfix">
						              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
						                <input type="checkbox" id="inputCheckbox{{ ++$key }}">
						                {{ Form::checkbox('purchase_requisition_detail_id[' . $value->id . ']' , 1, $value->status1) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@elseif($approver == 2)
				               	 	{!! Form::hidden('status', 2) !!}
				               	 	<div class="form-group clearfix">
						              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
						                <input type="checkbox" id="inputCheckbox{{ ++$key }}">
						                {{ Form::checkbox('purchase_requisition_detail_id[' . $value->id . ']' , 1, $value->status2) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@endif
			               </td>



			            </tr>

			            @endforeach
			          @endforeach
			          </tbody>

			        </table>

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
