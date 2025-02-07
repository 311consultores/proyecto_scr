<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use DateTime;

class Controller extends BaseController
{
    public function encode_json($code){
        $rand = rand(3,9);
        $base_64 = base64_encode($code);
        $cabecera = substr($base_64, 0, 3);
        $cola = substr($base_64,3);
        for($r =0; $r<$rand; $r++){
                $cabecera = base64_encode($cabecera);
        }
        $longitud = strlen($cabecera);
        $cabecera_cola = substr($cola, 0, -1);
        $cola_cola = substr($cola, -1);
        $longitud_1er = substr($longitud,0,1);
        $longitud_2do = substr($longitud, -1);
        $letras = $this->convert1toInvers($longitud_1er) . $this->convert1toInvers($longitud_2do);
        $letra_rand = $this->convertAto1($rand);
        return $letra_rand.$cabecera.$cabecera_cola.$letras.$cola_cola;
    }

    public static function convertDateToText($fecha, $tipo=0) {
        // Crear un objeto DateTime con la fecha
        $date = new DateTime($fecha);

        // Obtener el día de la semana, día, mes y año
        $dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
        $meses = ['January' => 'ENE', 'February' => 'FEB', 'March' => 'MAR', 'April' => 'ABR', 'May' => 'MAY', 'June' => 'JUN', 'July' => 'JUL', 'August' => 'AGO', 'September' => 'SEP', 'October' => 'OCT', 'November' => 'NOV', 'December' => 'DIC'];
        $clase = ['January' => 'm-ene', 'February' => 'm-feb', 'March' => 'm-mar', 'April' => 'm-abr', 'May' => 'm-may', 'June' => 'm-jun', 'July' => 'm-jul', 'August' => 'm-ago', 'September' => 'm-sep', 'October' => 'm-oct', 'November' => 'm-nov', 'December' => 'm-dic'];

        $diaSemana = $dias[$date->format('l')]; // Traducir día de la semana
        $dia = $date->format('d');
        $mes = $meses[$date->format('F')]; // Traducir mes
        $clase = $clase[$date->format('F')]; // Traducir mes
        $anio = $date->format('Y');

        // Formatear la fecha como texto
        if($tipo == 1) {
            return [$dia,$mes,$anio,$clase];
        }
        return "$dia $mes $anio";
    }

    public function convertAto1($num){
        if(is_numeric($num)){
            switch($num) {
                case 3: return "e";
                        break;
                case 4: return "A";
                        break;
                case 5: return "r";
                        break;
                case 6: return "M";
                        break;
                case 7: return "z";
                        break;
                case 8: return "L";
                        break;
                case 9: return "S";
                        break; 
            }
        }else{
            switch($num) {
                case "e": return 3;
                        break;
                case "A": return 4;
                        break;
                case "r": return 5;
                        break;
                case "M": return 6;
                        break;
                case "z": return 7;
                        break;
                case "L": return 8;
                        break;
                case "S": return 9;
                        break;
                        
            }
        }
    
    }
    
    public function convert1toInvers($num){
        if(is_numeric($num)){
            switch($num) {
                case 0: return "z";
                        break;
                case 1: return "Y";
                        break;
                case 2: return "x";
                        break;
                case 3: return "W";
                        break;
                case 4: return "v";
                        break;
                case 5: return "U";
                        break;
                case 6: return "t";
                        break;
                case 7: return "S";
                        break;
                case 8: return "r";
                        break;
                case 9: return "Q";
                        break;
                        
            }
        }else{
            switch($num) {
                case "z": return 0;
                        break;
                case "Y": return 1;
                        break;
                case "x": return 2;
                        break;
                case "W": return 3;
                        break;
                case "v": return 4;
                        break;
                case "U": return 5;
                        break;
                case "t": return 6;
                        break;
                case "S": return 7;
                        break;
                case "r": return 8;
                        break;
                case "Q": return 9;
                        break;
            }
        }
    
    } 
}
