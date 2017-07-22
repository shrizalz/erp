@extends('layouts.master')

@section('title', 'Material Requisition Approval')

@include('include.css')

@section('content')
    <!-- Page -->
    <div class="page animsition">
	    <div class="page-header">
	        <ol class="breadcrumb">
	        <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
	        <li><a href="{{ URL::to('my-approval') }}">My Approval</a></li>
	        <li class="active">Material Requisition Approval</li>
	      	</ol>
		</div>
        <div class="page-content">
        	<div class="panel">
        		@include('include.flash')
        		<div class="panel-heading">
              <div class="panel-actions">
                <a href="{{ URL::to('material-requisition-archive') }}" class="btn white btn-warning"><i aria-hidden="true" class="white icon wb-library"></i> ARCHIVE</a>
              </div>
              <h3 class="panel-title font-size-16 text-uppercase padding-bottom-20"><i aria-hidden="true" class="icon md-camera-switch"></i>Material Requisition Approval</h3>
            </div>

	        	<div class="panel-body">
		        	<div class="example-wrap">

		        	@if(count($material_requisitions))

	        <hr>

					<table class="margin-top-20 table table-hover dataTable table-striped width-full" data-plugin="dataTable">
				          <thead>
				            <tr>
				              <th class="text-uppercase">MRF ID</th>
				              <th class="text-uppercase">Request Date</th>
				              <th class="text-uppercase">Requester</th>
				              <th class="text-uppercase">Project Name</th>
				              <th class="text-uppercase">Site Name</th>
				              <th class="cell-100 text-uppercase">Status</th>
				              <th class="text-uppercase">Action</th>
				            </tr>
				          </thead>
				          <tbody class="table-hover">
				          @foreach ($material_requisitions as $data)
				            <tr>
				              <td><a href="{{ URL::to('material-requisition-approval/' . $data->id) }}">{{ $data->id }}</a></td>

				              <td>{{ Date::parse($data->created_at)->format('d/m/Y') }}</td>
				              <td>{{ $data->user->full_name }}</td>
				              <td><span class="label label-primary">{{ $data->project->nick_name }}</span> {{ $data->project->name }}</td>
			              <td><span class="label label-primary">{{ $data->site->site_code }}</span> {{ $data->site->site_name }}</td>

				              <td>
				          	 	@set('number', 0)
				          	 	@set('key', 0)
					        		@foreach ($data->material_requisition_detail as $key => $value)
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
				               <a href="{{ URL::to('material-requisition-approval/' . $data->id) }}" class="tooltip-primary btn btn-icon btn-info verticle-align-middle" style="text-decoration: none;" data-original-title="View Details" data-trigger="hover" data-placement="right" data-toggle="tooltip"><i aria-hidden="true" class="icon md-eye"></i> Details</a>
				              </td>

				            </tr>
				          @endforeach
				          </tbody>
				    </table>

				    @else
					    <div class="well bg-primary">
							There 's no material requisition approval! ¯\_(ツ)_/¯
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

@include('include.js')
