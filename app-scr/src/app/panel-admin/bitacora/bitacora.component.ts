import { Component, Directive } from '@angular/core';
import { BitacoraService } from '../../core/services/bitacora.service';
import { finalize } from 'rxjs';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-bitacora',
  imports: [],
  templateUrl: './bitacora.component.html',
  styleUrl: './bitacora.component.scss'
})
export class BitacoraComponent {

  $table: any[] = [];

  constructor(
    private _bitacoraService: BitacoraService
  ) {}

  ngOnInit() {
    this.cargaInicial();
  }

  async cargaInicial() {
    this._bitacoraService.indexAdmin()
    .subscribe({
      next: (response) => {
        if(response.ok) {
          this.$table = response.data;
          return;
        }
        alert(response.data);
      },
      error: (error) => alert(error)
    })
  }

  getPDF(id : number){
    window.open(environment.apiUrl+"reportes/getPDFReportEvidencia/"+id, '_blank');
  }
}
