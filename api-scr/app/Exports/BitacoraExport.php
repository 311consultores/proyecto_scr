<?php

namespace App\Exports;

use Fpdf\Fpdf;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class BitacoraExport {

    static function generar($data, $tipo_export = 0)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','U',11);
        $pdf->SetDrawColor(216, 216, 216);        
        $pdf->SetAutoPageBreak(true, 10);
        #region [Cabecera]
        $pdf->setXY(0,0);
        $pdf->Cell(50,20,"",0,0,"L");
        $pdf->setFillColor(0,0,0); 
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(160,5,"www.grupo-scr.com",0,0,"C",1);
        $pdf->Image(Storage::disk('img')->path($data->bitacora->path),-5,-1,60,0);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->setXY(50,6);
        $pdf->setFillColor(255,255,255); 
        $pdf->SetFont('Arial','B',11);
        $pdf->SetTextColor(255, 51, 0);
        $pdf->Cell(160,6,$data->bitacora->titulo,0,0,"C",1);
        $pdf->setXY(50,12);
        $pdf->SetTextColor(44, 36, 32);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(160,6,utf8_decode("INFORME EJECUTIVO"." [".$data->bitacora->folio_reporte."]"),0,0,"C",1);  
        $pdf->setXY(0,20);
        $pdf->setFillColor(237, 125, 49);
        $pdf->Cell(210,1,"",0,0,"C",1);  
        $pdf->setXY(5,22.5); 
        $pdf->setFillColor(255, 255, 255); 
        $pdf->SetTextColor(73, 77, 85);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(200,5,utf8_decode("FECHA: ".Controller::convertDateToText($data->bitacora->fecha)),0,0,"R",1);
        $pdf->setXY(5,30);
        $pdf->SetTextColor(44, 36, 32);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,5,"Folio",0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(90,5,'Sitio',0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(70,5,"Responsable",0,0,"L",1);
        $pdf->setXY(5,36);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(30,5,utf8_decode($data->bitacora->folio_reporte),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(90,5,utf8_decode(self::truncarTexto($pdf,$data->bitacora->sitio,110)),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(70,5,utf8_decode($data->bitacora->responsable),0,0,"L",1);
        $pdf->setXY(5,43);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,5,"Cliente",0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(170,5,"Proyecto",0,0,"L",1);
        $pdf->setXY(5,49);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(30,5,utf8_decode($data->bitacora->cliente),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(170,5,utf8_decode(self::truncarTexto($pdf,$data->bitacora->proyecto,200)),0,0,"L",1);
        #endregion
        #region [Actividades]
        $pdf->setXY(5,$pdf->getY()+10);
        foreach($data->actividades as $actividad) {
            #region [Cabecera Actividad]
            $pdf->setXY(5,$pdf->getY());
            $pdf->SetFont('Arial','B',12);
            $pdf->SetTextColor(44, 36, 32);      
            $pdf->SetTextColor(255, 51, 0);
            $pdf->Cell(200,5,utf8_decode($actividad->orden_trabajo),0,0,"C",1);
            $pdf->setXY(5,$pdf->getY()+5);
            $pdf->setFillColor(226, 222, 215);
            $pdf->Cell(200,1,"",0,0,"C",1);
            #endregion
            #region [Cuerpo Actividad]
            $pdf->setFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial','B',12);
            $pdf->setXY(5,$pdf->getY()+5);
            $pdf->Cell(30,5,"Equipo",0,0,"L",1);
            $pdf->SetFont('Arial','',11);
            $pdf->setXY(5,$pdf->getY()+6);  
            $pdf->Cell(200,5,utf8_decode(self::truncarTexto($pdf,$actividad->equipo,200)),0,0,"L",1);
            $pdf->setXY(5,$pdf->getY()+7);
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(30,5,utf8_decode("Descripción"),0,0,"L",1);
            $pdf->setXY(5,$pdf->getY()+6);
            $pdf->SetFont('Arial','',11);
            $pdf->MultiCell(200, 5, utf8_decode($actividad->observacion), 0, "L", 1);
            if(count($actividad->fotografias) > 0) {
                if ($pdf->getY() + 70 > $pdf->GetPageHeight()) {
                    $pdf->AddPage();
                    $y = 10; // Reiniciar la posición en la nueva página
                }
                $pdf->setXY(5,$pdf->getY()+2);
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(200,5,utf8_decode("Evidencia fotográfica"),0,0,"C",1);
                $pdf->setXY(5,$pdf->getY()+5);
                $x = $pdf->getX();
                $y= $pdf->getY();
                foreach($actividad->fotografias as $index => $foto) {
                    $pdf->Image(Storage::disk('reportes')->path($foto->url_path),$x,$y,60,65);
                    $index++;  
                    $x += 70;
                    if($index == count($actividad->fotografias) && (count($actividad->fotografias) % 2 == 0 || in_array($index,[1,3,5,7,9,11,13,15,17]))) {
                        $y += 68;
                    }else {
                        if(in_array($index,[3,6,9,12,15,18,21,24,27,30])) {
                            $x = 5;
                            $y += 68;
                        }
                    }                    
                }
                $pdf->setY($y);
            }
            #endregion
        }
        #endregion
        if($tipo_export == 1) {
            return $pdf->Output("I","ReporteEvidencia.pdf");
        }
        return base64_encode($pdf->Output("S","ReporteEvidencia.pdf"));
    }

    static function preview($data)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','U',11);
        $pdf->SetDrawColor(216, 216, 216);        
        $pdf->SetAutoPageBreak(true, 10);
        #region [Cabecera]
        $pdf->setXY(0,0);
        $pdf->Cell(50,20,"",0,0,"L");
        $pdf->setFillColor(0,0,0); 
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(160,5,"www.grupo-scr.com",0,0,"C",1);
        $img = "";
        switch($data->encabezado["tipo_bitacora"]) {
            case 1: $img = "MAI.png"; break;
            case 2: $img = "SCE.png"; break;
            case 3: $img = "MPR.png"; break;
            default: $img = "logo.png"; break;
        }
        $pdf->Image(Storage::disk('img')->path($img),-5,-1,60,0);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->setXY(50,6);
        $pdf->setFillColor(255,255,255); 
        $pdf->SetFont('Arial','B',11);
        $pdf->SetTextColor(255, 51, 0);
        $pdf->Cell(160,6,$data->encabezado["titulo"],0,0,"C",1);
        $pdf->setXY(50,12);
        $pdf->SetTextColor(44, 36, 32);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(160,6,utf8_decode("INFORME EJECUTIVO"." [".$data->encabezado["folio_reporte"]."]"),0,0,"C",1);  
        $pdf->setXY(1,20);
        $pdf->setFillColor(237, 125, 49);
        $pdf->Cell(208,1,"",0,0,"C",1);  
        $pdf->setXY(5,22.5); 
        $pdf->setFillColor(255, 255, 255); 
        $pdf->SetTextColor(73, 77, 85);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(200,5,utf8_decode("FECHA: ".Controller::convertDateToText($data->encabezado["fecha"])),0,0,"R",1);
        $pdf->setXY(3,30);
        $pdf->SetTextColor(44, 36, 32);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(30,5,"Folio",0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(70,5,'Sitio',0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(90,5,"Responsable",0,0,"L",1);
        $pdf->setXY(3,36);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(30,5,utf8_decode($data->encabezado["folio_reporte"]),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(70,5,utf8_decode(self::truncarTexto($pdf,$data->encabezado["sitio_id"],110)),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(90,5,utf8_decode(self::truncarTexto($pdf,strtoupper($data->encabezado["responsable"]),90)),0,0,"L",1);
        $pdf->setXY(3,43);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(30,5,"Cliente",0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(170,5,"Proyecto",0,0,"L",1);
        $pdf->setXY(3,49);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(30,5,utf8_decode($data->encabezado["cliente_id"]),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(170,5,utf8_decode(self::truncarTexto($pdf,$data->encabezado["proyecto"],200)),0,0,"L",1);
        #endregion
        #region [Actividades]
        $pdf->setXY(1,$pdf->getY()+10);
        foreach($data->contenido as $contenido) {
            $titulo_cont = "";
            switch($contenido["tipo"]) {
                case 1: $titulo_cont = "CLIMA"; break;
                case 2: $titulo_cont = "MANO DE OBRA"; break;
                case 3: $titulo_cont = "CONTROL DE CONSUMOS"; break;
                case 4: $titulo_cont = strtoupper($contenido["titulo"]); break;
                default: $titulo_cont = "Sin titulo"; break;
            }
            #region [Cabecera Contenido]
            $pdf->setXY(1,$pdf->getY());
            $pdf->SetFont('Arial','B',11);    
            $pdf->SetTextColor(0, 0, 0);
            $pdf->setFillColor(191, 191, 191);
            $pdf->Cell(208,5,self::truncarTexto($pdf,utf8_decode($titulo_cont),205),1,0,"C",1);
            $pdf->setXY(1,$pdf->getY()+5);
            $pdf->Cell(208,1,"",0,0,"C",1);
            #endregion
            #region [Cuerpo Clima]
            if($contenido["tipo"] == 1) {
                $pdf->setXY(1,$pdf->getY()+2);
                $pdf->SetFont('Arial','B',9);
                $pdf->setFillColor(255, 255, 255);
                $pdf->Cell(20,5,"Hr",1,0,"C",1);
                $pdf->Cell(20,5,"T",1,0,"C",1);
                $pdf->Cell(20,5,"T.A",1,0,"C",1);
                $pdf->Cell(26,5,"V",1,0,"C",1);
                $pdf->Cell(20,5,"H",1,0,"C",1);
                $pdf->Cell(20,5,"P.R",1,0,"C",1);
                $pdf->Cell(26,5,"P",1,0,"C",1);
                $pdf->Cell(56,5,"Desc.",1,0,"L",1);
                $pdf->SetFont('Arial','',8);
                foreach($contenido["climas"] as $clima) {
                    $pdf->setXY(1,$pdf->getY()+5);
                    $pdf->Cell(20,5,utf8_decode($clima["hora"]),1,0,"C",1);
                    $pdf->Cell(20,5,utf8_decode($clima["temp"] . "°C"),1,0,"C",1);
                    $pdf->Cell(20,5,utf8_decode($clima["temp_aparente"] . "°C"),1,0,"C",1);
                    $pdf->Cell(26,5,utf8_decode($clima["viento"] > 0 ? $clima["viento"] . " Km/h" : "Calma"),1,0,"C",1);
                    $pdf->Cell(20,5,utf8_decode($clima["humedad"] ."%"),1,0,"C",1);
                    $pdf->Cell(20,5,utf8_decode($clima["punto_rocio"] . "°C"),1,0,"C",1);
                    $pdf->Cell(26,5,utf8_decode($clima["presion"] ."mb"),1,0,"C",1);
                    $pdf->Cell(56,5,utf8_decode($clima["desc"]),1,0,"L",1);
                }
                $pdf->SetFont('Arial','B',8);
                $pdf->setXY(1,$pdf->getY()+6);
                $pdf->Cell(208,5,utf8_decode("Hr: Hora - T: Temperatura - T.P: Temperatura aparente - V: Viento - H: Humedad - P.R: Punto rocio - P: Presión - Desc: Descripción"),0,0,"C",1);
                $pdf->setXY(1,$pdf->getY()+6);
            }
            #region [Cuerpo Mano de Obra]
            if($contenido["tipo"] == 2) {
                $pdf->setXY(1,$pdf->getY()+2);
                $pdf->SetFont('Arial','B',9);
                $pdf->setFillColor(255, 255, 255);
                $pdf->Cell(40,5,"Categoria",1,0,"C",1);
                $pdf->Cell(108,5,"Nombre",1,0,"L",1);
                $pdf->Cell(30,5,"Inicio",1,0,"C",1);
                $pdf->Cell(30,5,"Fin",1,0,"C",1);
                $pdf->SetFont('Arial','',9);
                foreach($contenido["horarios"] as $horario) {
                    $pdf->setXY(1,$pdf->getY()+5);
                    $pdf->Cell(40,5,utf8_decode(strtoupper($horario["categoria"])),1,0,"C",1);
                    $pdf->Cell(108,5,utf8_decode(strtoupper($horario["nombre"])),1,0,"L",1);
                    $pdf->Cell(30,5,utf8_decode($horario["hora_inicio"]),1,0,"C",1);
                    $pdf->Cell(30,5,utf8_decode($horario["hora_fin"]),1,0,"C",1);
                }
                $pdf->SetFont('Arial','B',8);
                $pdf->setXY(1,$pdf->getY()+6);
                $pdf->Cell(208,5,utf8_decode("Horas en formato 24H"),0,0,"C",1);
                $pdf->setXY(1,$pdf->getY()+6);
            }
            #endregion
            #region [Cuerpo Consumo]
            if($contenido["tipo"] == 3) {
                $pdf->setXY(1,$pdf->getY()+2);
                $pdf->SetFont('Arial','B',9);
                $pdf->setFillColor(255, 255, 255);
                $pdf->Cell(138,5,utf8_decode("Descripción"),1,0,"L",1);
                $pdf->Cell(70,5,"Cantidad",1,0,"C",1);
                $pdf->SetFont('Arial','',9);
                foreach($contenido["consumos"] as $consumo) {
                    $pdf->setXY(1,$pdf->getY()+5);
                    $pdf->Cell(138,5,utf8_decode(strtoupper($consumo["descripcion"])),1,0,"L",1);
                    $pdf->Cell(70,5,utf8_decode($consumo["cantidad"] . " " . strtoupper($consumo["unidad_medida"])),1,0,"C",1);
                }
                $pdf->setXY(1,$pdf->getY()+6);
            }
            #endregion
            #region [Cuerpo Actividad]
            if($contenido["tipo"] == 4) {
                $pdf->setXY(1,$pdf->getY()+3);
                $pdf->setFillColor(255, 255, 255);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(30,5,"Equipo:",0,0,"L",1);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(109,5,utf8_decode(self::truncarTexto($pdf,strtoupper($contenido["data"]["equipo"]),90)),0,0,"C",1);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(30,5,"Horas de uso:",0,0,"L",1);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(40,5,utf8_decode(self::truncarTexto($pdf,$contenido["data"]["horas_funcion"],40)),0,0,"C",1);
                $pdf->setXY(1,$pdf->getY()+6);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(30,5,"Modelo:",0,0,"L",1);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(40,5,utf8_decode(self::truncarTexto($pdf,strtoupper($contenido["data"]["modelo"]),40)),0,0,"C",1);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(30,5,"No. Economico:",0,0,"L",1);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(40,5,utf8_decode(self::truncarTexto($pdf,$contenido["data"]["no_economico"],40)),0,0,"C",1);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(30,5,"No. Serie:",0,0,"L",1);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(39,5,utf8_decode(self::truncarTexto($pdf,strtoupper($contenido["data"]["no_serie"]),40)),0,0,"C",1);
                $pdf->setXY(1,$pdf->getY()+5);
                $pdf->SetFont('Arial','B',11);
                $pdf->setXY(1,$pdf->getY()+5);
                $pdf->Cell(208,5,utf8_decode("Descripción de actividad"),0,0,"C",1);
                $pdf->setXY(1,$pdf->getY()+1);
                self::renderHTMLToFPDF($pdf, $contenido["data"]["descripcion"], 208);
                // $pdf->MultiCell(208, 5, utf8_decode($contenido["data"]["descripcion"]), 0, "L", 0);  // <--- Aqui iria la funcion y le pasaria la variable $contenido["data"]["descripcion"]
                if(count($contenido["data"]["fotos_ant"]) > 0) {
                    $pdf->setXY(1,$pdf->getY()+4);  
                    $pdf->SetFont('Arial','B',11);
                    $pdf->Cell(208,5,utf8_decode("Evidencia fotografica"),0,0,"C",1);
                    if(count($contenido["data"]["fotos_des"]) > 0) {
                        $pdf->setXY(1,$pdf->getY()+6);  
                        $pdf->SetFont('Arial','B',10);
                        $pdf->Cell(105,5,utf8_decode("Antes"),0,0,"C",1);
                        $pdf->Cell(105,5,utf8_decode("Despues"),0,0,"C",1);
                    }
                }
                $pdf->setXY(1,$pdf->getY()+10); 
                if($contenido["data"]["bFinalizo"]) {                
                    $pdf->Rect(160, $pdf->getY()+0.5, 4, 4);
                    $pdf->SetXY(160.1,$pdf->getY()+0.5);
                    $pdf->SetFont('Arial', '', 7);
                    $pdf->Cell(4, 4, 'X', 0, 0, 'C');
                    $pdf->setXY(165,$pdf->getY()-0.5); 
                } else {
                    $pdf->Rect(160, $pdf->getY(), 4, 4);
                    $pdf->setX(165);
                }
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(30,5, utf8_decode("Actividad finalizada"),0,0,"L",1);
                $pdf->setXY(1,$pdf->getY()+6);  
                if(!$contenido["data"]["bFinalizo"]) {  
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(30,5,utf8_decode("Motivo de no finalización:"),0,0,"L",1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->setXY(1,$pdf->getY()+6);  
                    $pdf->MultiCell(208, 5, utf8_decode($contenido["data"]["motivo"]), 0, "L", 1);
                }
                if($contenido["data"]["recomendacion"] != "") {
                    $pdf->setXY(1,$pdf->getY()+3);  
                    $pdf->setFillColor(0, 0, 0); 
                    $pdf->Cell(208,1,"",0,0,"C",1);  
                    $pdf->SetFont('Arial','B',10);  
                    $pdf->setFillColor(255,255,255); 
                    $pdf->setXY(1,$pdf->getY()+2);  
                    $pdf->Cell(30,5,utf8_decode("Recomendación:"),0,0,"L",1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->setXY(1,$pdf->getY()+6);  
                    $pdf->MultiCell(208, 5, utf8_decode($contenido["data"]["recomendacion"]), 0, "L", 1);
                }
                $pdf->setXY(1,$pdf->getY()+6);  
                // $pdf->setXY(0,$pdf->getY()+6);
                // $pdf->SetFont('Arial','',11);
            }
            // if(count($actividad->fotografias) > 0) {
            //     if ($pdf->getY() + 70 > $pdf->GetPageHeight()) {
            //         $pdf->AddPage();
            //         $y = 10; // Reiniciar la posición en la nueva página
            //     }
            //     $pdf->setXY(5,$pdf->getY()+2);
            //     $pdf->SetFont('Arial','B',12);
            //     $pdf->Cell(200,5,utf8_decode("Evidencia fotográfica"),0,0,"C",1);
            //     $pdf->setXY(5,$pdf->getY()+5);
            //     $x = $pdf->getX();
            //     $y= $pdf->getY();
            //     foreach($actividad->fotografias as $index => $foto) {
            //         $pdf->Image(Storage::disk('reportes')->path($foto->url_path),$x,$y,60,65);
            //         $index++;  
            //         $x += 70;
            //         if($index == count($actividad->fotografias) && (count($actividad->fotografias) % 2 == 0 || in_array($index,[1,3,5,7,9,11,13,15,17]))) {
            //             $y += 68;
            //         }else {
            //             if(in_array($index,[3,6,9,12,15,18,21,24,27,30])) {
            //                 $x = 5;
            //                 $y += 68;
            //             }
            //         }                    
            //     }
            //     $pdf->setY($y);
            // }
            #endregion
        }
        #endregion
        return base64_encode($pdf->Output("S","ReporteEvidencia.pdf"));
    }

    static private function truncarTexto($pdf, $texto, $anchoMax, $sufijo = "...") {
        $anchoTexto = $pdf->GetStringWidth($texto);
        $anchoSufijo = $pdf->GetStringWidth($sufijo);
    
        // Si el texto es más ancho que el espacio disponible, recortarlo
        if ($anchoTexto > $anchoMax) {
            while ($pdf->GetStringWidth($texto) + $anchoSufijo > $anchoMax) {
                $texto = substr($texto, 0, -1); // Quitar un carácter
            }
            return $texto . $sufijo; // Agregar "..."
        }
        return $texto; // Devolver el texto original si cabe
    }

    static private function renderHTMLToFPDF(FPDF $pdf, $html, $width = 208, $x = 0) {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = str_replace(["\n", "\r"], '', $html);
    
        $pattern = '/(<\/?(?:p|strong|ul|ol|li)>)/i';
        $parts = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    
        $isBold = false;
        $isOrdered = false;
        $isUnordered = false;
        $listIndex = 1;
    
        foreach ($parts as $part) {
            $tag = strtolower(trim($part));
    
            switch ($tag) {
                case '<p>':
                case '</p>':
                    $pdf->Ln(3);
                    break;
    
                case '<strong>':
                    $isBold = true;
                    $pdf->SetFont('Arial', 'B', 10);
                    break;
    
                case '</strong>':
                    $isBold = false;
                    $pdf->SetFont('Arial', '', 10);
                    break;
    
                case '<ul>':
                    $isUnordered = true;
                    break;
    
                case '</ul>':
                    $isUnordered = false;
                    break;
    
                case '<ol>':
                    $isOrdered = true;
                    $listIndex = 1;
                    break;
    
                case '</ol>':
                    $isOrdered = false;
                    break;
    
                case '<li>':
                case '</li>':
                    break;
    
                default:
                    $text = trim($part);
                    if ($text === '') continue 2;
    
                    if ($isOrdered) {
                        $text = $listIndex++ . '. ' . $text;
                    } elseif ($isUnordered) {
                        $text = '-' . ' ' . $text;
                    }
    
                    // Alinear en X especificada
                    $pdf->SetX($x);
                    $pdf->MultiCell($width - $x * 2, 5, utf8_decode($text), 0, "L", false);
            }
        }
    
        $pdf->SetFont('Arial', '', 10); // restaurar fuente
    }
}