import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
import { BitacoraService } from '../../core/services/bitacora.service';
import { WebcamComponent } from '../../shared/components/webcam/webcam.component';
import { AlertaComponent } from '../../shared/components/alerta/alerta.component';
import { GaleriaComponent } from '../../shared/components/galeria/galeria.component';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faRotate, faCamera } from '@fortawesome/free-solid-svg-icons';
import Swal from 'sweetalert2';
import * as LZString from 'lz-string';

interface Actividad {
  id_actividad?: number,
  titulo : string,
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
    titulo: "",
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
  };
  isWebcamModalOpen: boolean = false;
  currentFotografias: string[] = []; // Fotografías capturadas actualmente
  currentActivityIndex: number | null = null; // Índice de la actividad actual

  constructor (
      private library: FaIconLibrary,
      private _bitacoraService: BitacoraService ) 
    { 
      library.addIcons(faRotate, faCamera);
    }

  ngOnInit() {
    this.cargarBitacora();
  }

  cargarBitacora() {
    if(localStorage.getItem("data-bitacora") != null) {
      this.bitacora = JSON.parse(LZString.decompress(localStorage.getItem("data-bitacora")+""));
      if(this.bitacora.actividades.length == 0) {
        this.nuevaActividad();
      }
    }
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
        if(response.ok) {
          this.funciones.alerta.bShow = "true";
          this.funciones.alerta.sMessage=response.data;
          this.funciones.alerta.sTipo = "success";
          setTimeout(()=> {
            this.bitacora.titulo = this.bitacora.folio_reporte + " - ";
            this.bitacora.titulo += this.bitacora.bSitio ? this.bitacora.sitio : this.bitacora.proyecto;
            if(!response.bUpdate) {
              this.bitacora.id_bitacora = response.id;
              this.nuevaActividad();
            }
            let json = LZString.compress(JSON.stringify(this.bitacora));
            localStorage.setItem("data-bitacora",json);
            this.funciones.alerta.bShow = "false";
          },2000);
        } else {
          this.funciones.alerta.bShow = "true";
          this.funciones.alerta.sMessage= response.message;
          this.funciones.alerta.sTipo = "danger";
        }
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
        if(response.ok) {
          this.bitacora.actividades[index].alerta = "true";
          this.funciones.alerta.sMessage=response.data;
          this.funciones.alerta.sTipo = "success";
          setTimeout(()=> {
            this.bitacora.actividades[index].titulo =  this.bitacora.actividades[index].orden_trabajo;
            if(!response.bUpdate) {
              this.bitacora.actividades[index].id_actividad = response.id;
              this.nuevaActividad();
            }
            this.bitacora.actividades[index].alerta = "false";
            let json = LZString.compress(JSON.stringify(this.bitacora));
            localStorage.setItem("data-bitacora",json);
          },2000);
        } else {
          this.bitacora.actividades[index].alerta = "true";
          this.funciones.alerta.sMessage= response.message;
          this.funciones.alerta.sTipo = "danger";
        }        
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

  finalizarCaptura() {
    this._bitacoraService.finalizarBitacora({
      id_bitacora : this.bitacora.id_bitacora
    }).subscribe({
      next: (response) => {
        if(response.ok) {
          Swal.fire({
            title: "Has finalizado la captura de tu actividad con exito",
            text: "¿Que deseas hacer?",
            icon: "success",
            showCancelButton: true,
            cancelButtonText: "Cerrar",
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Descargar Reporte",
            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
            allowEscapeKey: false,    // Evita que se cierre al presionar ESC
            allowEnterKey: false,     // Evita que se active el botón con Enter
          }).then((result) => {
            if (result.isConfirmed) {
              this._bitacoraService.generarReporteBitacora({ id_bitacora : this.bitacora.id_bitacora })
              .subscribe({
                next: (response) => {
                  if(response.ok) {
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
                    localStorage.removeItem("data-bitacora");
                    location.reload();
                  }
                }
              });
            } else {
              localStorage.removeItem("data-bitacora");
              location.reload();
            }
          });
          return;
        }
        Swal.fire("Ha sucedio un problema", response.message, "warning");
      }
    });
  }

  nuevaActividad() {
    this.bitacora.actividades.push({
      id_actividad: 0,
      titulo : "",
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

  // Abrir la cámara para una actividad específica
  openWebcamForActivity(index: number) {
    this.currentActivityIndex = index;
    this.currentFotografias = this.bitacora.actividades[index].fotografias || [];
    this.isWebcamModalOpen = true; // Abrir el modal
  }

  // Cuando se capturan fotos, asignarlas a la actividad actual
  onPhotosCaptured(photos: string[]) {
    if (this.currentActivityIndex !== null) {
        this.bitacora.actividades[this.currentActivityIndex].fotografias = photos;
    }
  }

  // Cerrar el modal
  onWebcamModalClosed() {
    this.isWebcamModalOpen = false;
    this.currentActivityIndex = null; // Limpiar el índice de la actividad
  }
  //#endregion

}
