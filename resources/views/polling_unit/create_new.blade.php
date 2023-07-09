@extends('polling_unit.master')
@section('head-links')
@endsection
@section('main')
{{-- scrollin wrapper --}}
<div class="container-fluid">
    <div class='col-md-12'>
        <div class='card p-4'>
            <h3>
                Create New Result
            </h3>


            <form action='{{ route("submit_result") }}' method='post'>@csrf
                <div class="row">
                    <div class="col-md-6">
                        <label>LGA</label>
                        <select id='lga' required name='lga' class="form-control">
                            <option>--Select LGA Unit--</option>
                            @foreach($lgas as $lga)
                            <option value="{{ $lga->lga_id }}">{{ $lga->lga_name }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-6">
                        <label>Polling Unit</label>
                        <select id='polling_unit' required name='polling_unit' class="form-control">
                            <option>--Select Polling Unit--</option>

                        </select>

                    </div>
                </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Party</label>
                            <select required name='party_abbreviation' class="form-control">
                                <option>--Select Party--</option>
                                <option value='PDP'>PDP</option>
                                <option value='DPP'>DPP</option>
                                <option value='ACN'>ACN</option>
                                <option value='PPA'>PPA</option>
                                <option value='CDC'>CDC</option>
                                <option value='JP'>JP</option>
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label>Party Score</label>
                            <input type="number" name='party_score' class="form-control" placeholder="Party Score">

                        </div>
                    </div>
                        <div class='row'>
                            <div class='col-md-6'>
                                <label>Name of reporter</label>
                                <input name='entered_by_user' type="text" class="form-control"
                                    placeholder="Entered By User">
                            </div>
                            <div class='col-md-6'>
                                <label>IP-Address</label>
                                <input readonly value='{{ $ip_address }}' name='user_ip_address' type="text" class="form-control">
                            </div>

                        </div>
                        <div class='row m-auto mt-4'>
                            <button class='btn btn-primary' type='submit'>Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        @if (session('message'))
        Swal.fire('Success!',"{{ session('message') }}",'success');
        @endif
       
        $("#lga").on('change',async function(e) {
        e.preventDefault();
        Swal.fire({
        title: 'Fetching Polling Unit',
        html: 'Please wait...',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
          Swal.showLoading();
        }
        });

        var lga_id = $(this).val()
        var fd = new FormData;
        fd.append('lga_id',lga_id);
     
    
        console.log(fd)
      $.ajax({
          type: 'POST',
          url: "{{ route('fetch_polling_unit') }}",
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
              $("#polling_unit").empty()
              $.each(data, function(index, value) {
                $("#polling_unit").append($('<option></option>').attr('value', value.uniqueid).text(value.polling_unit_name));

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