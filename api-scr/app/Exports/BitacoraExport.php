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
        $pdf->Image(Storage::disk('img')->path('logo-MAI.png'),-5,-1,60,0);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->setXY(50,6);
        $pdf->setFillColor(255,255,255); 
        $pdf->SetFont('Arial','B',11);
        $pdf->SetTextColor(255, 51, 0);
        $pdf->Cell(160,6,"SURVEY, CONSULTANTS AND ENGINEERING SA",0,0,"C",1);
        $pdf->setXY(50,12);
        $pdf->SetTextColor(44, 36, 32);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(160,6,utf8_decode("INFORME EJECUTIVO"." [".$data->folio_reporte."]"),0,0,"C",1);  
        $pdf->setXY(0,20);
        $pdf->setFillColor(237, 125, 49);
        $pdf->Cell(210,1,"",0,0,"C",1);  
        $pdf->setXY(5,22.5); 
        $pdf->setFillColor(255, 255, 255); 
        $pdf->SetTextColor(73, 77, 85);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(200,5,utf8_decode("FECHA: ".Controller::convertDateToText($data->fecha)),0,0,"R",1);
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
        $pdf->Cell(30,5,utf8_decode($data->folio_reporte),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(90,5,utf8_decode(self::truncarTexto($pdf,$data->sitio,110)),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(70,5,utf8_decode($data->responsable),0,0,"L",1);
        $pdf->setXY(5,43);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,5,"Cliente",0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(170,5,"Proyecto",0,0,"L",1);
        $pdf->setXY(5,49);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(30,5,utf8_decode($data->cliente),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(170,5,utf8_decode(self::truncarTexto($pdf,$data->proyecto,200)),0,0,"L",1);
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
                $pdf->Cell(200,5,utf8_decode("Evidencia Fotografia"),0,0,"C",1);
                $pdf->setXY(5,$pdf->getY()+5);
                $x = $pdf->getX();
                $y= $pdf->getY();
                foreach($actividad->fotografias as $index => $foto) {
                    $pdf->Image(Storage::disk('reportes')->path($foto->path),$x,$y,60,65);
                    $index++;  
                    $x += 70;
                    if($index == count($actividad->fotografias) && (count($actividad->fotografias) % 2 == 0 || in_array($index,[1]))) {
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
}