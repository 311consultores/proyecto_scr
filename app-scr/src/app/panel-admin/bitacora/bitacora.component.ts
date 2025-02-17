import { Component, Directive } from '@angular/core';
import { BitacoraService } from '../../core/services/bitacora.service';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import { LoadingModalComponent } from '../../shared/components/loading-modal/loading-modal.component';
import * as LZString from 'lz-string';

@Component({
  selector: 'app-bitacora',
  imports: [
    LoadingModalComponent
  ],
  templateUrl: './bitacora.component.html',
  styleUrl: './bitacora.component.scss'
})
export class BitacoraComponent {

  $table: any[] = [];
  public isLoading = false;

  constructor(
    private router : Router,
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

  editarBitacora(id : any) {
    this.isLoading = true;
    this._bitacoraService.obtenerBitacoraPorId(id)
    .subscribe({
      next: (response) => {
        if(response.ok) {
          const url = this.router.createUrlTree(['/bitacora']).toString();
          // Abrir la URL en una nueva pestaña
          let json = LZString.compress(JSON.stringify(response.data[0]));
          this.isLoading = false;
          localStorage.setItem("data-bitacora",json);
          window.open(url, '_blank');
        } else {
          Swal.fire("Ha ocurrido un problema",response.message,"warning");
        }
      }
    })
  }

  getPDF(id : number){
    this.isLoading = true;
    this._bitacoraService.generarReporteBitacora({ id_bitacora : id})
    .subscribe({
      next: (response) => {
        if(response.ok) {
          this.isLoading = false;
          // Decodificar el Base64 a un array de bytes
          const byteCharacters = atob(response.data); // Decodifica el Base64
          const byteNumbers = new Array(byteCharacters.length);
          for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
          }
          const byteArray = new Uint8Array(byteNumbers);
          // Crear un Blob con el array de bytes
          const blob = new Blob([byteArray], { type: 'application/pdf' });
          // Generar una URL temporal para el Blob
          const url = window.URL.createObjectURL(blob);
          // Abrir la URL en una nueva pestaña
          window.open(url, '_blank');   
           // Liberar la URL temporal cuando ya no sea necesaria
          window.URL.revokeObjectURL(url);
        }
      }
    })
  }
}
