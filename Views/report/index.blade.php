@extends('layouts.master')

@section('title', 'Report List')
@section('css')
{!! Html::style('global/vendor/select2/select2.css') !!}
<style>
  label {
    text-transform: uppercase !important;
    font-size: 14px;
    /*font-weight: 400;*/
    color: #555;
  }

     .select2-container {
    width: 100% !important;
  }

  .select2-container--open {
    z-index: 9999;
  }

    .select2-container {
      box-sizing: border-box;
      display: inline-block !important;
      margin: 0;
      position: relative;
      vertical-align: middle;
  }


</style>
@endsection

@section('content')
    <!-- Page -->
    <div class="page animsition">
      <div class="page-header">
          <ol class="breadcrumb">
          <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
          <li class="active">Report List</li>
          </ol>
          <div class="page-header-actions" style="z-index: 9;">
            <div class="btn-group">
                    <button type="button" class="btn btn-info btn-round dropdown-toggle" id="riskSettings" data-toggle="dropdown" aria-expanded="false"><i class="icon md-settings" aria-hidden="true"></i> Report Settings
                      <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu bullet" aria-labelledby="assetSettings" role="menu">
                      <li role="presentation"><a href="{{ URL::to('report/create') }}" role="menuitem">Create Report</a></li>
                    </ul>
                  </div>
          </div>
    </div>
      <div class="page-content">
          <div class="panel">
              @include('include.flash')
              <div class="panel-body">
                @if(count($reports))
                  <div class="panel-heading">
                  <h3 class="example-title font-size-16"><i aria-hidden="true" class="icon md-laptop-mac"></i>Report List</h3>
                  <div class="panel-actions">
                    <a class="panel-action icon wb-expand" data-toggle="panel-fullscreen" aria-hidden="true"></a>
                  </div>
                  </div>
                  <hr>
                  <div class="table-responsive">
                    <table class="margin-top-20 margin-bottom-40 table table-striped table-hover table-bordered">
                      <thead>
                        <tr>
                          <th class="text-uppercase text-center">ID</th>
                          <th class="text-uppercase text-center">DEPARTMENT</th>
                          <th class="text-uppercase text-center">NAME</th>
                          <th class="text-uppercase text-center">DATE SUBMITTED</th>
                          <th class="text-uppercase text-center">MANAGER</th>
                          <th class="text-uppercase text-center">TASK</th>
                          <th class="text-uppercase text-center">STATUS SUMMARY</th>
                          <th class="text-uppercase text-center">ACCOMPLISHMENT</th>
                          <th class="text-uppercase text-center">UPCOMING TASK</th>
                          <th class="text-uppercase text-center">RISKS AND ISSUES</th>
                          <th class="text-uppercase text-center">STATUS</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($reports as $key => $data)
                        <tr>
                          <td class="text-center">{{ ++$key }}</td>
                          <td class="text-center">
                             @foreach($data->department as $department)
                              {{ $department->name }}
                             @endforeach
                          </td>
                          <td class="text-center">
                             @foreach($data->user as $user)
                              {{ $user->full_name }}
                             @endforeach
                          </td>
                          <td class="text-center">
                            {{ Date::parse($data->date_submitted)->format('d F Y') }}
                          </td>
                          <td class="text-center">
                             @foreach($data->manager as $manager)
                              {{ $manager->full_name }}
                             @endforeach
                          </td>
                          <td class="text-center">
                            {{ $data->task }}
                          </td>
                          <td class="text-center">
                            {{ $data->status_summary }}
                          </td>
                          <td class="text-center">
                            {{ $data->accomplishment }}
                            </td>
                          <td class="text-center">
                            {{ $data->upcoming_task }}
                          </td>
                          <td class="text-center">
                            {{ $data->risk_and_issue }}
                          </td>

                          {{-- MODAL --}}
                            <div tabindex="-1" role="dialog" aria-labelledby="edit_risk" aria-hidden="true" id="edit_report_{{ $data->id }}" class="modal modal-primary fade modal-3d-sign edit_report" style="display: none;">
                              <div class="modal-dialog">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                                              <span aria-hidden="true">×</span>
                                          </button>
                                          <h4 class="modal-title pull-left">Update Status</h4>
                                      </div>
                                      <div class="modal-body text-align-left">

                                      {!! Form::open(['url' => 'report/' . $data->id, 'method' => 'PUT']) !!}
                                <div class="form-group">
                                        {!! Form::label('status', 'Status', ['class' => 'label-status']) !!}
                                         {!! Form::select('status', ['' => '', '0' => 'Pending', '1' => 'Completed'], $data->status, ['class' => 'cloneF status form-control', 'required' => 'required', 'autocomplete' => 'off', 'data-plugin' => 'select2', 'data-placeholder' => 'Select Status']) !!}
                                        <small class="text-danger">{{ $errors->first('status') }}</small>
                                    </div>
                                      {!! Form::submit("Update", ['class' => 'text-uppercase btn btn-block btn-lg btn-primary waves-effect waves-light']) !!}
                                      {!! Form::close() !!}
                                      </div>
                                  </div>
                              </div>
                            </div>

                          <td class="text-center">
                            @if($data->user_id == $user_id)

                            @if($data->status == 0)
                            <a role="button" href="javascript:void(0)" class="text-uppercase btn btn-danger btn-icon waves-effect waves-classic" data-toggle="modal" data-target="#edit_report_{{ $data->id }}"> <i aria-hidden="true" class="icon wb-wrench"></i> Pending
                              </a>
                            @else
                              <a role="button" href="javascript:void(0)" class="text-uppercase btn btn-success btn-icon waves-effect waves-classic" data-toggle="modal" data-target="#edit_report_{{ $data->id }}"> <i aria-hidden="true" class="icon wb-wrench"></i> Completed
                              </a>
                            @endif
                            @else
                            {!! $data->report_status !!}
                            @endif
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <div class="text-center">
                      {{ $reports->links() }}
                  </div>
                @else
                   <div class="well bg-primary">
                  There 's no report list! ¯\_(ツ)_/¯
                  </div>
                @endif

              </div>
          </div>
      </div>
    </div>
    <!-- End Page -->
@endsection

@section('script')
{!! Html::script('global/vendor/select2/select2.min.js') !!}
{!! Html::script('global/js/components/select2.js') !!}
@endsection
