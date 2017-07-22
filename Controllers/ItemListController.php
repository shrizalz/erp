<?php

namespace App\Http\Controllers;

use App\Models\Item;

class ItemListController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('inventory');
        if (\Auth::check()) {
            $this->user_id = \Auth::user()->id;
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
        $items = Item::with('category', 'metrics')->orderBy('id', 'desc')->paginate(30);

        return view('item_list.index', compact('items'));
    }

}
