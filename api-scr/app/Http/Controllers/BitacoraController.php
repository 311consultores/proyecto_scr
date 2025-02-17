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
    public function index() {
        try {
            $catalogos = ["sitios" => null, "clientes" => null];
            $catalogos["sitios"] = DB::table("cat_sitios")->select('id_sitio','sitio','abreviatura')->get();
            $catalogos["clientes"] = DB::table("cat_clientes")->select('id_cliente','cliente','abreviatura')->get();
            return ["ok" => true, "data"=> $catalogos];
        }catch(\Exception $e) {
            Log::error("Error en [BitacoraController::index]: ".$e->getMessage());
            return ["ok" => false, "message"=> "Error al obtener los catálogos"];
        }
    }

    public function recuperarFolio(Request $request) {
        try {
            $cliente = DB::table("cat_clientes")->where("id_cliente",$request->cliente_id)->first();
            if($cliente) {
                $folio= $cliente->abreviatura."-".date('m');
                $last_folio = DB::table("bitacora")
                ->where("folio_reporte","like","%".$folio."%")
                ->orderBy("id_bitacora","desc")
                ->first();
                if($last_folio) {
                    $cont = explode('-', $last_folio->folio_reporte)[2];
                    $cont = intval($cont);
                    $cont++;
                    $cont = $cont > 9 ? $cont : "0".$cont;
                    return [ "ok" => true, "data" => ["folio" => $folio."-".$cont ] ];
                }
                return [ "ok" => true, "data" => ["folio" => $folio."-"."01" ] ];
            }
            return ["ok" => false, "message"=> "No pudimos encontrar al cliente seleccionado"];
        }catch(\Exception $e) {
            Log::error("Error en [BitacoraController::recuperarFolio]: ".$e->getMessage());
            return ["ok" => false, "message"=> "Error al obtener el folio"];
        }
    }

    public function guardarBitacora(Request $request) {
        #region [Validaciones]
        $validator = Validator::make($request->all(),[
            "folio_reporte" => 'required|max:15',
            "cliente_id" => 'required',
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
        if(boolval($request->bSitio)) {
            if(intval($request->sitio_id) == 0) return ["ok" => false, "message" => "No se ha seleccionado un sitio"];
        }
        if ($validator->fails()) {
            $errores = [];
            foreach ($validator->errors()->all() as $key => $mensaje) {
                array_push($errores, $mensaje);
            }
            return response()->json([
                "ok" => false,
                "message" => $errores[0]
            ]);
        }
        #endregion        
        try {
            if($request->id_bitacora > 0) {
                return $this->editarBitacora($request);
            }
            DB::beginTransaction();

            // Obtener los datos del request como un array
            $bitacora = $request->only([
                'folio_reporte', 'bSitio', 'proyecto', 'sitio_id', 'cliente_id', 'fecha', 'equipo'
            ]);
            $bitacora['bFinalizado'] = 0;
            $bitacora['dt_creacion'] = date('Y-m-d');
            $bitacora['activo'] = 1;

            //Insertamos en la BD
            $id_bitacora = DB::table('bitacora')->insertGetId($bitacora);

            //Cerramos las transacciónes a BD
            DB::commit();

            return response()->json([
                "ok" => true,
                "bUpdate" => false,
                "data" => "Has iniciado el registro de tu actividad correctamente",
                "id" => $id_bitacora// Incluir el ID en la respuesta,

            ], 201); // Código HTTP 201 para creación exitosa

        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [guardarBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ]); // Código HTTP 500 para errores del servidor
        }
    }

    public function guardarDetBitacora(Request $request) {
        #region [Validaciones]
            $validator = Validator::make($request->all(),[
                "bitacora_id" => 'required',
                "orden_trabajo" => 'required|max:300',
                "equipo" => 'required|max:300',
                "observacion" => 'max:5000',
                "fotografias" => 'array'
            ],[
                'bitacora_id.required' => "El id_bitacora es obligatorio",
                'orden_trabajo.required' => "El campo 'Orden de Trabajo' es obligatorio",
                'orden_trabajo.max' => "El campo 'Orden de Trabajo' acepta maximo 300 caracteres",
                'equipo.required' => "El campo 'Equipo' es obligatorio",
                'equipo.max' => "El campo 'Equipo' acepta maximo 300 caracteres",
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
                ]);
            }
        #endregion
        try {
            if($request->id_actividad > 0) {
                return $this->editarDetBitacora($request);
            }
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
                "bUpdate" => false,
                "data" => "Has registrado tu actividad correctamente",
                "id" => $id_actividad// Incluir el ID en la respuesta,

            ], 201); // Código HTTP 201 para creación exitosa

        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [guardarBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ]);
        }
    }

    public function finalizarBitacora(Request $request) {
        try {
            DB::table("bitacora")->where("id_bitacora",$request->id_bitacora)->update([
                "bFinalizado" => 1
            ]);
            return ["ok" => true, "data" => "Bitacora Finalizada con exito"];
        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [finalizarBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ]); // Código HTTP 500 para errores del servidor
        }
    }
    //Genericos
    public function editarBitacora(Request $request) {
        try {
            $bitacora = $request->only([
                'folio_reporte', 'bSitio', 'proyecto', 'sitio_id', 'cliente_id', 'fecha', 'equipo'
            ]);
            DB::beginTransaction();
            //Actualizamos
            DB::table('bitacora')->where("id_bitacora",$request->id_bitacora)
            ->update($bitacora);

            //Cerramos las transacciónes a BD
            DB::commit();

            return response()->json([
                "ok" => true,
                "bUpdate" => true,
                "data" => "Has actualizado la información de tu registro"

            ], 201); // Código HTTP 201 para creación exitosa
        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [editarBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ]); // Código HTTP 500 para errores del servidor
        }
    }

    public function editarDetBitacora(Request $request) {
        try {
            DB::beginTransaction();
            // Obtener los datos del request como un array
            $actividad = $request->only([
                'orden_trabajo', 'equipo', 'observacion'
            ]);
            //Actualizamos la informacion de la actividad
            DB::table("det_bitacora")->where("id_actividad",$request->id_actividad)
            ->update($actividad);
            //Manejo de Imagenes
            //Paso 1. Eliminamos las imagenes almacenadas y en BD
            $foto_respaldo = DB::table("fotos_bitacora")->where("actividad_id",$request->id_actividad)->get();
            foreach($foto_respaldo as $foto) {
                //Eliminamos imagenes almacenadas
                if (Storage::disk('reportes')->exists($foto->path)) {
                    Storage::disk('reportes')->delete($foto->path);
                }
                // Eliminar el registro de la base de datos
                DB::table("fotos_bitacora")
                ->where('path', $foto->path)
                ->delete();
            }
            //Paso 2. Si encontramos imagenes, las guardamos en Storage & BD
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
                        'actividad_id' => $request->id_actividad,
                        'nombre_doc' => $request->orden_trabajo.$iCont,
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
                "bUpdate" => true,
                "data" => "Has actualizado tu actividad correctamente"
            ], 201); // Código HTTP 201 para creación exitosa
        } catch(\PdoException | \Exception | \Error $e) {
            DB::rollBack();
            Log::error("Método [editarDetBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"
            ]);
        }
    }
    //Admin Methods
    public function adminIndex() {
        try {
            $bitacoras = DB::table('bitacora as b')
            ->select(
                'b.id_bitacora',
                'b.folio_reporte',
                'b.bSitio',
                'b.proyecto',
                'b.fecha',
                's.sitio',
                'b.bFinalizado',
                DB::raw("COALESCE(
                    JSON_ARRAYAGG(
                        IF(d.id_actividad IS NOT NULL, JSON_OBJECT(
                            'orden_trabajo', d.orden_trabajo,
                            'observacion', d.observacion,
                            'hora_creacion', TIME_FORMAT(d.hora_creacion, '%H:%i')
                        ), NULL)
                    ), '[]'
                ) as detalles")
            )
            ->leftJoin('det_bitacora as d', 'b.id_bitacora', '=', 'd.bitacora_id')
            ->join('cat_clientes as c', 'b.cliente_id', '=', 'c.id_cliente')
            ->leftJoin('cat_sitios as s', 'b.sitio_id', '=', 's.id_sitio')
            ->groupBy('b.id_bitacora', 'b.folio_reporte', 'b.bSitio', 'b.proyecto', 'b.fecha', 'b.equipo', 'c.cliente', 's.sitio', 'b.dt_creacion','b.bFinalizado')
            ->get()
            ->map(function ($bitacora) {
                $detalles = json_decode($bitacora->detalles, true);
                $bitacora->fecha = $this->convertDateToText($bitacora->fecha);
                $bitacora->detalles = array_filter($detalles, function ($item) {
                    return $item !== null;
                });        
                return $bitacora;
            });
        
            return response()->json(['ok' => true, 'data' => $bitacoras]);
          
        }catch(\Exception $e) {
            Log::error("Error en [BitacoraController::adminIndex]: ".$e->getMessage());
            return ["ok" => false, "message"=> "Error al obtener las bitacoras"];
        }
    }

    public function obtenerBitacoraPorId($id, $tipo=0) {
        try {
            $bitacora = DB::table('bitacora as b')
            ->select(
                'b.id_bitacora',
                'b.folio_reporte',
                'b.bSitio',
                'b.proyecto',
                'b.fecha',
                'b.sitio_id',
                's.sitio',
                'b.cliente_id',
                'c.cliente',
                'b.equipo',
                DB::raw("COALESCE(
                    JSON_ARRAYAGG(
                        IF(d.id_actividad IS NOT NULL, JSON_OBJECT(
                            'id_actividad', d.id_actividad,
                            'equipo', d.equipo,
                            'orden_trabajo', d.orden_trabajo,
                            'observacion', d.observacion
                        ), NULL)
                    ), '[]'
                ) as actividades")
            )
            ->leftJoin('det_bitacora as d', 'b.id_bitacora', '=', 'd.bitacora_id')
            ->leftJoin('cat_sitios as s','s.id_sitio', '=', 'b.sitio_id')
            ->join('cat_clientes as c','c.id_cliente', '=', 'b.cliente_id')
            ->groupBy('b.id_bitacora', 'b.folio_reporte', 'b.bSitio', 'b.proyecto', 'b.fecha', 'b.equipo', 'b.cliente_id', 'b.sitio_id','s.sitio','c.cliente')
            ->where('id_bitacora', $id)
            ->get()
            ->map(function ($bitacora) use ($tipo) {
                $actividades = json_decode($bitacora->actividades, true);
                $bitacora->actividades = $actividades[0] !== null ? $actividades : [];
                $bitacora->titulo = $bitacora->folio_reporte . " - ";
                $bitacora->titulo .= $bitacora->bSitio ? $bitacora->sitio : $bitacora->proyecto;
                //Buscamos las fotografias si cuenta
                foreach($bitacora->actividades as &$actividad) {
                    $actividad["fotografias"] = DB::table('fotos_bitacora')
                    ->select("id_foto","path")
                    ->where('actividad_id',$actividad["id_actividad"])
                    ->get()
                    ->map(function ($foto) use ($tipo) { // <-- Pasar $tipo con use()
                        if ($tipo == 1) {
                            $base64 = $this->getPhotoBase64($foto->path);
                            if ($base64 != null) {
                                $foto = $base64;
                            }
                        }
                        return $foto;
                    });
                    $actividad["titulo"]= $actividad["orden_trabajo"];
                }
                unset($actividad);
                return $bitacora;
            });
            return [ "ok" => true, "data" => $bitacora ];
        } catch(\Exception $e) {
            Log::error("Método [obtenerBitacoraPorId] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"]
            ); // Código HTTP 500 para errores del servidor
        }
    }

    //Reporte
    public function generarReporteBitacora(Request $request) {
        try {
            $bitacora = json_decode(json_encode($this->obtenerBitacoraPorId($request->id_bitacora)));
            if($bitacora->ok) {
                $pdf = BitacoraExport::generar($bitacora->data[0]);
                return [ 
                    "ok" => true,
                    "data" => $pdf
                ];    
            }
            //Error al obtener la información de la bitacora
            return [ 
                "ok" => false,
                "message" => "Ha ocurrido un error al generar el PDF"
            ];            
        } catch(\Exception $e) {
            Log::error("Método [generarReporteBitacora] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"]
            ); // Código HTTP 500 para errores del servidor
        }
        
    }

    #region [Métodos Privados]
    public function getPhotoBase64($nombreArchivo)
    {
        // Verificar si el archivo existe
        if (!Storage::disk("reportes")->exists($nombreArchivo)) {
            return null;
        }

        // Leer el contenido del archivo
        $fileContent = Storage::disk("reportes")->get($nombreArchivo);

        // Convertir el contenido a base64
        $base64Content = base64_encode($fileContent);

        // Obtener el tipo MIME del archivo
        $mimeType = Storage::disk("reportes")->mimeType($nombreArchivo);

        // Reconstruir el base64 con el formato correcto
        $base64Image = 'data:' . $mimeType . ';base64,' . $base64Content;

        // Devolver el base64 reconstruido
        return $base64Image;
    }
    #endregion
}