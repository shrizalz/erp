@extends('layouts.master')

@section('title', 'Medical Claim Approval')

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
    <!-- Page -->
    <div class="page animsition">
	    <div class="page-header">
	        <ol class="breadcrumb">
	        <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
	        <li><a href="{{ URL::to('my-approval') }}">My Approval</a></li>
	        <li class="active">Medical Claim Approval</li>
	      	</ol>
		</div>
        <div class="page-content">
        	<div class="panel">
        		@include('include.flash')
        		<div class="panel-heading">
              <div class="panel-actions">
                <a href="{{ URL::to('medical-claim-archive') }}" class="btn white btn-warning"><i aria-hidden="true" class="white icon wb-library"></i> ARCHIVE</a>
              </div>
              <h3 class="panel-title font-size-16 text-uppercase padding-bottom-20"><i aria-hidden="true" class="icon md-money"></i>Medical Claim Approval</h3>
            </div>

	        	<div class="panel-body">
		        	<div class="example-wrap">

		        	@if(count($medical_claims))
					<table data-animate="fade" class="table footable">
				          <thead>
				            <tr class="animation-fade" style="animation-fill-mode: backwards; animation-duration: 250ms; animation-delay: 0ms;">
				              <th class="cell-100 text-uppercase"># ID</th>
				              <th class="cell-400 text-uppercase">Request Date</th>
				              <th class="cell-400 text-uppercase">Requester</th>
				              <th class="cell-300 text-uppercase">Clinic Name</th>
				              <th class="cell-100 text-uppercase">Status</th>
				              <th class="cell-300 text-uppercase">Action</th>
				            </tr>
				          </thead>
				          <tbody class="table-hover">
				          @foreach ($medical_claims as $data)
				            <tr class="animation-fade" style="animation-fill-mode: backwards; animation-duration: 250ms; animation-delay: 50ms;">
				              <td><a href="{{ URL::to('claim-approval/' . $data->id) }}" class="font-size-16">MCF{{ $data->id }}</a></td>

				              <td>{{ Date::parse($data->created_at)->format('d/m/Y') }}</td>

				              <td class="font-size-16">{{ $data->user->full_name }}</td>

				              <td class="font-size-16">{{ $data->clinic_name }}</td>

				              <td class="font-size-16">
				          	 	@set('number', 0)
					        	@foreach ($data->medical_claim_detail as $key => $value)
					              	@if($data->approver1_id == Auth::user()->id && $value->status1 == 0)
					              		@set('number', $number + 1)
					                @elseif($data->approver2_id == Auth::user()->id && $value->status2 == 0)
					              		@set('number', $number + 1)
					              	@endif
					              	@set('key', ++$key)
					            @endforeach
					            <span class="label label-outline label-info label-lg">{{ $number }} / {{  $key }}</span>

				              </td>
				              <td>
				              <a href="{{ URL::to('medical-claim-approval/' . $data->id) }}" class="tooltip-primary btn btn-icon white btn-info verticle-align-middle" style="text-decoration: none;" data-original-title="View Details" data-trigger="hover" data-placement="right" data-toggle="tooltip"><i aria-hidden="true" class="icon md-eye"></i> Details</a>
				              <a target="_blank" href="{{ URL::to('download-medical-claim/' . $data->resit_url ) }}" class="tooltip-primary btn btn-icon white btn-success verticle-align-middle" style="text-decoration: none;" data-original-title="Receipt" data-trigger="hover" data-placement="right" data-toggle="tooltip"><i aria-hidden="true" class="icon md-collection-pdf"></i> View PDF</a>
				              </td>

				            </tr>
				          @endforeach
				          </tbody>
				    </table>
				    @else
					    <div class="well bg-primary">
							There 's no medical claim approval! ¯\_(ツ)_/¯
					    </div>
				    @endif

				    @include('include.back')

			        </div>
	            </div>
            </div>
        </div>
    </div>
    <!-- End Page -->
@endsection

@section('script')
{!! Html::script('global/vendor/footable/footable.all.min.js') !!}
   <script type="text/javascript">
	$(function () {
	    $('.footable').footable();
	});
	</script>
@endsection
