<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Jenssegers\Date\Date;
use Illuminate\Http\Request;
use App\Models\InventoryItemClass;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryCycleCount;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryCycleSetting;
use App\Models\InventoryCycleCountList;
use App\Models\Inventory\InventoryLocation;
use Illuminate\Database\Eloquent\Collection;

class InventoryCycleCountController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        if (Auth::check()) {
            $this->user_id = Auth::user()->id;
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (session()->has('cycle_count_date') && session()->has('store_id')) {
            $date     = session()->get('cycle_count_date');
            $store_id = session()->get('store_id');
        } else {
            $date     = date('Y-m-d');
            $store_id = 1;
        }

        $day      = Date::parse($date)->format('l');
        $item_ids = InventoryCycleSetting::where('day', $day)->whereDate('date', '=', $date)->get();
        if (!count($item_ids)) {
            $item_ids = InventoryCycleSetting::where('day', $day)->whereDate('date', '<', $date)->get();
            if (!count($item_ids)) {
                $item_ids = InventoryCycleSetting::where('day', $day)->whereDate('date', '>', $date)->get();
            }
        }

        $stores = InventoryLocation::pluck('name', 'id');
        $stores = $stores->prepend('', '');

        $all_records           = new Collection;
        $last_transaction_date = null;
        $array_items           = [];
        foreach ($item_ids as $key => $item) {
            $last_transaction_date = $item->date;
            $item_id               = $item->item_id;
            $cycle_count_lists     = InventoryCycleCountList::with('items.metrics', 'items.classes')->groupBy('store_id')->whereDate('date', '=', $date)->where('item_id', $item_id)->whereStoreId($store_id)->select(DB::raw('*, SUM(grn_supplier) as total_grn_supplier, SUM(grn_site) as total_grn_site, SUM(siv) as total_siv, MAX(date) as total_date, MAX(id) as orderById, MAX(updated_at) as orderByUpdatedAt'))->first();
            // Find date less that selected date
            if (!count($cycle_count_lists)) {
                $cycle_count_lists = InventoryCycleCountList::with('items.metrics', 'items.classes')->groupBy('store_id')->whereDate('date', '<', $date)->where('item_id', $item_id)->whereStoreId($store_id)->select(DB::raw('*, SUM(grn_supplier) as total_grn_supplier, SUM(grn_site) as total_grn_site, SUM(siv) as total_siv, MAX(date) as total_date, MAX(id) as orderById, MAX(updated_at) as orderByUpdatedAt'))->first();
                // Find date higher that selected date
                if (!count($cycle_count_lists)) {
                    $cycle_count_lists = InventoryCycleCountList::with('items.metrics', 'items.classes')->groupBy('store_id')->whereDate('date', '>', $date)->where('item_id', $item_id)->whereStoreId($store_id)->select(DB::raw('*, SUM(grn_supplier) as total_grn_supplier, SUM(grn_site) as total_grn_site, SUM(siv) as total_siv, MAX(date) as total_date, MAX(id) as orderById, MAX(updated_at) as orderByUpdatedAt'))->first();
                }
                $all_records->push($cycle_count_lists);
            } else {
                $cycle_count_lists = InventoryCycleCountList::with('items.metrics', 'items.classes')->groupBy('store_id')->whereDate('date', '<=', $date)->where('item_id', $item_id)->whereStoreId($store_id)->select(DB::raw('*, SUM(grn_supplier) as total_grn_supplier, SUM(grn_site) as total_grn_site, SUM(siv) as total_siv, MAX(date) as total_date, MAX(id) as orderById, MAX(updated_at) as orderByUpdatedAt'))->first();
                $all_records->push($cycle_count_lists);
            }

        }

        return view('inventory.cycle_count.index', compact('all_records', 'stores'))->with('date', $date);
    }

