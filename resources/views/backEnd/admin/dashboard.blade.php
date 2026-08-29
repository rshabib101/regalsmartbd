@extends('backEnd.layouts.master')
@section('title','Sales & Orders Dashboard')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.5/dist/apexcharts.css" rel="stylesheet">
<style>
    /* ===================================================
       MODERN ANIMATED & COLORFUL DASHBOARD DESIGN SYSTEM
       =================================================== */

    /* Hero Metric Cards with Radiant Gradients */
    .hero-metric-card {
        position: relative;
        border-radius: 18px;
        padding: 24px;
        color: #fff;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: block;
        border: 1px solid rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
    }
    .hero-metric-card:hover {
        transform: translateY(-6px);
        color: #fff;
    }
    .hero-metric-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
        transition: transform 0.5s ease;
    }
    .hero-metric-card:hover::before {
        transform: scale(1.35);
    }

    /* Gradients */
    .card-gradient-total {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        box-shadow: 0 12px 28px -6px rgba(79, 70, 229, 0.42);
    }
    .card-gradient-today {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 12px 28px -6px rgba(5, 150, 105, 0.42);
    }
    .card-gradient-hold {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 12px 28px -6px rgba(217, 119, 6, 0.42);
    }
    .card-gradient-cancel {
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        box-shadow: 0 12px 28px -6px rgba(225, 29, 72, 0.42);
    }

    .hero-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }
    .hero-card-label {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        opacity: 0.9;
        margin: 0;
    }
    .hero-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    .hero-metric-card:hover .hero-card-icon {
        transform: scale(1.1) rotate(5deg);
    }
    .hero-amount {
        font-size: 27px;
        font-weight: 800;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    .hero-count-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .hero-footer-link {
        margin-top: 14px;
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        opacity: 0.92;
    }

    /* Secondary Pipeline Status Cards */
    .status-card {
        background: #fff;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        border: 1px solid #f1f5f9;
        border-left: 5px solid #cbd5e1;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
    }
    .status-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    .status-card-pending {
        border-left-color: #0284c7;
    }
    .status-card-processing {
        border-left-color: #7c3aed;
    }
    .status-card-courier {
        border-left-color: #2563eb;
    }
    .status-card-completed {
        border-left-color: #059669;
    }

    .status-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .status-icon-pending { background: #e0f2fe; color: #0284c7; }
    .status-icon-processing { background: #ede9fe; color: #7c3aed; }
    .status-icon-courier { background: #dbeafe; color: #2563eb; }
    .status-icon-completed { background: #d1fae5; color: #059669; }

    .status-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 2px;
    }
    .status-card-amount {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .status-card-count {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    /* Financial Banner */
    .finance-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        color: #fff;
        padding: 22px 26px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        margin-bottom: 24px;
    }
    .finance-stat-item {
        padding: 6px 14px;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }
    .finance-stat-item:last-child {
        border-right: none;
    }
    .finance-stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .finance-stat-val {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
    }

    /* Standard Card Container */
    .content-box {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid #f1f5f9;
        height: 100%;
    }
    .content-box-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Table Styles */
    .dashboard-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 14px;
    }
    .dashboard-table tbody td {
        padding: 13px 14px;
        vertical-align: middle;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .order-status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .pill-pending { background: #e0f2fe; color: #0284c7; }
    .pill-processing { background: #ede9fe; color: #7c3aed; }
    .pill-courier { background: #dbeafe; color: #2563eb; }
    .pill-completed { background: #dcfce7; color: #15803d; }
    .pill-hold { background: #fef3c7; color: #b45309; }
    .pill-cancelled { background: #ffe4e6; color: #be123c; }

    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        display: inline-block;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
        70% { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Header with User Greeting & Quick Date Filter --}}
    <div class="row mb-3 align-items-center">
        <div class="col-md-8">
            <h4 class="fw-bold text-dark mb-1" style="font-size: 22px;">
                Welcome back, {{ Auth::user()->name ?? 'Admin' }}! 👋
            </h4>
            <p class="text-muted font-size-13 mb-0">
                <span class="pulse-dot me-1"></span> Live Store Overview &bull; {{ \Carbon\Carbon::now()->format('l, d F Y') }}
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-2 mt-md-0">
            <a href="{{ route('admin.orders', ['slug'=>'all']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fe-shopping-bag me-1"></i> View All Orders
            </a>
        </div>
    </div>

    {{-- ========================================================
         PRIMARY 4 HERO CARDS (COUNT & TAKAR AMOUNT TOGETHER)
         ======================================================== --}}
    <div class="row g-3 mb-3">
        {{-- Total Orders --}}
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('admin.orders', ['slug'=>'all']) }}" class="hero-metric-card card-gradient-total">
                <div class="hero-card-header">
                    <span class="hero-card-label">Total Orders</span>
                    <div class="hero-card-icon">
                        <i class="fe-shopping-cart"></i>
                    </div>
                </div>
                <div class="hero-amount">
                    ৳ {{ number_format($total_order_amount, 2) }}
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="hero-count-pill">
                        <i class="fe-check-circle"></i> {{ number_format($total_order) }} Orders
                    </span>
                    <span class="badge bg-white text-dark rounded-pill px-2 py-1 font-size-11">All Time</span>
                </div>
                <div class="hero-footer-link">
                    <span>Manage all orders</span>
                    <i class="fe-arrow-right"></i>
                </div>
            </a>
        </div>

        {{-- Today's Orders --}}
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('admin.orders', ['slug'=>'all']) }}" class="hero-metric-card card-gradient-today">
                <div class="hero-card-header">
                    <span class="hero-card-label">Today's Orders</span>
                    <div class="hero-card-icon">
                        <i class="fe-zap"></i>
                    </div>
                </div>
                <div class="hero-amount">
                    ৳ {{ number_format($today_order_amount, 2) }}
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="hero-count-pill">
                        <i class="fe-clock"></i> {{ number_format($today_order) }} Orders Today
                    </span>
                    <span class="badge bg-white text-dark rounded-pill px-2 py-1 font-size-11">Today</span>
                </div>
                <div class="hero-footer-link">
                    <span>View today's sales</span>
                    <i class="fe-arrow-right"></i>
                </div>
            </a>
        </div>

        {{-- Hold Orders --}}
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('admin.orders', ['slug'=>'hold']) }}" class="hero-metric-card card-gradient-hold">
                <div class="hero-card-header">
                    <span class="hero-card-label">Hold Orders</span>
                    <div class="hero-card-icon">
                        <i class="fe-pause-circle"></i>
                    </div>
                </div>
                <div class="hero-amount">
                    ৳ {{ number_format($hold_order_amount, 2) }}
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="hero-count-pill">
                        <i class="fe-alert-circle"></i> {{ number_format($hold_order) }} On Hold
                    </span>
                    <span class="badge bg-white text-dark rounded-pill px-2 py-1 font-size-11">Pending Review</span>
                </div>
                <div class="hero-footer-link">
                    <span>Process hold orders</span>
                    <i class="fe-arrow-right"></i>
                </div>
            </a>
        </div>

        {{-- Cancel Orders --}}
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('admin.orders', ['slug'=>'cancelled']) }}" class="hero-metric-card card-gradient-cancel">
                <div class="hero-card-header">
                    <span class="hero-card-label">Cancelled Orders</span>
                    <div class="hero-card-icon">
                        <i class="fe-x-circle"></i>
                    </div>
                </div>
                <div class="hero-amount">
                    ৳ {{ number_format($cancel_order_amount, 2) }}
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="hero-count-pill">
                        <i class="fe-slash"></i> {{ number_format($cancel_order) }} Cancelled
                    </span>
                    <span class="badge bg-white text-dark rounded-pill px-2 py-1 font-size-11">Lost Revenue</span>
                </div>
                <div class="hero-footer-link">
                    <span>View cancel details</span>
                    <i class="fe-arrow-right"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- ========================================================
         SECONDARY OPERATIONAL STATUS PIPELINE (COUNT & AMOUNT)
         ======================================================== --}}
    <div class="row g-3 mb-4">
        {{-- Pending Orders --}}
        <div class="col-lg-3 col-md-6 col-6">
            <a href="{{ route('admin.orders', ['slug'=>'pending']) }}" class="status-card status-card-pending">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="status-card-title">Pending Orders</span>
                    <div class="status-card-icon status-icon-pending">
                        <i class="fe-clock"></i>
                    </div>
                </div>
                <div class="status-card-amount">৳ {{ number_format($pending_order_amount, 2) }}</div>
                <div class="status-card-count">
                    <span class="text-primary fw-bold">{{ number_format($pending_order) }}</span> Orders Pending
                </div>
            </a>
        </div>

        {{-- Processing Orders --}}
        <div class="col-lg-3 col-md-6 col-6">
            <a href="{{ route('admin.orders', ['slug'=>'processing']) }}" class="status-card status-card-processing">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="status-card-title">Processing</span>
                    <div class="status-card-icon status-icon-processing">
                        <i class="fe-refresh-cw"></i>
                    </div>
                </div>
                <div class="status-card-amount">৳ {{ number_format($processing_order_amount, 2) }}</div>
                <div class="status-card-count">
                    <span class="text-purple fw-bold" style="color: #7c3aed;">{{ number_format($processing_order) }}</span> In Processing
                </div>
            </a>
        </div>

        {{-- In Courier / On The Way --}}
        <div class="col-lg-3 col-md-6 col-6">
            <a href="{{ route('admin.orders', ['slug'=>'in-courier']) }}" class="status-card status-card-courier">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="status-card-title">In Courier / Transit</span>
                    <div class="status-card-icon status-icon-courier">
                        <i class="fe-truck"></i>
                    </div>
                </div>
                <div class="status-card-amount">৳ {{ number_format($courier_order_amount, 2) }}</div>
                <div class="status-card-count">
                    <span class="text-blue fw-bold" style="color: #2563eb;">{{ number_format($courier_order) }}</span> Shipped
                </div>
            </a>
        </div>

        {{-- Completed / Delivered --}}
        <div class="col-lg-3 col-md-6 col-6">
            <a href="{{ route('admin.orders', ['slug'=>'completed']) }}" class="status-card status-card-completed">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="status-card-title">Delivered & Completed</span>
                    <div class="status-card-icon status-icon-completed">
                        <i class="fe-check-circle"></i>
                    </div>
                </div>
                <div class="status-card-amount">৳ {{ number_format($delivered_order_amount, 2) }}</div>
                <div class="status-card-count">
                    <span class="text-success fw-bold">{{ number_format($total_delivery) }}</span> Successfully Delivered
                </div>
            </a>
        </div>
    </div>

    {{-- ========================================================
         STORE & FINANCIAL INSIGHTS STRIP
         ======================================================== --}}
    <div class="finance-banner">
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-3 col-6 mb-2 mb-md-0 finance-stat-item">
                <div class="finance-stat-label"><i class="fe-dollar-sign me-1"></i> Today's Profit</div>
                <div class="finance-stat-val text-success">৳ {{ number_format($today_profit ?? 0, 2) }}</div>
            </div>
            <div class="col-md-3 col-6 mb-2 mb-md-0 finance-stat-item">
                <div class="finance-stat-label"><i class="fe-briefcase me-1"></i> Fund Balance</div>
                <div class="finance-stat-val text-info">৳ {{ number_format($fund_balance ?? 0, 2) }}</div>
            </div>
            <div class="col-md-3 col-6 finance-stat-item">
                <div class="finance-stat-label"><i class="fe-credit-card me-1"></i> Total Expenses</div>
                <div class="finance-stat-val text-warning">৳ {{ number_format($total_expenses ?? 0, 2) }}</div>
            </div>
            <div class="col-md-3 col-6 finance-stat-item">
                <div class="finance-stat-label"><i class="fe-users me-1"></i> Total Customers</div>
                <div class="finance-stat-val text-white">{{ number_format($total_customer ?? 0) }}</div>
            </div>
        </div>
    </div>

    {{-- ========================================================
         CHARTS SECTION
         ======================================================== --}}
    <div class="row g-3 mb-4">
        {{-- Sales By Category --}}
        <div class="col-lg-4">
            <div class="content-box">
                <div class="content-box-title">
                    <span><i class="fe-pie-chart text-primary me-2"></i> Sales by Category</span>
                </div>
                <div id="categoryChart"></div>
            </div>
        </div>

        {{-- Monthly Sales Overview --}}
        <div class="col-lg-8">
            <div class="content-box">
                <div class="content-box-title">
                    <span><i class="fe-trending-up text-success me-2"></i> Sales Revenue Trend</span>
                    <span class="badge bg-light text-dark font-size-12 px-2 py-1 rounded-pill">Recent Transactions</span>
                </div>
                <div id="salesChart"></div>
            </div>
        </div>
    </div>

    {{-- ========================================================
         RECENT ORDERS & RECENT CUSTOMERS
         ======================================================== --}}
    <div class="row g-3">
        {{-- Recent Orders Table --}}
        <div class="col-lg-8">
            <div class="content-box">
                <div class="content-box-title">
                    <span><i class="fe-shopping-bag text-primary me-2"></i> Recent Orders</span>
                    <a href="{{ route('admin.orders', ['slug'=>'all']) }}" class="btn btn-sm btn-link p-0 text-primary font-size-13 text-decoration-none">
                        View All <i class="fe-chevron-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table dashboard-table mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latest_order ?? [] as $order)
                            @php
                                $statusSlug = $order->status?->slug ?? 'pending';
                                $statusName = $order->status?->name ?? 'Pending';
                                
                                $pillClass = 'pill-pending';
                                if ($statusSlug === 'completed' || $order->order_status == 6) {
                                    $pillClass = 'pill-completed';
                                } elseif ($statusSlug === 'processing' || $order->order_status == 2) {
                                    $pillClass = 'pill-processing';
                                } elseif (in_array($statusSlug, ['in-courier', 'on-the-way']) || in_array($order->order_status, [3, 5])) {
                                    $pillClass = 'pill-courier';
                                } elseif ($statusSlug === 'hold' || $order->order_status == 12) {
                                    $pillClass = 'pill-hold';
                                } elseif (in_array($statusSlug, ['cancelled', 'cancel']) || $order->order_status == 11) {
                                    $pillClass = 'pill-cancelled';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders', ['slug'=>'all']) }}" class="fw-bold text-dark">
                                        #{{ $order->invoice_id ?? $order->id }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $order->shipping?->name ?? ($order->customer?->name ?? 'Guest') }}</div>
                                    <small class="text-muted">{{ $order->shipping?->phone ?? ($order->customer?->phone ?? '-') }}</small>
                                </td>
                                <td class="fw-bold text-dark">
                                    ৳ {{ number_format($order->amount, 2) }}
                                </td>
                                <td>
                                    <span class="order-status-pill {{ $pillClass }}">
                                        {{ $statusName }}
                                    </span>
                                </td>
                                <td class="text-muted font-size-12">
                                    {{ optional($order->created_at)->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No recent orders found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Customers --}}
        <div class="col-lg-4">
            <div class="content-box">
                <div class="content-box-title">
                    <span><i class="fe-users text-info me-2"></i> Recent Customers</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($latest_customer ?? [] as $cust)
                    <div class="list-group-item d-flex align-items-center px-0 py-3 border-bottom">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 42px; height: 42px; font-size: 16px;">
                            {{ strtoupper(substr($cust->name ?? 'C', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark font-size-14">{{ $cust->name }}</h6>
                            <small class="text-muted"><i class="fe-phone font-size-11 me-1"></i>{{ $cust->phone ?? 'No phone' }}</small>
                        </div>
                        <span class="badge bg-light text-muted font-size-11">
                            {{ optional($cust->created_at)->diffForHumans(null, true, true) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        No customers registered yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.5"></script>
<script>
// Category Sales Donut Chart
new ApexCharts(document.querySelector("#categoryChart"), {
    chart: { type: 'donut', height: 280 },
    labels: @json($categoryLabels ?? ['Organic Products']),
    series: @json($categorySeries ?? [1000]),
    colors: ['#4f46e5', '#059669', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6'],
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { enabled: false },
    tooltip: {
        y: {
            formatter: function(val) {
                return '৳ ' + Number(val).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
    },
    plotOptions: {
        pie: {
            donut: {
                size: '72%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total Sales',
                        fontSize: '13px',
                        fontWeight: 600,
                        color: '#64748b',
                        formatter: function() {
                            var total = @json(array_sum($categorySeries ?? [0]));
                            return '৳ ' + Number(total).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        }
                    }
                }
            }
        }
    }
}).render();

// Monthly Sales Area Chart
new ApexCharts(document.querySelector("#salesChart"), {
    chart: { 
        type: 'area', 
        height: 280, 
        toolbar: { show: false },
        zoom: { enabled: false }
    },
    colors: ['#4f46e5'],
    series: [{
        name: 'Revenue (৳)',
        data: @json(($monthly_sale ?? collect())->pluck('amount'))
    }],
    xaxis: {
        categories: @json(($monthly_sale ?? collect())->pluck('date')),
        labels: { style: { fontSize: '11px', colors: '#64748b' } }
    },
    yaxis: {
        labels: {
            formatter: function(val) {
                return '৳ ' + Number(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            },
            style: { fontSize: '11px', colors: '#64748b' }
        }
    },
    stroke: { curve: 'smooth', width: 3 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 90, 100]
        }
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return '৳ ' + Number(val).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
    }
}).render();
</script>
@endsection
