import { Component } from '@angular/core';
import { BitacoraService } from '../../core/services/bitacora.service';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import { LoadingModalComponent } from '../../shared/components/loading-modal/loading-modal.component';
import * as LZString from 'lz-string';
import {
  FaIconLibrary,
  FontAwesomeModule,
} from '@fortawesome/angular-fontawesome';
import {
  faEye,
  faFilePdf,
  faFilePen,
  faFilter,
  faFilterCircleXmark,
  faPen,
  faTableCells,
  faTableCellsLarge,
  faTableList,
} from '@fortawesome/free-solid-svg-icons';
import { FormsModule } from '@angular/forms';
import { AutocompleteComponent } from '../../shared/components/autocomplete/autocomplete.component';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-bitacora',
  imports: [
    FormsModule,
    FontAwesomeModule,
    LoadingModalComponent,
    AutocompleteComponent,
  ],
  templateUrl: './bitacora.component.html',
  styleUrl: './bitacora.component.scss',
})
export class BitacoraComponent {
  $table: any[] = [];
  $tableFiltered: any[] = [];
  public isLoading = false;
  public filtros = {
    rows: 15,
    data: Array(),
    fechaIncial: '',
    fechaFinal: '',
    status: '1',
    bFiltros: false,
  };
  public tipo_show = 0;
  public loading = true;

  constructor(
    private library: FaIconLibrary,
    private router: Router,
    private _bitacoraService: BitacoraService
  ) {
    this.library.addIcons(
      faFilter,
      faFilterCircleXmark,
      faTableList,
      faTableCellsLarge,
      faFilePen,
      faFilePdf
    );
  }

  ngOnInit() {
    this.cargaInicial();
  }

  async cargaInicial() {
    this._bitacoraService.poblarFiltros().subscribe({
      next: (response) => {
        if (response.ok) {
          this.filtros.data = [
            ...response.data.sitios,
            ...response.data.clientes,
            ...response.data.registros,
          ];
          return;
        }
        alert(response.message);
      },
    });
    this.getResults();
  }

  editarBitacora(id: any) {
    this.isLoading = true;
    this._bitacoraService.obtenerBitacoraPorId(id).subscribe({
      next: (response) => {
        if (response.ok) {
          const url = this.router.createUrlTree(['/bitacora']).toString();
          // Abrir la URL en una nueva pestaña
          let json = LZString.compress(JSON.stringify(response.data));
          this.isLoading = false;
          localStorage.setItem('data_cache', json);
          window.open(url, '_blank');
        } else {
          this.isLoading = false;
          Swal.fire('Ha ocurrido un problema', response.message, 'warning');
        }
      },
    });
  }

  getPDF(id: number) {
    this.isLoading = true;
    this._bitacoraService
      .generarReporteBitacora({ id_bitacora: id })
      .subscribe({
        next: (response) => {
          if (response.ok) {
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
          } else {
            this.isLoading = false;
            Swal.fire('Ha ocurrido un problema', response.message, 'warning');
          }
        },
      });
  }

  getResults() {
    this._bitacoraService
      .indexAdmin(this.filtros.rows)
      .pipe(
        finalize(() => {
          this.loading = false;
        })
      )
      .subscribe({
        next: (response) => {
          if (response.ok) {
            this.$table = response.data;
            this.$tableFiltered = response.data;
            return;
          }
          alert(response.data);
        },
        error: (error) => alert(error),
      });
  }

  // Buscador
  busquedaSeleccionada(result: any) {
    this.loading = true;
    result.rows = this.filtros.rows;
    this._bitacoraService
      .obtenerResultadoBusqueda(result)
      .pipe(
        finalize(() => {
          this.loading = false;
        })
      )
      .subscribe({
        next: (response) => {
          if (response.ok) {
            this.$table = response.data;
            this.$tableFiltered = response.data;
            return;
          }
          Swal.fire('Ha sucedio un problema', response.message, 'warning');
        },
      });
  }

  limpiarBusqueda() {
    this.loading = true;
    this.getResults();
  }

  //#region [Métodos privados]
  aplicarFiltros() {
    this.removeFiltros();
    if (this.filtros.fechaIncial != '' && this.filtros.fechaFinal != '') {
      const startDate = this.formatDate(new Date(this.filtros.fechaIncial));
      const endDate = this.formatDate(new Date(this.filtros.fechaFinal));
      this.$table = this.$table.filter((bitacora: any) => {
        const bitacoraDate = this.formatDate(
          this.parseCustomDate(bitacora.fecha)
        );
        console.log(startDate + '=>' + bitacoraDate + '<=' + endDate);
        return bitacoraDate >= startDate && bitacoraDate <= endDate;
      });
    }
    this.$table = this.$table.filter((bitacora: any) => {
      switch (this.filtros.status) {
        case '1':
          return true;
        case '2':
          return bitacora.detalles.length > 0;
        case '3':
          return bitacora.detalles.length == 0;
        case '4':
          return bitacora.bFinalizado;
      }
    });
    this.filtros.fechaFinal = '';
    this.filtros.fechaIncial = '';
    this.filtros.status = '1';
    this.filtros.bFiltros = true;
  }

  removeFiltros() {
    this.$table = [];
    this.$tableFiltered.forEach((element: any) => {
      this.$table.push(element);
    });
    this.filtros.bFiltros = false;
  }

  parseCustomDate(dateString: string): Date {
    let months = {
      ENE: 0,
      FEB: 1,
      MAR: 2,
      ABR: 3,
      MAY: 4,
      JUN: 5,
      JUL: 6,
      AGO: 7,
      SEP: 8,
      OCT: 9,
      NOV: 10,
      DIC: 11,
    };

    let [day, month, year] = dateString.split(' ');
    return new Date(
      Date.UTC(Number(year), months[month as keyof typeof months], Number(day))
    );
  }

  formatDate(date: Date): string {
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    return `${day}-${month}-${year}`;
  }
  //#endregion
}
