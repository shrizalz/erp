@extends('layouts.master')

@section('title', 'Cash Requisition')

@section('css')
{!! Html::style('global/vendor/footable/footable.min.css') !!}
<style>
  label {
    text-transform: uppercase !important;
    font-size: 14px;
    font-weight: 400;
  }

  .approver {
    text-transform: uppercase !important;
    font-size: 14px;
    font-weight: 400;
  }

  .datepicker {
    z-index: 9999 !important;
  }
   .select2-container {
    width: 100% !important;
  }

  .select2-container--open {
    z-index: 9999;
  }
</style>
@endsection

@section('content')
		<div class="page animsition">
		    <div class="page-header">
		      <ol class="breadcrumb">
	          <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
	          <li><a href="{{ URL::to('cash-requisition-approval') }}">Cash Requisition Approval</a></li>
	           <li class="active">Details</a></li>
	          </ol>
		    </div>
		    <div class="page-content">
	        	<div class="panel">
	        		@include('include.flash')
		        	<div class="panel-body">
		        		<h3 class="example-title font-size-16 padding-bottom-20"><i aria-hidden="true" class="icon md-money"></i>Cash Requisition
		           {{--  <span class="padding-right-20 font-size-20 red-500 pull-right">CRF{{ $cash_requisitions->id }}</span> --}}

		        		</h3>

		        {{-- 	<p>Date of Required: {{ Date::parse($cash_requisitions->date_required)->format('d/m/Y') }}</p> --}}
		        	@set('total', 0)
		        	{!! Form::open(['method' => 'PUT', 'url' => 'cash-requisition-approval/' . $cash_requisition_id ]) !!}
		        	<table data-animate="fade" class="margin-top-20 table table-hover dataTable table-striped width-full" data-plugin="dataTable">
			          <thead>
			            <tr class="animation-fade" style="animation-fill-mode: backwards; animation-duration: 250ms; animation-delay: 0ms;">
			              <th class="text-uppercase">#</th>
			              <th class="text-uppercase">Description</th>
			              <th class="text-uppercase">Priority</th>
			              <th class="text-uppercase">Amount (RM)</th>
			              <th class="text-uppercase">Status</th>
			              <th class=" text-uppercase" data-class-name="all">
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
			          @foreach ($cash_requisitions as $cash_requisition)
			          	@foreach ($cash_requisition->cash_requisition_detail as $key => $value)
			          	<tr class="animation-fade" style="animation-fill-mode: backwards; animation-duration: 250ms; animation-delay: 50ms;">
			              <td><p class="font-size-16">{{ ++$key }}</p></td>
			              <td><p class="font-size-16">{{ $value->description }}</p></td>
			              <td>
			              	@if($value->priority == "High")
			              		<p class="label label-outline label-danger font-size-16">{{ $value->priority }}</p>
			                @elseif($value->priority == "Medium")
			              		<p class="label label-outline label-warning font-size-16">{{ $value->priority }}</p>
			              	@elseif($value->priority == "Low")
			              		<p class="label label-outline label-info font-size-16">{{ $value->priority }}</p>
			              	@endif
			              </td>
			              <td class="text-center"><p class="font-size-16">{{  number_format($value->amount, 2, '.', ', ') }}</p></td>
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
						                {{ Form::checkbox('cash_requisition_detail_id[' . $value->id . ']' , 1, $value->status1) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@elseif($approver == 2)
				               	 	{!! Form::hidden('status', 2) !!}
				               	 	<div class="form-group clearfix">
						              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
						                <input type="checkbox" id="inputCheckbox{{ ++$key }}">
						                {{ Form::checkbox('cash_requisition_detail_id[' . $value->id . ']' , 1, $value->status2) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@endif
			               </td>

			            </tr>

			             @set('total', $total += $value->amount)

			            @endforeach
			          @endforeach
			          </tbody>
			        </table>

			        <div class="pull-right">
			         	{!! Form::submit("approve", ['name' => 'action', 'class' => 'text-uppercase btn btn-success']) !!}
			         	{!! Form::submit("reject", ['name' => 'action', 'class' => 'text-uppercase btn btn-danger']) !!}
			        </div>
			        {!! Form::close() !!}

			        @if($remark != null)
			         	<div class="font-size-16"><span class="font-size-16 label label-outline label-info">Remark:</span> {{ $remark }}.</div>
			        @else
			         	<div class="font-size-16"><span class="font-size-16 label label-outline label-info">Remark:</span> N/A</div>
			        @endif

			         <div class="text-uppercase text-center font-size-20">
						TOTAL (RM): {{  number_format($total, 2, '.', ', ') }}
			         </div>

			         @include('include.back')

		        	</div>
		        </div>
		    </div>
		</div>


@endsection

@section('script')
	{!! Html::script('global/vendor/footable/footable.all.min.js') !!}
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


   <script type="text/javascript">
	$(function () {
	    $('.footable').footable();
	});
	</script>
@endsection
