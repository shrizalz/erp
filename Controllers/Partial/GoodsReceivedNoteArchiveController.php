<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Helper\Helper;
use App\Models\Inventory\GoodsReceivedNote;

class GoodsReceivedNoteArchiveController extends Controller
{
    public function __construct()
    {
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
    public function index()
    {
        $approver1 = GoodsReceivedNote::with(['goods_received_note_detail', 'user', 'project', 'site'])
            ->where('approver1_id', $this->user_id)
            ->where('approver1_status', 1)->get();
        $approver2 = GoodsReceivedNote::with(['goods_received_note_detail', 'user', 'project', 'site'])
            ->where('approver2_id', $this->user_id)
            ->where('approver2_status', 1)->get();
        $grnsuppliers = $approver1->merge($approver2);

        $grnsuppliers = Helper::paginateCollection($grnsuppliers);

        return view('inventory/grnsupplier_archive.index', compact('grnsuppliers'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int                         $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user        = GoodsReceivedNote::whereId($id)->first();
        $getApprover = GoodsReceivedNote::whereId($id)->where('approver1_id', $this->user_id)->first();

        if (count($getApprover)) {
            $approver_id  = $getApprover->approver1_id;
            $grnsuppliers = GoodsReceivedNote::with(['goods_received_note_detail.items', 'goods_received_note_detail.stores', 'goods_received_note_detail.metrics'])
                ->whereId($id)->where('approver1_id', $this->user_id)
                ->orderBy('id', 'desc')->get();
            //Display project and site
            $po_project_site = GoodsReceivedNote::whereId($id)->first();

            return view('inventory/grnsupplier_archive.show', compact('grnsuppliers', 'po_project_site'))->with('approver', 1);
        } else {
            $getApprover = GoodsReceivedNote::whereId($id)->where('approver2_id', $this->user_id)->first();

            $approver_id  = $getApprover->approver2_id;
            $grnsuppliers = GoodsReceivedNote::with(['goods_received_note_detail.items', 'goods_received_note_detail.stores', 'goods_received_note_detail.metrics'])
                ->whereId($id)->where('approver2_id', $this->user_id)
                ->orderBy('id', 'desc')->get();

            //Display project and site
            $po_project_site = GoodsReceivedNote::whereId($id)->first();

            return view('inventory/grnsupplier_archive.show', compact('grnsuppliers', 'po_project_site'))->with('approver', 2);
        }

        return back();
    }

}
