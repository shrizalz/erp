<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionDetail;
use App\User;
use Auth;
use Illuminate\Http\Request;

class MaterialRequisitionApprovalController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('project');
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
		$approver1 = MaterialRequisition::with(['material_requisition_detail', 'user', 'project', 'site'])
			->where('approver1_id', $this->user_id)
			->where('approver1_status', 0)->get();
		$approver2 = MaterialRequisition::with(['material_requisition_detail', 'user', 'project', 'site'])
			->where('approver2_id', $this->user_id)
			->where('approver2_status', 0)->get();
		$material_requisitions = $approver1->merge($approver2);

		return view('material_requisition_approval.index', compact('material_requisitions'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int                         $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$user = MaterialRequisition::whereId($id)->first();
		$getApprover = MaterialRequisition::whereId($id)->where('approver1_id', $this->user_id)->first();

		if (count($getApprover)) {
			$approver_id = $getApprover->approver1_id;
			$material_requisitions = MaterialRequisition::with('material_requisition_detail.items')
				->whereId($id)->where('approver1_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$mrf_project_site = MaterialRequisition::with(['project', 'site'])
				->whereId($id)->first();

			return view('material_requisition_approval.show', compact('material_requisitions', 'mrf_project_site'))->with('approver', 1);
		} else {
			$getApprover = MaterialRequisition::whereId($id)->where('approver2_id', $this->user_id)->first();
			$approver_id = $getApprover->approver2_id;
			$material_requisitions = MaterialRequisition::with('material_requisition_detail.items')
				->whereId($id)->where('approver2_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$mrf_project_site = MaterialRequisition::with(['project', 'site'])
				->whereId($id)->first();

			return view('material_requisition_approval.show', compact('material_requisitions', 'mrf_project_site'))->with('approver', 2);
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

		$MaterialRequisitionDetailId = [];
		if ($request->material_requisition_detail_id) {
			foreach ($request->material_requisition_detail_id as $key => $value) {
				$MaterialRequisitionDetailId[] = $key;
			}

			$hasPending = false;
			if ($request->action == 'approve') {
				if ($request->status == 1) {
					$update_mrf = MaterialRequisitionDetail::whereIn('id', $MaterialRequisitionDetailId)->update(['status1' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = MaterialRequisitionDetail::whereMaterialRequisitionId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE MATERIAL REQUISITION STATUS TO 1 'DONE'
						MaterialRequisition::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_crf = MaterialRequisitionDetail::whereIn('id', $MaterialRequisitionDetailId)->update(['status2' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = MaterialRequisitionDetail::whereMaterialRequisitionId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE MATERIAL REQUISITION STATUS TO 1 'DONE'
						MaterialRequisition::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully approved!');

			} elseif ($request->action == 'reject') {
				if ($request->status == 1) {
					$update_mcf = MaterialRequisitionDetail::whereIn('id', $MaterialRequisitionDetailId)->update(['status1' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = MaterialRequisitionDetail::whereMaterialRequisitionId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE MATERIAL REQUISITION STATUS TO 1 'DONE'
						MaterialRequisition::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_mcf = MaterialRequisitionDetail::whereIn('id', $MaterialRequisitionDetailId)->update(['status2' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = MaterialRequisitionDetail::whereMaterialRequisitionId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE MATERIAL REQUISITION STATUS TO 1 'DONE'
						MaterialRequisition::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully rejected!');
			}
		}

		//Check to flag notification
		$requester = MaterialRequisition::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
		if (count($requester)) {
			$requester->notification = 1;
			$requester->save();

			//SEND EMAIL TO REQUESTER
			\App\Helper\Helper::requestMail($requester->user_id);
		}

		return back();
	}

}
