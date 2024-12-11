<!DOCTYPE html>
<html>
<head>
  <title>Order @if($order)- {{$order->order_number}} @endif</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>

@if($order)
<style type="text/css">
  .invoice-header {
    background: #f7f7f7;
    padding: 10px 20px;
    border-bottom: 1px solid gray;
  }
  .site-logo {
    margin-top: 20px;
  }
  .invoice-right-top h3 {
    color: green;
    font-size: 30px!important;
    font-family: serif;
  }
  .invoice-left-top {
    border-left: 4px solid green;
    padding-left: 20px;
    padding-top: 20px;
  }
  thead {
    background: green;
    color: #FFF;
  }
  .thanks h4 {
    color: green;
    font-size: 25px;
    font-weight: normal;
    margin-top: 20px;
  }
  .table-header {
    padding: .75rem 1.25rem;
    background-color: rgba(0,0,0,.03);
    border-bottom: 1px solid rgba(0,0,0,.125);
  }
</style>
  <div class="invoice-header">
    <div class="float-left site-logo">
      <img src="{{asset('backend/img/logo.png')}}" alt="Logo">
    </div>
    <div class="float-right site-address">
      <h4>{{env('APP_NAME')}}</h4>
      <p>{{env('APP_ADDRESS')}}</p>
      <p>Phone: <a href="tel:{{env('APP_PHONE')}}">{{env('APP_PHONE')}}</a></p>
      <p>Email: <a href="mailto:{{env('APP_EMAIL')}}">{{env('APP_EMAIL')}}</a></p>
    </div>
    <div class="clearfix"></div>
  </div>

  <div class="invoice-description">
    <div class="invoice-left-top float-left">
      <h6>Invoice to</h6>
      <h3>{{$order->first_name}} {{$order->last_name}}</h3>
      <p><strong>Address:</strong> {{ $order->address }}</p>
      <p><strong>Phone:</strong> {{ $order->phone }}</p>
      <p><strong>Email:</strong> {{ $order->email }}</p>
    </div>
    <div class="invoice-right-top float-right text-right">
      <h3>Invoice #{{$order->order_number}}</h3>
      <p>{{ $order->created_at->format('D d M Y') }}</p>
    </div>
    <div class="clearfix"></div>
  </div>

  <section class="order_details pt-3">
    <div class="table-header">
      <h5>Order Details</h5>
    </div>
    <table class="table table-bordered table-stripe">
      <thead>
        <tr>
          <th scope="col">Product</th>
          <th scope="col">Quantity</th>
          <th scope="col">Total</th>
        </tr>
      </thead>
      <tbody>
      @foreach($order->cart_info as $cart)
      @php 
        $product=DB::table('products')->select('title')->where('id',$cart->product_id)->first();
      @endphp
        <tr>
          <td>{{$product->title}}</td>
          <td>x{{$cart->quantity}}</td>
          <td>Rp{{number_format($cart->price,2)}}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th scope="col" class="empty"></th>
          <th scope="col" class="text-right">Subtotal:</th>
          <th scope="col"><span>Rp{{ number_format($order->sub_total, 2) }}</span></th>
      </tr>
      <tr>
          <th scope="col" class="empty"></th>
          <th scope="col" class="text-right">Shipping:</th>
          <th scope="col"><span>Rp{{ number_format($shippingCost, 2) }}</span></th>
      </tr>
      <tr>
          <th scope="col" class="empty"></th>
          <th scope="col" class="text-right">Total:</th>
          <th scope="col">
              <span>Rp{{ number_format($order->sub_total + $shippingCost, 2) }}</span>
          </th>
      </tr>
      
      </tfoot>
    </table>
  </section>

  <div class="thanks mt-3">
    <h4>Thank you for your business!</h4>
  </div>

  <div class="authority float-right mt-5">
    <p>-----------------------------------</p>
    <h5>Authority Signature:</h5>
  </div>

  <div class="clearfix"></div>
@else
  <h5 class="text-danger">Invalid Order</h5>
@endif
</body>
</html>
