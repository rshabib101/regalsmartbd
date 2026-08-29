<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderDetails;
use App\Models\FundTransaction;
use App\Models\Expense;
use App\Models\Category;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Session;
use Toastr;
use Auth;
use DB;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    public function dashboard()
    {
        // =========================
        // STATUS IDS RESOLUTION
        // =========================
        $holdStatus = OrderStatus::where('slug', 'hold')->first();
        $holdStatusId = $holdStatus ? $holdStatus->id : 12;

        $cancelStatus = OrderStatus::whereIn('slug', ['cancelled', 'cancel'])->first();
        $cancelStatusId = $cancelStatus ? $cancelStatus->id : 11;

        $pendingStatus = OrderStatus::where('slug', 'pending')->first();
        $pendingStatusId = $pendingStatus ? $pendingStatus->id : 1;

        $processingStatus = OrderStatus::where('slug', 'processing')->first();
        $processingStatusId = $processingStatus ? $processingStatus->id : 2;

        $completedStatus = OrderStatus::where('slug', 'completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : 6;

        $courierStatusIds = OrderStatus::whereIn('slug', ['in-courier', 'on-the-way'])->pluck('id')->toArray();
        if (empty($courierStatusIds)) {
            $courierStatusIds = [3, 5];
        }

        // =========================
        // 1. PRIMARY METRICS (COUNT & TAKAR AMOUNT)
        // =========================
        // Total Orders
        $total_order        = Order::count();
        $total_order_amount = (float) (Order::sum('amount') ?? 0);

        // Today's Orders
        $today_order        = Order::whereDate('created_at', Carbon::today())->count();
        $today_order_amount = (float) (Order::whereDate('created_at', Carbon::today())->sum('amount') ?? 0);

        // Hold Orders
        $hold_order         = Order::where('order_status', $holdStatusId)->count();
        $hold_order_amount  = (float) (Order::where('order_status', $holdStatusId)->sum('amount') ?? 0);

        // Cancel Orders
        $cancel_order        = Order::where('order_status', $cancelStatusId)->count();
        $cancel_order_amount = (float) (Order::where('order_status', $cancelStatusId)->sum('amount') ?? 0);

        // =========================
        // 2. OPERATIONAL STATUS PIPELINE (COUNT & AMOUNT)
        // =========================
        // Pending Orders
        $pending_order        = Order::where('order_status', $pendingStatusId)->count();
        $pending_order_amount = (float) (Order::where('order_status', $pendingStatusId)->sum('amount') ?? 0);

        // Processing Orders
        $processing_order        = Order::where('order_status', $processingStatusId)->count();
        $processing_order_amount = (float) (Order::where('order_status', $processingStatusId)->sum('amount') ?? 0);

        // In Courier / On The Way
        $courier_order        = Order::whereIn('order_status', $courierStatusIds)->count();
        $courier_order_amount = (float) (Order::whereIn('order_status', $courierStatusIds)->sum('amount') ?? 0);

        // Completed / Delivered Orders
        $total_delivery         = Order::where('order_status', $completedStatusId)->count();
        $delivered_order_amount = (float) (Order::where('order_status', $completedStatusId)->sum('amount') ?? 0);

        // =========================
        // 3. STORE & CUSTOMER COUNTS
        // =========================
        $total_product  = Product::count();
        $total_customer = Customer::count();

        // Recent Orders with relations
        $latest_order = Order::latest()
            ->with(['customer', 'shipping', 'status'])
            ->limit(7)
            ->get();

        $latest_customer = Customer::latest()->limit(6)->get();

        // Today Delivered
        $today_delivery = Order::where('order_status', $completedStatusId)
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // Last Week Delivered
        $last_week = Order::where('order_status', $completedStatusId)
            ->whereBetween('updated_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();

        // Last Month Delivered
        $last_month = Order::where('order_status', $completedStatusId)
            ->whereYear('updated_at', Carbon::now()->subMonth()->year)
            ->whereMonth('updated_at', Carbon::now()->subMonth()->month)
            ->count();

        // =========================
        // 4. MONTHLY SALE CHART
        // =========================
        $monthly_sale = Order::select(
                DB::raw('DATE(updated_at) as date')
            )
            ->selectRaw('SUM(amount) as amount')
            ->where('order_status', $completedStatusId)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Fallback for monthly sales chart if no completed orders yet
        if ($monthly_sale->isEmpty()) {
            $monthly_sale = Order::select(
                    DB::raw('DATE(created_at) as date')
                )
                ->selectRaw('SUM(amount) as amount')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->limit(15)
                ->get();
        }

        // =========================
        // 5. TODAY PROFIT
        // =========================
        $todayDeliveredOrders = Order::where('order_status', $completedStatusId)
            ->whereDate('updated_at', Carbon::today())
            ->get();

        $today_sales = $todayDeliveredOrders->sum('amount');
        $todayOrderIds = $todayDeliveredOrders->pluck('id');

        $todayDetails = OrderDetails::whereIn('order_id', $todayOrderIds)
            ->with('product:id,purchase_price')
            ->get();

        $today_cogs = 0;
        foreach ($todayDetails as $row) {
            $purchase_price = $row->purchase_price ?? ($row->product->purchase_price ?? 0);
            $today_cogs += ($purchase_price * $row->qty);
        }

        $today_profit = $today_sales - $today_cogs;

        // =========================
        // 6. FUND & EXPENSES
        // =========================
        $total_fund_in  = FundTransaction::where('direction', 'in')->sum('amount');
        $total_fund_out = FundTransaction::where('direction', 'out')->sum('amount');
        $fund_balance   = $total_fund_in - $total_fund_out;

        $total_expenses = Expense::sum('amount');
        $today_expenses = Expense::whereDate('created_at', Carbon::today())->sum('amount');
        $monthly_expenses = Expense::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        // =========================
        // 7. SALES BY CATEGORY
        // =========================
        $categorySales = OrderDetails::join('products', 'order_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_details.sale_price * order_details.qty) as total_sales')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'DESC')
            ->limit(6)
            ->get();

        $categoryLabels = $categorySales->pluck('category_name')->toArray();
        $categorySeries = $categorySales->pluck('total_sales')->map(function($amount) {
            return (float) number_format($amount, 2, '.', '');
        })->toArray();

        if (empty($categoryLabels)) {
            $categoryLabels = ['Organic Products', 'Honey', 'Oils'];
            $categorySeries = [1200, 800, 450];
        }

        return view('backEnd.admin.dashboard', compact(
            'total_order',
            'total_order_amount',
            'today_order',
            'today_order_amount',
            'hold_order',
            'hold_order_amount',
            'cancel_order',
            'cancel_order_amount',
            'pending_order',
            'pending_order_amount',
            'processing_order',
            'processing_order_amount',
            'courier_order',
            'courier_order_amount',
            'total_delivery',
            'delivered_order_amount',
            'total_product',
            'total_customer',
            'latest_order',
            'latest_customer',
            'today_delivery',
            'last_week',
            'last_month',
            'monthly_sale',
            'today_profit',
            'fund_balance',
            'total_expenses',
            'today_expenses',
            'monthly_expenses',
            'categoryLabels',
            'categorySeries'
        ));
    }

    public function changepassword()
    {
        return view('backEnd.admin.changepassword');
    }

    public function newpassword(Request $request)
    {
        $this->validate($request, [
            'old_password'     => 'required',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = User::find(Auth::id());
        $check = Hash::check($request->old_password, $user->password);

        if ($check) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            Toastr::success('Password Changed Successfully', 'Success');
            return back();
        } else {
            Toastr::error('Current Password Does Not Match', 'Failed');
            return back();
        }
    }
}
