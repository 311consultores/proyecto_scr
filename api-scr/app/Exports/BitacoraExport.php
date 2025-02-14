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
        $pdf->SetAutoPageBreak(true,5); 
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
        $titulo = $data->bSitio ? 'Sito' : 'Proyecto';
        $pdf->Cell(110,5,$titulo,0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(50,5,"Cliente",0,0,"L",1);
        $pdf->setXY(5,36);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(30,5,utf8_decode($data->folio_reporte),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $p_titulo = $data->bSitio ? self::truncarTexto($pdf,$data->sitio,110) : self::truncarTexto($pdf,$data->proyecto,110);
        $pdf->Cell(110,5,utf8_decode($p_titulo),0,0,"L",1);
        $pdf->setX($pdf->getX()+5);
        $pdf->Cell(50,5,utf8_decode($data->cliente),0,0,"L",1);
        $pdf->setXY(5,43);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,5,"Equipo",0,0,"L",1);
        $pdf->setXY(5,49);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(200,5,utf8_decode(self::truncarTexto($pdf,$data->equipo,100)),0,0,"L",1);
        $pdf->setXY(5,60);
        $pdf->SetFont('Arial','B',14);
        $pdf->SetTextColor(255, 131, 66);
        $pdf->Cell(50,5,"Actividades Realizadas",0,0,"L",1);
        #endregion
        #region [Actividades]
        $index=1;
        foreach($data->actividades as $actividad) {
            #region [Cabecera Actividad]
            $pdf->SetFont('Arial','B',12);
            $pdf->SetTextColor(44, 36, 32);
            $pdf->setXY(5,$pdf->getY()+10);
            $pdf->Cell(200,5,utf8_decode($actividad->orden_trabajo),0,0,"C",1);
            $pdf->setXY(5,$pdf->getY()+5);
            $pdf->setFillColor(226, 222, 215);
            $pdf->Cell(200,1,"",0,0,"C",1);
            #endregion
            #region [Cuerpo Actividad]
            $pdf->setFillColor(255, 255, 255);
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
            $pdf->Cell(200,5,utf8_decode(self::truncarTexto($pdf,$actividad->observacion,200)),0,0,"L",1);
            if(count($actividad->fotografias) > 0) {
                foreach($actividad->fotografias as $foto) {

                }
            }
            #endregion
        }
            // $index=1;
            // foreach($data->objEvidencias as $evidencia) {
            //     $pdf->setFillColor(242, 242, 242);
            //     $pdf->SetTextColor(0, 0, 0);
            //     $pdf->SetFont('Arial','B',11);                
            //     $pdf->setX(4);
            //     $pdf->Cell(202,7,utf8_decode(strtoupper($evidencia->sTitulo)),1,0,"C",1);
            //     $pdf->Ln();
            //     $pdf->setX(4);
            //     $pdf->SetFont('Arial','',12);
            //     if($evidencia->bImagenes) {
            //         $pdf->MultiCell(202,6,utf8_decode($evidencia->sDescripcion),"RL",'L');
            //         $pdf->setX(4);
            //         $pdf->SetFont('Arial','B',11);
            //         $pdf->Cell(202,6,utf8_decode("EVIDENCIA FOTOGRÁFICA"),1,0,"C",1);
            //         $pdf->Ln();
            //         $pdf->setX(4);
            //         $pdf->setFillColor(255, 255, 255);
            //         $pdf->Cell(202,55,"",1,0,"C",1);
            //         $posicionX=0;
            //         $sumaX=0;
            //         $valorx=0;
            //         $valorY=53;
            //         switch($evidencia->iTotalImagenes) {
            //             case 1:
            //                 $posicionX = 78;
            //                 $valorX=55;
            //                 break;
            //             case 2:
            //                 $posicionX=45;
            //                 $valorX=55;
            //                 $sumaX= 65;
            //                 break;
            //             case 3: 
            //                 $posicionX=13;
            //                 $valorX=55;
            //                 $sumaX=65;
            //                 break;
            //             case 4:
            //                 $posicionX=6;
            //                 $valorX=48;
            //                 $sumaX=50;
            //                 break;
            //             case 5:
            //                 $posicionX=5;
            //                 $valorX=40;
            //                 $sumaX=40;
            //                 break;
            //         }
            //         $pdf->SetX($posicionX);
            //         foreach ($evidencia->objImagenes as $imagen) {
            //             if($imagen->namefile != ""){
            //                 $path_image = Storage::disk('reportes')->path($imagen->file);
            //                 $extension = explode('.',$imagen->file);
            //                 $pdf->Image($path_image,$pdf->GetX(),$pdf->GetY()+1,$valorX,$valorY,$extension[count($extension)-1],'');
            //                 $pdf->SetX($pdf->GetX()+$sumaX);
            //             }
            //         }                   
            //     }else{
            //         $pdf->MultiCell(202,6,utf8_decode($evidencia->sDescripcion),"RBL",'L');
            //     }
            //     if(($pdf->GetY()+60+60) >= 270 && $index < count($data->objEvidencias)) {
            //         $pdf->AddPage();
            //     }else{
            //         $pdf->SetY($pdf->GetY()+60);
            //     }
            //     $index++; 
            // }
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