<?php

namespace App\Http\Controllers;

use App\Models\AdminFund;
use App\Models\Airtime;
use App\Models\AirtimePrice;
use App\Models\AirtimeToCash;
use App\Models\Alert;
use App\Models\Data;
use App\Models\ElectricityPrice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Notification;
use App\Models\TotalFund;
use App\Models\ManualFunding;
use App\Models\ReferralAmount;
use App\Models\SchoolPin;
use App\Models\ExamPin;
use App\Models\ReportAccount;
use App\Models\SelfService;
use App\Models\Ticket;
use App\Models\TvPrice;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Contracts\Translation\TranslatorTrait;

class AdminController extends Controller
{
    use TranslatorTrait;


    public function admindashboard()
    {
        if(Auth::user()->email == 'fasanyafemi@gmail.com'){
            $data['users'] = User::latest()->get();
            $data['pusers'] = User::latest()->take(4)->get();
          
            $data['total_balance'] = User::sum('balance');
            $data['transactions'] = Transaction::latest()->get();
          
    
            return view('admin.index', $data);
        }
        else {
            return redirect()->back();
        }
       
     
    }
    public function admin_tickets() {
        $data['tickets'] = Ticket::latest()->get();
        return view('admin.ticket',$data);
    }
    public function admin_reported_user() {
        $data['tickets'] = ReportAccount::latest()->get();
        return view('admin.report-account',$data);
    }
    public function data_service() {
        $data['datas'] = Data::latest()->get();
        return view('admin.data-service',$data);
    }
    public function admin_self_service() {
        $data['tickets'] = SelfService::latest()->get();
        return view('admin.self-service',$data);   
    }
    public function delete_self_service(Request $request) {
        $id = $request->id;
        $service = SelfService::find($id);
        $service->delete();
        return true;
    }
    public function change_status(Request $request) {
        $this->validate($request,[
            'network' => 'required',
            'status' => 'required'
        ]);
     
        $status = intval($request->status);
        
        $datas = Data::where('network',$request->network)->get();
       
        foreach($datas as $data) {
            $data->status = $status;
            $data->save();
        }
        return true;

    }
    public function admin_airtime_to_cash() {
        $data['airtimes'] = AirtimeToCash::latest()->get();
       
        return view('admin.airtime_to_cash',$data);


    }
    public function delete_airtime_to_cash(Request $request) {
        $id = $request->id;
        AirtimeToCash::find($id)->delete();
        return 'deleted';
    }
    public function allusers()
    {
        $data['users'] = User::get();
        return view('admin.users', $data);
    }
    public function adminreferrals()
    {
        $data['transactions']  = Transaction::where('title', 'Referral Bonus')->latest()->get();
        $data['referral_amount'] = ReferralAmount::find(1)->amount;

        return view('admin.referral', $data);
    }
    public function set_ref_amount(Request $request)
    {
        $ra = ReferralAmount::find(1);
        $ra->amount = $request->amount;

        $ra->save();
        return ('set successfully');
    }
    public function alerts()
    {
        $data['alerts']  = Alert::latest()->get();

        return view('admin.alerts', $data);
    }
    public function createalert(Request $request)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);
        Alert::create([
            'title' => $request->title
        ]);
        return ('alert successfully created');
    }
    public function createalert_api($title)
    {

        Alert::create([
            'title' => $title
        ]);
        return response('created', 202);
    }
    public function updatealert(Request $request, $id)
    {
        $alert = Alert::where('id', $id)->update($request->all(), $id);
        return response($alert, 202);
    }
    public function credituser()
    {
        $data['transactions']  = Transaction::where('title', 'Admin credit user')->orWhere('title', 'Admin debit user')->latest()->get();
        $data['users'] = User::get();
        return view('admin.creditusers', $data);
    }
    public function airtimeslug($slug)
    {
        $data['airtimes'] = Airtime::where('network', $slug)->latest()->get();
        $data['mtn'] = Airtime::where('network', 'MTN')->get();
        $data['glo'] = Airtime::where('network', 'GLO')->get();
        $data['airtel'] = Airtime::where('network', 'AIRTEL')->get();
        $data['mobile'] = Airtime::where('network', '9MOBILE')->get();

        return view('admin.airtime', $data);
    }
    public function airtime()
    {

        $data['airtimes'] = AirtimePrice::latest()->get();
        $data['mtn'] = Airtime::where('network', 'MTN')->get();
        $data['glo'] = Airtime::where('network', 'GLO')->get();
        $data['airtel'] = Airtime::where('network', 'AIRTEL')->get();
        $data['mobile'] = Airtime::where('network', '9MOBILE')->get();
        return view('admin.airtime', $data);
    }
    public function loadairtime(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'network' => 'required',
            'pin' => 'required',
        ]);
        $pins = explode(',', $request->pin);
        foreach ($pins as $pin) {

            if (strlen($pin) > 10) {
                Airtime::create([
                    'amount' => $request->amount,
                    'pin' => $pin,
                    'network' => $request->network,
                    'serial_no' => Str::random(8),
                ]);
            }
        }
    }
    public function deleteairtime(Request $request)
    {
        $id = $request->id;
        $airtime = Airtime::find($id);
        $airtime->delete();
        return 'airtime deleted successfully';
    }
    public function data()
    {
        $data['datas'] = $dd = Data::where('network', 'MTN')->orderBy('actual_price')->get();
      
        return view('admin.dataprice', $data);
    }
    public function veenode()
    {
        $data['datas'] = $dd = Data::where('network', 'MTN')->orderBy('actual_price')->get();
        $response = Http::withHeaders([
            'Authorization' => 'Token 27568c756b857081a7b2d9dff073d3d8e02dbb50',
            'Accept' => "application/json",
            "Content-Type" => "application/json",
        ])->get('https://www.clekktelecoms.com/api/user');
        $data['datas'] = $mtn = $response->json()['Dataplans']['MTN_PLAN']['SME'];

        return view('admin.veenode', $data);
    }
    public function airtimeprice()
    {
        $data['airtimes'] = AirtimePrice::latest()->get();
        return view('admin.airtimeprice', $data);
    }
    public function electricityprice()
    {
        $data['airtimes'] = ElectricityPrice::latest()->get();
        return view('admin.electricityprice', $data);
    }
    public function tvprice()
    {
        $data['airtimes'] = TvPrice::latest()->get();
        return view('admin.tvprice', $data);
    }
    public function schoolpin()
    {
        $data['airtimes'] = ExamPin::latest()->get();
        return view('admin.schoolpin', $data);
    }
    public function updateexam(Request $request)
    {
        $values = explode(",", $request->value);
        // dd($values);
        $exam = ExamPin::where('name', $values[0])->first();

        if ($values[1] > $exam->actual_amount) {

            $exam->set_amount = $values[1];
            $exam->save();
            return true;
        } else {
            return false;
        }
    }
    public function editdata(Request $request)
    {
        $id = $request->id;
        $data = Data::find($id);
        return $data;
    }
    public function updatedata(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'amount' => 'required',

        ]);
        $data = Data::find($request->id);

        $data->amount = $request->amount;

        $data->save();
        return "Data Updated";
    }
    public function getActualPrice(Request $request)
    {
        $check = Data::where('plan_id', $request->plan_id)->where('network', $request->network)->get();
        if ($check->isEmpty()) {
            return 0;
        } else {
            $data = Data::where('plan_id', $request->plan_id)->where('network', $request->network)->first();
            return $data->veenode_price;
        }
    }
    public function setdata(Request $request)
    {
        $values = explode(",", $request->value);
        
        $check = Data::where('name', $values[0])->where('network', $values[1])->get();
        if ($check->isEmpty()) {
            if ($values[3] >= $values[2]) {
                Data::create([
                    'name' => $values[0],
                    'network' => $values[1],
                    'actual_price' => $values[2],
                    'set_price' => $values[3],
                    'plan_id' => $values[4]
                ]);
                return true;
            } else {
                return 'too_much';
            }
        } else {
            if ($values[3] >= $values[2]) {

                $data =  Data::where('name', $values[0])->where('network', $values[1])->first();
                $data->network  = $values[1];
                $data->actual_price = $values[2];
                $data->set_price = $values[3];
                $data->plan_id = $values[4];
                $data->save();
                return true;
            } else {
                return 'too_much';
            }
        }


        Data::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'network' => $request->network,


        ]);
    }

    public function set_veenode_data(Request $request)
    {
        $values = explode(",", $request->value);
        //   dd($values);
        $check = Data::where('name', $values[0])->where('network', $values[1])->get();
        if ($check->isEmpty()) {

            Data::create([
                'name' => $values[0],
                'network' => $values[1],
                'actual_price' => $values[2],
                'veenode_price' => $values[3],
                'plan_id' => $values[4]
            ]);
            return true;
        } else {
            $data =  Data::where('name', $values[0])->where('network', $values[1])->first();
            $data->network  = $values[1];
            $data->actual_price = $values[2];
            $data->veenode_price = $values[3];
            $data->plan_id = $values[4];
            $data->save();
            return true;
        }
    }
    public function getdiscount(Request $request)
    {
        $values = explode(",", $request->value);
        if ($values[0] == 'electricity') {
            $discount = ElectricityPrice::where('name', $values[1])->first()->actual_price;
            $current = ElectricityPrice::where('name', $values[1])->first()->set_price;
            return array($discount, $current);
        } elseif ($values[0] == 'tv') {
            $discount = TvPrice::where('name', $values[1])->first()->actual_price;
            $current = TvPrice::where('name', $values[1])->first()->set_price;
            return array($discount, $current);
        } elseif ($values[0] == 'airtime') {
            $discount = AirtimePrice::where('network', $values[1])->first()->actual_price;
            $current = AirtimePrice::where('network', $values[1])->first()->set_price;
            return array($discount, $current);
        } elseif ($values[0] == 'data') {
            $discount = Data::where('network', $values[1])->first()->actual_price;
            $current = Data::where('network', $values[1])->first()->set_price;
            return array($discount, $current);
        } elseif ($values[0] == 'waec') {
        }
    }
    public function setpercent(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'percent' => 'required',
            'type' => 'required',
        ]);
        // dd($request->all());
        if ($request->type == 'electricity') {
            $elec = ElectricityPrice::where('name', $request->name)->first();
            if ($elec->actual_price >= $request->percent) {
                $elec->set_price = $request->percent;
                $elec->save();
                return true;
            } else {
                return "fraud";
            }
        } elseif ($request->type == 'tv') {

            $elec = TvPrice::where('name', $request->name)->first();
            if ($elec->actual_price >= $request->percent) {
                $elec->set_price = $request->percent;
                $elec->save();
                return true;
            } else {
                return "fraud";
            }
        } elseif ($request->type == 'airtime') {
            $elec = AirtimePrice::where('network', $request->name)->first();
            if ($elec->actual_price >= $request->percent) {
                $elec->set_price = $request->percent;
                $elec->save();
                return true;
            } else {
                return "fraud";
            }
        } elseif ($request->type == 'data') {
            $elec = Data::where('network', $request->name)->first();
            if ($elec->actual_price >= $request->percent) {
                $elec->set_price = $request->percent;
                $elec->save();
                return true;
            } else {
                return "fraud";
            }
        } elseif ($request->type == 'waec') {
        } else {
            return false;
        }
    }
    public function deletedata(Request $request)
    {
        $id = $request->id;
        $airtime = Data::find($id);
        $airtime->delete();
        return 'data deleted successfully';
    }
    public function searchuser(Request $request)
    {
        $val = $request->val;
        $user = User::where('name', 'LIKE', '%' . $val . '%')->orWhere('email', 'LIKE', '%' . $val . '%')->get();
        return $user;
    }

    public function creditusers2(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'type' => 'required',
            'amount' => 'required'
        ]);
        // dd($request->all());
        $fund = TotalFund::find(1);
        $admin_balance = $fund->amount;
        $reference = 'tranx' . Str::random(8);
        if ($admin_balance >= intval($request->amount)) {
            $user = User::find($request->user_id);
            if ($request->type == 'credit') {
                $before = $user->balance;
                $adminfund = TotalFund::find(1);
                $adminfund->amount += $request->amount;
                $adminfund->save();
                $user->balance += $request->amount;
                $user->save();
                Transaction::create([
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'status' => 'success',
                    'title' => 'Admin credit user',
                    'before' => $before,
                    'after' => $user->balance,
                    'service_type' => 'Admin credit user',
                    'description' => '' . $user->name . ' credited with ' . $request->amount . ' through the admin',
                    'amount' => $request->amount,
                ]);
            } else {
                $before = $user->balance;
                $user->balance -= $request->amount;
                $user->save();
                Transaction::create([
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'status' => 'success',
                    'title' => 'Admin debit user',
                    'before' => $before,
                    'after' => $user->balance,
                    'service_type' => 'Admin debit user',
                    'description' => '' . $user->name . ' debitted with ' . $request->amount . ' through the admin',
                    'amount' => $request->amount,
                ]);
            }
            
            $fund->amount -= $request->amount;
            $fund->save();

            return true;
        } else {
            return false;
        }
    }
    public function deletealert(Request $request)
    {
        $id = $request->id;
        $alert = Alert::find($id);
        $alert->delete();
        return 'alert deleted';
    }
    public function alltransactions()
    {
        $data['transactions'] = $t = Transaction::latest()->get();
        
        return view('admin.transactions', $data);
    }
    public function viewtransaction($id)
    {
        $data['transactions'] = $t = Transaction::where('user_id', $id)->latest()->get();
      
        return view('admin.usertransaction', $data);
    }
    public function notifications()
    {
        $notifications = Notification::latest()->get();
        return view('admin.notifications');
    }
    public function createnotification(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',

        ]);
        $notification =  Notification::create([
            'title' => $request->title,
            'description' => $request->description
        ]);
        return $notification;
    }
    public function deletenotification(Request $request)
    {

        $id = $request->id;
        $notification = Notification::find($id);
        $notification->delete();
        return 'notification deleted';
    }

    public function deleteticket(Request $request)
    {
        $id = $request->id;
        $notification = Ticket::find($id);
        $notification->delete();
        return 'notification deleted';
    }

    public function delete_reported_user(Request $request)
    {
        $id = $request->id;
        $account = ReportAccount::find($id);
        $account->delete();
        return ' deleted';
    }

    public function deletemanual(Request $request)
    {
        $id = $request->id;
        $notification = ManualFunding::find($id);
        $notification->delete();
        return 'manual fundiug deleted';
    }
}
