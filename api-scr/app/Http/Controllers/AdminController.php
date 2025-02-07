<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function login(Request $request) {

        $user = DB::table('api_users')
        ->where('user', $request['user'])
        ->first();
         
        if(!$user){
            return response()->json(['ok' => false, 'message' => 'El usuario no existe']);   
        }else{
            if($user->password == $request['password']){
                return response()->json(['ok' => true, 'data' => $this->encode_json(json_encode($user))]);
            }
            return response()->json(['ok' => false, 'message' => 'La contraseña es invalida']);
        }
    }

    public function getReports() {
        $respuesta = DB::table('wprq_frm_items as items')
        ->select(
            'items.id',
            'items.form_id',
            DB::raw("CONCAT(UPPER(items.id), UPPER(items.item_key)) AS folio"),
            'items.name',
            "created_at"
        )
        ->where('items.form_id', 3)
        ->limit(50)
        ->orderBy('items.created_at', 'DESC')
        ->get()
        ->map(function ($item) {
            $item->created_at = $this->convertDateToText($item->created_at, 1);
            $item->columnas = DB::table('wprq_frm_fields as field')
                ->select('field.id', 'field.name as Columna', 'field_res.meta_value as Respuesta')
                ->join('wprq_frm_item_metas as field_res', 'field.id', '=', 'field_res.field_id')
                ->where('field.form_id', 3)
                ->where('field_res.item_id', $item->id)
                ->orderBy('field.field_order', 'ASC')
                ->get();
            return $item;
        });

        return $respuesta;
    }

}