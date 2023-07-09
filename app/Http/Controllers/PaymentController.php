<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminFund;
use App\Models\TotalFund;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\ReferralAmount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Unicodeveloper\Paystack\Facades\Paystack;


class CustomTransactionHashUtil
{

    public static function computeSHA512TransactionHash($stringifiedData, $clientSecret)
    {
        $computedHash = hash_hmac('sha512', $stringifiedData, $clientSecret);
        return $computedHash;
    }
}
class PaymentController extends Controller
{
    public function redirectToGateway()
    {
        try {
          

            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {

            return Redirect::back()->with('message', 'The payment token has expired. Please refresh the page and try again.');
            // return Redirect::back()->withMessage(['msg' => 'The paystack token has expired. Please refresh the page and try again.', 'type' => 'error']);
        }
    }
    public function paystack(Request $request) {
        file_put_contents(__DIR__ . '/log5.txt', json_encode($request->all(), JSON_PRETTY_PRINT), FILE_APPEND);
    $email = $request->input('data.customer.email');
    $r_amountpaid = ($request->input('data.amount')) / 100;
    $amountpaid = $r_amountpaid;
    $user_id = User::where('email', $email)->firstOrFail()->id;
    $user = User::find($user_id);
    $reference = 'tranx' . Str::random(8);
    if ($user->balance == 0 && $user->spent == 0) {
        $referral_amount = ReferralAmount::find(1)->amount;
        $user_referred = User::where('referral_link', $user->referred_by)->first();
        if($user_referred == null) {
            $before = $user->balance;
            $user->balance += $amountpaid;
            $user->save();
            $adminfund = TotalFund::find(1);
            $adminfund->amount += $amountpaid;
            $adminfund->save();
            Transaction::create([
                'user_id' => $user_id,
                'reference' => $reference,
                'status' => 'success',
                'before' => $before,
                'after' => $user->balance,
                'title' => 'Funding Wallet Through Paystack',
                'service_type' => 'Wallet Fund',
                'description' => 'Account Funded through paystack, amount: #' . $amountpaid,
                'amount' => $amountpaid,
            ]);
        }
        else {
        

        $user_before = $user_referred->balance;
        $user_referred->balance += $referral_amount;
        $user_referred->save();
        $new_balance = $amountpaid - $referral_amount;
        $before = $user->balance;
        $user->balance += $new_balance;
        $user->save();
        $adminfund = TotalFund::find(1);
        $adminfund->amount += $amountpaid;
        $adminfund->save();
        Transaction::create([
            'user_id' => $user_id,
            'reference' => $reference,
            'status' => 'success',
            'before' => $before,
            'after' => $user->balance,
            'title' => 'Funding Wallet Through Paystack',
            'service_type' => 'Wallet Fund',
            'description' => 'Account Funded through paystack, amount: #' . $new_balance,
            'amount' => $new_balance,
        ]);

        Transaction::create([
            'user_id' => $user_referred->id,
            'title' => 'Referral Bonus Earned',
            'reference' => $reference,
            'status' => 'success',
            'before' => $user_before,
            'after' => $user_referred->balance,
            'service_type' => 'Referral Bonus',
            'description' => 'Account Funded with: #' . $referral_amount . 'From ' . $user->name,
            'amount' => $new_balance,
        ]);
    }

    } else {
        $before = $user->balance;
        $user->balance += $amountpaid;
        $user->save();
        $adminfund = TotalFund::find(1);
        $adminfund->amount += $amountpaid;
        $adminfund->save();
        Transaction::create([
            'user_id' => $user_id,
            'title' => 'Funding Wallet Through Paystack',
            'reference' => $reference,
            'status' => 'success',
            'before' => $before,
            'after' => $user->balance,
            'service_type' => 'Wallet Fund',
            'description' => 'Account Funded through paystack, amount: #' . $amountpaid,
            'amount' => $amountpaid,
        ]);
    }



    return response()->json("OK", 200);
    }
    public function paystack_veenode(Request $request) {
        file_put_contents(__DIR__ . '/log5.txt', json_encode($request->all(), JSON_PRETTY_PRINT), FILE_APPEND);
    $email = $request->input('data.customer.email');
    $r_amountpaid = ($request->input('data.amount')) / 100;
    $amountpaid = $r_amountpaid;
    $user_id = User::where('email', $email)->firstOrFail()->id;
    $adminfund = AdminFund::find(1);
    $adminfund->balance += $amountpaid;
    $adminfund->save();
    Transaction::create([
        'user_id' => $user_id,
        'title' => 'Admin Fund Wallet',
        'service_type' => 'Wallet Fund',
        'description' => 'Account Funded by admin, amount: #' . $amountpaid,
        'amount' => $amountpaid,
    ]);


    return response()->json("OK", 200);
    }

    public function monnifyTransactionComplete2(Request $request)
    {
        
        $DEFAULT_MERCHANT_CLIENT_SECRET = env("DEFAULT_MERCHANT_CLIENT_SECRET");
        $data = json_encode($request->eventData);
         $req = $request->all();
        // $email = $req['eventData']['customer']['email'];
        $email = $request->input('customer.email');
        $eventType = $request->input('paymentStatus');
        $r_amountPaid = $request->input('amountPaid');
        $amountPaid = $r_amountPaid - 50;
        $reference = 'tranx' . Str::random(8);
         Log::info('monnifyWebhook',array('customer' => $request->all(),'rara' => $email));
        $computedHash = CustomTransactionHashUtil::computeSHA512TransactionHash($data, $DEFAULT_MERCHANT_CLIENT_SECRET);
        if ($eventType == "PAID") {
             $id = User::where('email', $email)->firstOrFail()->id;
            $user = User::find($id);
            if ($user->balance == 0 && $user->spent == 0) {
                $referral_amount = ReferralAmount::find(1)->amount;
                $user_refer = User::where('referral_link', $user->referred_by)->first();
                if ($user_refer == null) {
                    $before = $user->balance;
                    $user->balance += $amountPaid;
                    $user->save();
                    $adminfund = TotalFund::find(1);
                    $adminfund->amount += $amountPaid;
                    $adminfund->save();
                    Transaction::create([
                        'user_id' => $id,
                        'title' => 'Funding Wallet Through Monnify',
                        'reference' => $reference,
                        'status' => 'success',
                        'before' => $before,
                        'after' => $user->balance,
                        'service_type' => 'Wallet Fund',
                        'description' => 'Account Funded through transfer #' . $amountPaid,
                        'amount' => $amountPaid,
                    ]);
                }
             
                else {
                    $ref_id = $user_refer->id;
                    $user_referred = User::find($ref_id);
                    $user_before = $user_referred->balance;
                    $user_referred->balance += $referral_amount;
                    $user_referred->save();
                    $new_balance = intval($amountPaid) - intval($referral_amount);
                    $before = $user->balance;
                    $user->balance += $new_balance;
                    $user->save();
                    $adminfund = TotalFund::find(1);
                    $adminfund->amount += $amountPaid;
                    $adminfund->save();
                    Transaction::create([
                        'user_id' => $id,
                        'title' => 'Funding Wallet Through Monnify',
                        'reference' => $reference,
                        'status' => 'success',
                        'before' => $before,
                        'after' => $user->balance,
                        'service_type' => 'Wallet Fund',
                        'description' => 'Account Funded through transfer #' . $new_balance,
                        'amount' => $new_balance,
                    ]);

                    Transaction::create([
                        'user_id' => $ref_id,
                        'title' => 'Referral Bonus Earned',
                        'reference' => $reference,
                        'status' => 'success',
                        'before' => $user_before,
                        'after' => $user_referred->balance,
                        'service_type' => 'Referral Bonus',
                        'description' => 'Account Funded with: #' . $referral_amount . 'From ' . $user->name,
                        'amount' => $referral_amount,
                    ]);
                    
                }
            } else {
                $before = $user->balance;
                $user->balance += $amountPaid;
                $user->save();
                $adminfund = TotalFund::find(1);
                $adminfund->amount += $amountPaid;
                $adminfund->save();
                Transaction::create([
                    'user_id' => $id,
                    'title' => 'Funding Wallet Through Monnify',
                    'reference' => $reference,
                    'before' => $before,
                    'after' => $user->balance,
                    'status' => 'success',
                    'service_type' => 'Wallet Fund',
                    'description' => 'Account Funded through transfer #' . $amountPaid,
                    'amount' => $amountPaid,
                ]);
            }

            http_response_code(200);
        } else if ($eventType == "PENDING_TRANSACTION") {
            $id = User::where('email', $email)->firstOrFail()->id;
            $user = User::find($id);

            Transaction::create([
                'user_id' => $id,
                'title' => 'Pending Wallet Funding',
                'service_type' => 'Wallet Fund',
                'description' => 'Pending funding transaction',
                'amount' => 0,
            ]);
            http_response_code(401);
            //Tell that particular user that the transaction is under pending
        } else {
            http_response_code(200);
        }
    }
    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();
        $amount = $paymentDetails['data']['amount'] / 100;

        return redirect()->route('dashboard');
    }
    public function handleGatewayCallbackVeenode()
    {
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/:reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer SECRET_KEY",
                "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {

            return redirect()->route('admindashboard');
        }
    }

    public function monnifyTransactionRefund(Request $request)
    {
        dd($request->all());
    }
    public function handleWebhook(Request $request)
    {
        $input = @file_get_contents("php://input");
        file_put_contents(__DIR__ . '/log5.txt', json_encode($request->all(), JSON_PRETTY_PRINT), FILE_APPEND);
        $email = $request->input('data.customer.email');
        $amountpaid = ($request->input('data.amount')) / 100;
        $user_id = User::where('email', $email)->firstOrFail()->id;
        $user = User::find($user_id);
        $user->balance += $amountpaid;
        $user->save();
        $adminfund = TotalFund::find(1);
        $adminfund->amount += $amountpaid;
        $adminfund->save();

        return response()->json("OK", 200);
    }
}
