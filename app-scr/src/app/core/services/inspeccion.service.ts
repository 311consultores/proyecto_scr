import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, map, Observable, tap, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class InspeccionesService {


  constructor(private http: HttpClient) {   }

  getReports() : Observable<any> {
    let url = environment.apiUrl+"admin/getReports";
    return this.http.get<any>( url );
  }
  
}