<?php
namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Claim;
use App\Models\ClaimDetail;
use App\User;
use Auth;
use Illuminate\Http\Request;

class ClaimApprovalController extends Controller
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
        $approver1 = Claim::with(['claim_detail', 'user'])
            ->where('approver1_id', $this->user_id)
            ->where('approver1_status', 0)->orderBy('id', 'desc')->get();
        $approver2 = Claim::with(['claim_detail', 'user'])
            ->where('approver2_id', $this->user_id)
            ->where('approver2_status', 0)->orderBy('id', 'desc')->get();
        $claims = $approver1->merge($approver2);

        $claims = Helper::paginateCollection($claims);

        return view('claim_approval.index', compact('claims'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int                         $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $getApprover = Claim::whereId($id)->where('approver1_id', $this->user_id)->first();

        if (count($getApprover)) {
            $approver_id = $getApprover->approver1_id;
            $remark = $getApprover->remark;
            $claims = Claim::with('claim_detail.project')
                ->whereId($id)->where('approver1_id', $this->user_id)
                ->orderBy('id', 'desc')->get();

            return view('claim_approval.show', compact('claims'))->with('approver', 1)->with('remark', $remark);
        } else {
            $getApprover = Claim::whereId($id)->where('approver2_id', $this->user_id)->first();
            $approver_id = $getApprover->approver2_id;
            $remark = $getApprover->remark;
            $claims = Claim::with('claim_detail.project')
                ->whereId($id)->where('approver2_id', $this->user_id)
                ->orderBy('id', 'desc')->get();

            return view('claim_approval.show', compact('claims'))->with('approver', 2)->with('remark', $remark);
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

        $claimDetailId = [];
        if ($request->claim_detail_id) {
            foreach ($request->claim_detail_id as $key => $value) {
                $claimDetailId[] = $key;
            }

            $hasPending = false;
            if ($request->action == 'approve') {
                if ($request->status == 1) {
                    $update_claim = ClaimDetail::whereIn('id', $claimDetailId)->update(['status1' => 1]);
                    // CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
                    $status1 = ClaimDetail::whereClaimsId($id)->get();
                    foreach ($status1 as $key => $value) {
                        if ($value->status1 == 0) {
                            $hasPending = true;
                        }
                    }
                    if ($hasPending == false) {
                        // UPDATE CASH REQUISITION STATUS TO 1 'DONE'
                        Claim::whereId($id)->update(['approver1_status' => 1]);
                    }
                } elseif ($request->status == 2) {
                    $update_claim = ClaimDetail::whereIn('id', $claimDetailId)->update(['status2' => 1]);
                    // CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
                    $status2 = ClaimDetail::whereClaimsId($id)->get();
                    foreach ($status2 as $key => $value) {
                        if ($value->status2 == 0) {
                            $hasPending = true;
                        }
                    }
                    if ($hasPending == false) {
                        // UPDATE CASH REQUISITION STATUS TO 1 'DONE'
                        Claim::whereId($id)->update(['approver2_status' => 1]);
                    }
                }
                flash()->success('Successfully approved!');

            } elseif ($request->action == 'reject') {

                if ($request->status == 1) {
                    $update_claim = ClaimDetail::whereIn('id', $claimDetailId)->update(['status1' => -1]);
                    // CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
                    $status1 = ClaimDetail::whereClaimsId($id)->get();
                    foreach ($status1 as $key => $value) {
                        if ($value->status1 == 0) {
                            $hasPending = true;
                        }
                    }
                    if ($hasPending == false) {
                        // UPDATE CASH REQUISITION STATUS TO 1 'DONE'
                        Claim::whereId($id)->update(['approver1_status' => 1]);
                    }
                } elseif ($request->status == 2) {
                    $update_claim = ClaimDetail::whereIn('id', $claimDetailId)->update(['status2' => -1]);
                    // CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
                    $status2 = ClaimDetail::whereClaimsId($id)->get();
                    foreach ($status2 as $key => $value) {
                        if ($value->status2 == 0) {
                            $hasPending = true;
                        }
                    }
                    if ($hasPending == false) {
                        // UPDATE CASH REQUISITION STATUS TO 1 'DONE'
                        Claim::whereId($id)->update(['approver2_status' => 1]);
                    }
                }
                flash()->success('Successfully rejected!');

            }

        }

        // Check to flag notification
        $requester = Claim::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
        if (count($requester)) {
            $requester->notification = 1;
            $requester->save();

            //SEND EMAIL TO REQUESTER
            \App\Helper\Helper::requestMail($requester->user_id);
        }

        return back();
    }

    /*$hasPending = false;
if ($request->action == 'approve') {
if ($request->status == 1) {
$update_crf = ClaimDetail::whereId($request->id)->update(['status1' => 1]);
// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
$status1 = ClaimDetail::whereClaimsId($id)->get();
foreach ($status1 as $key => $value) {
if ($value->status1 == 0) {
$hasPending = true;
}
}
if ($hasPending == false) {
// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
Claim::whereId($id)->update(['approver1_status' => 1]);
}
} elseif ($request->status == 2) {
$update_crf = ClaimDetail::whereId($request->id)->update(['status2' => 1]);
// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
$status2 = ClaimDetail::whereClaimsId($id)->get();
foreach ($status2 as $key => $value) {
if ($value->status2 == 0) {
$hasPending = true;
}
}
if ($hasPending == false) {
// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
Claim::whereId($id)->update(['approver2_status' => 1]);
}
}
flash()->success('Successfully approved!');

} elseif ($request->action == 'reject') {
if ($request->status == 1) {
$update_crf = ClaimDetail::whereId($request->id)->update(['status1' => -1]);
// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
$status1 = ClaimDetail::whereClaimsId($id)->get();
foreach ($status1 as $key => $value) {
if ($value->status1 == 0) {
$hasPending = true;
}
}
if ($hasPending == false) {
// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
Claim::whereId($id)->update(['approver1_status' => 1]);
}
} elseif ($request->status == 2) {
$update_crf = ClaimDetail::whereId($request->id)->update(['status2' => -1]);
// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
$status2 = ClaimDetail::whereClaimsId($id)->get();
foreach ($status2 as $key => $value) {
if ($value->status2 == 0) {
$hasPending = true;
}
}
if ($hasPending == false) {
// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
Claim::whereId($id)->update(['approver2_status' => 1]);
}
}
flash()->success('Successfully rejected!');
}

// Check to flag notification
$requester = Claim::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
if (count($requester)) {
$requester->notification = 1;
$requester->save();

//SEND EMAIL TO REQUESTER
\App\Helper\Helper::requestMail($requester->user_id);
}

return back();*/

}
