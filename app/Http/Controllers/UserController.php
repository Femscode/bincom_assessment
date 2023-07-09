<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Data;
use App\Models\User;
use App\Models\Alert;
use App\Models\Ticket;
use App\Models\Airtime;
use App\Models\ExamPin;
use App\Models\TvPrice;
use Barryvdh\DomPDF\PDF;
use App\Models\AdminFund;
use App\Models\TotalFund;
use App\Mail\ContactUsMail;
use App\Models\SelfService;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\AirtimePrice;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\AirtimeToCash;
use App\Models\ManualFunding;
use App\Models\ReportAccount;
use App\Models\ReferralAmount;
use App\Models\TelnetingEvent;
use App\Models\ElectricityPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\TransactionTrait;
use Illuminate\Auth\Events\Registered;
use App\Models\PendingUserTransactions;
use Illuminate\Contracts\Session\Session;

class UserController extends Controller
{
    use TransactionTrait;
    public function sendmail()
    {
        $data = array('name' => 'fasanya', 'user_email' => 'fasanyafemi@gmail.com', 'user_subject' => 'Please I am trying to fund my wallet', 'user_message' => "Please I have been trying to fund my wallet");

        Mail::send('mail.contact', $data, function ($message) {
            $message->to('fasanyafemi@gmail.com', 'telneting')->subject('User request from steadyhub');
            $message->from('support@telneting.com', 'Telneting');
        });
        return 'sent';
        // $name = 'Fasanya Oluwapelumi';
        // $location = "Kobape, Ogun State";
        // // dd($location);
        // $mail =  Mail::to('fasanyafemi@gmail.com')->send(new ContactUsMail());
        // dd($mail, Mail::failures());
    }
    public function verifydata(Request $request)
    {
        $data['tran'] = $tran = Transaction::where('reference', $request->reference)->first();
        $data['user'] = Auth::user();
        if ($tran !== null) {
            $data['successful'] = 'successful';
        }

        return view('user.self-service', $data);
    }

