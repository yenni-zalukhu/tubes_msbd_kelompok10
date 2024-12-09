<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('id', 'DESC')->paginate(10);
        return view('backend.order.index')->with('orders', $orders);
    }

    public function create()
    {
        // Fitur create dihapus karena tidak ada lagi pengiriman
    }

    public function store(Request $request)
    {
        // Validasi input dari pengguna
        $this->validate($request, [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'address1' => 'string|required',
            'pickup_date' => 'date|nullable',
            'coupon' => 'nullable|numeric',
            'phone' => 'numeric|required',
            'post_code' => 'string|nullable',
            'email' => 'string|required'
        ]);

        // Cek apakah keranjang (cart) kosong
        if (empty(Cart::where('user_id', auth()->user()->id)->where('order_id', null)->first())) {
            request()->session()->flash('error', 'Cart is Empty!');
            return back();
        }

        // Persiapkan data order baru
        $order = new Order();
        $order_data = $request->all();
        $order_data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $order_data['user_id'] = $request->user()->id;
        
        // Total harga dan jumlah produk dalam keranjang
        $order_data['sub_total'] = Helper::totalCartPrice();
        $order_data['quantity'] = Helper::cartCount();

        // Jika ada kupon, hitung total setelah diskon
        if (session('coupon')) {
            $order_data['coupon'] = session('coupon')['value'];
            $order_data['total_amount'] = Helper::totalCartPrice() - session('coupon')['value'];
        } else {
            $order_data['total_amount'] = Helper::totalCartPrice();
        }

        // Status order dan metode pembayaran
        $order_data['status'] = "pending";
        if (request('payment_method') == 'transfer_bank') {
            $order_data['payment_method'] = 'transfer_bank';
            $order_data['payment_status'] = 'sudah dibayar';
        } else {
            $order_data['payment_method'] = 'bayarditoko';
            $order_data['payment_status'] = 'belum dibayar';
        }

        // Simpan order baru
        $order->fill($order_data);
        $status = $order->save();

        // Kirim notifikasi ke admin jika order berhasil
        if ($order) {
            $users = User::where('role', 'admin')->first();
            $details = [
                'title' => 'New order created',
                'actionURL' => route('order.show', $order->id),
                'fas' => 'fa-file-alt'
            ];
            Notification::send($users, new StatusNotification($details));

            // Jika pembayaran menggunakan paypal, arahkan ke halaman payment
            if (request('payment_method') == 'paypal') {
                return redirect()->route('payment')->with(['id' => $order->id]);
            } else {
                // Jika menggunakan COD, hapus cart dan coupon
                session()->forget('cart');
                session()->forget('coupon');
            }
        }

        // Update status cart menjadi terhubung dengan order yang baru
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);

        // Beri flash message bahwa order berhasil dibuat
        request()->session()->flash('success', 'Your product successfully placed in order');
        return redirect()->route('home');
    }

    public function show($id)
    {
        $order = Order::find($id);
        return view('backend.order.show')->with('order', $order);
    }

    public function edit($id)
    {
        $order = Order::find($id);
        return view('backend.order.edit')->with('order', $order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $this->validate($request, [
            'status' => 'required|in:pending,process,finished,cancel'
        ]);
        $data = $request->all();

        // Jika statusnya finished, kurangi stok produk
        if ($request->status == 'finished') {
            foreach ($order->cart as $cart) {
                $product = $cart->product;
                $product->stock -= $cart->quantity;
                $product->save();
            }
        }

        // Update order status
        $status = $order->fill($data)->save();

        // Beri feedback jika update berhasil
        if ($status) {
            request()->session()->flash('success', 'Successfully updated order');
        } else {
            request()->session()->flash('error', 'Error while updating order');
        }
        return redirect()->route('order.index');
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $status = $order->delete();
            if ($status) {
                request()->session()->flash('success', 'Order Successfully deleted');
            } else {
                request()->session()->flash('error', 'Order can not be deleted');
            }
            return redirect()->route('order.index');
        } else {
            request()->session()->flash('error', 'Order not found');
            return redirect()->back();
        }
    }

    public function orderTrack()
    {
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request)
    {
        $order = Order::where('user_id', auth()->user()->id)->where('order_number', $request->order_number)->first();
        if ($order) {
            if($order->status=="pending"){
                request()->session()->flash('success','Your order has been placed. Please wait for our confirmation.');
                return redirect()->route('home');
    
                }
                elseif($order->status=="process"){
                    request()->session()->flash('success','Your order is under processing please wait.');
                    return redirect()->route('home');
        
                }
                elseif($order->status=="finished"){
                    request()->session()->flash('success','Your order has been successfully picked up.');
                    return redirect()->route('home');
            } else {
                request()->session()->flash('error', 'Your order was canceled. Please try again.');
                return redirect()->route('home');
            }
        } else {
            request()->session()->flash('error', 'Invalid order number. Please try again.');
            return back();
        }
    }

    // PDF generate
    public function pdf(Request $request)
{
    set_time_limit(120);
    // Temukan order berdasarkan ID dari request
    $order = Order::with('cart_info')->find($request->id);

    // Jika order tidak ditemukan, kembalikan error
    if (!$order) {
        return redirect()->back()->with('error', 'Order not found.');
    }

    // Nama file untuk PDF yang dihasilkan
    $file_name = $order->order_number . '-' . $order->first_name . '.pdf';

    // Generate PDF menggunakan Blade yang sudah dimodifikasi
    $pdf = Pdf::loadView('backend.order.pdf', compact('order'));

    // Download file PDF
    return $pdf->download($file_name);
}



    // public function pdf(Request $request)
    // {
    //     set_time_limit(120);
    //     $order = Order::find($request->id);
    //     $file_name = $order->order_number . '-' . $order->first_name . '.pdf';
    //     $pdf = PDF::loadview('backend.order.pdf', compact('order'));
    //     return $pdf->download($file_name);
    // }

    // Income chart
    public function incomeChart(Request $request)
    {
        $year = \Carbon\Carbon::now()->year;
        $items = Order::with(['cart_info'])->whereYear('created_at', $year)->where('status', 'finished')->get()
            ->groupBy(function ($d) {
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });

        $result = [];
        foreach ($items as $month => $item_collections) {
            foreach ($item_collections as $item) {
                $amount = $item->cart_info->sum('amount');
                $m = intval($month);
                isset($result[$m]) ? $result[$m] += $amount : $result[$m] = $amount;
            }
        }

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $data[$monthName] = !empty($result[$i]) ? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }

        return $data;
    }

public function showBankTransfer($id)
{
    $order = Order::findOrFail($id); // Pastikan model Order sudah diimport
    return view('frontend.pages.bank-transfer', compact('order'));
}

public function uploadPaymentProof(Request $request)
{
    // Validasi file input
    $request->validate([
        'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Maksimal 2MB
    ]);

    // Simpan file ke folder 'payment_proofs' di storage
    if ($request->hasFile('payment_proof')) {
        $file = $request->file('payment_proof');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/payment_proofs', $fileName);

        $order = Order::find($request->order_id);
        if ($order) {
            $order->payment_proof = $fileName;
            $order->save();
        }
        

        // Redirect dengan pesan sukses
        return back()->with('success', 'Bukti transfer berhasil diunggah.');
    }

    // Jika file tidak ditemukan, tampilkan pesan error
    return back()->with('error', 'Gagal mengunggah bukti transfer. Silakan coba lagi.');
}


}
