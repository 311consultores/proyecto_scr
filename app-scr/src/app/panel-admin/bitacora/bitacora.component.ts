import { Component } from '@angular/core';
import { BitacoraService } from '../../core/services/bitacora.service';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import { LoadingModalComponent } from '../../shared/components/loading-modal/loading-modal.component';
import * as LZString from 'lz-string';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faFilter, faFilterCircleXmark } from '@fortawesome/free-solid-svg-icons';
import { FormsModule } from '@angular/forms';
import { AutocompleteComponent } from '../../shared/components/autocomplete/autocomplete.component';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-bitacora',
  imports: [
    FormsModule,
    FontAwesomeModule,
    LoadingModalComponent,
    AutocompleteComponent
  ],
  templateUrl: './bitacora.component.html',
  styleUrl: './bitacora.component.scss'
})
export class BitacoraComponent {

  $table: any[] = [];
  public isLoading = false;
  public filtros = {
    rows : 15,
    data : Array(),
    fechaIncial: Date,
    fechaFinal: Date
  };
  public loading = true;

  constructor(
    private library: FaIconLibrary,
    private router : Router,
    private _bitacoraService: BitacoraService
  ) {
    this.library.addIcons(faFilter, faFilterCircleXmark);
  }

  ngOnInit() {
    this.cargaInicial();
  }

  async cargaInicial() {
    this._bitacoraService.poblarFiltros()
    .subscribe({
      next: (response) => {
        if(response.ok) {
          this.filtros.data = [
            ...response.data.sitios,
            ...response.data.clientes,
            ...response.data.registros
          ];
          return;
        }
        alert(response.message);
      }
    });
    this.getResults();
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

  getResults() {
    this._bitacoraService.indexAdmin(this.filtros.rows)
    .pipe(
      finalize(() =>  {
        this.loading = false;
      })
    )
    .subscribe({
      next: (response) => {
        if(response.ok) {
          this.$table = response.data;
          return;
        }
        alert(response.data);
      },
      error: (error) => alert(error)
    });
  }

  // Buscador
  busquedaSeleccionada(result : any) {
    this.loading = true;
    result.rows = this.filtros.rows;
    this._bitacoraService.obtenerResultadoBusqueda(result)
    .pipe(
      finalize(() =>  {
        this.loading = false;
      })
    )
    .subscribe({
      next: (response) => {
        if(response.ok) {
          this.$table = response.data;
          return;
        }
        Swal.fire("Ha sucedio un problema", response.message, "warning");
      }
    })
  }

  limpiarBusqueda() {
    this.loading = true;
    this.getResults();
  }

  //#region [Métodos privados]
  aplicarFiltros() {
    // Filtrar el arreglo
    // const bitacorasFiltradas = this.$table.filter((bitacora : any) => {
    //   const bitacoraDate = this.parseCustomDate(bitacora.fecha);
    //   return bitacoraDate >= this.filtros.fechaIncial && bitacoraDate <= this.filtros.fechaFinal;
    // });
  }

  parseCustomDate(dateString: string): Date {
    let months = {
      'ENE': 0, 'FEB': 1, 'MAR': 2, 'ABR': 3, 'MAY': 4, 'JUN': 5,
      'JUL': 6, 'AGO': 7, 'SEP': 8, 'OCT': 9, 'NOV': 10, 'DIC': 11
    };

    let [day, month, year] = dateString.split(' ');
    return new Date(Number(year), months[month as keyof typeof months], Number(day));
  }
  //#endregion
}
