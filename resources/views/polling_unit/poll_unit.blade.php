@extends('user.master')
@section('head-links')
@endsection
@section('main')
{{-- scrollin wrapper --}}
<div class="container-fluid">
 
  
  <div class='col-md-4' style='font-size:0px;background:transparent;color:#000C40;'>
    <a id='rolexbtn' style='border-radius:0px;background:#000C40' class='btn btn-primary m-0'>Bank Details</a>
  </div>
  
  
  <div class="row">
    <div id='rolex' class="col-md-4" >
      <div class="card bg-transparent shadow-xl" style='border-bottom-left-radius:0px;border-bottom-right-radius:0px;'>
        <div class="overflow-hidden position-relative"
          style="background-image: url('../assets/img/curved-images/curved14.jpg'); border-bottom-left-radius:20px; border-bottom-right-radius:20px;border-top-right-radius:20px">
          <span class="mask bg-gradient-dark"></span>
          <div style='border-radius:0px' class="card-body position-relative z-index-1 p-3">
            <i class="fas fa-wifi text-white p-2"></i>
            <h5 class="text-white mt-4 mb-5 pb-2">
           {{ $user->account_number }}
            </h5>
            <div class="d-flex">
              <div class="d-flex">
                <div class="me-4">
                  <p class="text-white text-sm opacity-8 mb-0">Account Name</p>
                  <h6 class="text-white mb-0"> {{ $user->name }}</h6>
                </div>
                <div>
                  <p class="text-white text-sm opacity-8 mb-0">Bank Name</p>
                  <h6 class="text-white mb-0">Net-Centric Bank</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id='wema' style='display:none' class="col-md-4">
      <div class="card bg-transparent shadow-xl" style='border-bottom-left-radius:0px;border-bottom-right-radius:0px;'>
        <div class="overflow-hidden position-relative"
          style="background-image: url('../assets/img/curved-images/curved14.jpg');border-bottom-left-radius:20px; border-bottom-right-radius:20px;border-top-right-radius:20px">
          <span class="mask bg-gradient-dark"></span>
          <div class="card-body position-relative z-index-1 p-3">
            <i class="fas fa-wifi text-white p-2"></i>
            <h5 class="text-white mt-4 mb-5 pb-2">
             {{ $user->account_number }}
            </h5>
            <div class="d-flex">
              <div class="d-flex">
                <div class="me-4">
                  <p class="text-white text-sm opacity-8 mb-0">Account Name</p>
                  <h6 class="text-white mb-0">{{ $user->name }}</h6>
                </div>
                <div>
                  <p class="text-white text-sm opacity-8 mb-0">Bank Name</p>
                  <h6 class="text-white mb-0"> Net-Centric Bank</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
      <!--   Group 1 -->
    
      <div class="col-lg-8 col-md-6 mb-md-0 mb-4 mt-4">
        <div class='row ml-8 p-2'>
        <div class="col-md-6 card media-element"
          style='background:#d4edda;color:#155724;border-left-color:#155724;border-left-width:5px'>
          <div class="card-body">
            <div class="row">
              <div class="col-12">
                <div class="numbers">
                  <i class="fas fa-wifi scroll-icon p-2"></i>
                  <a href='transfer' class="mb-0" style='color:#155724'>
                    Make Transfer
  
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="col-md-6 card media-element"
          style='background:#d1ecf1;color:#0c5460;border-left-color:#0c5460;border-left-width:5px'>
          <div class="card-body">
            <div class="row">
              <div class="col-12">
                <div class="numbers">
                  <i class="fas fa-mobile scroll-icon p-2"></i>
                  <a href='/transaction' class="mb-0" style='color:#0c5460'>
                    Transactions
  
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>

        
  
      <div class="card">
        <div class="card-header pb-0">
          <div class="row">
            <div class="col-lg-6 col-7 mb-0 pg-0">
              <h6 style="color: #000C40;"><i class="fa fa-check text-info" aria-hidden="true"></i>Account Overview</h6>
            </div>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">History</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 style="color: #000C40;" class="mb-0 text-sm">Current Wallet Balance</h6>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="text-xs font-weight-bold"> ₦{{number_format($user->balance,2)}} </span>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 style="color: #000C40;" class="mb-0 text-sm">Total Fundings</h6>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="text-xs font-weight-bold"> ₦{{number_format($user->spent,2)}} </span>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 style="color: #000C40;" class="mb-0 text-sm">Amount Spent</h6>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="text-xs font-weight-bold"> ₦{{number_format($user->spent,2)}}</span>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 style="color: #000C40;" class="mb-0 text-sm">Transactions</h6>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="text-xs font-weight-bold"> {{count($transactions)}} </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-7 mb-lg-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-lg-6">
              <div class="d-flex flex-column h-100">
                <p class="mb-1 pt-2 text-bold">Better banking,</p>
                <h5 class="font-weight-bolder">Better world</h5>
                <p class="mb-5">With CSC 444 Group(3) Banking System, you can do the impossible!</p>
                <a class="text-body text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="javascript:;">
                  Transfer now
                  <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                </a>
              </div>
            </div>
            <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
              <div class="bg-gradient-primary border-radius-lg h-100">
                <img src="../assets/img/shapes/waves-white.svg"
                  class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">
                <div class="position-relative d-flex align-items-center justify-content-center h-100">
                  <img class="w-100 position-relative z-index-2 pt-4" src="../assets/img/illustrations/rocket-white.png"
                    alt="rocket">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card h-100 p-3">
        <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100"
          style="background-image: url('../assets/img/ivancik.jpg');">
          <span class="mask bg-gradient-dark"></span>
          <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
            <h5 class="text-white font-weight-bolder mb-4 pt-2">A very powerful administrator interface</h5>
            <p class="text-white">Our administrator interface allows admin to manage users and transactions.</p>
            <a href='/admin' class="text-white text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="javascript:;">
              Visit Admin
              <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script>
$(document).ready(function() {
 
  $("#rolexbtn").on('click',function() {
  
   $("#rolexbtn").css('backgroundColor','#000C40')
   $("#rolexbtn").css('color','#fff')
   $("#wemabtn").css('backgroundColor','#fff')
   $("#wemabtn").css('color','#000C40')
    $("#rolex").hide()
    $("#wema").show()
  })
  $("#wemabtn").on('click',function() {
  
   $("#wemabtn").css('backgroundColor','#000C40')
   $("#wemabtn").css('color','#fff')
   $("#rolexbtn").css('backgroundColor','#fff')
   $("#rolexbtn").css('color','#000C40')
    $("#rolex").show()
    $("#wema").hide()
  })
})
</script>
@endsection