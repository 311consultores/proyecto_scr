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
            $pdf->SetXY(0,0);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(60,30,"",0,0,"",1);
            $pdf->Image(Storage::disk('img')->path('311.png'),5,4,45,10,'PNG','');
            // $pdf->AddFont('Montserrat-Bold','','Montserrat-Bold.php', public_path('assets/fonts/Montserrat'));
            // $pdf->AddFont('Montserrat-Regular','','Montserrat-Regular.php', public_path('assets/fonts/Montserrat'));
            $pdf->SetFont('Helvetica', 'U', 10);
            $pdf->SetFillColor(22, 33, 33);
            $pdf->SetTextColor(255,255,255);
            $pdf->Cell(150,5,"311consultores.com.mx",0,0,"C",1,"https://thispersondoesnotexist.com/");
            //Titulo
            $pdf->SetXY(80,9);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(100,5,utf8_decode(strtoupper($data->sTitulo)),0,0,"C");
            $pdf->SetXY(80,16);
        #endregion
        #region [Division]
            $pdf->SetXY(0,21);
            $pdf->SetFillColor(22, 33, 33);
            $pdf->Cell(210,1.5,"",0,0,"",1);
        #endregion
        #region [Información Seleccionada y de Contacto]
            $pdf->SetXY(4, 25);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetDrawColor(22, 33, 33);
            $pdf->Cell(132,5,utf8_decode("INFORMACION"),"L");
            //Cliente
            $pdf->SetXY(5, 33);
            $pdf->SetFillColor(244, 244, 244);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->Cell(25,6,"Cliente",1,0,"L",1);
            $pdf->SetFont('Helvetica', '', 11);            
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(35,6,utf8_decode(strtoupper($data->sCliente)),1,0,"C",1);
            //Proyecto            
            // $iCInteres= $data["info_lote"]->iPrecioM2Contado + (($data["info_plazo"]->iInteres/100) * $data["info_lote"]->iPrecioM2Contado);
            $pdf->SetX($pdf->GetX()+5);
            $pdf->SetFillColor(244, 244, 244);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->Cell(31,6,utf8_decode("Proyecto"),1,0,"L",1);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Helvetica', '', 11);
            $pdf->Cell(35,6,utf8_decode(strtoupper($data->sEquipo)),1,0,"C",1);
            //Fecha
            $pdf->SetX($pdf->GetX()+5);
            $pdf->SetFillColor(244, 244, 244);
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->Cell(28,6,utf8_decode("Fecha"),1,0,"L",1);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Helvetica', '', 11);
            $pdf->Cell(38,6,date('d/m/Y',strtotime($data->sFecha)),1,0,"C",1);
        #endregion
    
        #region [Evidencias]
            $pdf->setXY(4,42);
            $index=1;
            foreach($data->objEvidencias as $evidencia) {
                $pdf->setFillColor(255, 255, 255);
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