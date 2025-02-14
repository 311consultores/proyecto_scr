<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Exports\InspeccionExport;


class InspeccionController extends Controller
{
    //Admin
    public function index()
    {
        // Code to list all inspections
    }

    public function show($id)
    {
        // Code to show a specific inspection
    }

    public function store(Request $request)
    {
        // Code to create a new inspection
    }

    public function update(Request $request, $id)
    {
        // Code to update an existing inspection
    }

    public function generarReporteBitacora($id)
    {
        $data = [];
        $data = DB::table('wprq_frm_fields as field')
        ->select('field.id', 'field.name as Columna', 'field_res.meta_value as Respuesta')
        ->join('wprq_frm_item_metas as field_res','field.id','=','field_res.field_id')
        ->where('field.form_id', 3)
        ->where('field_res.item_id', $id)
        ->orderBy('field.field_order', 'ASC')
        ->get();
        $respuestas = [];
        $fotografias = "";
        foreach($data as $respuesta) {
            array_push($respuestas, $respuesta->Respuesta);
            if($respuesta->Columna == 'FOTOGRAFÍAS'){
                $fotografias = $respuesta->Respuesta;
            }
        }
        try{
            //Obtenemos los Nombres de las imagenes
            $fotografias = explode(';',$fotografias);
            $fotografias = str_replace('i:','',$fotografias);
            $fotografias = str_replace('}','',$fotografias);
            $fotografias = str_replace('{','',$fotografias);
            $getNamePhotos = DB::table('wprq_postmeta')
            ->select('meta_value as Fotografia')
            ->where('meta_key','_wp_attached_file')
            ->whereIn('post_id',$fotografias)
            ->get();

            //Acedemos remotamente al servidor de imagenes
            $ftp_conn=ftp_connect(getenv('FTP_HOST'),getenv('FTP_PORT'));
            $login = ftp_login($ftp_conn,getenv('FTP_USER'),getenv('FTP_PASSWORD'));
            

            //Descargamos las fotos
            $fotografias = [];
            $fotografiasDelete = [];
            foreach($getNamePhotos as $foto) {
                $fotografia = explode('/',$foto->Fotografia)[2];
                $local_file = Storage::disk('temps')->path($fotografia);
                $server_file = $fotografia;

                ftp_get($ftp_conn, $local_file, $server_file, FTP_BINARY);

                array_push($fotografias,$local_file);
                array_push($fotografiasDelete,$fotografia);
            }
            ftp_close($ftp_conn);

        }catch(\Exception $e) {
            return "No se ha podido acceder al servidor de Imagenes ".$e->getMessage();
        }
        try {
            $pdf = new InspeccionExport();
            $pdfStream = $pdf->generatePDF($respuestas, $fotografias);
        }catch(\Exception $e) {
            return "Error al generar el PDF. Error: [".$e->getMessage()."]";
        }
        try{
            //Eliminamos imagenes temp
            for($i=0;$i<count($fotografiasDelete); $i++) {
                Storage::disk('temps')->delete($fotografiasDelete[$i]);
            }
        }catch(\Exception $e) {
            return "Error al Eliminar Imagenes Temp. Error: [".$e->getMessage()."]";
        }
        return $pdfStream; 
    }
}