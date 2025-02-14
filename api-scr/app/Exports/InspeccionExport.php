<?php

namespace App\Exports;

use Fpdf\Fpdf;
use Illuminate\Support\Facades\Storage;

class InspeccionExport {

    public function generatePDF($data, $fotografias)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','U',10);
        $pdf->SetDrawColor(216, 216, 216);        
        $pdf->SetAutoPageBreak(true,5); 
        #region [CABECERA]
            $pdf->setXY(0,0);
            $pdf->Cell(50,20,"",0,0,"L");
            $pdf->setFillColor(0,0,0); 
            $pdf->SetTextColor(255,255,255);
            $pdf->Cell(160,5,"www.grupo-scr.com",0,0,"C",1);
            $pdf->Image(Storage::disk('img')->path('logo.png'),15,3,20,15,'PNG','');
            $pdf->Cell(35,5,"",0,0,"L");
            $pdf->setXY(50,6);
            $pdf->setFillColor(255,255,255); 
            $pdf->SetFont('Arial','B',9);
            $pdf->SetTextColor(255, 51, 0);
            $pdf->Cell(160,6,"SURVEY, CONSULTANTS AND ENGINEERING SA",0,0,"C",1);
            $pdf->setXY(50,12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(60,6,utf8_decode("REPORTE DE INSPECCIÓN"),0,0,"R",1);        
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(100,6,"[STATUS ESTRUCTURAL Y RECUBRIMIENTOS]",0,0,"L",1);
            $pdf->setXY(0,20);
            $pdf->setFillColor(237, 125, 49);
            $pdf->Cell(210,1,"",0,0,"C",1);
        #endregion
        #region [DATOS GENERALES]
            $pdf->setXY(5,22);
            $pdf->SetFont('Arial','',7);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(28,5,"GRUA",1,0,"L",1);
            $pdf->setFillColor(255, 255, 255);
            $pdf->Cell(40,5,utf8_decode($data[1]),1,0,"L");
            $pdf->setXY(78,22);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(20,5,"FECHA",1,0,"C",1);
            $pdf->setFillColor(255, 255, 255);
            $pdf->Cell(30,5,$data[3],1,0,"L");
            $pdf->setXY(134,22);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(36,5,utf8_decode("DAÑO ESTRUCTURAL"),1,0,"C",1);
            $pdf->Cell(35,5,"No. DE INCIDENCIA",1,0,"C",1);

            $pdf->setXY(5,27);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(28,5,"SISTEMA / AREA",1,0,"L",1);
            $pdf->Cell(40,5,utf8_decode($data[2]),1,0,"L");
            $pdf->setXY(78,27);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(20,5,"HORA INICIO",1,0,"C",1);
            $pdf->setFillColor(255, 255, 255);
            $pdf->Cell(30,5,$data[5],1,0,"L");
            $pdf->setXY(134,27);
            $pdf->setFillColor(255, 255, 0);
            if($data[4] == "SI") {
                $pdf->Cell(18,10,"SI",1,0,"C",1);
            }else{
                $pdf->Cell(18,10,"SI",1,0,"C");
            }
            if($data[4] == "NO") {
                $pdf->Cell(18,10,"NO",1,0,"C",1);
            }else{
                $pdf->Cell(18,10,"NO",1,0,"C");
            }            
            $pdf->Cell(35,10,"",1,0,"L");   //Dano estructural
            $pdf->setFillColor(255, 255, 255);

            $pdf->setXY(5,32);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(28,5,"ZONA",1,0,"L",1);
            $pdf->Cell(40,5,$data[19],1,0,"L");
            $pdf->setXY(78,32);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(20,5,"HORA FINAL",1,0,"C",1);
            $pdf->setFillColor(255, 255, 255);
            $pdf->Cell(30,5,$data[6],1,0,"L");

            $pdf->setXY(0,38);
            $pdf->setFillColor(237, 125, 49);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(210,1,"",0,0,"C",1);
        #endregion
        #region [CONDICIONES AMBIENTALES]
            $pdf->setXY(5,40);
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell(205,5,"CONDICIONES AMBIENTALES",0,0,"L");
            $pdf->setXY(5,46);            
            $pdf->SetFont('Arial','',7);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(35,5,"TEMPERATURA",1,0,"L",1);
            $pdf->setXY(45,46);
            $pdf->Cell(40,5,"HUMEDAD RELATIVA",1,0,"L",1);
            $pdf->setXY(90,46);
            $pdf->Cell(40,5,"PUNTO DE ROCIO",1,0,"L",1);
            $pdf->setXY(135,46);
            $pdf->Cell(70,5,"CATEGORIA CORROSION ATMOSFERICA",1,0,"L",1);
            $pdf->setXY(5,51);
            $pdf->Cell(35,9,utf8_decode($data[7]."°C"),1,0,"C");
            $pdf->setXY(45,51);
            $pdf->Cell(40,9,$data[8]."%",1,0,"C");
            $pdf->setXY(90,51);
            $pdf->Cell(40,9, utf8_decode($data[9]."°C"),1,0,"C");
            $pdf->setXY(135,51);
            $pdf->Cell(70,9,utf8_decode($data[10]),1,0,"C");
            $pdf->setXY(5,60);
            $pdf->Cell(35,5,utf8_decode("No. DE DAÑO"),1,0,"L",1);
            $pdf->setXY(45,60);
            $pdf->Cell(40,5,"NOMENCLATURA",1,0,"L",1);
            $pdf->setXY(90,60);
            $pdf->Cell(40,5,"NOMBRE DE ESTRUCTURA",1,0,"L",1);
            $pdf->setXY(135,60);
            $pdf->Cell(70,5,"TEMP MAXIMA  SERVICIO",1,0,"L",1);
            $pdf->setXY(5,65);
            $pdf->Cell(35,9,utf8_decode($data[11]),1,0,"C");
            $pdf->setXY(45,65);
            $pdf->Cell(40,9,utf8_decode($data[12]),1,0,"C");
            $pdf->setXY(90,65);
            $pdf->Cell(40,9,utf8_decode($data[13]),1,0,"C");
            $pdf->setXY(135,65);
            $pdf->Cell(70,9,utf8_decode($data[14]."°C"),1,0,"C");
            $pdf->setXY(5,74);
            $pdf->Cell(35,5,"VEL. VIENTO",1,0,"L",1);
            $pdf->setXY(45,74);
            $pdf->Cell(40,5,"TEMPERATURA SUBSTRATO",1,0,"L",1);
            $pdf->setXY(90,74);
            $pdf->Cell(40,5,"INDICE DE UV",1,0,"L",1);
            $pdf->setXY(135,74);
            $pdf->Cell(70,5,"PLANO DE REFERENCIA",1,0,"L",1);
            $pdf->setXY(5,79);
            $pdf->Cell(35,9,utf8_decode($data[15]),1,0,"C");
            $pdf->setXY(45,79);
            $pdf->Cell(40,9,utf8_decode($data[16]),1,0,"C");
            $pdf->setXY(90,79);
            $pdf->Cell(40,9,utf8_decode($data[17]),1,0,"C");
            $pdf->setXY(135,79);
            $pdf->Cell(70,9,utf8_decode(""),1,0,"C");
            
            $pdf->setXY(0,89);
            $pdf->setFillColor(237, 125, 49);
            $pdf->Cell(210,1,"",0,0,"C",1);
        #endregion
        #region [DATOS TÉCNICOS A DETALLE DE LA OBSERVACIÓN]
            $pdf->setXY(5,91);
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell(205,5,utf8_decode("DATOS TÉCNICOS A DETALLE DE LA OBSERVACIÓN"),0,0,"L");

            $pdf->setXY(5,97);
            $pdf->setFillColor(242, 242, 242);
            $pdf->SetFont('Arial','',7);
            $pdf->Cell(74,5,utf8_decode("DISEÑO"),1,0,"L",1);
            $pdf->Cell(126,5,utf8_decode("FOTOGRAFIAS"),1,0,"L",1);
            $pdf->setXY(5,102);
            $pdf->Image(Storage::disk('gruas')->path(utf8_decode($data[12]).'.jpg'),5,102,74,70,'JPG','');
            $pdf->Cell(74,70,"",1,0,"C");
            $pdf->Cell(63,35,"",1,0,"C");
            if(isset($fotografias[0])){
                $pdf->Image($fotografias[0],79,102,63,35,explode('.',$fotografias[0])[1],'');
            }
            $pdf->Cell(63,35,"",1,0,"C");
            if(isset($fotografias[1])){
                $pdf->Image($fotografias[1],142,102,63,35,explode('.',$fotografias[1])[1],'');
            }            
            $pdf->setXY(79,137);
            $pdf->Cell(63,35,"",1,0,"C");
            if(isset($fotografias[2])){
                $pdf->Image($fotografias[2],79,137,63,35,explode('.',$fotografias[2])[1],'');
            } 
            $pdf->Cell(63,35,"",1,0,"C");
            if(isset($fotografias[3])){
                $pdf->Image($fotografias[3],142,137,63,35,explode('.',$fotografias[3])[1],'');
            } 

            $pdf->setXY(5,174);
            $pdf->SetFont('Arial','',7);
            $pdf->setFillColor(242, 242, 242);
            $pdf->Cell(25,4,"ZONA",1,0,"L",1);
            $pdf->Cell(49,4,utf8_decode($data[19]),1,0,"L");
            $pdf->setXY(82,174);
            $pdf->Cell(55,4,"DETALLES RECUBRIMIENTO",1,0,"C",1);
            $pdf->setXY(142,174);
            $pdf->Cell(63,4,"DETALLES ESTRUCTURALES",1,0,"C",1);

            $pdf->setXY(5,178);
            $pdf->Cell(25,4,"EPS MILS",1,0,"L",1);
            $pdf->Cell(49,4,utf8_decode($data[20]),1,0,"L");
            $pdf->setXY(82,178);
            $pdf->Cell(45,4,"CONCEPTOS",1,0,"C",1);
            $pdf->Cell(10,4,"[X]",1,0,"C",1);
            $pdf->setXY(142,178);
            $pdf->Cell(53,4,"CONCEPTOS",1,0,"C",1);
            $pdf->Cell(10,4,"[X]",1,0,"C",1);

            $seleccionDetalle = "";
            $pdf->setXY(5,182);
            $pdf->Cell(74,4,"",0,0,"L");
            $pdf->setXY(82,182);
            $pdf->Cell(45,4,"AMPOLLAMIENTO",1,0,"L");
            str_contains($data[27], 'AMPOLLAMIENTO') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,182);
            $pdf->Cell(53,4,"FRACTURAS",1,0,"L");
            str_contains($data[28], 'FRACTURAS') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $pdf->setXY(5,186);
            $pdf->Cell(74,4,utf8_decode("CLASIFICACIÓN DE DAÑO ISO 8501-2 CORROSION"),1,0,"L",1);
            $pdf->setXY(82,186);
            $pdf->Cell(45,4,"CASCARA NARANJA",1,0,"L");
            str_contains($data[27], 'CASCARA NARANJA') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,186);
            $pdf->Cell(53,4,"SOLDADURA POROSA",1,0,"L");
            str_contains($data[28], 'SOLDADURA POROSA') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $pdf->setXY(5,190);
            $pdf->Cell(24,4,"[TIPO]",1,0,"C",1);
            $pdf->Cell(10,4,"[x]",1,0,"C",1);
            $pdf->Cell(40,4,"AREA EN M2",1,0,"C",1);
            $pdf->setXY(82,190);
            $pdf->Cell(45,4,"DESPRENDIMIENTO",1,0,"L");
            str_contains($data[27], 'DESPRENDIMIENTO') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,190);
            $pdf->Cell(53,4,"CORTE IRREGULAR",1,0,"L");
            str_contains($data[28], 'DESPRENDIMIENTO') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $areaM2 = "";
            $pdf->setXY(5,194);
            $pdf->Cell(24,4,"E",1,0,"C");
            str_contains($data[21], 'E') ? $seleccionDetalle= 'X' : $seleccionDetalle = "";
            str_contains($data[21], 'E') ? $areaM2= $data[22] : $areaM2 = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->Cell(40,4,$areaM2,1,0,"C");
            $pdf->setXY(82,194);
            $pdf->Cell(45,4,"FALTA CONT. PELICULA",1,0,"L");
            str_contains($data[27], 'FALTA CONT PELICULA') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,194);
            $pdf->Cell(53,4,"DESGASTE",1,0,"L");
            str_contains($data[28], 'DESGASTE') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $pdf->setXY(5,198);
            $pdf->Cell(24,4,"F",1,0,"C");
            str_contains($data[21], 'F') ? $seleccionDetalle= 'X' : $seleccionDetalle = "";
            str_contains($data[21], 'F') ? $areaM2= $data[22] : $areaM2 = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->Cell(40,4,$areaM2,1,0,"C");
            $pdf->setXY(82,198);
            $pdf->Cell(45,4,utf8_decode("DECOLORACIÓN"),1,0,"L");
            str_contains($data[27], 'DECOLORACION') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,198);            
            $pdf->Cell(53,4,utf8_decode("DESGASTE POR FRICCIÓN"),1,0,"L");
            str_contains($data[28], 'DESGASTE POR FRICCION') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $pdf->setXY(5,202);
            $pdf->Cell(24,4,"G",1,0,"C");
            str_contains($data[21], 'G') ? $seleccionDetalle= 'X' : $seleccionDetalle = "";
            str_contains($data[21], 'G') ? $areaM2= $data[22] : $areaM2 = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->Cell(40,4,$areaM2,1,0,"C");
            $pdf->setXY(82,202);
            $pdf->Cell(45,4,utf8_decode("FALTA BRILLO"),1,0,"L");
            str_contains($data[27], 'FALTA BRILLO') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,202);
            $pdf->Cell(53,4,utf8_decode("GOLPES O ABOLLADURA"),1,0,"L");
            str_contains($data[28], 'GOLPES O ABOLLADURA') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $pdf->setXY(5,206);
            $pdf->Cell(24,4,"H",1,0,"C");
            str_contains($data[21], 'H') ? $seleccionDetalle= 'X' : $seleccionDetalle = "";
            str_contains($data[21], 'H') ? $areaM2= $data[22] : $areaM2 = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->Cell(40,4,$areaM2,1,0,"C");
            $pdf->setXY(82,206);
            $pdf->Cell(45,4,utf8_decode("MALA ADHESION"),1,0,"L");
            str_contains($data[27], 'MALA ADHESION') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,206);
            $pdf->Cell(53,4,utf8_decode("TORNILLERIA EN MAL ESTADO"),1,0,"L");
            str_contains($data[28], 'TORNILLERIA EN MAL ESTADO') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");

            $pdf->setXY(5,210);
            $pdf->Cell(74,4,"",0,0,"L");
            $pdf->setXY(82,210);
            $pdf->Cell(45,4,"ESCURRIMIENTOS",1,0,"L");
            str_contains($data[27], 'ESCURRIMIENTOS') ? $seleccionDetalle='X' : $seleccionDetalle = "";
            $pdf->Cell(10,4,$seleccionDetalle,1,0,"C");
            $pdf->setXY(142,210);
            $pdf->Cell(53,4,"",1,0,"L");
            $pdf->Cell(10,4,"",1,0,"C");

            $pdf->setXY(5,214);
            $pdf->Cell(74,4,"TIPO DE LIMPIEZA RECOMENDADO","LTR",0,"C",1);

            $pdf->setXY(5,218);
            $pdf->Cell(74,4,utf8_decode("[PREPARACIÓN DE SUPERFICIE NORMA ISO 8501 -1]"),"LBR",0,"C",1);
            $pdf->setXY(82,218);
            $pdf->Cell(123,4,"CARACTERISTICAS DEL RECUBRIMIENTO",1,0,"C",1);

            $pdf->setXY(5,222);
            $pdf->setFillColor(255, 255, 0);
            str_contains($data[23], 'ST- 2') ? $pdf->Cell(74,5,"ST-2",1,0,"C",1) : $pdf->Cell(74,5,"ST-2",1,0,"C");
            $pdf->setFillColor(255, 255, 255); 
            $pdf->setXY(82,222);
            $pdf->Cell(123,4,utf8_decode("[COLOR ESPECIFICACIÓN DE RAL]"),1,0,"L");

            $pdf->setXY(5,227);
            $pdf->setFillColor(255, 255, 0);
            str_contains($data[23], 'ST- 3 X') ? $pdf->Cell(74,5,"ST-3 X",1,0,"C",1) : $pdf->Cell(74,5,"ST-3 X",1,0,"C");
            $pdf->setFillColor(255, 255, 255);          
            $data[24] = substr($data[24],0,217);
            $pdf->setXY(82,226);
            $pdf->Cell(123,8,utf8_decode(strtoupper($data[24])),1,0,"L");

            $pdf->setXY(5,232);
            $pdf->setFillColor(255, 255, 0);
            str_contains($data[23], 'SA - 1') ? $pdf->Cell(74,5,"SA-1",1,0,"C",1) : $pdf->Cell(74,5,"SA-1",1,0,"C");
            $pdf->setFillColor(255, 255, 255); 

            $pdf->setXY(5,237);
            $pdf->setFillColor(255, 255, 0);
            str_contains($data[23], 'SA - 2') ? $pdf->Cell(74,5,"SA-2",1,0,"C",1) : $pdf->Cell(74,5,"SA-2",1,0,"C");
            $pdf->setFillColor(255, 255, 255);
            $pdf->setXY(82,234);
            $pdf->Cell(123,5,"[TIPO DE RECUBRIMIENTOS]",1,0,"L");

            $pdf->setXY(5,242);
            $pdf->setFillColor(255, 255, 0);
            str_contains($data[23], 'SA - 2 1/2') ? $pdf->Cell(74,5,"SA-2 1/2",1,0,"C",1) : $pdf->Cell(74,5,"SA-2 1/2",1,0,"C");
            $pdf->setFillColor(255, 255, 255);
            strlen($data[24]) > 108 ? $pdf->setXY(82,230) : $pdf->setXY(82,231);
            $data[25] = substr($data[25],0,217);
            $pdf->setXY(82,238);
            $pdf->Cell(123,8,utf8_decode(strtoupper($data[25])),1,0,"L");            
            $pdf->setXY(5,247);
            $pdf->setFillColor(255, 255, 0);
            str_contains($data[23], 'SA - 3') ? $pdf->Cell(74,5,"SA-3",1,0,"C",1) : $pdf->Cell(74,5,"SA-3",1,0,"C");
            $pdf->setFillColor(255, 255, 255);

            $pdf->setXY(5,252);
            str_contains($data[23], 'OTRO') ? $pdf->Cell(74,5,"OTRO",1,0,"C",1) : $pdf->Cell(74,5,"OTRO",1,0,"C");
            $pdf->setXY(82,246);
            $pdf->Cell(123,4,"[OBSERVACIONES]",1,0,"L");

            $pdf->setXY(5,251);
            $pdf->Cell(74,4,"",0,0,"C");
            $pdf->setXY(82,250);
            $data[26] = substr($data[26],0,320);
            $pdf->MultiCell(123,4, strtoupper(utf8_decode($data[26])),1,'L');
            $pdf->setXY(0,264);
            $pdf->setFillColor(237, 125, 49);
            $pdf->Cell(210,1,"",0,0,"C",1);
        #endregion
        #region [FIRMAS]
            $pdf->setXY(5,266);
            $pdf->setFillColor(242, 242, 242);
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(40,4,"EJECUTA POR","LTR",0,"C",1);
            $pdf->Cell(40,4,"INSPECTOR POR","LTR",0,"C",1);
            $pdf->Cell(40,4,utf8_decode("DIRECCIÓN"),"LTR",0,"C",1);
            $pdf->Cell(40,4,"SUPERVISOR","LTR",0,"C",1);
            $pdf->Cell(40,4,"GERENCIA","LTR",0,"C",1);
            $pdf->setXY(5,270);
            $pdf->SetFont('Arial','',6);
            $data[0] = substr($data[0],0,28);
            strlen($data[0]) == 28 ? $data[0].'...' : $data[0];
            $pdf->Cell(40,4,utf8_decode(strtoupper($data[0])),"LBR",0,"C",1);
            $pdf->Cell(40,4,utf8_decode(strtoupper($data[0])),"LBR",0,"C",1);
            $pdf->Cell(40,4,"SCR SURVEY","LBR",0,"C",1);
            $pdf->Cell(40,4,"DPW CAUCEDO","LBR",0,"C",1);
            $pdf->Cell(40,4,"DPW CAUCEDO","LBR",0,"C",1);
            $pdf->setXY(5,274);
            $pdf->Cell(40,15,"",1,0,"C");
            $pdf->Cell(40,15,"",1,0,"C");
            $pdf->Cell(40,15,"",1,0,"C");
            $pdf->Cell(40,15,"",1,0,"C");
            $pdf->Cell(40,15,"",1,0,"C");


        #endregion
        return $pdf->Output("I","ReporteEquipo.pdf");
    }
}