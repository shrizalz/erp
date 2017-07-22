@extends('layouts.master')

@section('title', 'Item')

@include('include.css')

@section('content')
    <!-- Page -->
    <div class="page animsition">
        <div class="page-header">
            <ol class="breadcrumb">
            <li><a href="{{ URL::to('dashboard') }}">Dashboard</a></li>
            <li><a href="{{ URL::to('inventory') }}">Inventory</a></li>
            <li class="active">Item List</li>
            </ol>
            </div>
        <div class="page-content">
            <div class="panel">
                @include('include.flash')
                <div class="panel-body">
                @if(count($items))
                <div class="panel-heading">
                <h3 class="example-title font-size-16"><i aria-hidden="true" class="icon md-view-list-alt"></i>Item List</h3>
                </div>

                <hr>

                 <table class="margin-top-20 table table-hover dataTable table-striped width-full" data-plugin="dataTable">
                      <thead>
                        <tr>
                          <th class="text-uppercase"># ID</th>
                          <th class="text-uppercase">Category</th>
                          <th class="text-uppercase">Item Code</th>
                          <th class="text-uppercase">Item Description</th>
                          <th class="text-uppercase">Unit</th>
                          <th class="text-uppercase">Class</th>
                          <th class="text-uppercase">Status</th>
                        </tr>
                      </thead>
                      <tbody class="table-hover">
                      @foreach ($items as $data)
                        <tr>
                          <td>{{ $data->id }}</td>
                          </td>
                          <td>{{ $data->category->name }}
                          </td>
                          <td>{{ $data->item_code }}</td>
                          <td>{{ $data->item_description }}</td>
                          <td>
                            @foreach($data->metrics as $metric)
                              {{ $metric->name }}
                            @endforeach
                          </td>
                          <td>{{ $data->class }}
                          </td>
                          <td>
                            @if($data->approver1_status == 0 || $data->approver2_status == 0)
                            <span class="label label-outline label-warning label-lg">Pending</span>
                            @elseif($data->status1 == 1 && $data->status2 == 1)
                            <span class="label label-outline label-success label-lg">Approved</span>
                            @else
                            <span class="label label-outline label-danger label-lg">Rejected</span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>

                    <div class="text-center">
                      {{ $items->links() }}
                    </div>

                @else
                    <div class="well bg-primary">
                        There 's no record item! ¯\_(ツ)_/¯
                    </div>
                @endif

                @include('include.back')

                </div>
            </div>
        </div>
    </div>
    <!-- End Page -->
@endsection

@include('include.js')
