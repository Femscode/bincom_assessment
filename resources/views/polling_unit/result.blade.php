@extends('polling_unit.master')
@section('head-links')
@endsection
@section('main')
{{-- scrollin wrapper --}}
<div class="container-fluid">


    <div class='col-md-4' style='font-size:0px;background:transparent;color:#000C40;'>
        <a id='rolexbtn' style='border-radius:0px;background:#000C40' class='btn btn-primary m-0'>LGA Result</a>
    </div>


    <div class="row">
        <div id='rolex' class="col-md-4">
            <div class="card bg-transparent shadow-xl"
                style='border-bottom-left-radius:0px;border-bottom-right-radius:0px;'>
                <div class="overflow-hidden position-relative"
                    style="background-image: url('../assets/img/curved-images/curved14.jpg'); border-bottom-left-radius:20px; border-bottom-right-radius:20px;border-top-right-radius:20px">
                    <span class="mask bg-gradient-dark"></span>
                    <div style='border-radius:0px' class="card-body position-relative z-index-1 p-3">
                        <form>
                            <label class='text-white'>Select Local Govt.</label>
                            <select id='changeLGA' required class='form control form-select'>
                                <option>-- Select LGA --</option>
                                @foreach($lgas as $lga)
                                <option data-name='{{ $lga->lga_name  }}' value="{{ $lga->lga_id }}">{{ $lga->lga_name }}</option>
                                @endforeach
                            </select>
                        </form>



                    </div>
                </div>
            </div>
        </div>


        <!--   Group 1 -->


        <div class="col-lg-8 col-md-12 sm-12 mb-md-0 mb-4 mt-4">
            <div class='row'>
                <div class="col-md-6 card media-element"
                    style='background:#d1ecf1;color:#0c5460;border-left-color:#0c5460;border-left-width:5px'>
                    <div class="card-body">
                       
                            <div class="col">
                                <div class="numbers">

                                    <a href='#' class="mb-0" style='color:#0c5460'>
                                        Local Government : <span id='lg'></span>

                                    </a>
                                </div>
                            </div>
                       
                    </div>
                </div>
                <div class="col-md-6 card media-element"
                    style='background:#d4edda;color:#155724;border-left-color:#155724;border-left-width:5px'>
                    <div class="card-body">
                       
                            <div class="col">
                                <div class="numbers">
                                    <i class="fas fa-mobile scroll-icon p-2"></i>
                                    <a href='#' class="mb-0" style='color:#155724'>
                                        Winner : <span id='winner'>Select LGA Above</span>

                                    </a>
                                </div>
                            </div>
                       
                    </div>
                </div>


            </div>



            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7 mb-0 pg-0">
                            <h6 style="color: #000C40;"><i class="fa fa-check text-info" aria-hidden="true"></i>LGA
                                Result</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table id="partyScoresTable" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Party</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Result
                                    </th>
                                </tr>
                            </thead>
                            <tbody>


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

                                <h5 class="font-weight-bolder">Other Polling Units Results</h5>
                                <p class="mb-5">Click the link below to view other polling unit results!</p>
                                <a class="text-body text-sm font-weight-bold mb-0 icon-move-right mt-auto"
                                    href="/polling_unit_results">
                                    View
                                    <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                            <div class="bg-gradient-primary border-radius-lg h-100">
                                <img src="../assets/img/shapes/waves-white.svg"
                                    class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">
                                <div class="position-relative d-flex align-items-center justify-content-center h-100">
                                    <img class="w-100 position-relative z-index-2 pt-4"
                                        src="../assets/img/illustrations/rocket-white.png" alt="rocket">
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
                        <h5 class="text-white font-weight-bolder mb-4 pt-2">Create a new polling unit result!</h5>
                        <p class="text-white">Our administrator interface allows admin to manage users and transactions.
                        </p>
                        <a href='/create_new' class="text-white text-sm font-weight-bold mb-0 icon-move-right mt-auto"
                            href="javascript:;">
                            Create Now
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
       
        $("#changeLGA").on('change',async function(e) {
        e.preventDefault();
        Swal.fire({
        title: 'Fetching result',
        html: 'Please wait...',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
          Swal.showLoading();
        }
        });

        var lga_id = $(this).val()
        var selectedOption = $('#changeLGA option:selected');
        var name = selectedOption.data('name');

        $("#lg").text(name)
     
        var fd = new FormData;
        fd.append('lga_id',lga_id);
     
    
        console.log(fd)
      $.ajax({
          type: 'POST',
          url: "{{ route('fetch_result') }}",
          data: fd,
          cache: false,
          contentType: false,
            headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
          processData: false,
          success: function(data) {
              console.log('the data', data)
              swal.close()
              $("#partyScoresTable tbody").empty()

              var winnerParty = null;
                var highestScore = 0;

                for (var party in data) {
                  if (data.hasOwnProperty(party)) {
                    var score = data[party];
                    if (score > highestScore) {
                      highestScore = score;
                      winnerParty = party;
                    }
                  }
                }
                $("#winner").text(winnerParty)
             
              $.each(data, function(abbreviation, score) {
                  var row = $("<tr>");
                  row.append($("<td>").text(abbreviation));
                  row.append($("<td>").text(score));
                  $("#partyScoresTable tbody").append(row);
                });

          },
          error: function(data) {
              console.log(data)
              swal.close()
              Swal.fire('Opps!', 'Something went wrong, please try again later', 'error')
          }
      })
  })
})
</script>
@endsection