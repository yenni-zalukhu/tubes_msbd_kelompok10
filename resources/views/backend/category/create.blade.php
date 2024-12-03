@extends('backend.layouts.master')

@section('main-content')

<div class="card">
  <h5 class="card-header">Add Transaction</h5>
  <div class="card-body">
      <form method="post" action="{{route('transaction.store')}}">
          {{csrf_field()}}

          <!-- Transaction ID (Auto-generate or User Input) -->
          <div class="form-group">
              <label for="transaction_id" class="col-form-label">Transaction ID <span class="text-danger">*</span></label>
              <input id="transaction_id" type="text" name="transaction_id" placeholder="Enter transaction ID" value="{{old('transaction_id')}}" class="form-control">
              @error('transaction_id')
                  <span class="text-danger">{{$message}}</span>
              @enderror
          </div>

          <!-- Customer ID -->
          <div class="form-group">
              <label for="customer_id" class="col-form-label">Customer ID <span class="text-danger">*</span></label>
              <input id="customer_id" type="number" name="customer_id" placeholder="Enter customer ID" value="{{old('customer_id')}}" class="form-control">
              @error('customer_id')
                  <span class="text-danger">{{$message}}</span>
              @enderror
          </div>

          <!-- Transaction Date -->
          <div class="form-group">
              <label for="transaction_date" class="col-form-label">Transaction Date <span class="text-danger">*</span></label>
              <input type="date" name="transaction_date" class="form-control" value="{{old('transaction_date')}}">
              @error('transaction_date')
                  <span class="text-danger">{{$message}}</span>
              @enderror
          </div>

          <!-- Total Amount -->
          <div class="form-group">
              <label for="total_amount" class="col-form-label">Total Amount <span class="text-danger">*</span></label>
              <input id="total_amount" type="number" step="0.01" name="total_amount" placeholder="Enter total amount" value="{{old('total_amount')}}" class="form-control">
              @error('total_amount')
                  <span class="text-danger">{{$message}}</span>
              @enderror
          </div>

          <!-- Status -->
          <div class="form-group">
            <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-control">
                <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            
              @error('status')
                  <span class="text-danger">{{$message}}</span>
              @enderror
          </div>

          <!-- Buttons -->
          <div class="form-group mb-3">
              <button type="reset" class="btn btn-warning">Reset</button>
              <button class="btn btn-success" type="submit">Submit</button>
          </div>
      </form>
  </div>
</div>

@endsection
