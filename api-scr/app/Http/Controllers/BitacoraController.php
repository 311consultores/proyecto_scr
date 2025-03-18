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
            "sitio_id" => 'required|integer|min:1',
            "fecha" => 'required|date',
            "proyecto" => 'required|max:300',
            "responsable" => 'required|max:350'
        ],[
            'folio_reporte.required' => "El campo 'Reporte' es obligatorio",
            'folio_reporte.max' => "El campo 'Reporte' acepta maximo 15 caracteres",
            'proyecto.required' => "El campo 'Proyecto' es obligatorio",
            'proyecto.max' => "El campo 'Proyecto' acepta maximo 300 caracteres",
            'cliente.required' => "El campo 'Cliente' es obligatorio",
            'fecha.required' => "El campo 'Fecha' es obligatorio",
            'fecha.date' => "El campo 'Fecha' no es una fecha valida",
            'sitio_id.required' => "El campo 'Sitio' es obligatorio",
            'responsable.required' => "El campo 'Responsable' es obligatorio",
            'sitio_id.min' => "No has seleccionado un Sitio"
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
            if($request->id_bitacora > 0) {
                return $this->editarBitacora($request);
            }
            DB::beginTransaction();

            // Obtener los datos del request como un array
            $bitacora = $request->only([
                'folio_reporte', 'proyecto', 'sitio_id', 'cliente_id', 'fecha', 'tipo_bitacora', 'responsable'
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
                "observacion" => 'max:10000',
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
            $actividad["fecha_creacion"] = date('Y-m-d');
            $actividad['activo'] = 1;

            //Insertamos en la BD
            $id_actividad = DB::table('det_bitacora')->insertGetId($actividad);

            //Si encontramos imagenes, las guardamos en Storage & BD
            $fotografias = $request->input('fotografias', []);
            $iCont=0;
            foreach($fotografias as $fotografia) {
                //Guardabamos en BD
                DB::table("fotos_bitacora")->insert([
                    'actividad_id' => $id_actividad,
                    'nombre_doc' => $actividad['orden_trabajo'].$iCont,
                    'path' => $fotografia["id_temp"],
                    "dt_creacion" => date('Y-m-d'),
                    'activo' => 1
                ]);    
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

    public function subirFotoTemp(Request $request) {
        try {
            if (empty($request->fotografia)) {
                throw new \Exception("No se recibió una fotografía válida.");
            }
    
            if (!preg_match('/^data:image\/(\w+);base64,/', $request->fotografia, $matches)) {
                throw new \Exception("Formato de imagen no válido.");
            }
    
            $type = strtolower($matches[1]); // Extraemos el tipo de imagen
    
            // Validamos que sea un formato permitido
            $formatosPermitidos = ['jpeg', 'jpg', 'png'];
            if (!in_array($type, $formatosPermitidos)) {
                throw new \Exception("Formato de imagen no permitido.");
            }
    
            // Decodificamos la imagen
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->fotografia));
    
            if ($image === false) {
                throw new \Exception("Error al decodificar la imagen.");
            }
    
            // Generamos un nombre único para la imagen
            $nombreArchivo = "Bitacora-" . uniqid() . '.' . $type;
    
            // Guardamos la imagen en el disco configurado
            Storage::disk('reportes')->put($nombreArchivo, $image);
    
            // Retornamos la URL de la foto y su identificador
            return [
                "ok" => true,
                "data" => [
                    "id_temp" => $nombreArchivo,
                    "url_path" => Storage::disk('reportes')->url($nombreArchivo)
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en [BitacoraController::subirFotoTemp]: " . $e->getMessage());
            return ["ok" => false, "message" => "Error al subir la fotografía, intente de nuevo."];
        }
    }

    public function eliminarFotoTemp(Request $request) {
        try {
            if (empty($request->id_temp)) {
                throw new \Exception("No se recibió la fotografia a eliminar");
            }
            //Eliminamos imagenes almacenadas
            if (Storage::disk('reportes')->exists($request->id_temp)) {
                Storage::disk('reportes')->delete($request->id_temp);
            }
            return ["ok" => true, "data" => "Fotografia Eliminada"];
        } catch (\Exception $e) {
            Log::error("Error en [BitacoraController::eliminarFotoTemp]: " . $e->getMessage());
            return ["ok" => false, "message" => "Error al eliminar la fotografía, intente de nuevo."];
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
                'folio_reporte', 'proyecto', 'sitio_id', 'cliente_id', 'fecha', 'responsable', 'tipo_bitacora'
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
                // Eliminar el registro de la base de datos
                DB::table("fotos_bitacora")
                ->where('path', $foto->path)
                ->delete();
            }
            //Paso 2. Si encontramos imagenes, las guardamos en Storage & BD
            $fotografias = $request->input('fotografias', []);
            $iCont=0;
            foreach($fotografias as $fotografia) {
                //Guardabamos en BD
                DB::table("fotos_bitacora")->insert([
                    'actividad_id' => $request->id_actividad,
                    'nombre_doc' => $request->orden_trabajo.$iCont,
                    'path' => $fotografia["id_temp"],
                    "dt_creacion" => date('Y-m-d'),
                    'activo' => 1
                ]);             
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
    public function adminIndex($rows) {
        try {
            $bitacoras = DB::table('bitacora as b')
            ->select(
                'b.id_bitacora',
                'b.folio_reporte',
                'b.proyecto',
                'b.fecha',
                'b.responsable',
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
            ->groupBy('b.id_bitacora', 'b.folio_reporte', 'b.proyecto', 'b.responsable', 'b.fecha', 'c.cliente', 's.sitio', 'b.dt_creacion','b.bFinalizado')
            ->orderBy("b.fecha","desc")
            ->take($rows)
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
          
        } catch(\Exception $e) {
            Log::error("Error en [BitacoraController::adminIndex]: ".$e->getMessage());
            return ["ok" => false, "message"=> "Error al obtener las bitacoras"];
        }
    }

    public function obtenerBitacoraPorId($id, $tipo=0) {
        try {
            $columns = $tipo == 0 ? 
            ['ct.titulo','ct.path','folio_reporte','b.proyecto','b.responsable','b.fecha','s.sitio','c.cliente'] :
            ['b.id_bitacora','b.tipo_bitacora','b.folio_reporte','b.proyecto','b.responsable','b.fecha','b.sitio_id','b.cliente_id','s.sitio','c.cliente','b.bFinalizado'];
            $bitacora = DB::table('bitacora as b')
            ->select($columns)
            ->leftJoin('cat_sitios as s', 's.id_sitio', '=', 'b.sitio_id')
            ->join('cat_clientes as c', 'c.id_cliente', '=', 'b.cliente_id')
            ->join('cat_tipos_bitacora as ct', 'ct.id_tipo_bitacora', '=', 'b.tipo_bitacora')
            ->where('b.id_bitacora', $id) // Suponiendo que $id es el ID de la bitácora a consultar
            ->first(); // Usamos first() en lugar de get() para obtener un solo resultado
            if($tipo == 1) $bitacora->titulo = $bitacora->folio_reporte." - ".$bitacora->proyecto;

            $actividades = DB::table('det_bitacora as d')
            ->select(
                'd.id_actividad',
                'd.orden_trabajo',
                'd.observacion',
                'd.equipo'
            )
            ->where('d.bitacora_id', $id)
            ->get()
            ->map(function ($actividad) use ($tipo) {
                if($tipo == 1) $actividad->titulo = $actividad->orden_trabajo;
                $fotografias = DB::table('fotos_bitacora as f')
                    ->select('f.id_foto as id_temp', 'f.path as url_path')
                    ->where('f.actividad_id', $actividad->id_actividad)
                    ->get()
                    ->map(function ($foto) use ($tipo) {
                        if ($tipo == 1) {
                            $foto->id_temp = $foto->url_path;
                            $foto->url_path = Storage::disk("reportes")->url($foto->url_path);
                        }
                        return $foto;
                    });

                $actividad->fotografias = $fotografias;
                return $actividad;
            });

            return [ "ok" => true, "data" => [
                'bitacora' => $bitacora,
                'actividades' => $actividades
            ]];
        } catch(\Exception $e) {
            Log::error("Método [obtenerBitacoraPorId] - Error: " . $e->getMessage());
            return response()->json([
                "ok" => false,
                "message" => "Plataforma temporalmente fuera de servicio"]
            ); // Código HTTP 500 para errores del servidor
        }
    }

    public function poblarFiltros() {
        try {
            $sitios = DB::table("cat_sitios")->select('id_sitio','sitio as nombre')->get();
            $clientes = DB::table("cat_clientes")->select('id_cliente','cliente as nombre')->get();
            $registros = DB::table('bitacora as b')
            ->select('b.id_bitacora','b.folio_reporte as nombre')
            ->orderBy("b.fecha","desc")
            ->limit(100)
            ->get();

            return ["ok" => true, "data" => [
                "sitios" => $sitios,
                "clientes" => $clientes,
                "registros" => $registros,
            ]];
        } catch(\Exception $e) {
            Log::error("Error en [BitacoraController::poblarFiltros]: ".$e->getMessage());
            return ["ok" => false, "message"=> "Error al poblar el filtro"];
        }
    }

    public function obtenerResultadoBusqueda(Request $request) {
        try {
            $bitacoras = DB::table('bitacora as b')
            ->select(
                'b.id_bitacora',
                'b.folio_reporte',
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
            ->groupBy('b.id_bitacora', 'b.folio_reporte', 'b.proyecto', 'b.fecha', 'c.cliente', 's.sitio', 'b.dt_creacion','b.bFinalizado')
            ->orderBy("b.fecha","desc")
            ->when(isset($request->id_sitio) && $request->id_sitio > 0, function($query) use ($request) {
                return $query->where("s.id_sitio",$request->id_sitio);
            })
            ->when(isset($request->id_cliente) && $request->id_cliente > 0, function($query) use ($request) {
                return $query->where("c.id_cliente",$request->id_cliente);
            })
            ->when(isset($request->id_bitacora) && $request->id_bitacora > 0, function($query) use ($request) {
                return $query->where("b.id_bitacora",$request->id_bitacora);
            })
            ->take($request->rows)
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
        } catch(\Exception $e) {
            Log::error("Error en [BitacoraController::obtenerResultadoBusqueda]: ".$e->getMessage());
            return ["ok" => false, "message"=> "Error al obtener Resultado"];
        }
    }

    //Reporte
    public function generarReporteBitacora(Request $request) {
        try {
            $bitacora = json_decode(json_encode($this->obtenerBitacoraPorId($request->id_bitacora)));
            if($bitacora->ok) {
                $pdf = BitacoraExport::generar($bitacora->data);
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
    public function getPhotoBase64($nombreArchivo) {
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