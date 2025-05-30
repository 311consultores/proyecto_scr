import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, map, Observable, tap, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class BitacoraService {
  constructor(private http: HttpClient) {}

  index() {
    return this.http.get<any>(environment.apiUrl + 'bitacora/index');
  }

  recuperarFolio(json: any) {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/recuperarFolio',
      json
    );
  }

  getReports(): Observable<any> {
    return this.http.get<any>(
      environment.apiUrl + 'reportes/getReportesEvidencia'
    );
  }

  enviarFormularioBitacora(bitacora: any): Observable<any> {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/guardarBitacora',
      bitacora
    );
  }

  subirFotoTemp(fotografia: any): Observable<any> {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/subirFotoTemp',
      fotografia
    );
  }

  eliminarFotoTemp(fotografia: any): Observable<any> {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/eliminarFotoTemp',
      fotografia
    );
  }

  enviarFormularioActividad(actividad: any): Observable<any> {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/guardarDetBitacora',
      actividad
    );
  }

  finalizarBitacora(json: any): Observable<any> {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/finalizarBitacora',
      json
    );
  }
  //Admin
  indexAdmin(rows: any) {
    return this.http.get<any>(
      environment.apiUrl + 'bitacora/admin/index/' + rows
    );
  }

  obtenerBitacoraPorId(id_bitacora: any) {
    return this.http.get<any>(
      environment.apiUrl +
        'bitacora/admin/obtenerBitacoraPorId/' +
        id_bitacora +
        '/1'
    );
  }

  poblarFiltros() {
    return this.http.get<any>(
      environment.apiUrl + 'bitacora/admin/poblarFiltros'
    );
  }

  obtenerResultadoBusqueda(json: any) {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/admin/obtenerResultadoBusqueda',
      json
    );
  }

  getPreview(json : any) {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/obtenerPrevisualizacion',
      json
    );
  }

  //Generales
  generarReporteBitacora(json: any) {
    return this.http.post<any>(
      environment.apiUrl + 'bitacora/reporte/generarReporteBitacora',
      json
    );
  }
}
