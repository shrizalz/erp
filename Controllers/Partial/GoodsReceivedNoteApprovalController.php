<?php

namespace App\Http\Controllers;

use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\GoodsReceivedNoteDetail;
use App\User;
use Auth;
use Illuminate\Http\Request;

class GoodsReceivedNoteApprovalController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('inventory');
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
		$approver1 = GoodsReceivedNote::with(['goods_received_note_detail', 'user', 'project', 'site'])
			->where('approver1_id', $this->user_id)
			->where('approver1_status', 0)->get();
		$approver2 = GoodsReceivedNote::with(['goods_received_note_detail', 'user', 'project', 'site'])
			->where('approver2_id', $this->user_id)
			->where('approver2_status', 0)->get();
		$grnsuppliers = $approver1->merge($approver2);

		return view('inventory/grnsupplier_approval.index', compact('grnsuppliers'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int                         $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$user = GoodsReceivedNote::whereId($id)->first();
		$getApprover = GoodsReceivedNote::whereId($id)->where('approver1_id', $this->user_id)->first();

		if (count($getApprover)) {
			$approver_id = $getApprover->approver1_id;
			$grnsuppliers = GoodsReceivedNote::with(['goods_received_note_detail.items', 'goods_received_note_detail.stores', 'goods_received_note_detail.metrics'])
				->whereId($id)->where('approver1_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$po_project_site = GoodsReceivedNote::whereId($id)->first();

			return view('inventory/grnsupplier_approval.show', compact('grnsuppliers', 'po_project_site'))->with('approver', 1);
		} else {
			$getApprover = GoodsReceivedNote::whereId($id)->where('approver2_id', $this->user_id)->first();

			$approver_id = $getApprover->approver2_id;
			$grnsuppliers = GoodsReceivedNote::with(['goods_received_note_detail.items', 'goods_received_note_detail.stores', 'goods_received_note_detail.metrics'])
				->whereId($id)->where('approver2_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$po_project_site = GoodsReceivedNote::whereId($id)->first();

			return view('inventory/grnsupplier_approval.show', compact('grnsuppliers', 'po_project_site'))->with('approver', 2);
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

		$goodsReceivedNoteDetailId = [];
		if ($request->goods_received_note_detail_id) {
			foreach ($request->goods_received_note_detail_id as $key => $value) {
				$goodsReceivedNoteDetailId[] = $key;
			}

			$hasPending = false;
			if ($request->action == 'approve') {
				if ($request->status == 1) {
					$update_mrf = GoodsReceivedNoteDetail::whereIn('id', $goodsReceivedNoteDetailId)->update(['status1' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = GoodsReceivedNoteDetail::whereGrnId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReceivedNote::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_crf = GoodsReceivedNoteDetail::whereIn('id', $goodsReceivedNoteDetailId)->update(['status2' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = GoodsReceivedNoteDetail::whereGrnId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReceivedNote::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully approved!');

			} elseif ($request->action == 'reject') {
				if ($request->status == 1) {
					$update_mcf = GoodsReceivedNoteDetail::whereIn('id', $goodsReceivedNoteDetailId)->update(['status1' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = GoodsReceivedNoteDetail::whereGrnId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReceivedNote::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_mcf = GoodsReceivedNoteDetail::whereIn('id', $goodsReceivedNoteDetailId)->update(['status2' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = GoodsReceivedNoteDetail::whereGrnId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReceivedNote::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully rejected!');
			}

		}

		// Check to flag notification
		$requester = GoodsReceivedNote::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
		if (count($requester)) {
			$requester->notification = 1;
			$requester->save();

			//SEND EMAIL TO REQUESTER
			\App\Helper\Helper::requestMail($requester->user_id);

		}

		return back();
	}

}
