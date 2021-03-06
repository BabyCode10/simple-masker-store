<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use PDF;
use Carbon\Carbon;
use DataTables;

use App\Models\Order;
use App\Models\Province;
use App\Models\City;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.admin.order.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.admin.order.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return view('pages.admin.order.show', compact('order'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function dropshipper(Order $order)
    {
        return view('pages.admin.order.dropshipper', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        return view('pages.admin.order.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'first_name'    => 'required|max:25',
            'last_name'     => 'required|max:25',
            'resi'          => 'max:30',
            'status'        => 'required',
        ]);

        try {
            $order->bill->update([
                'total'     => $request->total
            ]);

            if ($request->courier) {
                $shipping   = $order->bill->shipping;
                $total      = $order->bill->total - $order->bill->shipping;

                $order->bill->update([
                    'shipping'  => 0,
                    'total'     => $total,
                ]);
            }

            if ($order->status != 'waited' && $order->status != 'sended' && $order->status != 'delivered') {
                foreach ($order->orderProducts as $orderProduct) {
                    $orderProduct->product->increment('stock', $orderProduct->quantity);
                }
            }

            $order->update([
                'first_name'=> $request->first_name,
                'last_name' => $request->last_name,
                'resi'      => $request->resi,
                'status'    => $request->status
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.order.index')->with('info', "Order $order->invoice updated!");
    }

    public function table(Request $request) {
        $data   = Order::with('province', 'city', 'bill.courier')->where(function ($order) use ($request) {
                $date = $request->date ? Carbon::createFromFormat('d/m/Y', $request->date) : null;

                if ($request->date && $request->status) {
                    return $order->whereDate('created_at', $date)->where('status', $request->status);
                } elseif ($request->date) {
                    return $order->whereDate('created_at', $date);
                } elseif ($request->status) {
                    return $order->where('status', $request->status);
                }
            })
            ->where('status', '!=', 'canceled')
            ->orderByDesc('updated_at')
            ->get();

        $table  = Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('buyer', function ($order) {
                return $order->first_name . ' ' . $order->last_name;
            })
            ->addColumn('province', function ($order) {
                return $order->province->name;
            })
            ->addColumn('city', function ($order) {
                return $order->city->name;
            })
            ->addColumn('courier', function ($order) {
                $name = $order->bill->courier->name ?? null;
                $service = $order->bill->courier->service ?? null;

                return $name . ' ' . $service;
            })
            ->addColumn('status', function($order) {
                $badge = '<span class="badge badge-pill badge-danger"> Canceled </span>';

                if ($order->status == 'waited') {
                    $badge = '<span class="badge badge-pill badge-primary"> Waited </span>';
                } elseif ($order->status == 'payment_confirmation') {
                    $badge = '<span class="badge badge-pill badge-warning"> Payment Confirmation </span>';
                } elseif ($order->status == 'paid') {
                    $badge = '<span class="badge badge-pill badge-light"> Paid </span>';
                } elseif ($order->status == 'sended') {
                    $badge = '<span class="badge badge-pill badge-success"> Sended </span>';
                } elseif ($order->status == 'delivered') {
                    $badge = '<span class="badge badge-pill badge-secondary"> Delivered </span>';
                }

                return $badge;
            })
            ->addColumn('marketing', function ($order) {
                return $order->user ? $order->user->name : null;
            })
            ->addColumn('quantity', function ($order) {
                return $order->orderProducts()->sum('quantity');
            })
            ->addColumn('update', function ($order) {
                return $order->updated_at->diffForHumans();
            })
            ->addColumn('action', function($order) {
                $btn = '
                    <a href="' . route('admin.order.edit', $order->id) . '" class="btn btn-sm btn-primary rounded-circle">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="' . route('admin.order.show', $order->id) . '" class="btn btn-sm btn-primary rounded-circle">
                        <i class="fas fa-eye"></i>
                    </a>

                    <a href="' . route('admin.order.dropshipper', $order->id) . '" class="btn btn-sm btn-secondary rounded-circle">
                        <i class="fas fa-eye"></i>
                    </a>
                ';

                return $btn;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
        
        return $table;
    }

    public function print(Request $request) {
        try {
            $date   = $request->from_date ? Carbon::createFromFormat('d/m/Y', $request->from_date) : null;

            $fromDate   = $request->from_date ? Carbon::createFromFormat('d/m/Y', $request->from_date) : Carbon::now()->startOfMonth();
            $toDate     = $request->to_date ? Carbon::createFromFormat('d/m/Y', $request->to_date) : Carbon::now()->endOfMonth();

            $datas  = Order::with('province', 'city', 'bill.courier', 'user')->where(function ($order) use ($request) {
                    if ($request->marketing) {
                        $order->whereHas('user', function ($query) use ($request) {
                            if ($request->marketing) {
                                return $query->where('name', $request->marketing);
                            }
                        });
                    }
                })
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->where(function ($order) use ($request) {
                    if ($request->status) {
                        return $order->where('status', $request->status);
                    }

                    return $order->where('status', '!=', 'canceled');
                })
                ->orderByDesc('updated_at')
                ->get();

            $pdf    = PDF::loadView('pdfs.order', compact('datas', 'date'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return $pdf->stream('Order ' . ($date ? $date->format('d-m-Y') : 'All Date'));
    }
}
