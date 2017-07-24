<?php

namespace App\Http\Controllers;

use App\Models\Inventory\GoodsReturnNote;
use App\Models\Inventory\GoodsReturnNoteDetail;
use App\User;
use Auth;
use Illuminate\Http\Request;

class GoodsReturnNoteApprovalController extends Controller {
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
		$approver1 = GoodsReturnNote::with(['goods_return_note_detail', 'user', 'project', 'site'])
			->where('approver1_id', $this->user_id)
			->where('approver1_status', 0)->get();
		$approver2 = GoodsReturnNote::with(['goods_return_note_detail', 'user', 'project', 'site'])
			->where('approver2_id', $this->user_id)
			->where('approver2_status', 0)->get();
		$grnsites = $approver1->merge($approver2);

		return view('inventory/grnsite_approval.index', compact('grnsites'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int                         $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$user = GoodsReturnNote::whereId($id)->first();
		$getApprover = GoodsReturnNote::whereId($id)->where('approver1_id', $this->user_id)->first();

		if (count($getApprover)) {
			$approver_id = $getApprover->approver1_id;
			$grnsites = GoodsReturnNote::with(['goods_return_note_detail.items', 'goods_return_note_detail.stores', 'goods_return_note_detail.metrics'])
				->whereId($id)->where('approver1_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$po_project_site = GoodsReturnNote::whereId($id)->first();

			return view('inventory/grnsite_approval.show', compact('grnsites', 'po_project_site'))->with('approver', 1);
		} else {
			$getApprover = GoodsReturnNote::whereId($id)->where('approver2_id', $this->user_id)->first();

			$approver_id = $getApprover->approver2_id;
			$grnsites = GoodsReturnNote::with(['goods_return_note_detail.items', 'goods_return_note_detail.stores', 'goods_return_note_detail.metrics'])
				->whereId($id)->where('approver2_id', $this->user_id)
				->orderBy('id', 'desc')->get();

			//Display project and site
			$po_project_site = GoodsReturnNote::whereId($id)->first();

			return view('inventory/grnsite_approval.show', compact('grnsites', 'po_project_site'))->with('approver', 2);
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

		$goodsReturnNoteDetailId = [];
		if ($request->goods_return_note_detail_id) {
			foreach ($request->goods_return_note_detail_id as $key => $value) {
				$goodsReturnNoteDetailId[] = $key;
			}

			$hasPending = false;
			if ($request->action == 'approve') {
				if ($request->status == 1) {
					$update_mrf = GoodsReturnNoteDetail::whereIn('id', $goodsReturnNoteDetailId)->update(['status1' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = GoodsReturnNoteDetail::whereGrnId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReturnNote::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_crf = GoodsReturnNoteDetail::whereIn('id', $goodsReturnNoteDetailId)->update(['status2' => 1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = GoodsReturnNoteDetail::whereGrnId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReturnNote::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully approved!');

			} elseif ($request->action == 'reject') {
				if ($request->status == 1) {
					$update_mcf = GoodsReturnNoteDetail::whereIn('id', $goodsReturnNoteDetailId)->update(['status1' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status1 = GoodsReturnNoteDetail::whereGrnId($id)->get();
					foreach ($status1 as $key => $value) {
						if ($value->status1 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReturnNote::whereId($id)->update(['approver1_status' => 1]);
					}
				} elseif ($request->status == 2) {
					$update_mcf = GoodsReturnNoteDetail::whereIn('id', $goodsReturnNoteDetailId)->update(['status2' => -1]);
					// CHECK IF ALL ITEM UPDATE FLAG STATUS IN TO DONE
					$status2 = GoodsReturnNoteDetail::whereGrnId($id)->get();
					foreach ($status2 as $key => $value) {
						if ($value->status2 == 0) {
							$hasPending = true;
						}
					}
					if ($hasPending == false) {
						// UPDATE STATUS TO 1 'DONE'
						GoodsReturnNote::whereId($id)->update(['approver2_status' => 1]);
					}
				}
				flash()->success('Successfully rejected!');
			}
		}

		// Check to flag notification
		$requester = GoodsReturnNote::whereId($id)->where('approver1_status', 1)->where('approver2_status', 1)->first();
		if (count($requester)) {
			$requester->notification = 1;
			$requester->save();

			//SEND EMAIL TO REQUESTER
			\App\Helper\Helper::requestMail($requester->user_id);
		}

		return back();

	}

}
