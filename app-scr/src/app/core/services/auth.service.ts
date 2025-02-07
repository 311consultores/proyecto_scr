import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment.prod';

@Injectable({
  providedIn: 'root'
})
export class AuthService {


  constructor(private http: HttpClient) {   }

  public SERVER_API = environment.apiUrl;

  login(credentials : any): Observable<any>{
    return this.http.post<any>( this.SERVER_API+"admin/login", credentials );
  }

  isLoggedIn() {
    return !!localStorage.getItem('token');
  }
}