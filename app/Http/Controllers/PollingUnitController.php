<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\LGA;
use App\Models\State;
use App\Models\Result;
use App\Models\PollingUnit;
use Illuminate\Http\Request;

class PollingUnitController extends Controller
{
    public function pollingUnit()
    {

        $data['polling_unit'] = $pu = PollingUnit::where('polling_unit_id', 8)->first();
        $data['lga'] = $lga = LGA::where('lga_id', $pu->lga_id)->first();
        $data['results'] = Result::where('polling_unit_uniqueid', $pu->polling_unit_id)->get();
        return view('polling_unit.index', $data);
    }
    public function pollingUnitResult()
    {
        $data['lgas'] = LGA::all();

        $data['polling_unit'] = $pu = PollingUnit::where('polling_unit_id', 8)->first();
        $data['lga'] = $lga = LGA::where('lga_id', $pu->lga_id)->first();
        $data['results'] = Result::where('polling_unit_uniqueid', $pu->polling_unit_id)->get();
        return view('polling_unit.result', $data);
    }
    public function createNew(Request $request)
    {
        $data['lgas'] = LGA::all();
        $data['ip_address'] =  $ipAddress = $request->ip();

        $data['polling_units'] = PollingUnit::all();
        return view('polling_unit.create_new', $data);
    }
    public function fetchResult(Request $request)
    {

        $lga =  LGA::where('lga_id', $request->lga_id)->first();

        $pu = PollingUnit::where('lga_id', $lga->lga_id)->get();

        $result = Result::whereIn('polling_unit_uniqueid', $pu->pluck('polling_unit_id'))->get();

        $party_sum = [];
        foreach ($result as $item) {
            $partyAbbreviation = $item->party_abbreviation;
            $partyScore = $item->party_score;

            if (isset($party_sum[$partyAbbreviation])) {
                $party_sum[$partyAbbreviation] += $partyScore;
            } else {
                $party_sum[$partyAbbreviation] = $partyScore;
            }
        }
        return $party_sum;
    }
    public function fetchPollingUnit(Request $request)
    {

        $lga =  LGA::where('lga_id', $request->lga_id)->first();

        $pu = PollingUnit::where('lga_id', $lga->lga_id)->get();
        return $pu;
    }
    public function submitResult(Request $request)
    {
        Result::create([
            "polling_unit_uniqueid" => $request->polling_unit,
            "party_abbreviation" => $request->party_abbreviation,
            "party_score" => $request->party_score,
            "entered_by_user" => $request->entered_by_user,
            "user_ip_address" => $request->user_ip_address,
            "date_entered" => Carbon::now()
        ]);
        return redirect()->back()->with('message', 'Result Submitted Successfully!');
    }
}
