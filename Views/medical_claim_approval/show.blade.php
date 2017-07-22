@extends('layouts.master')

@section('title', 'Claim')

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
	          <li><a href="{{ URL::to('medical-claim-approval') }}">Medical Claim Approval</a></li>
	           <li class="active">Details</a></li>
	          </ol>
		    </div>
		    <div class="page-content">
	        	<div class="panel">
	        		@include('include.flash')
		        	<div class="panel-body">
		        		<h3 class="text-uppercase example-title font-size-16 padding-bottom-20"><i aria-hidden="true" class="icon md-money"></i>Medical Claim

			        @foreach ($medical_claims as $medical_claim)
		            <span class="padding-right-20 font-size-24 red-500 pull-right">MCF{{ $medical_claim->id }}</span>
		            @endforeach

		        		</h3>

		        	<div class="padding-bottom-20 text-uppercase font-size-14">
		        		Balance: RM {{ $balance }}
	        		</div>

		        	@set('total', 0)

		        	{!! Form::open(['method' => 'PUT', 'url' => 'medical-claim-approval/' . $medical_claim->id ]) !!}

		        	<table data-animate="fade" class="margin-top-20 table table-hover dataTable table-striped width-full table footable" data-plugin="dataTable">
			          <thead>
			            <tr class="animation-fade" style="animation-fill-mode: backwards; animation-duration: 250ms; animation-delay: 0ms;">
			              <th class="cell-50 text-uppercase">#</th>
			              <th class="cell-300 text-uppercase">Description</th>
			              <th class="cell-200 text-uppercase">Receipt Number</th>
			              <th class="cell-150 text-uppercase">Amount (RM)</th>
			              <th class="cell-100 text-uppercase">Status</th>
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
			          @foreach ($medical_claims as $medical_claim)
			          	@foreach ($medical_claim->medical_claim_detail as $key => $value)
			          	<tr class="animation-fade" style="animation-fill-mode: backwards; animation-duration: 250ms; animation-delay: 50ms;">
			              <td><p class="font-size-16">{{ ++$key }}</p></td>
			              <td><p class="font-size-16">{{ $value->description }}</p></td>
			              <td><p class="font-size-16">{{ $value->receipt }}</p></td>
			              <td><p class="font-size-16">{{ $value->amount }}</p></td>
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
						                {{ Form::checkbox('medical_claim_detail_id[' . $value->id . ']' , 1, $value->status1) }}
						                <label for="inputCheckbox{{ $key }}"></label>
						              </div>
						            </div>
				               	@elseif($approver == 2)
				               	 	{!! Form::hidden('status', 2) !!}
				               	 	<div class="form-group clearfix">
						              <div class="checkbox-custom checkbox-inline checkbox-success pull-left">
						                <input type="checkbox" id="inputCheckbox{{ ++$key }}">
						                {{ Form::checkbox('medical_claim_detail_id[' . $value->id . ']' , 1, $value->status2) }}
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
			         	<div class="font-size-16"><span class="font-size-16 label label-outline label-info">Remark:</span> Not available.</div>
			        @endif

			        <div class="text-uppercase text-center font-size-20">
						TOTAL (RM): {{ $total }}
			        </div>
			        @foreach ($medical_claims as $medical_claim)
			        <div class="text-center padding-top-10">
			        <a target="_blank" href="{{ URL::to('download-medical-claim/' . $medical_claim->resit_url ) }}" class="text-uppercase btn btn-lg btn-info">View Receipt</a>
			        </div>
			        @endforeach

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
   <script type="text/javascript">

   	$("#checkAll").click(function () {
	     $('input:checkbox').not(this).prop('checked', this.checked);
	 	});

	// $(function () {
	//     $('.footable').footable();
	// });

	</script>
@endsection

<!-- @section('script')
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
 -->