/**
 * Show the form for creating a new resource.
 *
 * @return \Illuminate\Http\Response
 */
    public function create()
    {
        $classes = InventoryItemClass::pluck('class', 'id');
        $classes = $classes->prepend('', '');

        if (!session()->has('cycle_setting_day')) {
            $array_items = InventoryCycleSetting::all()->pluck('item_id');
        } else {
            $date        = session()->get('cycle_setting_day');
            $array_items = InventoryCycleSetting::where('date', $date)->pluck('item_id');
        }

        $items = Item::with('inventory_item_class')->whereIn('id', $array_items)->paginate(30);

        return view('inventory.cycle_count.create', compact('classes', 'items'));
    }

    public function getFilterCycleCountDay($day)
    {
        $array_day = [
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday',
            '7' => 'Sunday',
        ];

        $day = $array_day[$day];

        if (!in_array($day, $array_day)) {
            return back();
        }

        $settings = InventoryCycleSetting::where('day', $day)->pluck('date')->toArray();

        if (count($settings)) {
            $max         = max(array_map('strtotime', $settings));
            $highest_day = date('Y-m-d', $max);

            session()->put('cycle_setting_day', $highest_day);

            return redirect('cycle-count/create');
        }

        flash()->error('No item on selected date');
        return back();

    }

    public function getUnFilterCycleCountDay()
    {
        session()->forget('cycle_setting_day');

        return redirect('cycle-count/create');
    }

    public function postCycleCountDate(Request $request)
    {
        $this->validate($request, [
            'date'     => 'required',
            'store_id' => 'required',
        ]);

        $date = str_replace('/', '-', $request->date);
        $date = date('Y-m-d', strtotime($date));

        session()->put('cycle_count_date', $date);
        session()->put('store_id', $request->store_id);

        return redirect('cycle-count');
    }

