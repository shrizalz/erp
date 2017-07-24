<?php
namespace App\Http\Controllers;

use Auth;
use App\User;
use Carbon\Carbon;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\InventoryCycleCountList;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\GoodsReceivedNote as GRN;
use App\Models\Inventory\GoodsReceivedNoteApprover;
use App\Models\Inventory\GoodsReceivedNoteDetail as GRNDetails;

class GoodsReceivedNoteController extends Controller
{
    /**
     * @var GRN
     */
    private $grn;

    /**
     * @param GRN $grn
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
        GoodsReceivedNote::whereUserId($this->user_id)->update(['notification' => 0]);
        $grnsuppliers = GoodsReceivedNote::with('goods_received_note_detail', 'project', 'site', 'supplier')->whereUserId($this->user_id)->orderBy('id', 'desc')->get();

        return view('inventory.grn.supplier.index', compact('grnsuppliers'));
    }

    public function create()
    {
        $approver = GoodsReceivedNoteApprover::whereRequesterId($this->user_id)->first();
        if (!count($approver)) {
            flash()->error("There's no approver. Kindly contact HR.");

            return back();
        }
        $approver1 = User::find($approver->approver1_id);
        $approver2 = User::find($approver->approver2_id);

        $designation1    = $approver1->roles()->first()->display_name;
        $designation2    = $approver2->roles()->first()->display_name;
        $projects        = Project::lists('name', 'id');
        $projects        = $projects->prepend('', '');
        $suppliers       = Supplier::where('name', '!=', '')->whereStatus1(1)->whereStatus2(1)->lists('name', 'id');
        $suppliers       = $suppliers->prepend('', '');
        $inventories     = InventoryLocation::lists('name', 'id');
        $inventories     = $inventories->prepend('', '');
        $purchase_orders = PurchaseOrder::lists('id', 'id');
        $purchase_orders = $purchase_orders->prepend('None', '-1');
        $purchase_orders = $purchase_orders->prepend('', '');
        $metrics         = Metric::pluck('name', 'id');
        $metrics         = $metrics->prepend('', '');

        return view('inventory.grn.supplier.create',
            compact('inventories', 'suppliers', 'items', 'approver1', 'approver2',
                'designation1', 'designation2', 'projects', 'purchase_orders', 'metrics'));

    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'grn_date'        => 'required',
            'project_id'      => 'required',
            'supplier'        => 'required',
            'attention_to'    => 'required',
            'forwarder_agent' => 'required',
            'vrn'             => 'required',
            'don'             => 'required',
            'invoice_no'      => 'required',
            'itemCode.*'      => 'required',
            'quantity.*'      => 'required',
            'unit.*'          => 'required',
            'store.*'         => 'required',
        ]);

        $approver = GoodsReceivedNoteApprover::whereRequesterId($this->user_id)->first();
        # get latest grn code so that we can sequentially add the next grn Codes
        $grnCode = GRN::orderBy('created_at', 'desc')->first();

        $grn_date = str_replace('/', '-', $request->grn_date);
        $grn_date = date('Y-m-d', strtotime($grn_date));

        $grnData = [
            'user_id'                     => $this->user_id,
            'purchase_order_id'           => $request->purchase_order_number,
            'grn_code'                    => is_null($grnCode) ? 1 : $grnCode->grn_code + 1,
            'grn_date'                    => $grn_date,
            'project_id'                  => $request->project_id,
            'supplier_id'                 => $request->supplier,
            'attention_to'                => $request->attention_to,
            'vehicle_registration_number' => $request->vrn,
            'forwarder_agent'             => $request->forwarder_agent,
            'delivery_order_number'       => $request->don,
            'invoice_number'              => $request->invoice_no,
            'approver1_id'                => $approver->approver1_id,
            'approver2_id'                => $approver->approver2_id,
        ];

        $createGRN = $this->grn->create($grnData);

        $grnDetailsData = [];

        if ($createGRN) {
            $array = count($request->get('itemCode'));

            for ($i = 0; $i < $array; $i++) {

                if ($request->remark[$i] == '') {
                    $remark = 'N/A';
                } else {
                    $remark = $request->remark[$i];
                }

                $grnDetailsData[] = [
                    'grn_id'     => $createGRN->id,
                    'item_id'    => $request->itemCode[$i],
                    'quantity'   => $request->quantity[$i],
                    'metric_id'  => $request->unit[$i],
                    'store_id'   => $request->store[$i],
                    'remarks'    => $remark,
                    'matrix'     => $request->itemCode[$i] . '0' . $request->store[$i],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                $item_id  = $request->itemCode[$i];
                $store_id = $request->store[$i];
                // $erp_balances = InventoryCycleCountList::groupBy('store_id')->whereItemId($item_id)->whereStoreId($store_id)->select('*', \DB::raw("SUM(grn_supplier) as total_grn_supplier"), \DB::raw("SUM(grn_site) as total_grn_site"), \DB::raw("SUM(siv) as total_siv"))->get();

                // $sum_erp_balance = 0;
                // foreach ($erp_balances as $erp_balance) {
                //     $sum_erp_balance += ($erp_balance->total_grn_supplier + $erp_balance->total_grn_site) - $erp_balance->total_siv;
                // }

                $data = [
                    'user_id'      => $this->user_id,
                    'date'         => $grn_date,
                    'item_id'      => $request->itemCode[$i],
                    'store_id'     => $request->store[$i],
                    'grn_supplier' => $request->quantity[$i],
                    // 'erp_balance'  => $sum_erp_balance + $request->quantity[$i],
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
        $gensuppliers = GoodsReceivedNote::with('goods_received_note_detail.items', 'goods_received_note_detail.stores', 'goods_received_note_detail.metrics')->whereId($id)->whereUserId($this->user_id)->orderBy('id', 'desc')->first();
        if (count($gensuppliers)) {
            $approver1    = User::find($gensuppliers->approver1_id);
            $approver2    = User::find($gensuppliers->approver2_id);
            $designation1 = $approver1->roles()->first()->display_name;
            $designation2 = $approver2->roles()->first()->display_name;

            //Display details
            $grnsupplier_detail = GoodsReceivedNote::with(['supplier', 'project', 'site'])
                ->whereId($id)->first();

            return view('inventory.grn.supplier.show', compact('gensuppliers', 'grnsupplier_detail'))
                ->with('approver1', $approver1)
                ->with('designation1', $designation1)
                ->with('approver2', $approver2)
                ->with('designation2', $designation2);
        }

        return back();
    }

    public function selectSiteOrSupplier()
    {
        return view('inventory.grn.index');
    }

}
