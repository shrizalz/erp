<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Report;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        if (Auth::check()) {
            $this->user_id = Auth::user()->id;
        } else {
            return redirect('/');
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reports = Report::with('user', 'department', 'manager')->orderBy('created_at', 'desc')->paginate(30);

        return view('report.index', compact('reports'))->with('user_id', $this->user_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::orderBy('name', 'asc')->pluck('name', 'id');
        $departments = $departments->prepend('', '');
        $managers    = User::where('id', '!=', $this->user_id)->orderBy('first_name', 'ASC')->get();
        $array_users = [];
        $array_users = array_add($array_users, '', '');
        foreach ($managers as $user) {
            $array_users[$user->id] = $user->full_name;
        }

        return view('report.create', compact('departments', 'array_users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'department' => 'required',
            'manager' => 'required',
            'task' => 'required',
            'status_summary' => 'required',
            'accomplishment' => 'required',
            'upcoming_task' => 'required',
            'risk_and_issue' => 'required',
            'status' => 'required',
        ]);

        $data = [
            'department_id' => $request->department,
            'user_id' => $this->user_id,
            'date_submitted' => date('Y-m-d'),
            'manager_id' => $request->manager,
            'task' => $request->task,
            'status_summary' => $request->status_summary,
            'accomplishment' => $request->accomplishment,
            'upcoming_task' => $request->upcoming_task,
            'risk_and_issue' => $request->risk_and_issue,
            'status' => $request->status,
        ];

        Report::create($data);
        flash()->success('Successfully saved!');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required',
        ]);

        Report::findOrFail($id)->update(['status' => $request->status]);

        flash()->success('Successfully updated!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
