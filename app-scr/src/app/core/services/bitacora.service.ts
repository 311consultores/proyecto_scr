import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, map, Observable, tap, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BitacoraService {


  constructor(private http: HttpClient) {   }
  
  index() {
    return this.http.get<any>(environment.apiUrl+"bitacora/index");
  }

  recuperarFolio(json : any) {
    return this.http.post<any>(environment.apiUrl+"bitacora/recuperarFolio", json);
  }

  getReports(): Observable<any> {
    return this.http.get<any>(environment.apiUrl+"reportes/getReportesEvidencia");
  }

  enviarFormularioBitacora(bitacora : any): Observable<any> {
    return this.http.post<any>(environment.apiUrl+"bitacora/guardarBitacora", bitacora);
  }
  
  enviarFormularioActividad(actividad : any) : Observable<any> {
    return this.http.post<any>( environment.apiUrl+"bitacora/guardarDetBitacora", actividad);
  }

  finalizarBitacora(json : any) : Observable<any> {
    return this.http.post<any>( environment.apiUrl+"bitacora/finalizarBitacora", json);
  }
  //Admin
  indexAdmin(rows : any) {
    return this.http.get<any>(environment.apiUrl+"bitacora/admin/index");
  }

  obtenerBitacoraPorId(id_bitacora : any) {
    return this.http.get<any>(environment.apiUrl+"bitacora/admin/obtenerBitacoraPorId/"+id_bitacora+"/1");
  }

  poblarFiltros() {
    return this.http.get<any>(environment.apiUrl+"bitacora/admin/poblarFiltros");
  }

  obtenerResultadoBusqueda(json : any) {
    return this.http.post<any>(environment.apiUrl+"bitacora/admin/obtenerResultadoBusqueda", json);
  }

  //Generales
  generarReporteBitacora(json : any) {
    return this.http.post<any>(environment.apiUrl+"bitacora/reporte/generarReporteBitacora", json);
  }
}