<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Helper\Helper;
use App\Models\Inventory\GoodsReturnNote;

class GoodsReturnNoteArchiveController extends Controller
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
        $approver1 = GoodsReturnNote::with(['goods_return_note_detail', 'user', 'project', 'site'])
            ->where('approver1_id', $this->user_id)
            ->where('approver1_status', 1)->get();
        $approver2 = GoodsReturnNote::with(['goods_return_note_detail', 'user', 'project', 'site'])
            ->where('approver2_id', $this->user_id)
            ->where('approver2_status', 1)->get();
        $grnsites = $approver1->merge($approver2);

        $grnsites = Helper::paginateCollection($grnsites);

        return view('inventory/grnsite_archive.index', compact('grnsites'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int                         $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user        = GoodsReturnNote::whereId($id)->first();
        $getApprover = GoodsReturnNote::whereId($id)->where('approver1_id', $this->user_id)->first();

        if (count($getApprover)) {
            $approver_id = $getApprover->approver1_id;
            $grnsites    = GoodsReturnNote::with(['goods_return_note_detail.items', 'goods_return_note_detail.stores', 'goods_return_note_detail.metrics'])
                ->whereId($id)->where('approver1_id', $this->user_id)
                ->orderBy('id', 'desc')->get();

            //Display project and site
            $po_project_site = GoodsReturnNote::whereId($id)->first();

            return view('inventory/grnsite_archive.show', compact('grnsites', 'po_project_site'))->with('approver', 1);
        } else {
            $getApprover = GoodsReturnNote::whereId($id)->where('approver2_id', $this->user_id)->first();

            $approver_id = $getApprover->approver2_id;
            $grnsites    = GoodsReturnNote::with(['goods_return_note_detail.items', 'goods_return_note_detail.stores', 'goods_return_note_detail.metrics'])
                ->whereId($id)->where('approver2_id', $this->user_id)
                ->orderBy('id', 'desc')->get();

            //Display project and site
            $po_project_site = GoodsReturnNote::whereId($id)->first();

            return view('inventory/grnsite_archive.show', compact('grnsites', 'po_project_site'))->with('approver', 2);
        }

        return back();
    }

}