/**
 * Store a newly created resource in storage.
 *
 * @param  \Illuminate\Http\Request    $request
 * @return \Illuminate\Http\Response
 */
    public function store(Request $request)
    {
        $this->validate($request, [
            'day'   => 'required',
            'class' => 'required',
            'item'  => 'required',
        ]);

        $array_day = [
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday',
            '7' => 'Sunday',
        ];

        $current_dayname = $array_day[$request->day];

        if (date("w") == 1) {
            $date = date('Y-m-d', strtotime("this $current_dayname"));
        } else {
            $date = date('Y-m-d', strtotime("previous $current_dayname"));
        }

        $data = [
            'user_id'                 => $this->user_id,
            'day'                     => $current_dayname,
            'date'                    => $date,
            'inventory_item_class_id' => $request->class,
            'item_id'                 => $request->item,
        ];

        $inventory_cycle_setting = InventoryCycleSetting::updateOrCreate(['day' => $current_dayname, 'inventory_item_class_id' => $request->class, 'item_id' => $request->item], $data);

        // $data = [
        //     'user_id'                    => $this->user_id,
        //     'date'                       => $date,
        //     'inventory_cycle_setting_id' => $inventory_cycle_setting->id,
        // ];

        // InventoryCycleCount::create($data);

        session()->put('cycle_setting_day', $date);

        flash()->success('Successfully saved');
        return back();
    }

    public function postPhysicalBalance(Request $request)
    {

        $date = str_replace('/', '-', $request->date);
        $date = date('Y-m-d', strtotime($date));

        if ($request->physical_balance == null || $request->physical_balance == '') {
            $physical_balance = null;
        } else {
            $physical_balance = $request->physical_balance;
        }

        foreach ($request->physical_balance as $key => $physical_balance) {
            if ($key[0] == 'A') {
                // NEW ERP BALANCE
                $data = [
                    'user_id'          => $this->user_id,
                    'date'             => $date,
                    'item_id'          => $request->item_id,
                    'store_id'         => $request->store_id,
                    'physical_balance' => $physical_balance,
                ];
                InventoryCycleCountList::create($data);
            } else {
                // KEY IS AN ID
                $id = $key;
                InventoryCycleCountList::findOrFail($id)->update(['user_id' => $this->user_id, 'physical_balance' => $physical_balance]);
            }
            $data = [];
        }

        /*
        if ($request->has('no_inventory_setting_id')) {

        $date = str_replace('/', '-', $request->date);
        $date = date('Y-m-d', strtotime($date));

        $cycle_count_date       = InventoryCycleCount::groupBy('date')->pluck('date')->toArray();
        $today                  = Date::parse('now')->format('l');
        $array_cycle_count_date = [];

        foreach ($cycle_count_date as $count_date) {
        $group_date = Date::parse($count_date)->format('l');
        if ($today == $group_date) {
        $array_cycle_count_date[] = $count_date;
        }
        }

        if (empty($array_cycle_count_date)) {
        $array_cycle_count_date = [$today];
        }

        $max         = max(array_map('strtotime', $array_cycle_count_date));
        $highest_day = date('Y-m-d', $max);
        $day         = Date::parse($highest_day)->format('l');

        foreach ($request->physical_balance as $key => $physical_balance) {

        $cycle_setting = InventoryCycleSetting::whereItemId($key)->where('date', $highest_day)->first();

        if (count($cycle_setting)) {

        $data_cycle_setting = [
        'user_id'                 => $this->user_id,
        'day'                     => $day,
        'date'                    => $date,
        'inventory_item_class_id' => $cycle_setting->inventory_item_class_id,
        'item_id'                 => $key,
        ];

        $setting = InventoryCycleSetting::create($data_cycle_setting);

        $data = [
        'user_id'                    => $this->user_id,
        'date'                       => $date,
        'inventory_cycle_setting_id' => $setting->id,
        'physical_balance'           => $physical_balance,
        ];
        InventoryCycleCount::create($data);
        $data = [];

        $data_cycle_setting = [
        'user_id' => $this->user_id,
        'day'     => $day,
        'date'    => $highest_day,
        'item_id' => $key,
        ];
        $setting = InventoryCycleSetting::create($data_cycle_setting);
        $data    = [
        'user_id'                    => $this->user_id,
        'date'                       => $date,
        'inventory_cycle_setting_id' => $setting->id,
        'physical_balance'           => $physical_balance,
        ];
        InventoryCycleCount::updateOrCreate(['date' => $date], $data);
        $data = [];
        }

        }
        } else {
        $date = str_replace('/', '-', $request->date);
        $date = date('Y-m-d', strtotime($date));

        $cycle_count_date       = InventoryCycleCount::groupBy('date')->pluck('date')->toArray();
        $today                  = Date::parse('now')->format('l');
        $array_cycle_count_date = [];

        foreach ($cycle_count_date as $count_date) {
        $group_date = Date::parse($count_date)->format('l');
        if ($today == $group_date) {
        $array_cycle_count_date[] = $count_date;
        }
        }

        if (empty($array_cycle_count_date)) {
        $array_cycle_count_date = [$today];
        }

        $max         = max(array_map('strtotime', $array_cycle_count_date));
        $highest_day = date('Y-m-d', $max);
        $day         = Date::parse($highest_day)->format('l');

        foreach ($request->physical_balance as $key => $physical_balance) {
        $data = [
        'user_id'                    => $this->user_id,
        'date'                       => $date,
        'inventory_cycle_setting_id' => $key,
        'physical_balance'           => $physical_balance,
        ];

        InventoryCycleCount::where('date', $date)->where('inventory_cycle_setting_id', $key)->update($data);
        $data = [];
        }

        }
         */

        flash()->success('Successfully updated');
        return back();

    }

/**
 * Display the specified resource.
 *
 * @param  int                         $id
 * @return \Illuminate\Http\Response
 */
    public function show($id)
    {
        //
    }

/**
 * Show the form for editing the specified resource.
 *
 * @param  int                         $id
 * @return \Illuminate\Http\Response
 */
    public function edit($id)
    {
        //
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
        //
    }

/**
 * Remove the specified resource from storage.
 *
 * @param  int                         $id
 * @return \Illuminate\Http\Response
 */
    public function destroy($id)
    {
        if (!session()->has('cycle_setting_day')) {
            $deletedRows = InventoryCycleSetting::whereItemId($id)->pluck('id');
            InventoryCycleSetting::whereItemId($id)->delete();
            InventoryCycleCount::whereIn('inventory_cycle_setting_id', $deletedRows)->delete();

            flash()->success('Successfully deleted');
            return back();
        }

        $date        = session()->get('cycle_setting_day');
        $deletedRows = InventoryCycleSetting::whereItemId($id)->where('date', $date)->first();
        $deletedRows->delete();

        InventoryCycleCount::where('inventory_cycle_setting_id', $deletedRows->id)->where('date', $date)->delete();

        flash()->success('Successfully deleted');
        return back();
    }
};
