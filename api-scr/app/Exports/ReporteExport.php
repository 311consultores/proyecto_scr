<?php

namespace App\Exports;

use Fpdf\Fpdf;
use Illuminate\Support\Facades\Storage;

class ReporteExport {

    public function generateReporteEvidencia($data, $tipo_export = 0)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','U',10);
        $pdf->SetDrawColor(216, 216, 216);        
        $pdf->SetAutoPageBreak(true,5); 
        #region [Cabecera]
            $pdf->setXY(0,0);
            $pdf->Image(Storage::disk('img')->path('logo-MAI.png'),10,3,50,25,'PNG','');
            $pdf->Image(Storage::disk('img')->path('logo.png'),175,3,20,20,'PNG','');
            $pdf->setXY(4,28);
            $pdf->setFillColor(255,255,255); 
            $pdf->SetFont('Arial','B',11);
            $pdf->SetDrawColor(0,0,0);
            $pdf->SetLineWidth(0.4);
            $pdf->Cell(202,7,utf8_decode(strtoupper($data->sTitulo)),1,0,"C",1);
            $pdf->setXY(4,35.5);
            // $pdf->setFillColor(242, 242, 242);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(50.5,5,"CLIENTE","LR",0,"C",1);
            $pdf->Cell(50.5,5,"FECHA","LR",0,"C",1);
            $pdf->Cell(50.5,5,"EQUIPO","LR",0,"C",1);
            $pdf->Cell(50.5,5,"REPORTE","LR",0,"C",1);
            $pdf->setXY(4,41);
            $pdf->SetFont('Arial','',11);
            $pdf->SetTextColor(232, 101, 27);
            $pdf->Cell(50.5,5,utf8_decode(strtoupper($data->sCliente)),"LBR",0,"C",1);
            $pdf->Cell(50.5,5,date('d/m/Y',strtotime($data->sFecha)),"LBR",0,"C",1);
            $pdf->Cell(50.5,5,utf8_decode(strtoupper($data->sEquipo)),"LBR",0,"C",1);
            $pdf->Cell(50.5,5,utf8_decode(strtoupper($data->sReporte)),"LBR",0,"C",1);
        #endregion
        #region [Evidencias]
            $pdf->setXY(4,50);
            $index=1;
            foreach($data->objEvidencias as $evidencia) {
                $pdf->setFillColor(242, 242, 242);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial','B',11);                
                $pdf->setX(4);
                $pdf->Cell(202,7,utf8_decode(strtoupper($evidencia->sTitulo)),1,0,"C",1);
                $pdf->Ln();
                $pdf->setX(4);
                $pdf->SetFont('Arial','',12);
                if($evidencia->bImagenes) {
                    $pdf->MultiCell(202,6,utf8_decode($evidencia->sDescripcion),"RL",'L');
                    $pdf->setX(4);
                    $pdf->SetFont('Arial','B',11);
                    $pdf->Cell(202,6,utf8_decode("EVIDENCIA FOTOGRÁFICA"),1,0,"C",1);
                    $pdf->Ln();
                    $pdf->setX(4);
                    $pdf->setFillColor(255, 255, 255);
                    $pdf->Cell(202,55,"",1,0,"C",1);
                    $posicionX=0;
                    $sumaX=0;
                    $valorx=0;
                    $valorY=53;
                    switch($evidencia->iTotalImagenes) {
                        case 1:
                            $posicionX = 78;
                            $valorX=55;
                            break;
                        case 2:
                            $posicionX=45;
                            $valorX=55;
                            $sumaX= 65;
                            break;
                        case 3: 
                            $posicionX=13;
                            $valorX=55;
                            $sumaX=65;
                            break;
                        case 4:
                            $posicionX=6;
                            $valorX=48;
                            $sumaX=50;
                            break;
                        case 5:
                            $posicionX=5;
                            $valorX=40;
                            $sumaX=40;
                            break;
                    }
                    $pdf->SetX($posicionX);
                    foreach ($evidencia->objImagenes as $imagen) {
                        if($imagen->namefile != ""){
                            $path_image = Storage::disk('reportes')->path($imagen->file);
                            $extension = explode('.',$imagen->file);
                            $pdf->Image($path_image,$pdf->GetX(),$pdf->GetY()+1,$valorX,$valorY,$extension[count($extension)-1],'');
                            $pdf->SetX($pdf->GetX()+$sumaX);
                        }
                    }                   
                }else{
                    $pdf->MultiCell(202,6,utf8_decode($evidencia->sDescripcion),"RBL",'L');
                }
                if(($pdf->GetY()+60+60) >= 270 && $index < count($data->objEvidencias)) {
                    $pdf->AddPage();
                }else{
                    $pdf->SetY($pdf->GetY()+60);
                }
                $index++; 
            }
        #endregion
        if($tipo_export == 1) {
            return $pdf->Output("I","ReporteEvidencia.pdf");
        }
        return base64_encode($pdf->Output("S","ReporteEvidencia.pdf"));
    }
}