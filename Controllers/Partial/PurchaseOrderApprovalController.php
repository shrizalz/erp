<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\User;
use Auth;
use Illuminate\Http\Request;

class PurchaseOrderApprovalController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('procurement');
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
		$approver1 = PurchaseOrder::with(['purchase_order_detail', 'user', 'project'])
			->where('approver1_id', $this->user_id)
			->where('approver1_status', 0)->get();
		$approver2 = PurchaseOrder::with(['purchase_order_detail', 'user', 'project'])
			->where('approver2_id', $this->user_id)
			->where('approver2_status', 0)->get();
		$purchase_orders = $approver1->merge($approver2);

		return view('purchase_order_approval.index', compact('purchase_orders'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int                         $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$user = PurchaseOrder::whereId($id)->first();
		$getApprover = PurchaseOrder::whereId($id)->where('approver1_id', $this->user_id)->first();

		if (count($getApprover)) {
			$approver_id = $getApprover->approver1_id;
			$purchase_orders = PurchaseOrder::with('purchase_order_detail.items')
				->whereId($id)->where('approver1_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$po_project_site = PurchaseOrder::with(['supplier', 'department', 'project'])
				->whereId($id)->first();

			return view('purchase_order_approval.show', compact('purchase_orders', 'po_project_site'))->with('approver', 1);
		} else {
			$getApprover = PurchaseOrder::whereId($id)->where('approver2_id', $this->user_id)->first();
			$approver_id = $getApprover->approver2_id;
			$purchase_orders = PurchaseOrder::with('purchase_order_detail.items')
				->whereId($id)->where('approver2_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$po_project_site = PurchaseOrder::with(['supplier', 'department', 'project'])
				->whereId($id)->first();

			return view('purchase_order_approval.show', compact('purchase_orders', 'po_project_site'))->with('approver', 2);
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

		$purchaseOrderDetailId = [];
		if ($request->purchase_order_detail_id) {
			foreach ($request->purchase_order_detail_id as $key => $value) {
				$purchaseOrderDetailId[] = $key;
			}

			$hasPending = false;
			if ($request->action == 'approve') {
				if ($request->status == 1) {
					$update_mrf = PurchaseOrderDetail::whereIn('id', $purchaseOrderDetailId)->update(['status1' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = PurchaseOrderDetail::wherePurchaseOrderId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE MATERIAL REQUISITION STATUS TO 1 'DONE'
						PurchaseOrder::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_crf = PurchaseOrderDetail::whereIn('id', $purchaseOrderDetailId)->update(['status2' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = PurchaseOrderDetail::wherePurchaseOrderId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE MATERIAL REQUISITION STATUS TO 1 'DONE'
						PurchaseOrder::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully approved!');

			} elseif ($request->action == 'reject') {
				if ($request->status == 1) {
					$update_mcf = PurchaseOrderDetail::whereIn('id', $purchaseOrderDetailId)->update(['status1' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = PurchaseOrderDetail::wherePurchaseOrderId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						PurchaseOrder::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_mcf = PurchaseOrderDetail::whereIn('id', $purchaseOrderDetailId)->update(['status2' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = PurchaseOrderDetail::wherePurchaseOrderId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						PurchaseOrder::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully rejected!');
			}
		}

		// Check to flag notification
		$requester = PurchaseOrder::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
		if (count($requester)) {
			$requester->notification = 1;
			$requester->save();

			//SEND EMAIL TO REQUESTER
			\App\Helper\Helper::requestMail($requester->user_id);
		}

		return back();
	}

}
