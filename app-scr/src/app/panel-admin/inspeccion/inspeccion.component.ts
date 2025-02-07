import { Component } from '@angular/core';
import { InspeccionesService } from '../../core/services/inspeccion.service';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-inspeccion',
  imports: [],
  templateUrl: './inspeccion.component.html',
  styleUrl: './inspeccion.component.scss'
})
export class InspeccionComponent {
  public $table: any[] = [];

  constructor(
    private _inspService: InspeccionesService
  ) {}

  ngOnInit() {
    this.cargaInicial();
  }

  async cargaInicial() {
    this._inspService.getReports()
    .pipe(
      finalize(() =>  {
        
      })
    )
    .subscribe({
      next: (response) => {
        if(response.length > 0) {
          this.$table = response;
          return;
        }
        alert(response.data);
      },
      error: (error) => alert(error)
    })
  }
}
