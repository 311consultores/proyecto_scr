import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
import { BitacoraService } from '../../core/services/bitacora.service';
import { WebcamComponent } from '../../shared/components/webcam/webcam.component';
import { AlertaComponent } from '../../shared/components/alerta/alerta.component';
import { GaleriaComponent } from '../../shared/components/galeria/galeria.component';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faRotate } from '@fortawesome/free-solid-svg-icons';
import Swal from 'sweetalert2';

interface Actividad {
  id_actividad?: number,
  orden_trabajo: string,
  equipo: string,
  observacion: string,
  fotografias : string[],
  alerta: string
};
@Component({
  selector: 'app-bitacora-actividad',
  imports: [
    FormsModule,
    CommonModule,
    AlertaComponent,
    WebcamComponent,
    GaleriaComponent,
    FontAwesomeModule
  ],
  templateUrl: './bitacora-actividad.component.html',
  styleUrl: './bitacora-actividad.component.scss'
})
export class BitacoraActividadComponent {
  public catalogos : any = {"clientes" : [], "sitios": []};
  public bitacora = {
    id_bitacora: null,
    folio_reporte: "",
    proyecto: "",
    bSitio: false,
    sitio_id : 0,
    sitio: '',
    cliente_id: 0,
    fecha: this.formatearFecha(new Date()),
    equipo: "",
    actividades: [] as Actividad[]
  };
  public funciones = {
    alerta: {
      bShow: "false",
      sMessage: "",
      sTipo: "success"
    },
    bLoad: false
  }

  constructor (
      private library: FaIconLibrary,
      private _bitacoraService: BitacoraService ) 
    { 
      library.addIcons(faRotate);
    }

  ngOnInit() {
    this.cargarBitacora();
  }

  cargarBitacora() {
    if(localStorage.getItem("data-bitacora") != null)
      this.bitacora = JSON.parse(decodeURIComponent(atob(localStorage.getItem("data-bitacora")+"")));
    this._bitacoraService.index().subscribe({
      next: (response) => {
        this.catalogos = response.data;
      }
    })
  }

  async enviarFormularioBitacora() {
    this.funciones.bLoad = true;
    this._bitacoraService.enviarFormularioBitacora(this.bitacora)
    .pipe(
      finalize(() =>  {
        this.funciones.bLoad = false;
      })
    )
    .subscribe({
      next: (response) => {
        this.funciones.alerta.bShow = "true";
        this.funciones.alerta.sMessage=response.data;
        this.funciones.alerta.sTipo = "success";
        setTimeout(()=> {
          this.bitacora.id_bitacora = this.bitacora.id_bitacora == null ? response.id : null;
          this.nuevaActividad();
          let json = btoa(JSON.stringify(this.bitacora));
          localStorage.setItem("data-bitacora",json);
        },2000);
      },
      error: (error) => {
        this.funciones.alerta.bShow = "true";
        this.funciones.alerta.sMessage=error.error.message;
        this.funciones.alerta.sTipo = "danger";
      }
    });
  }
  
  async enviarFormularioActividad(data : any, index : number) {
    data.bitacora_id = this.bitacora.id_bitacora;
    this.funciones.bLoad = true;
    this._bitacoraService.enviarFormularioActividad(data)
    .pipe(
      finalize(() =>  {
        this.funciones.bLoad = false;
      })
    )
    .subscribe({
      next: (response) => {
        this.bitacora.actividades[index].alerta = "true";
        this.funciones.alerta.sMessage=response.data;
        this.funciones.alerta.sTipo = "success";
        setTimeout(()=> {
          this.bitacora.actividades[index].id_actividad = this.bitacora.actividades[index].id_actividad == 0 ? response.id : 0;
          this.nuevaActividad();
          let json = btoa(JSON.stringify(this.bitacora));
          localStorage.setItem("data-bitacora",json);
        },2000);
      },
      error: (error) => {
        this.bitacora.actividades[index].alerta = "true";
        this.funciones.alerta.sMessage=error.error.message;
        this.funciones.alerta.sTipo = "danger";
      }
    });
  }

  async recuperarFolio() {
    this._bitacoraService.recuperarFolio({
      cliente_id : this.bitacora.cliente_id
    }).subscribe({
      next: (response) => {
        if(response.ok) {
          this.bitacora.folio_reporte = response.data.folio;
          return;
        }
        this.funciones.alerta.bShow = "true";
        this.funciones.alerta.sMessage= response.message;
        this.funciones.alerta.sTipo = "danger";
      }
    });
  }

  nuevaActividad() {
    this.bitacora.actividades.push({
      id_actividad: 0,
      orden_trabajo: "",
      equipo: "",
      observacion: "",
      fotografias: [],
      alerta: 'false'
    });
  }

  //#region [Metodos Privados]
  setSitio() {
    let sitio = this.catalogos.sitios.filter((x: any)=> x.id_sitio == this.bitacora.sitio_id);
    this.bitacora.sitio = sitio[0].sitio;
  }

  formatearFecha(fecha: Date): string {
    return new Date(fecha.getTime() - fecha.getTimezoneOffset() * 60000)
      .toISOString()
      .split('T')[0];
  }

  finalizarCaptura() {
    Swal.fire("Completado", "Has finalizado la captura de tus actividades","success");
    localStorage.removeItem("data-bitacora");
  }

  saltoLinea(index : any, event: Event ) {
    // Obtener el textarea correspondiente
    const textarea = event.target as HTMLTextAreaElement;
    // Obtener el valor actual del textarea
    let texto = this.bitacora.actividades[index].observacion;
    if (texto.includes(';')) {
      texto = texto.replace(/;/g, '\n');
      this.bitacora.actividades[index].observacion = texto;
      textarea.value = texto;
    }
  }
  //#endregion

}
