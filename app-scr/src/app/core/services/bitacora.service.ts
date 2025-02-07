import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, map, Observable, tap, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BitacoraService {


  constructor(private http: HttpClient) {   }

  getReports(): Observable<any> {
    return this.http.get<any>(environment.apiUrl+"reportes/getReportesEvidencia");
  }

  enviarFormularioBitacora(bitacora : any): Observable<any> {
    return this.http.post<any>(environment.apiUrl+"bitacora/guardarBitacora", bitacora);
  }
  
  enviarFormularioActividad(actividad : any) : Observable<any> {
    return this.http.post<any>( environment.apiUrl+"bitacora/guardarDetBitacora", actividad);
  }
}