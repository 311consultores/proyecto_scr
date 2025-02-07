<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\ReporteExport;

class ReporteController extends Controller
{
    public function guardarReporteEvidencia(Request $res) {
        $data = json_decode(json_encode($res->all()));
        foreach($data->objEvidencias as $evidencia) {
            if($evidencia->bImagenes) {
                foreach($evidencia->objImagenes as $imagen) {
                    if($imagen->file != "") {
                        try {
                            $extension = explode('.',$imagen->namefile);
                            $extension = $extension[count($extension)-1];
                            $image = explode(',',$imagen->file);
                            $img = base64_decode($image[1]);
                            $filename= uniqid('IMG').".".$extension;
                            Storage::disk('reportes')->put($filename, $img);
                            $imagen->file = $filename;
                        } catch(Exception $e) {
                            $imagen->file = "not_loaded.jpg";
                        }                    
                    }                
                }
            }            
        }
        try {
            $pdf = new ReporteExport();
            $pdfb64 = $pdf->generateReporteEvidencia($data);
        }catch(\Exception $e) {
            return [ "ok" => false, "message" => "Error al generar el PDF. Error: [".$e->getMessage()."]"];
        }
        $id= DB::table('cat_reporte')->insertGetId([
            "nombre_reporte" => "reporte_evidencia",
            "datos_reporte" => json_encode($data),
            "activo" => 1
        ]);
        return [ "ok" => true, "id"=> $id, "data" => $pdfb64];
    }

    public function getReportesEvidencia() {
        $reportes = DB::table("cat_reporte")
        ->select("id_reporte", "datos_reporte")
        ->get();
    
        $respuesta = [];
        if (count($reportes) > 0) {
            foreach ($reportes as $report) {
                $json = json_decode($report->datos_reporte);
                $arreglo = [
                    "id" => $report->id_reporte,
                    "fecha_creacion" => $this->convertDateToText($json->sFecha),
                    "titulo" => $json->sTitulo,
                    "reporte" => strtoupper($json->sReporte),
                    "cliente" => $json->sCliente,
                    "actividades" => []
                ];
                foreach ($json->objEvidencias as $actividad ) {
                    array_push($arreglo["actividades"], [
                        "titulo" => $actividad->sTitulo,
                        "desc" => $actividad->sDescripcion
                    ]);
                }
                array_push($respuesta, $arreglo);
            }        
            return ["ok" => true, "data" => $respuesta];
        }
        return ["ok" => false, "data" => "Sin reportes"];
    }

    public function getPDFReportEvidencia($id_reporte) {
        $reporte = DB::table("cat_reporte")
        ->select("datos_reporte")
        ->where("id_reporte",$id_reporte)
        ->first();

        if($reporte) {
            try {
                $pdf = new ReporteExport();
                $pdf = $pdf->generateReporteEvidencia(json_decode($reporte->datos_reporte),1);
                return $pdf;
            }catch(\Exception $e) {
                return "Error al generar el PDF. Error: [".$e->getMessage()."]";
            }
        }
        return "No se encontro el reporte";
    }
}