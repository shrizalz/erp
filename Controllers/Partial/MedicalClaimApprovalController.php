<?php

namespace App\Http\Controllers;

use App\Models\MedicalClaim;
use App\Models\MedicalClaimDetail;
use App\User;
use Auth;
use Illuminate\Http\Request;

class MedicalClaimApprovalController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('medical-claim');
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
	public function index() {
		$approver1 = MedicalClaim::with(['medical_claim_detail', 'user'])
			->where('approver1_id', $this->user_id)
			->where('approver1_status', 0)->get();
		$approver2 = MedicalClaim::with(['medical_claim_detail', 'user'])
			->where('approver2_id', $this->user_id)
			->where('approver2_status', 0)->get();
		$medical_claims = $approver1->merge($approver2);

		return view('medical_claim_approval.index', compact('medical_claims'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int                         $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$user = MedicalClaim::whereId($id)->first();

		$medical_claims = MedicalClaim::with('medical_claim_detail')->whereUserId($user->user_id)->get();
		$sum = 0;
		foreach ($medical_claims as $key => $medical_claim) {
			foreach ($medical_claim->medical_claim_detail as $medical_claim_detail) {
				if ($medical_claim_detail->years == date('Y') && $medical_claim_detail->status1 == 1 && $medical_claim_detail->status2 == 1) {
					$sum += $medical_claim_detail->amount;
				}
			}
		}

		$balance = 1000 - $sum;

		$getApprover = MedicalClaim::whereId($id)->where('approver1_id', $this->user_id)->first();

		if (count($getApprover)) {
			$approver_id = $getApprover->approver1_id;
			$remark = $getApprover->remark;
			$medical_claims = MedicalClaim::with('medical_claim_detail')
				->whereId($id)->where('approver1_id', $this->user_id)
				->orderBy('id', 'desc')->get();
			return view('medical_claim_approval.show', compact('medical_claims'))->with('approver', 1)->with('remark', $remark)->with('balance', $balance);
		} else {
			$getApprover = MedicalClaim::whereId($id)->where('approver2_id', $this->user_id)->first();
			$approver_id = $getApprover->approver2_id;
			$remark = $getApprover->remark;
			$medical_claims = MedicalClaim::with('medical_claim_detail')
				->whereId($id)->where('approver2_id', $this->user_id)
				->orderBy('id', 'desc')->get();
			return view('medical_claim_approval.show', compact('medical_claims'))->with('approver', 2)->with('remark', $remark)->with('balance', $balance);
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
	public function update(Request $request, $id) {

		$medicalClaimDetailId = [];
		if ($request->medical_claim_detail_id) {
			foreach ($request->medical_claim_detail_id as $key => $value) {
				$medicalClaimDetailId[] = $key;
			}

			$hasPending = false;
			if ($request->action == 'approve') {

				if ($request->status == 1) {

					// Check capped 1K
					$user = MedicalClaim::whereId($id)->first();
					$medical_claims = MedicalClaim::with('medical_claim_detail')->whereUserId($user->user_id)->get();
					$sum = 0;

					foreach ($medical_claims as $key => $medical_claim) {
						foreach ($medical_claim->medical_claim_detail as $medical_claim_detail) {
							if ($medical_claim_detail->years == date('Y') && $medical_claim_detail->status1 == 1 && $medical_claim_detail->status2 == 1) {
								$sum += $medical_claim_detail->amount;
							}
						}
					}

					$amount_approve = MedicalClaimDetail::whereIn('id', $medicalClaimDetailId)->whereStatus1(0)->first();
					$total = $amount_approve->amount;
					$balance = 1000 - $sum;

					if ($total > $balance) {
						flash()->error('Medical claim limit exceeded RM 1000.');
						return back();
					}
					// End

					$update_crf = MedicalClaimDetail::whereIn('id', $medicalClaimDetailId)->update(['status1' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = MedicalClaimDetail::whereMedicalClaimId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
						MedicalClaim::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {

					// Check capped 1K
					$user = MedicalClaim::whereId($id)->first();
					$medical_claims = MedicalClaim::with('medical_claim_detail')->whereUserId($user->user_id)->get();
					$sum = 0;

					foreach ($medical_claims as $key => $medical_claim) {
						foreach ($medical_claim->medical_claim_detail as $medical_claim_detail) {
							if ($medical_claim_detail->years == date('Y') && $medical_claim_detail->status1 == 1 && $medical_claim_detail->status2 == 1) {
								$sum += $medical_claim_detail->amount;
							}
						}
					}

					$amount_approve = MedicalClaimDetail::whereIn('id', $medicalClaimDetailId)->whereStatus2(0)->first();
					$total = $amount_approve->amount;
					$balance = 1000 - $sum;

					if ($total > $balance) {
						flash()->error('Medical claim limit exceeded RM 1000.');
						return back();
					}
					// End

					$update_crf = MedicalClaimDetail::whereIn('id', $medicalClaimDetailId)->update(['status2' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = MedicalClaimDetail::whereMedicalClaimId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
						MedicalClaim::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully approved!');

			} elseif ($request->action == 'reject') {
				if ($request->status == 1) {
					$update_mcf = MedicalClaimDetail::whereIn('id', $medicalClaimDetailId)->update(['status1' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = MedicalClaimDetail::whereMedicalClaimId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
						MedicalClaim::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_mcf = MedicalClaimDetail::whereIn('id', $medicalClaimDetailId)->update(['status2' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = MedicalClaimDetail::whereMedicalClaimId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE CASH REQUISITION STATUS TO 1 'DONE'
						MedicalClaim::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully rejected!');
			}
		}

		// Check to flag notification
		$requester = MedicalClaim::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
		if (count($requester)) {
			$requester->notification = 1;
			$requester->save();

			//SEND EMAIL TO REQUESTER
			\App\Helper\Helper::requestMail($requester->user_id);
		}

		return back();
	}

}
