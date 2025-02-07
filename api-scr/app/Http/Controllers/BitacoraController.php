<?php

namespace App\Http\Controllers;

use App\Exports\BitacoraExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;

class BitacoraController extends Controller
{
    public function exportPDF($id)
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
            $pdf = new BitacoraExport();
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

    public function guardarBitacora(Request $request) {
        #region [Validaciones]
            $validator = Validator::make($request->all(),[
                "folio_reporte" => 'required|max:15',
                "sitio_proyecto" => 'required|max:300',
                "cliente" => 'max:300',
                "fecha" => 'required|date',
                "equipo" => 'max:300',
            ],[
                'folio_reporte.required' => "El campo 'Reporte' es obligatorio",
                'folio_reporte.max' => "El campo 'Reporte' acepta maximo 15 caracteres",
                'sitio_proyecto.required' => "El campo 'Sitio y/o Proyecto' es obligatorio",
                'sitio_proyecto.max' => "El campo 'Sitio y/o Proyecto' acepta maximo 300 caracteres",
                'cliente.max' => "El campo 'Cliente' acepta maximo 300 caracteres",
                'fecha.required' => "El campo 'Fecha' es obligatorio",
                'fecha.date' => "El campo 'Fecha' no es una fecha valida",
                'equipo.max' => "El campo 'Equipo' acepta maximo 300 caracteres"
            ]);

            if ($validator->fails()) {
                $errores = [];
                foreach ($validator->errors()->all() as $key => $mensaje) {
                    array_push($errores, $mensaje);
                }
                return response()->json([
                    "ok" => false,
                    "message" => $errores
                ], 422);
            }
        #endregion
        try {
            DB::beginTransaction();

            // Obtener los datos del request como un array
            $bitacora = $request->only([
                'folio_reporte', 'sitio_proyecto', 'cliente', 'fecha', 'equipo'
            ]);
            $bitacora['dt_creacion'] = date('Y-m-d');
            $bitacora['activo'] = 1;

            //Insertamos en la BD
            $id_bitacora = DB::table('bitacora')->insertGetId($bitacora);

            //Cerramos las transacciónes a BD
            DB::commit();

            return response()->json([
                "ok" => true,
                "data" => "Has iniciado el registro de tu actividad correctamente",
                "id" => $id_bitacora// Incluir el ID en la respuesta,

            ], 201); // Código HTTP 201 para creación exitosa

        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [guardarBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ], 500); // Código HTTP 500 para errores del servidor
        }
    }

    public function guardarDetBitacora(Request $request) {
        #region [Validaciones]
            $validator = Validator::make($request->all(),[
                "bitacora_id" => 'required',
                "orden_trabajo" => 'required|max:15',
                "equipo" => 'required|max:300',
                "observacion" => 'max:5000',
                "fotografias" => 'array'
            ],[
                'bitacora_id.required' => "El id_bitacora es obligatorio",
                'orden_trabajo.required' => "El campo 'Reporte' es obligatorio",
                'orden_trabajo.max' => "El campo 'Reporte' acepta maximo 15 caracteres",
                'equipo.required' => "El campo 'Sitio y/o Proyecto' es obligatorio",
                'equipo.max' => "El campo 'Sitio y/o Proyecto' acepta maximo 300 caracteres",
                'observacion.max' => "El campo 'Observacion' acepta maximo 300 caracteres",
                'fotografias.array' => "Error al recibir el parametro 'Fotografia' debe se un arreglo",
            ]);

            if ($validator->fails()) {
                $errores = [];
                foreach ($validator->errors()->all() as $key => $mensaje) {
                    array_push($errores, $mensaje);
                }
                return response()->json([
                    "ok" => false,
                    "message" => $errores[0]
                ], 422);
            }
        #endregion
        try {
            DB::beginTransaction();

            // Obtener los datos del request como un array
            $actividad = $request->only([
                'bitacora_id','orden_trabajo', 'equipo', 'observacion'
            ]);
            $actividad['hora_creacion'] = date('H:i:s');
            $actividad['activo'] = 1;

            //Insertamos en la BD
            $id_actividad = DB::table('det_bitacora')->insertGetId($actividad);

            //Si encontramos imagenes, las guardamos en Storage & BD
            $fotografias = $request->input('fotografias', []);
            $iCont=0;
            foreach($fotografias as $fotografia) {
                if (preg_match('/^data:image\/(\w+);base64,/', $fotografia, $type)) {
                    $type = strtolower($type[1]); // png, jpeg, etc.
                    $image = base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '', $fotografia));
                    if ($image === false) {
                        continue; // Si la decodificación falla, continuar con la siguiente imagen
                    }
                    $nombreArchivo = "Bitacora-" . uniqid() . '.' . $type;
                    Storage::disk('reportes')->put($nombreArchivo, $image);
                    //Guardabamos en BD
                    DB::table("fotos_bitacora")->insert([
                        'actividad_id' => $id_actividad,
                        'nombre_doc' => $actividad['orden_trabajo'].$iCont,
                        'path' => $nombreArchivo,
                        "dt_creacion" => date('Y-m-d'),
                        'activo' => 1
                    ]);
                    
                }             
            }
            //Cerramos las transacciónes a BD
            DB::commit();

            return response()->json([
                "ok" => true,
                "data" => "Has registrado tu actividad correctamente",
                "id" => $id_actividad// Incluir el ID en la respuesta,

            ], 201); // Código HTTP 201 para creación exitosa

        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [guardarBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ], 500); // Código HTTP 500 para errores del servidor
        }
    }
}