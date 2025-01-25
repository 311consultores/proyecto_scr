<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function login(Request $request) {

        $this->validate($request, [
            'user' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::table('api_users')
        ->where('user', $request['user'])
        ->where('password', $request['password'])
        ->get();

        if(!$user) {
            return response()->json(['ok' => false, 'message' => 'El usuario no existe']);
        }

        return response()->json(['ok' => true, 'data' => $this->encode_json($user)]);

        
    }

    public function getReports() {
        $reports = DB::table('wprq_frm_items')
        ->select('id',DB::raw("CONCAT(UPPER(id),UPPER(item_key)) AS folio"), 'name', DB::raw("DATE_FORMAT(created_at, '%d/%m/%Y') AS created_at"))
        ->where('form_id',3)
        ->limit(50)        
        ->orderBy('created_at', 'DESC')
        ->get();
        return $reports;
    }

}