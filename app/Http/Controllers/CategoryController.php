<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        $data['categories'] = Category::where('user_id',Auth::user()->id)->get();
        return view('backend.category',$data);
    }
    public function create(Request $request) {
        $request->validate([
            'name' => 'required',
        
        ]);
        if($request->has('description')) {
            Category::create([
                'name' => $request->name,
                'user_id' => Auth::user()->id,
                'school_id' => Auth::user()->school_id,
                'description' => $request->description
            ]);
        } 
        else {
            Category::create([
                'name' => $request->name,
                'user_id' => Auth::user()->id,
                'school_id' => Auth::user()->school_id,
              
            ]);
        }

        return 'saved successfully';
    }
}
