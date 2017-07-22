@extends('layouts.master')

@section('title', 'Create Report')

@section('css')
{!! Html::style('global/vendor/select2/select2.css') !!}
<style>
  label {
    text-transform: uppercase !important;
    font-size: 14px;
    font-weight: 400;
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
	        <li class="active">Create Report
	        </li>
	      	</ol>
	      	<div class="page-header-actions" style="z-index: 9;">
		        <div class="btn-group">
                    <button type="button" class="btn btn-info btn-round dropdown-toggle" id="reportSettings" data-toggle="dropdown" aria-expanded="false"><i class="icon md-settings" aria-hidden="true"></i> Report Settings
                      <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu bullet" aria-labelledby="reportSettings" role="menu">
                      <li role="presentation"><a href="{{ URL::to('report') }}" role="menuitem"> Report List</a></li>
                    </ul>
                  </div>
	      	</div>
		</div>
        <div class="page-content">
        	<div class="panel">
        		@include('include.flash')
            	@include('include.error_required')
	        	<div class="panel-body">
	        	<div class="example-wrap">
	        	<h3 class="example-title font-size-16"><i aria-hidden="true" class="icon md-laptop-mac"></i>Create Report
	        	</h3>

	        	<hr>

	        	<span class="helper text-info">* Fields are required</span>

		            {!! Form::open(['url' => 'report']) !!}

		            <div class="row clone padding-top-20">

		            	<div class="col-md-6 form-group">
		                    {!! Form::label('department', 'Department*', ['class' => 'label-department']) !!}
		                    {!! Form::select('department', $departments, null, ['class' => 'department form-control', 'id' => 'department', 'required' => 'required', 'autocomplete' => 'off', 'data-plugin' => 'select2', 'data-placeholder' => 'Department']) !!}
		                </div>

		                <div class="col-md-6 form-group">
		                    {!! Form::label('manager', 'Manager*', ['class' => 'label-risk-type']) !!}
		                    {!! Form::select('manager', $array_users, null, ['class' => 'manager form-control', 'id' => 'manager', 'required' => 'required', 'autocomplete' => 'off', 'data-plugin' => 'select2', 'data-placeholder' => 'Manager']) !!}
		                </div>

		                <div class="col-md-3 form-group">
		                    {!! Form::label('task', 'Task*', ['class' => 'label-task']) !!}
		                    {!! Form::textarea('task', null, ['size' => '3x6', 'autocomplete' => 'off', 'class' => 'task form-control', 'spellcheck' => 'false', 'placeholder' => 'Task', 'required' => 'required']) !!}
		                </div>

		                 <div class="col-md-3 form-group">
		                    {!! Form::label('status_summary', 'Status Summary*', ['class' => 'label-status-summary']) !!}
		                    {!! Form::textarea('status_summary', null, ['size' => '3x6', 'autocomplete' => 'off', 'class' => 'status_summary form-control', 'spellcheck' => 'false', 'placeholder' => 'Status Summary', 'required' => 'required']) !!}
		                </div>

		                <div class="col-md-3 form-group">
		                    {!! Form::label('accomplishment', 'Accomplishment*', ['class' => 'label-accomplishment']) !!}
		                    {!! Form::textarea('accomplishment', null, ['size' => '3x6', 'autocomplete' => 'off', 'class' => 'accomplishment form-control', 'spellcheck' => 'false', 'placeholder' => 'Accomplishment', 'required' => 'required']) !!}
		                </div>

		                <div class="col-md-3 form-group">
		                    {!! Form::label('upcoming_task', 'Upcoming Task*', ['class' => 'label-upcoming_task']) !!}
		                    {!! Form::textarea('upcoming_task', null, ['size' => '3x6', 'autocomplete' => 'off', 'class' => 'upcoming_task form-control', 'spellcheck' => 'false', 'placeholder' => 'Upcoming Task', 'required' => 'required']) !!}
		                </div>

		                <div class="col-md-6 form-group">
		                    {!! Form::label('risk_and_issue', 'Risk and Issue*', ['class' => 'label-risk-and-issue']) !!}
		                    {!! Form::textarea('risk_and_issue', null, ['size' => '3x6', 'autocomplete' => 'off', 'class' => 'risk_and_issue form-control', 'spellcheck' => 'false', 'placeholder' => 'Risk and Issue', 'required' => 'required']) !!}
		                </div>

		                <div class="col-md-6 form-group">
		                    {!! Form::label('status', 'Status*', ['class' => 'label-status']) !!}
		                    {!! Form::select('status', ['' => '', '0' => 'Pending', '1' => 'Completed'], null, ['class' => 'status form-control', 'id' => 'status', 'required' => 'required', 'autocomplete' => 'off', 'data-plugin' => 'select2', 'data-placeholder' => 'Status']) !!}
		                </div>

		            </div>


		            <hr>

	                 {!! Form::submit("Submit", ['class' => 'submit text-uppercase btn-block  btn btn-lg btn-primary',  'id' => 'submit']) !!}

		            {!! Form::close() !!}

		        </div>
	            </div>
            </div>
        </div>
    </div>
    <!-- End Page -->
@endsection

@section('script')
{!! Html::script('global/vendor/select2/select2.min.js') !!}
{!! Html::script('global/js/components/select2.js') !!}
{!! Html::script('global/js/components/input-group-file.min.js') !!}
<script>
  	$('form').submit(function() {
	  $(this).find("button[type='submit']").prop('disabled',true);
	});
</script>
@endsection
