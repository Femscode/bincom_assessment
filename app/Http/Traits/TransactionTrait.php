<?php
namespace App\Http\Traits;
use PDF;
use App\Models\AirtimePrice;
use App\Models\Data;
use App\Models\ElectricityPrice;
use App\Models\ExamPin;
use App\Models\AdminFund;
use App\Models\Transaction;
use App\Models\TvPrice;
use App\Models\User;
use Illuminate\Support\Str;

trait TransactionTrait {
    public function index() {
      //
    }
    public function create_transaction($title,$reference, $details, $type, $amount,$user,$name) {
    //    dd($title, $details, $type, intval($amount),intval($user),$name);
        $r_user = User::find($user);
       $tranx =  Transaction::create([
            'user_id' => $user,
            'title' => $title,
            'reference' => $reference,
            'before' => $r_user->balance,
            'service_type' => $type,
            'description' => $details,
            'amount' => $amount,
        ]);
        
        if($title == 'Electricity Payment') {
           
            $n_price = ElectricityPrice::where('name',$name)->first();
            $user_price = $amount - ($n_price->set_price/100) * $amount;
            $nuser = User::find($user);
            $nuser->balance -= $user_price;
            $nuser->spent += $user_price;
            $nuser->save();
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';
            $tranx->save();
            return $tranx->id;
          
        }
        elseif($title == 'Tv Payment') {

            $n_price = TvPrice::where('name',$name)->first();
            $user_price = $amount - ($n_price->set_price/100) * $amount;
          
            $nuser = User::find($user);
            $nuser->balance -= $user_price;
            $nuser->spent += $user_price;
            $nuser->save();
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';

            $tranx->save();
            return $tranx->id;
           
        

        }

        elseif($title == 'sms service') {

            $user_price = $amount+50;
          
            $nuser = User::find($user);
            $nuser->balance -= $user_price;
            $nuser->spent += $user_price;
            $nuser->save();
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';
            $tranx->save();
            return $tranx->id;
           
        }


        elseif($title == 'Agent Upgrade') {
           
            $n_price = 50000;
            $user_price = 50000;
          
            $nuser = User::find($user);
            $nuser->balance -= $user_price;
            $nuser->spent += $user_price;
            $nuser->save();
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';
            $tranx->save();
            return $tranx->id;
           
          
        }
      
        elseif($title == 'Data Subscription') {

            $n_price = Data::where('network',$name)->where('set_price',$amount)->first();
            $user_price = $amount;
           
            $nuser = User::find($user);
            $nuser->balance -= $user_price;
            $nuser->spent += $user_price;
            $nuser->save();
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';
            $tranx->save();
            return $tranx->id;

        }
        elseif($title == "Airtime Payment") {
            $n_price = AirtimePrice::where('network',$name)->first();
            $user_price = $amount - ($n_price->set_price/100) * $amount;
         
            $nuser = User::find($user);
            $nuser->balance -= $user_price;
            $nuser->spent += $user_price;
            $nuser->save();
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';
            $tranx->save();
            return $tranx->id;
          
        }
        elseif($title == "Exam Pin Purchase") {
         
            $n_price = ExamPin::where('name',$name)->first();
            $user_price = $n_price->set_amount;
            $nuser = User::find($user);
            $nuser->balance -= $amount;
            $nuser->spent += $amount;
            $nuser->save();
           
            $tranx->after = $nuser->balance;
            $tranx->status = 'success';
            $tranx->save();
            return $tranx->id;
            
        }
      
        else {
            $tranx->status = 'failed';
            $nuser = User::find($user);
            $tranx->after = $nuser->balance;
            $tranx->save();
            
            return "not_completed";
        }
       
    }
}