    public function verifypaystack(Request $request)
    {
        $this->validate($request, [
            'reference_no' => 'required',
            'amount' => 'required',
            'date' => 'required'
        ]);
        dd($request->all());


        return view('user.paystack-verify');
    }
    public function verifymonnify(Request $request)
    {
        $this->validate($request, [
            'reference_no' => 'required',
            'amount' => 'required',
            'date' => 'required'
        ]);
        dd($request->all());


        return view('user.monnify-verify');
    }
    public function user_notifications()
    {
        $data['notifications'] = Notification::latest()->get();
        $data['user'] = Auth::user();
        return view('user.notifications', $data);
    }
    public function singlenotification($id)
    {
        $data['notification'] = Notification::where('id', $id)->first();
        $data['user'] = Auth::user();
        return view('user.single-notification', $data);
    }
    public function insurance()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['monnify'] = $mm = $this->reserveAccount();
        $data['exams'] = ExamPin::get();
        $data['user'] = Auth::user();
        return view('user.insurance', $data);
    }
    public function buyInsurance(Request $request)
    {


        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $amount = $request->amount;
        $exam_type = $request->exam_type;
        $package = $request->package;


        $user = Auth::user();
        if ($pin == $user_pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > intval($amount) && intval($amount) >= 100) {
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();

                    if ($check_pending !== null) {
                        return "pending";
                    } else {

                        // $save_pending = PendingUserTransactions::create([
                        //     'user_id' => $user->id,
                        //     'data' => $amount
                        // ]);
                        if ($request->type == "vehicle_insurance") {
                            $request_all = [
                                'request_id' => $request->request_id,
                                'serviceID' => $request->serviceID,
                                'billersCode' => $request->billersCode,
                                'variation_code' => $request->variation_code,
                                'amount' => $request->amount,
                                'phone' => $request->phone,
                                'Insured_Name' => $request->Insured_Name,
                                'Engine_Number' => $request->Engine_Number,
                                'Chasis_Number' => $request->Chasis_Number,
                                'Plate_Number' => $request->Plate_Number,
                                'Vehicle_Color' => $request->Vehicle_Color,
                                'Vehicle_Model' => $request->Vehicle_Model,
                                'Year_of_Make' => $request->Year_of_Make,
                                'Contact_Address' => $request->Contact_Address,
                            ];
                        } else {
                            $request_all = [
                                'request_id' => $request->request_id,
                                'serviceID' => $request->serviceID,
                                'billersCode' => $request->billersCode,
                                'variation_code' => $request->variation_code,
                                'amount' => $request->amount,
                                'phone' => $request->phone,
                                'full_name' => $request->full_name,
                                'address' => $request->address,
                                'dob' => $request->dob,
                                'next_kin_name' => $request->next_kin_name,
                                'next_kin_phone' => $request->next_kin_phone,
                                'business_occupation' => $request->business_occupation,

                            ];
                        }



                        $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/pay", $request_all);

                        if ($response->json()['errors']) {
                            return 'api_error';
                        } else {
                            $r_pin = $response->json()['purchased_code'];
                            $r_amount = $response->json()['amount'];

                            $data = array('name' => Auth::user()->name, 'pin' => $r_pin, 'amount' => $r_amount);
                            // Mail::send('mail.insurance', $data, function ($message) use ($user) {
                            //     $message->to($user->email, 'Telneting')->subject('Insurance Details');
                            //     $message->from('support@telneting.com', 'Telneting');
                            // });
                            return $response->json();
                        }
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }

    public function send_sms(Request $request)
    {


        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $message_length = Str::length($request->message);
        $phone_length = count(explode(',', $request->phone));

        //tell them the remaining character for each length section
        $amount = (ceil($message_length / 160)) * $phone_length * 4;
        // dd($message_length,$phone_length,$amount);
        $exam_type = $request->exam_type;
        $package = $request->package;



        $user = Auth::user();
        if ($pin == $user_pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > intval($amount)) {
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();

                    if ($check_pending !== null) {
                        return "pending";
                    } else {

                        // $save_pending = PendingUserTransactions::create([
                        //     'user_id' => $user->id,
                        //     'data' => $amount
                        // ]);

                        $req = [
                            'sender' => $user->name,
                            'recipient' => $request->phone,
                            'message' => $request->message,
                            'responsetype' => 'json',
                            'dlr' => 1,
                            'clientbatchid' => 299009,
                        ];
                        // dd($req);

                        $response2 = Http::withHeaders([
                            'X-Token' => 'VTP_PK_e1240a43d524d587a25745c049786d28ffc50a63926c319bfffa7848bc1f77e6',
                            'X-Secret' => 'VTP_SK_a96c97052c65f830849024b4fe93fa8790e2ea9f6643be55eee35e7557f96f1e',
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/x-www-form-urlencoded',

                        ])->post('https://messaging.vtpass.com/v2/api/sms/sendsms', $req);

                        dd($response2->json(), $response2);
                        return $response2->json();



                        $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/pay", $request_all);

                        if ($response->json()['errors']) {
                            return 'api_error';
                        } else {
                            $r_pin = $response->json()['purchased_code'];
                            $r_amount = $response->json()['amount'];

                            $data = array('name' => Auth::user()->name, 'pin' => $r_pin, 'amount' => $r_amount);
                            // Mail::send('mail.insurance', $data, function ($message) use ($user) {
                            //     $message->to($user->email, 'Telneting')->subject('Insurance Details');
                            //     $message->from('support@telneting.com', 'Telneting');
                            // });
                            return $response->json();
                        }
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }

    public function getInsurance(Request $request)
    {

        $serviceID = $request->serviceID;
        $email = Auth::user()->email;
        $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->get('https://vtpass.com/api/service-variations?serviceID=' . $serviceID);

        return $response->json();
        dd($response->json());
        $request_body = [
            'serviceID' => $request->serviceID,
            'request_id' => $request->request_id,
            'variation_code' => $request->type,
            'amount' => $request->amount,
            'phone' => $request->phone

        ];
        $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post('https://vtpass.com/api/pay', $request_body);
        $r_res = $response->json();

        if ($r_res['code'] == '000') {

            $serial = $r_res['cards']['Serial'];
            $pin = $r_res['cards']['Pin'];
            $email = Auth::user()->email;
            $data = array('name' => Auth::user()->name, 'pin' => $pin, 'serial' => $serial);
            Mail::send('mail.exampin', $data, function ($message) use ($email) {
                $message->to($email, 'Telneting')->subject('Exam Pin Details');
                $message->from('support@telneting.com', 'Telneting');
            });

            return $response->json();
        } else if ($r_res['code'] == '034') {
            return "service currently not available";
        } else {
            return "transaction error";
        }
    }

    public function agent()
    {
        $user = Auth::user();
        $data['alerts'] = Alert::latest()->get();


        if (session()->has('monnify') && session()->has('user')) {
            $data['monnify'] = session()->get('monnify');
            $data['user'] = session()->get('user');
        } else {
            $data['monnify'] = $mm = $this->reserveAccount();
            session()->put('monnify', $mm);
            $data['monnify'] = $this->reserveAccount();
            $data['user'] =  session()->put('user', Auth::user());
        }
        return view('user.agent', $data);
    }

    public function e_statement()
    {
        $data['user'] = Auth::user();
        return view('user.e-statement', $data);
    }
    public function report_account()
    {
        $data['user'] = Auth::user();
        return view('user.report-account', $data);
    }
    public function self_service()
    {
        $data['user'] = Auth::user();
        return view('user.self-service', $data);
    }
    public function paystack_verify()
    {
        $data['user'] = Auth::user();
        return view('user.paystack-verify', $data);
    }
    public function monnify_verify()
    {
        $data['user'] = Auth::user();
        return view('user.monnify-verify', $data);
    }
    public function user_report_account(Request $request)
    {

        $this->validate($request, [
            'details' => 'required',
            'name' => 'required'
        ]);
        ReportAccount::create([
            'name' => $request->name,
            'details' => $request->details,
            'user_id' => Auth::user()->id
        ]);
        return true;
    }

    public function create_self_service(Request $request)
    {

        $this->validate($request, [
            'details' => 'required',
            'name' => 'required',
            'type' => 'required'
        ]);
        SelfService::create([
            'reference' => $request->name,
            'type' => $request->type,
            'details' => $request->details,
            'user_id' => Auth::user()->id
        ]);
        return true;
    }

    public function download_e_statement(Request $request)
    {
        $month = $request->month;

        $data['transactions'] = $trans = Transaction::where('user_id', Auth::user()->id)->whereMonth('created_at', $month)->get();
        if ($trans->isEmpty()) {

            return redirect()->back()->with('message', 'No transactions performed for the month selected');
        } else {
            $dateObj   = DateTime::createFromFormat('!m', $month);
            $data['month'] = $dateObj->format('F');
            $pdf = PDF::loadView('receipt.e-statement', $data);

            return $pdf->download('e_statement' . $request->month . '.pdf');
            $data['user'] = Auth::user();
            return view('user.e-statement', $data);
        }
    }

    public function policy()
    {
        return view('frontend.policy');
    }
    public function print_transaction_receipt($id)
    {

        $data['transaction'] = Transaction::find($id);
        $pdf = PDF::loadView('receipt.transaction', $data);

        return $pdf->stream('transaction_receipt' . $id . '.pdf');
    }
    public function electricityAPI(Request $request)
    {
        if ($request->t_type == 'verify') {
            $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/merchant-verify?serviceID=" . $request->service_type . "&type=" . $request->type . "&billersCode=" . $request->billersCode);
            return $response->json();
        } else {
            $user = Auth::user();
            if ($user->balance >= $request->amount) {

                $reference = 'tranx' . Str::random(8);
                $response = Http::withBasicAuth('victorakinode@gmail.com', env('VTPASS_PASSWORD'))->post("https://vtpass.com/api/pay?request_id=" . $request->request_id . "&serviceID=" . $request->serviceID . "&billersCode=" . $request->billersCode . "&variation_code=" . $request->meter_type . "&amount=" . intval($request->amount) . "&phone=08111111111");
                $rres = json_decode($response);
                if ($rres->code == '000') {
                    $this->create_transaction('Electricity Payment', $reference, $request->details, 'debit', $request->amount, $request->user_id, $request->serviceID);

                    $r_pin = $response->json()['purchased_code'];
                    $r_amount = $response->json()['amount'];

                    $data = array('name' => Auth::user()->name, 'pin' => $r_pin, 'amount' => $r_amount);
                    Mail::send('mail.electricity', $data, function ($message) use ($user) {
                        $message->to($user->email, 'Telneting')->subject('Electricity TOken Details');
                        $message->from('support@telneting.com', 'Telneting');
                    });
                } else {
                    $this->create_transaction('Failed Transaction', $reference, $request->details, 'debit', $request->amount, $request->user_id, $request->serviceID);
                }

                return $response;
            } else {
                return "Insufficient Balance";
            }
        }
    }


    public function reserveAccount()
    {
        $user = Auth::user();

        if ($user->bank_name1 == null && $user->balance == 0) {


            $response = Http::withHeaders([
                // 'Authorization' => 'Basic ' . base64_encode(env("MONNIFY_API_TOKEN"))
                'Authorization' => 'Basic ' . base64_encode('MK_PROD_NJ0U4DZCC2:EGJEDSESSHPY9SHN92F0GF9DCWG1BLXW')
            ])->post('https://api.monnify.com/api/v1/auth/login');


            $response_token = $response['responseBody']['accessToken'];

            $req = [
                "accountReference" => "TELNETING-" . time(),
                "accountName" => "TELNETING/" . $user->email,
                "currencyCode" => "NGN",
                "contractCode" => "777143371479",
                "customerEmail" => $user->email,
                "customerName" => $user->name,
                "getAllAvailableBanks" => true,
                // "preferredBanks" => ["50515"]
            ];


            $response2 = Http::withHeaders([

                'Authorization' => 'Bearer ' . $response_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',

            ])->post('https://api.monnify.com/api/v2/bank-transfer/reserved-accounts', $req);


            $user->bank_name1 = $response2->json()['responseBody']['accounts'][0]['bankName'];
            $user->account_number1 = $response2->json()['responseBody']['accounts'][0]['accountNumber'];
            $user->account_name1 = $response2->json()['responseBody']['accounts'][0]['accountName'];
            $user->bank_name2 = $response2->json()['responseBody']['accounts'][1]['bankName'];
            $user->account_number2 = $response2->json()['responseBody']['accounts'][1]['accountNumber'];
            $user->account_name2 = $response2->json()['responseBody']['accounts'][1]['accountName'];
            $user->save();

            return $response2->json();
        } else {
            $response =  array([
                "responseBody" =>  [

                    "accountReference" => "TELNETING-1656330598",

                    "customerEmail" => $user->email,
                    "customerName" => $user->name,
                    "accounts" => array(
                        [
                            "bankName" => $user->bank_name1,
                            "accountNumber" => $user->account_number1,
                            "accountName" => $user->account_name1,

                        ],
                        [
                            "bankName" => $user->bank_name2,
                            "accountNumber" => $user->account_number2,
                            "accountName" => $user->account_name2,
                        ]
                    ),
                    "collectionChannel" => "RESERVED_ACCOUNT",
                    "reservationReference" => "J6W1VK474GU07LH21FML",
                    "reservedAccountType" => "GENERAL",
                    "status" => "ACTIVE",
                    "createdOn" => "2022-06-27 12:50:01.509",

                ]
            ]);
            return $response[0];
        }
    }

    public function paystack_veenode(Request $request)
    {
    }
    public function airtimeAPI(Request $request)
    {
        if ($request->t_type == 'verify') {
            $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/merchant-verify?serviceID=" . $request->service_type . "&type=" . $request->type . "&billersCode=1111111111111");
            return $response->json();
        } else {
            $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/pay?request_id=" . $request->request_id . "&serviceID=" . $request->serviceID . "&billersCode=1111111111111&variation_code=" . $request->meter_type . "&amount=" . intval($request->amount) . "&phone=" . $request->phone);
            return $response->json();
        }
    }
    public function buyExamAPI(Request $request)
    {

        $serviceID = $request->serviceID;
        $email = Auth::user()->email;
        $amount = ExamPin::where('url', $serviceID)->pluck('set_amount');
        return $amount[0];
    }
    public function tvAPI(Request $request)
    {

        if ($request->t_type == 'verify') {

            $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->get("https://vtpass.com/api/service-variations?serviceID=" . $request->cable_type);
            return $response->json();
        } else if ($request->t_type == "merchant_verify") {

            $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/merchant-verify?serviceID=" . $request->cable_type . '&billersCode=' . $request->billersCode);
            return $response->json();
        } else {
            dd($request->all());

            $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post('https://vtpass.com/api/pay?request_id=' . $request->request_id . '&amount=' . $request->amount . '&subscription_type=' . $request->subscription_type . '&phone=' . $request->phone . '&serviceID=' . $request->serviceID . '&billersCode=' . $request->billersCode);
            return $response->json();
        }
    }

    public function passwordreset()
    {
        $data['monnify'] = $mm = $this->reserveAccount();
        $data['user'] = Auth::user();
        return view('user.passwordreset', $data);
    }
    public function changepassword(Request $request)
    {
        $values = explode(",", $request->value);
        // $val = $request->validate([
        //     'values' => ['string', 'min:8', 'confirmed','regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/'],

        // ]);

        if (Auth::user()->pin == $values[0]) {
            $user = Auth::user();
            $user->password = Hash::make($values[1]);
            $user->save();

            return true;
            // $2y$10$yLJ4HGcoIh2/C9byKfuePuJYWDWMBW29rrRlCR59D3F16jPv1dFxi
        } else {

            return false;
        }
    }
    public function old_buy_real_data(Request $request)
    {

        $values = explode(',', $request->value);

        $network_id = $values[0];
        $phone = $values[1];
        $plan_id = $values[2];
        $name = $values[3];
        $contains = Str::contains($name, 'Corporate');
        dd($contains);


        $request_body = [
            "plan_id" => $plan_id,
            "network_id" => $network_id,
            "phone" => $phone
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("SMEPLUG_API_KEY"),
            'Accept' => "application/json",
            "Content-Type" => "application/json",
        ])->post('https://smeplug.ng/api/v1/data/purchase', $request_body);

        return $response->json();
    }
    public function buy_real_data(Request $request)
    {

        $values = explode(',', $request->value);

        $network_id = $values[0];
        $phone = $values[1];
        $plan_id2 = $values[2];
        $name = $values[3];
        $plan_id = $values[4];
        $request_body = [
            "plan" => $plan_id,
            "network" => $network_id,
            "mobile_number" => $phone,
            "Ported_number" => true
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token 27568c756b857081a7b2d9dff073d3d8e02dbb50',
            'Accept' => "application/json",
            "Content-Type" => "application/json",
        ])->post('https://www.clekktelecoms.com/api/data/', $request_body);
        // ])->post('https://www.clekktelecoms.com/api/data',$request_body);

        return $response->json();
    }
    public function fetchdata2(Request $request)
    {
        $network = $request->network;
        $dat = Data::where('network', $network)->orderBy('veenode_price')->get();
        return $dat;
    }
    public function fetchdata(Request $request)
    {
        $network = $request->network;
        $response = Http::withHeaders([
            'Authorization' => 'Token 27568c756b857081a7b2d9dff073d3d8e02dbb50',
            'Accept' => "application/json",
            "Content-Type" => "application/json",
        ])->get('https://www.clekktelecoms.com/api/user');
        // dd($response->json());
        if ($network == "MTN") {
            $data['datas'] = $dat = $response->json()['Dataplans']['MTN_PLAN']['SME'];
            return $dat;
        } elseif ($network == "AIRTEL") {
            $data['datas'] = $dat = $response->json()['Dataplans']['AIRTEL_PLAN']['ALL'];
            return $dat;
        } elseif ($network == "9MOBILE") {
            $data['datas'] = $dat = $response->json()['Dataplans']['9MOBILE_PLAN']['ALL'];
            return $dat;
        } elseif ($network == "GLO") {
            $data['datas'] = $dat = $response->json()['Dataplans']['GLO_PLAN']['ALL'];
            return $dat;
        } else {
            return false;
        }
    }
    public function getdataPrice(Request $request)
    {
        $network = $request->network;


        $response = Http::withHeaders([
            'Authorization' => 'Token 27568c756b857081a7b2d9dff073d3d8e02dbb50',
            'Accept' => "application/json",
            "Content-Type" => "application/json",
        ])->get('https://www.clekktelecoms.com/api/user');
        if ($network == "MTN") {
            $data['datas'] = $dat = $response->json()['Dataplans']['MTN_PLAN']['SME'];
            return $dat;
        } elseif ($network == "AIRTEL") {
            $data['datas'] = $dat = $response->json()['Dataplans']['AIRTEL_PLAN']['SME'];
            return $dat;
        } elseif ($network == "9MOBILE") {
            $data['datas'] = $dat = $response->json()['Dataplans']['9MOBILE_PLAN']['SME'];
            return $dat;
        } elseif ($network == "GLO") {
            $data['datas'] = $dat = $response->json()['Dataplans']['GLO_PLAN']['SME'];
            return $dat;
        } else {
            return false;
        }
    }
    public function getaccount(Request $request)
    {
        $acct_no = $request->account_number;
        // dd($acct_no);
        $data = User::where('account_number', $acct_no)->first();

        if ($data) {

            return $data;
        } else {

            return 'false';
        }
    }
    public function pin()
    {
        $data['user'] = Auth::user();
        return view('user.changepin', $data);
    }
    public function pinreset($slug)
    {
        $data['alerts'] = Alert::latest()->get();
        $data['user'] = Auth::user();


        //  $data['monnify'] = $mm = $this->reserveAccount();
        return view('user.pinreset', $data);
    }
    public function changepin(Request $request)
    {
        $value = explode(",", $request->value);
        $current = intval($value[0]);
        $new = intval($value[1]);
        // dd($current,$new);
        $user = Auth::user();
        $pin = $user->pin;
        if ($pin == $current) {
            $user->pin = $new;
            $user->save();
        } else {
            return "not_matched";
        }
    }
    public function forgetpin()
    {
        $user = Auth::user();
        $random = "ABDS7";
        $data = array('name' => $user->name, 'random' => $random);

        Mail::send('mail.resetpin', $data, function ($message) use ($user) {
            $message->to($user->email, '')->subject('Request to change pin');
            $message->from('support@telneting.com', 'Telneting');
        });
    }
    public function resetpin(Request $request)
    {
        $values = explode(",", $request->value);
        if ($values[0] == $values[1]) {
            $user = Auth::user();
            $user->pin = $values[0];
            $user->save();
            return true;
        } else {
            return "not_matched";
        }
    }
    public function referral_registration($slug)
    {
        $data['slug'] = $slug;
        // dd($slug);
        // return redirect()->route('register')->with($data);
        return view('auth.register', $data);
    }
    public function contact_form(Request $request)
    {

        $data = array('name' => $request->name, 'user_email' => $request->email, 'user_subject' => $request->subject, 'user_message' => $request->message);

        // Mail::send('mail.contact', $data, function($message) {
        //     $message->to('fasanyafemi@gmail.com', '')->subject
        //        ('User request from steadyhub');
        //     $message->from('steadysub@veenodetech.com','Steadyhub');
        //  });
        return 'sent';
    }
    public function processTransaction(Request $request)
    {
        $values = explode(",", $request->value);
        // dd($values);
        $result = $this->create_transaction($values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6]);

        if ($result !== 'not_completed') {
            return $result;
        } else {
            return "Transaction error";
        }
    }
    public function getdiscount2(Request $request)
    {

        $value = $request->value;
        $values = explode(",", $value);

        if ($values[1] == 'airtime') {
            $airtime = AirtimePrice::where('network', $values[0])->first()->set_price;

            return $airtime;
        } elseif ($values[1] == "data") {
        } elseif ($values[1] == "electricity") {
            $elec = ElectricityPrice::where('name', $values[0])->first()->set_price;
            return $elec;
        } elseif ($values[1] == "tv") {
            $tv = TvPrice::where('name', $values[0])->first()->set_price;

            return $tv;
        } else {
            return false;
        }
    }
    public function verification_resend()
    {

        $user = Auth::user();
        event(new Registered($user));
        return redirect()->back()->with('message', 'Verification link has been resent, please check your email address');
    }
    public function profile()
    {

        $data['user'] = Auth::user();
        //  $data['monnify'] = $mm = $this->reserveAccount();
        return view('user.profile', $data);
    }
    public function transactiondetials($id)
    {
        $data['alerts'] = Alert::latest()->get();
        $data['monnify'] = $mm = $this->reserveAccount();
        $data['transaction'] = Transaction::find($id);
        return view('user.transaction', $data);
    }
    public function usernotifications()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['monnify'] = $mm = $this->reserveAccount();
        $data['notifications'] = Notification::get();
        return view('user.notifications', $data);
    }
    public function manualfunding(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'bank_name' => 'required',
            'amount' => ['required', 'integer']
        ]);
        ManualFunding::create([
            'account_name' => $request->name,
            'bank_name' => $request->bank_name,
            'amount' => $request->amount,
            'user_id' => Auth::user()->id
        ]);
        return 'success';
    }
    public function ticket()
    {

        return view('user.ticket');
    }

    public function sms()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['user'] = Auth::user();

        return view('user.sms', $data);
    }

    public function kyc()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['user'] = Auth::user();

        return view('user.kyc', $data);
    }
    public function savekyc(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'dob' => 'required',
            'file' => 'required',
            'identification_number' => 'required',

        ]);
        $req = [
            'firstName' => $request->firstname,
            'lastName' => $request->lastname,
            'dob' => $request->dob,

        ];
        if ($request->document_type == 'voters_card') {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjE0ODQ1MSwiZW52IjoidGVzdCIsImlhdCI6MTY1NzE4Njc2MH0.lJJ9Ddd-NiXJYfxK0VX8tEilKJ95jaC9MxINgMT-6Oc',
                'Accept' => "application/json",
                "Content-Type" => "application/json",
            ])->post('https://vapi.verifyme.ng/v1/verifications/identities/vin/' . $request->identification_number, $req);
            if (!array_key_exists('statusCode', $response->json())  && ($response->json()['data']['firstName'] == $request->firstname || $response->json()['data']['lastName'] == $request->firstname)) {
                $file = $request->file;
                $filename = $file->hashName();
                $file->move(public_path() . '/kyc_documents', $filename);
                $user->verification_status = 1;
                $user->verification_document = $filename;
                $user->verification_number = $request->identification_number;
                $user->save();
                return redirect()->back()->with('success', 'approved successfully');
            } else {

                return redirect()->back()->with('message', 'KYC Verification failed, please make sure your inputs are correct and try again.');
            }
        } elseif ($request->document_type == 'drivers_license') {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjE0ODQ1MSwiZW52IjoidGVzdCIsImlhdCI6MTY1NzE4Njc2MH0.lJJ9Ddd-NiXJYfxK0VX8tEilKJ95jaC9MxINgMT-6Oc',
                'Accept' => "application/json",
                "Content-Type" => "application/json",
            ])->post('https://vapi.verifyme.ng/v1/verifications/identities/drivers_license/' . $request->identification_number, $req);
            if (!array_key_exists('statusCode', $response->json())  && ($response->json()['data']['firstName'] == $request->firstname || $response->json()['data']['lastName'] == $request->firstname)) {
                $file = $request->file;
                $filename = $file->hashName();
                $file->move(public_path() . '/kyc_documents', $filename);
                $user->verification_status = 1;
                $user->verification_document = $filename;
                $user->verification_number = $request->identification_number;
                $user->save();
                return redirect()->back()->with('success', 'approved successfully');
            } else {

                return redirect()->back()->with('message', 'KYC Verification failed, please make sure your inputs are correct and try again.');
            }
        } elseif ($request->document_type == 'tax_identification_number') {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjE0ODQ1MSwiZW52IjoidGVzdCIsImlhdCI6MTY1NzE4Njc2MH0.lJJ9Ddd-NiXJYfxK0VX8tEilKJ95jaC9MxINgMT-6Oc',
                'Accept' => "application/json",
                "Content-Type" => "application/json",
            ])->get('https://vapi.verifyme.ng/v1/verifications/identities/tin/' . $request->identification_number, $req);
            if (!array_key_exists('statusCode', $response->json())  && ($response->json()['data']['firstName'] == $request->firstname || $response->json()['data']['lastName'] == $request->firstname)) {
                $file = $request->file;
                $filename = $file->hashName();
                $file->move(public_path() . '/kyc_documents', $filename);
                $user->verification_status = 1;
                $user->verification_document = $filename;
                $user->verification_number = $request->identification_number;
                $user->save();
                return redirect()->back()->with('success', 'approved successfully');
            } else {

                return redirect()->back()->with('message', 'KYC Verification failed, please make sure your inputs are correct and try again.');
            }
        } else {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjE0ODQ1MSwiZW52IjoidGVzdCIsImlhdCI6MTY1NzE4Njc2MH0.lJJ9Ddd-NiXJYfxK0VX8tEilKJ95jaC9MxINgMT-6Oc',
                'Accept' => "application/json",
                "Content-Type" => "application/json",
            ])->post('https://vapi.verifyme.ng/v1/verifications/identities/nin/' . $request->identification_number, $req);
            if (!array_key_exists('statusCode', $response->json())  && ($response->json()['data']['firstName'] == $request->firstname || $response->json()['data']['lastName'] == $request->firstname)) {
                $file = $request->file;
                $filename = $file->hashName();
                $file->move(public_path() . '/kyc_documents', $filename);
                $user->verification_status = 1;
                $user->verification_document = $filename;
                $user->verification_number = $request->identification_number;
                $user->save();
                return redirect()->back()->with('success', 'approved successfully');
            } else {

                return redirect()->back()->with('message', 'KYC Verification failed, please make sure your inputs are correct and try again.');
            }
        }
    }

    public function createticket(Request $request)
    {
        $this->validate($request, [
            'details' => 'required',
            'title' => 'required',

        ]);
        Ticket::create([
            'title' => $request->title,
            'description' => $request->details,
            'user_id' => Auth::user()->id
        ]);
        $user = Auth::user();
        $data = array('name' => $user->name, 'title' => $request->title, 'details' => $request->details);

        Mail::send('mail.ticket', $data, function ($message) use ($user) {
            $message->to($user->email, 'Telneting')->subject('Complain Ticket');
            $message->from('support@telneting.com', 'Telneting');
        });
        return 'success';
    }
    public function submitAirtime(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required',
            'amount' => ['required', 'integer'],

        ]);
        AirtimeToCash::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'amount' => $request->amount,
            'user_id' => Auth::user()->id
        ]);
        return 'success';
    }
    public function editprofile()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['user'] = Auth::user();
        $data['monnify'] = $mm = $this->reserveAccount();

        return view('user.editprofile', $data);
    }
    public function cable()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['user'] = Auth::user();
        // $data['monnify'] = $mm = $this->reserveAccount();
        return view('user.cable', $data);
    }
    public function fetchairtime()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['monnify'] = $mm = $this->reserveAccount();
        $data['mtn'] =   Airtime::where('network', 'MTN')->get();
        $data['glo'] = Airtime::where('network', 'GLO')->get();
        $data['airtel'] = Airtime::where('network', 'AIRTEL')->get();
        $data['mobile'] = Airtime::where('network', '9MOBILE')->get();

        return view('user.fetchairtime', $data);
    }

    public function buycable2(Request $request)
    {
        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $amount = $request->amount;
        $cable_type = $request->cable_type;
        $decoder_no = $request->decoder_no;
        $user = Auth::user();



        if ($pin == $user->pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > intval($amount) && intval($amount) >= 100) {
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();
                    if ($check_pending !== null) {
                        return "pending";
                    } else {

                        $save_pending = PendingUserTransactions::create([
                            'user_id' => $user->id,
                            'data' => $amount
                        ]);
                        $reference = 'cable' . Str::random(8);


                        $response = Http::withBasicAuth('victorakinode@gmail.com', env('VTPASS_PASSWORD'))->post('https://vtpass.com/api/pay?request_id=' . $request->request_id . '&amount=' . $request->amount . '&subscription_type=' . $request->subscription_type . '&phone=' . $request->phone . '&serviceID=' . $request->serviceID . '&billersCode=' . $request->billersCode);
                        $rres = json_decode($response);
                        if ($rres->code == '000') {
                            $the_id = $this->create_transaction('Tv Payment', $reference, $request->details, 'debit', $request->amount, $request->user_id, $request->name);
                        } else {
                            $the_id =    $this->create_transaction('Failed Transaction', $reference, $request->details, 'debit', $request->amount, $request->user_id, $request->name);
                        }
                        return $the_id;
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }
    public function electricity()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['user'] = Auth::user();
        // $data['monnify'] = $mm = $this->reserveAccount();
        return view('user.electricity', $data);
    }
    public function electricity2(Request $request)
    {
        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $amount = $request->amount;
        $service_type = $request->service_type;
        $meter_type = $request->meter_type;
        $meter_no = $request->meter_no;
        $user = Auth::user();


        if ($pin == $user->pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > intval($amount) &&  intval($amount) >= 100) {
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();
                    if ($check_pending !== null) {
                        return "pending";
                    } else {

                        $save_pending = PendingUserTransactions::create([
                            'user_id' => $user->id,
                            'data' => $amount
                        ]);

                        $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post("https://vtpass.com/api/pay?request_id=" . $request->request_id . "&serviceID=" . $request->serviceID . "&billersCode=" . $request->billersCode . "&variation_code=" . $request->meter_type . "&amount=" . intval($request->amount) . "&phone=08111111111");
                        if ($response->json()['errors']) {
                            return 'api_error';
                        } else {
                            $r_pin = $response->json()['purchased_code'];
                            $r_amount = $response->json()['amount'];

                            $data = array('name' => Auth::user()->name, 'pin' => $r_pin, 'amount' => $r_amount);
                            Mail::send('mail.electricity', $data, function ($message) use ($user) {
                                $message->to($user->email, 'Telneting')->subject('Electricity TOken Details');
                                $message->from('support@telneting.com', 'Telneting');
                            });
                            return $response->json();
                        }
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }

    public function exam()
    {
        $data['alerts'] = Alert::latest()->get();
        $data['monnify'] = $mm = $this->reserveAccount();
        $data['exams'] = ExamPin::get();
        $data['user'] = Auth::user();
        return view('user.exam', $data);
    }
    public function buypin(Request $request)
    {


        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $amount = $request->amount;
        $exam_type = $request->exam_type;
        $package = $request->package;


        $user = Auth::user();
        if ($pin == $user_pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > intval($amount) &&  intval($amount) >= 100) {
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();

                    if ($check_pending !== null) {
                        return "pending";
                    } else {

                        $save_pending = PendingUserTransactions::create([
                            'user_id' => $user->id,
                            'data' => $amount
                        ]);



                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $request->serviceID,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => array(
                                'no_of_pins' => 1,
                            ),
                            CURLOPT_HTTPHEADER => array(
                                "AuthorizationToken: 3d73905084f5e1f35c2f63f5b50d692b",
                                "cache-control: no-cache"
                            ),
                        ));
                        $response = curl_exec($curl);
                        $reference = 'tranx' . Str::random(8);

                        $r_amount = $amount;
                        PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->delete();

                        $rres = json_decode($response);
                        if ($rres->success == 'true') {
                            $the_id = $this->create_transaction('Exam Pin Purchase', $reference, $request->details, 'debit', $amount, $request->user_id, $request->name);
                            $data = array('name' => Auth::user()->name, 'pin' => $rres->pin, 'amount' => $r_amount);
                            Mail::send('mail.exam', $data, function ($message) use ($user) {
                                $message->to($user->email, 'Kobbylinks')->subject('Exam Pin Details');
                                $message->from('support@kobbylinks.com', 'Kobbylinks');
                            });
                        } else {
                            $the_id =  $this->create_transaction('Failed Transaction', $reference, $request->details, 'debit', $amount, $request->user_id, $request->name);
                        }

                        return $the_id;
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }

    public function fundwallet()
    {

        $data['split'] = [
            "type" => "percentage",
            "currency" => "KES",
            "subaccounts" => [
                ["subaccount" => "ACCT_li4p6kte2dolodo", "share" => 10],
                ["subaccount" => "ACCT_li4p6kte2dolodo", "share" => 30],
            ],
            "bearer_type" => "all",
            "main_account_share" => 70
        ];

        $data['user'] = Auth::user();
        return view('user.fundwallet', $data);
    }
    public function fundwallet2(Request $request)
    {

        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $amount = $request->amount;
        $service_type = $request->service_type;
        $meter_type = $request->meter_type;
        $meter_no = $request->meter_no;
        $user = Auth::user();

        if ($pin == $user_pin) {
            //code for the referral
            if ($user->spent == null) {
                $ref = User::where('referral_link', $user->referred_by)->first();
                $referral_amount = ReferralAmount::find(1)->amount;
                $ref->balance += $referral_amount;
                $ref->save();
                $title = "Referral Bonus";
                $description = 'Account Funded with #100 from ' . $user->name;
                $type = 'credit';

                $this->create_transaction($title, $description, $type, $referral_amount, $ref);

                Transaction::create([
                    'user_id' => $user->id,
                    'title' => 'Funding Wallet',
                    'service_type' => 'Wallet Fund',
                    'description' => 'Account Funded with #' . $amount,
                    'amount' => $amount,
                ]);
                $user->balance += $amount - $referral_amount;
                $fund = TotalFund::find(1);
                $fund->amount = $fund->amount + $amount;

                $fund->save();

                $user->save();
            } else {
                Transaction::create([
                    'user_id' => $user->id,
                    'title' => 'Funding Wallet',
                    'service_type' => 'Wallet Fund',
                    'description' => 'Account Funded with #' . $amount,
                    'amount' => $amount,
                ]);
                $fund = TotalFund::find(1);
                $fund->amount = $fund->amount + $amount;

                $fund->save();
                $user->balance += $amount;

                $user->save();
            }




            return 'transaction successful';
            //here is where the purchase will take place

        } else {
            return "Incorrect Pin";
        }
    }

    public function buyairtime()
    {

        $data['user'] = Auth::user();
        $data['alerts'] = Alert::latest()->get();

        $data['discount'] = AirtimePrice::pluck('set_price');
        // $data['monnify'] = $mm = $this->reserveAccount();
        // dd($mm);
        // $data['monnify'] = $mm = $this->reserveAccount()['responseBody']['accounts'][0];

        return view('user.buyairtime', $data);
    }
    public function buyAirtimeAPI(Request $request)
    {


        $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post('https://vtpass.com/api/pay?request_id=' . $request->request_id . '&serviceID=' . $request->serviceID . '&amount=' . $request->amount . '&phone=' . $request->phone);
        return $response->json();
    }
    public function fetchairtime2(Request $request)
    {
        // dd('here');
        $data['alerts'] = Alert::latest()->get();
        $data['monnify'] = $mm = $this->reserveAccount();
        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $user_pin = (int)$pin1;
        // dd($user_pin,$request->all());

        $balance = Auth::user()->balance;
        $pin = Auth::user()->pin;
        $amount = $request->price;
        $network = $request->network;
        $quantity = $request->quantity;
        $user = Auth::user();
        // dd($request->all());
        $airtime = Airtime::where('network', $network)->where('amount', $amount)->where('status', 0)->get();
        if ($pin == $user_pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > $amount) {
                    if (count($airtime) >= $quantity) {

                        $data['airtimes'] = $airtime2 = Airtime::where('network', $network)->where('amount', $amount)->where('status', 0)->take($quantity)->get();
                        // dd($airtime2,$request->all());
                        Transaction::create([
                            'user_id' => $user->id,
                            'title' => 'Fetch airtime',
                            'service_type' => 'Fetch airtime pin',
                            'description' => '' . $network . ' Airtime pin generated, Total amount: ' . $amount * $quantity,
                            'amount' => $amount * $quantity,
                        ]);
                        $user->balance -= $amount * $quantity;
                        $user->spent += $amount * $quantity;
                        $user->save();
                        foreach ($airtime2 as $airtime) {

                            $airtime->delete();
                        }
                        $pdf = PDF::loadView('user.airtimefetchedpdf', $data);

                        return $pdf->download('generatedpin.pdf');
                        return view('user.airtimefetched', $data);
                    } else {
                        return redirect()->back()->with('message', "Airtime quantity not available");
                    }

                    //here is where the purchase will take place
                } else {
                    return redirect()->back()->with('message', "Opps, Insufficient Balance");
                }
            }
        } else {
            return redirect()->back()->with('message', "Opps, Incorrect Pin");
        }
    }


    public function updatepin(Request $request)
    {
        $user = Auth::user();
        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;

        $user->pin = $pin1;
        $user->save();
    }
    public function buyairtime2(Request $request)
    {
        $phone = $request->phone;
        $network = $request->network;
        $val = substr($phone, 0, 4);
        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;
        $user = Auth::user();
        $totalF = AdminFund::find(1);


        $balance = $user->balance;
        $pin = $user->pin;

        $amount = $request->amount;
        if ($pin == $user->pin) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > $amount && $amount >= 100) {
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();

                    if ($check_pending !== null) {
                        return "pending";
                    } else {
                        $save_pending = PendingUserTransactions::create([
                            'user_id' => $user->id,
                            'data' => $amount
                        ]);

                        // $response = Http::withBasicAuth('victorakinode@gmail.com', 'dewinner1039')->post('https://vtpass.com/api/pay?request_id=' . $request->request_id . '&serviceID=' . $request->serviceID . '&amount=' . $request->amount . '&phone=' . $request->phone);
                        $reference = 'tranx' . Str::random(8);

                        $network_id = $request->serviceID;
                        $phone = $request->phone;
                        $plan_id = $request->plan_id;
                        $name = $request->name;
                        if ($network_id == 'MTN') {
                            $network_id = '01';
                        } elseif ($network_id == 'GLO') {
                            $network_id = '02';
                        } elseif ($network_id == 'AIRTEL') {
                            $network_id = '03';
                        } else {
                            $network_id = '04';
                        }


                        // dd($request_body,$request->all(), env('EASY_ACCESS_TOKEN'));

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://easyaccessapi.com.ng/api/airtime.php",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => array(
                                'network' => intval($network_id),
                                'amount' => $amount,
                                'mobileno' => $phone,
                                'airtime_type' => '001',
                                'client_reference' => $reference,
                            ),
                            CURLOPT_HTTPHEADER => array(
                                "AuthorizationToken: 5cda03bd6d615cbf03aa0035994502ef",
                                "cache-control: no-cache"
                            ),
                        ));
                        $response = curl_exec($curl);
                        curl_close($curl);
                        PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->delete();

                        $rres = json_decode($response);


                        if ($rres->success == 'false') {
                            $the_id = $this->create_transaction('Failed Transaction', $request->reference, $request->details, 'debit', $amount, $request->user_id, $request->network);
                        } else {
                            $the_id = $this->create_transaction('Data Subscription', $request->reference, $request->details, 'debit', $amount, $request->user_id, $request->network);
                        }


                        return $the_id;

                        return $response;
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }
    public function confirmdatanetwork(Request $request)
    {
    }
    public function buydata()
    {
        $data['user'] = Auth::user();



        return view('user.buydata', $data);
    }
    public function searchdata(Request $request)
    {
        $val = $request->val;
        $check = Data::where('network', $val)->first();
        if ($check->status == 0) {
            return false;
        } else {

            $data = Data::where('network', $val)->orderBy('actual_price')->get();
            return $data;
        }
    }
    public function buydata2(Request $request)
    {


        $beneficial_user_id = $request->beneficial_user_id;

        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;
        $user = Auth::user();
        $balance = $user->balance;
        $pin = $user->pin;
        $amount = $request->amount;



        if ($pin == $pin1) {
            if ($balance > intval($amount)  && intval($amount) >= 100) {


                $reference = 'tranx' . Str::random(8);
                $user->balance -= $amount;
                $user->save();
                $ben_user = User::find($beneficial_user_id);
                $ben_user->balance += $amount;
                $ben_user->save();
                Transaction::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'amount' => $amount,
                    'type' => 'debit',
                    'details' => "Tranfer of " . $amount . " to " . $ben_user->name,
                    'receiver_name' => $ben_user->name,
                    'receiver_account' => $ben_user->account_number,
                ]);
                Transaction::create([
                    'user_id' => $ben_user->id,
                    'name' => $user->name,
                    'amount' => $amount,
                    'type' => 'credit',
                    'receiver_name' => $ben_user->name,
                    'receiver_account' => $ben_user->account_number,
                    'details' => "Received payment of " . $amount . " from " . $user->name
                ]);




                return 'success';
            } else {
                return "Insufficient fund";
            }
        } else {
            return "Incorrect Pin";
        }
    }

    public function upgrade(Request $request)
    {




        $user_pin = '';
        $user_pin .= $request->first;
        $user_pin .= $request->second;
        $user_pin .= $request->third;
        $user_pin .= $request->fourth;
        $pin1 = (int)$user_pin;
        $user = Auth::user();
        $balance = $user->balance;
        $pin = $user->pin;
        $amount = $request->amount;
        if ($pin == $pin1) {
            if ($user->spent >= 50000 && $user->verification_status == 0) {
                return 'limit_reached';
            } else {
                if ($balance > intval($amount) && intval($amount) >= 100) {
                    //Check for duplicate transaction
                    $check_pending = PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->first();

                    if ($check_pending !== null) {
                        return "pending";
                    } else {

                        // $save_pending = PendingUserTransactions::create([
                        //     'user_id' => $user->id,
                        //     'data' => $amount
                        // ]);
                        if ($user->agent == 1) {
                            PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->delete();
                            return false;
                        } else {
                            $user->agent = 1;
                            $user->save();



                            PendingUserTransactions::where('user_id', $user->id)->where('data', $amount)->delete();
                            return true;
                        }
                    }
                } else {
                    return "Insufficient Balance";
                }
            }
        } else {
            return "Incorrect Pin";
        }
    }


    public function checkpin(Request $request)
    {
        $pin = Auth::user()->pin;
        if ($pin == null) {
            return true;
        } else {
            return false;
        }
    }
    public function updateprofile(Request $request)
    {
        $user = Auth::user();

        $user->name = $request->name;
        $user->phone = $request->phone;
        if ($request->has('image')) {
            $image = $request->file('image');
            $fileName = $image->hashName();
            $image->move(public_path() . '/profilepic/', $fileName);
            $user->image = $fileName;
        } else {
            $user->image = Auth::user()->image;
        }
        $user->save();
    }
    public function landing()
    {
        $data['airtel'] = Data::where('network', 'airtel')->orderBy('set_price')->get();
        $data['mtn'] = Data::where('network', 'mtn')->orderBy('set_price')->get();
        $data['glo'] = Data::where('network', 'glo')->orderBy('set_price')->get();
        $data['mobile'] = Data::where('network', '9mobile')->orderBy('set_price')->get();
        // dd($data);
        return view('frontend.index', $data);
    }

    public function event()
    {
        $data['airtel'] = Data::where('network', 'airtel')->orderBy('set_price')->get();
        $data['mtn'] = Data::where('network', 'mtn')->orderBy('set_price')->get();
        $data['glo'] = Data::where('network', 'glo')->orderBy('set_price')->get();
        $data['mobile'] = Data::where('network', '9mobile')->orderBy('set_price')->get();
        // dd($data);
        return view('frontend.event', $data);
    }
    public function join_event(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'min:5'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'numeric', 'min:10'],
            'occupation' => 'required'
        ]);
        // return true;

        TelnetingEvent::create($request->all());
        return true;
    }
    public function about()
    {
        return view('frontend.about');
    }
    public function e404()
    {
        return view('frontend.404');
    }
    public function dashboard(Request $request)
    {
      
            return view('user.index');
       
    }

    public function polling_unit(Request $request)
    {
        $user = Auth::user();

        $data['transactions'] = Transaction::where('user_id', $user->id)->get();
        // session()->flush();
        $data['user'] = Auth::user();
        // $data['transactions'] = Transaction::where('user_id', Auth::user()->id)->latest()->get();
        if ($user->type == 0) {

            return view('user.poll_unit', $data);
        } else {

            return redirect()->route('admindashboard');
        }
    }
    public function verify_email()
    {
        $user = Auth::user();
        $data['alerts'] = Alert::latest()->get();

        // session()->flush();
        if (session()->has('monnify') && session()->has('user')) {
            $data['monnify'] = $d = session()->get('monnify');
            // dd($d,'the d');
            $data['user'] = session()->get('user');
        } else {
            $data['monnify'] = $mm = $this->reserveAccount();

            session()->put('monnify', $mm);
            $data['monnify'] = $this->reserveAccount();
            $data['user'] = Auth::user();
            session()->put('user', Auth::user());
        }
        return view('user.predashboard', $data);
    }
    public function create_ticket()
    {
        if (session()->has('monnify') && session()->has('user')) {
            $data['monnify'] = session()->get('monnify');
            $data['user'] = session()->get('user');
        } else {
            $data['monnify'] = $mm = $this->reserveAccount();
            session()->put('monnify', $mm);
            $data['user'] =  session()->put('user', Auth::user());
        }
        return view('user.createTicket', $data);
    }
    public function airtime_to_cash()
    {
        if (session()->has('monnify') && session()->has('user')) {
            $data['monnify'] = session()->get('monnify');
            $data['user'] = session()->get('user');
        } else {
            $data['monnify'] = $mm = $this->reserveAccount();
            session()->put('monnify', $mm);
            $data['user'] =  session()->put('user', Auth::user());
        }
        return view('user.airtimeCash', $data);
    }
    public function transaction()
    {





        $data['user'] =  Auth::user();


        $data['transactions'] = Transaction::where('user_id', Auth::user()->id)->latest()->get();



        return view('user.new_transactions', $data);
    }
}
