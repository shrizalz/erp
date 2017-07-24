<?php
namespace App\Http\Controllers;

use Auth;
use App\User;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\InventoryCycleCountList;
use App\Models\Inventory\GoodsReturnNote;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\GoodsReturnNote as GRN;
use App\Models\Inventory\GoodsReturnNoteApprover;
use App\Models\Inventory\GoodsReturnNoteDetail as GRNDetails;

class GoodsReturnNoteController extends Controller
{
    /**
     * @var GRN
     */
    private $grn;

    /**
     * @param Approver $approver
     * @param GRN      $grn
     */
    public function __construct(GRN $grn)
    {
        $this->middleware('auth');
        $this->middleware('inventory');
        if (Auth::check()) {
            $this->user_id = Auth::user()->id;
        } else {
            return redirect('/');
        }
        // inject grn model
        $this->grn = $grn;
    }

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {

        GoodsReturnNote::whereUserId($this->user_id)->update(['notification' => 0]);
        $grnsites = GoodsReturnNote::with('goods_return_note_detail', 'project', 'site')->whereUserId($this->user_id)->orderBy('id', 'desc')->get();

        return view('inventory.grn.site.index', compact('grnsites'));

    }

    /**
     * @param approver $approver
     */
    public function create()
    {
        $approver = GoodsReturnNoteApprover::whereRequesterId($this->user_id)->first();
        if (!count($approver)) {
            flash()->error("There's no approver. Kindly contact HR.");

            return back();
        }
        $approver1    = User::find($approver->approver1_id);
        $approver2    = User::find($approver->approver2_id);
        $designation1 = $approver1->roles()->first()->display_name;
        $designation2 = $approver2->roles()->first()->display_name;

        $purchaseOrderNumbers = PurchaseOrder::lists('id', 'id');
        $purchaseOrderNumbers = $purchaseOrderNumbers->prepend('', '');
        $projects             = Project::lists('name', 'id');
        $projects             = $projects->prepend('', '');
        $items                = Item::whereStatus1(1)->whereStatus2(1)->pluck('item_description', 'id');
        $items                = $items->prepend('', '');
        $suppliers            = Supplier::where('name', '!=', '')->lists('name', 'id');
        $suppliers            = $suppliers->prepend('', '');
        $stores               = InventoryLocation::lists('name', 'id');
        $stores               = $stores->prepend('', '');
        $metrics              = Metric::pluck('name', 'id');
        $metrics              = $metrics->prepend('', '');

        return view('inventory.grn.site.create', compact('approver1', 'approver2', 'designation1', 'designation2', 'purchaseOrderNumbers', 'projects', 'items', 'suppliers', 'stores', 'metrics'));

    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'grn_date'        => 'required',
            'project_id'      => 'required',
            'site_id'         => 'required',
            'attention_to'    => 'required',
            'forwarder_agent' => 'required',
            'vrn'             => 'required',
            'don'             => 'required',
            'invoice_no'      => 'required',
            'item_codes.*'    => 'required',
            'quantity'        => 'required',
            'unit.*'          => 'required',
            'store.*'         => 'required',
        ]);

        $approver = GoodsReturnNoteApprover::whereRequesterId($this->user_id)->first();
        # get latest grn code so that we can sequentially add the next grn Codes
        $grnCode = GRN::orderBy('created_at', 'desc')->first();

        $grn_date = str_replace('/', '-', $request->grn_date);
        $grn_date = date('Y-m-d', strtotime($grn_date));

        $grnData = [
            'grn_code'                    => is_null($grnCode) ? 1 : $grnCode->grn_code + 1,
            'grn_date'                    => $grn_date,
            'user_id'                     => $this->user_id,
            'project_id'                  => $request->project_id,
            'site_id'                     => $request->site_id,
            'attention_to'                => $request->attention_to,
            'forwarder_agent'             => $request->forwarder_agent,
            'vehicle_registration_number' => $request->vrn,
            'delivery_order_number'       => $request->don,
            'invoice_number'              => $request->invoice_no,
            'approver1_id'                => $approver->approver1_id,
            'approver2_id'                => $approver->approver2_id,
        ];

        $createGRN = $this->grn->create($grnData);

        $grnDetailsData = [];

        if ($createGRN) {
            $array = count($request->get('item_codes'));
            for ($i = 0; $i < $array; $i++) {
                if ($request->remark[$i] == '') {
                    $remark = 'N/A';
                } else {
                    $remark = $request->remark[$i];
                }
                $grnDetailsData[] = [
                    'grn_id'     => $createGRN->id,
                    'item_id'    => $request->item_codes[$i],
                    'metric_id'  => $request->unit[$i],
                    'quantity'   => $request->quantity[$i],
                    'store_id'   => $request->store[$i],
                    'remarks'    => $remark,
                    'matrix'     => $request->item_codes[$i] . '0' . $request->store[$i],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                $item_id  = $request->item_codes[$i];
                $store_id = $request->store[$i];
                // $erp_balances = InventoryCycleCountList::groupBy('store_id')->whereItemId($item_id)->whereStoreId($store_id)->select('*', \DB::raw("SUM(grn_supplier) as total_grn_supplier"), \DB::raw("SUM(grn_site) as total_grn_site"), \DB::raw("SUM(siv) as total_siv"))->get();

                // $sum_erp_balance = 0;
                // foreach ($erp_balances as $erp_balance) {
                //     $sum_erp_balance += ($erp_balance->total_grn_supplier + $erp_balance->total_grn_site) - $erp_balance->total_siv;
                // }

                $data = [
                    'user_id'  => $this->user_id,
                    'date'     => $grn_date,
                    'item_id'  => $item_id,
                    'store_id' => $store_id,
                    'grn_site' => $request->quantity[$i],
                    // 'erp_balance' => $sum_erp_balance + $request->quantity[$i],
                ];

                InventoryCycleCountList::create($data);
                // $sum_erp_balance = 0;
            }

            GRNDetails::insert($grnDetailsData);

            //SEND EMAIL TO APPROVAL
            \App\Helper\Helper::approvalMail($approver->approver1_id, $approver->approver2_id);

            flash()->success('Successfully saved!');
        }

        return back();

    }

    /**
     * @param $id
     */
    public function show($id)
    {
        $gensites = GoodsReturnNote::with('goods_return_note_detail.items', 'goods_return_note_detail.stores', 'goods_return_note_detail.metrics')->whereId($id)->whereUserId($this->user_id)->orderBy('id', 'desc')->first();

        if (count($gensites)) {
            $approver1    = User::find($gensites->approver1_id);
            $approver2    = User::find($gensites->approver2_id);
            $designation1 = $approver1->roles()->first()->display_name;
            $designation2 = $approver2->roles()->first()->display_name;

            //Display details
            $grnsite_detail = GoodsReturnNote::with(['project', 'site'])
                ->whereId($id)->first();

            return view('inventory.grn.site.show', compact('gensites', 'grnsite_detail'))
                ->with('approver1', $approver1)
                ->with('designation1', $designation1)
                ->with('approver2', $approver2)
                ->with('designation2', $designation2);
        }

        return back();
    }

}
