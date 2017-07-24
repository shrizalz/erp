<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\TravelRequisition;

class TravelRequisitionApprovalController extends Controller
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
        $approver1 = TravelRequisition::with('user')
            ->where('approver1_id', $this->user_id)
            ->where('approver1_status', 0)->get();
        $approver2 = TravelRequisition::with('user')
            ->where('approver2_id', $this->user_id)
            ->where('approver2_status', 0)->get();
        $travel_requisitions = $approver1->merge($approver2);

        return view('travel_requisition_approval.index', compact('travel_requisitions'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int                         $id
     * @return \Illuminate\Http\Response
     */
    public function show($status, $id)
    {
        $user_id = $this->user_id;

        if ($status == 'approve') {
            $travel_requisition = TravelRequisition::whereId($id)
                ->where(function ($q) use ($user_id) {
                    $q->where('approver1_id', $user_id)
                        ->orWhere('approver2_id', $user_id);
                })->first();

            if ($travel_requisition->approver1_id == $this->user_id) {
                $travel_requisition->approver1_status = 1;
                $travel_requisition->save();
                flash()->success('Travel requisition successfully approved!');
            } else {
                $travel_requisition->approver2_status = 1;
                $travel_requisition->save();
                flash()->success('Travel requisition successfully approved!');
            }

            // Check if both approver approved
            if ($travel_requisition->approver1_status == 1 && $travel_requisition->approver2_status == 1) {
                // Flag notification status to 1
                $travel_requisition->notification = 1;
                $travel_requisition->save();
            }

        } elseif ($status == 'reject') {
            $travel_requisition = TravelRequisition::whereId($id)
                ->where(function ($q) use ($user_id) {
                    $q->where('approver1_id', $user_id)
                        ->orWhere('approver2_id', $user_id);
                })->first();

            if ($travel_requisition->approver1_id == $this->user_id) {
                $travel_requisition->approver1_status = -1;
                $travel_requisition->save();
                flash()->success('Travel requisition successfully rejected!');
            } else {
                $travel_requisition->approver2_status = -1;
                $travel_requisition->save();
                flash()->success('Travel requisition successfully rejected!');
            }

            // Flag notification status to 1
            $travel_requisition->notification = 1;
            $travel_requisition->save();

        }

        return back();

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  int                         $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->action == 'approve') {
            if ($request->status == 1) {
                TravelRequisition::whereId($id)->update(['approver1_status' => 1]);
            } elseif ($request->status == 2) {
                TravelRequisition::whereId($id)->update(['approver2_status' => 1]);
            }

            flash()->success('Successfully approved!');

        } elseif ($request->action == 'reject') {
            if ($request->status == 1) {
                TravelRequisition::whereId($id)->update(['approver1_status' => -1]);
            } elseif ($request->status == 2) {
                TravelRequisition::whereId($id)->update(['approver2_status' => -1]);
            }
            flash()->success('Successfully rejected!');
        }

        // Check to flag notification
        $requester = TravelRequisition::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
        if (count($requester)) {
            $requester->notification = 1;
            $requester->save();

            //SEND EMAIL TO REQUESTER
            \App\Helper\Helper::requestMail($requester->user_id);
        }

        return back();

    }

}
