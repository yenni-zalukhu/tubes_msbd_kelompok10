<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders=Order::orderBy('id','DESC')->paginate(10);
        return view('backend.order.index')->with('orders',$orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'address' => 'string|required',
            'phone' => 'numeric|required',
            'email' => 'string|required',
            'shipping_id' => 'required|exists:shippings,id',
            
        ]);
    
        if (empty(Cart::where('user_id', auth()->user()->id)->where('order_id', null)->first())) {
            request()->session()->flash('error', 'Cart is Empty!');
            return back();
        }
    
        // Ambil data shipping cost
        $shippingCost = Shipping::where('id', $request->shipping_id)->value('price') ?? 0;
    
        // Hitung subtotal dan total amount
        $subTotal = Helper::totalCartPrice();
        $totalAmount = $subTotal + $shippingCost;
    
        // Simpan data pesanan
        $order = new Order();
        $order->fill([
            'product_id'=>$request->product_id,
            'title'=>$request->title,
            'user_id' => $request->user()->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'sub_total' => $subTotal,
            'shipping_id' => $request->shipping_id,
            'quantity' => Helper::cartCount(),
            'total_amount' => $totalAmount,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => 'belum dibayar',
        ]);
        $order->save();
    
        // Update cart dengan order_id
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);
    
        request()->session()->flash('success', 'Your product successfully placed in order');
        return redirect()->route('home');
    }
    

    public function showBankTransfer($id)
    {
        $order = Order::findOrFail($id); // Pastikan model Order sudah diimport
        return view('frontend.pages.bank-transfer', compact('order'));
    }

    public function uploadPaymentProof(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi file
        ]);
    
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/payment_proofs', $fileName);
    
            $order = Order::find($request->order_id);
            if ($order) {
                $order->payment_proof = 'payment_proofs/' . $fileName;
                $order->save();
            }
    
            return back()->with('success', 'Bukti pembayaran berhasil diunggah.');
        }
    
        return back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);
    
        // Hitung shipping cost
        $shippingCost = $order->shipping ? $order->shipping->price : 0;
    
        // Hitung total amount (subtotal + shipping)
        $totalAmount = $order->sub_total + $shippingCost;
    
        return view('user.order.show', compact('order', 'shippingCost', 'totalAmount'));
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order=Order::find($id);
        return view('backend.order.edit')->with('order',$order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order=Order::find($id);
        $this->validate($request,[
            'status'=>'required|in:pending,process,finished,cancel'
        ]);
        $data=$request->all();
        // return $request->status;
        if($request->status=='finished'){
            foreach($order->cart as $cart){
                $product=$cart->product;
                // return $product;
                $product->stock -=$cart->quantity;
                $product->save();
            }
        }
        $status=$order->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated order');
        }
        else{
            request()->session()->flash('error','Error while updating order');
        }
        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order=Order::find($id);
        if($order){
            $status=$order->delete();
            if($status){
                request()->session()->flash('success','Order Successfully deleted');
            }
            else{
                request()->session()->flash('error','Order can not deleted');
            }
            return redirect()->route('order.index');
        }
        else{
            request()->session()->flash('error','Order can not found');
            return redirect()->back();
        }
    }

    public function orderTrack(){
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request){
        // return $request->all();
        $order=Order::where('user_id',auth()->user()->id)->where('order_number',$request->order_number)->first();
        if($order){
            if($order->status=="pending"){
            request()->session()->flash('success','Your order has been placed. please wait.');
            return redirect()->route('home');

            }
            elseif($order->status=="process"){
                request()->session()->flash('success','Your order is under processing please wait.');
                return redirect()->route('home');
    
            }
            elseif($order->status=="finished"){
                request()->session()->flash('success','Your order is successfully finished.');
                return redirect()->route('home');
    
            }
            else{
                request()->session()->flash('error','Your order canceled. please try again');
                return redirect()->route('home');
    
            }
        }
        else{
            request()->session()->flash('error','Invalid order numer please try again');
            return back();
        }
    }

    // PDF generate
    public function pdf(Request $request) {
        set_time_limit(120);
        $order = Order::getAllOrder($request->id);
    
        // Pastikan data shipping_cost diambil dengan benar
        $shippingCost = $order->shipping ? $order->shipping->price : 0;
    
        // Periksa total_amount
        $totalAmount = $order->sub_total + $shippingCost;
    
        // Debugging jika diperlukan
        // dd($order->sub_total, $shippingCost, $totalAmount);
    
        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';
        $pdf = PDF::loadview('backend.order.pdf', compact('order', 'shippingCost', 'totalAmount'));
        return $pdf->download($file_name);
    }
    
    
    
    // Income chart
    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        // dd($year);
        $items=Order::with(['cart_info'])->whereYear('created_at',$year)->where('status','finished')->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
            // dd($items);
        $result=[];
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                $amount=$item->cart_info->sum('amount');
                // dd($amount);
                $m=intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }


}