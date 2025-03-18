import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
import { BitacoraService } from '../../core/services/bitacora.service';
import { WebcamComponent } from '../../shared/components/webcam/webcam.component';
import { AlertaComponent } from '../../shared/components/alerta/alerta.component';
import { GaleriaComponent } from '../../shared/components/galeria/galeria.component';
import {
  FaIconLibrary,
  FontAwesomeModule,
} from '@fortawesome/angular-fontawesome';
import {
  faRotate,
  faCamera,
  faImages,
  faCirclePlus,
} from '@fortawesome/free-solid-svg-icons';
import Swal from 'sweetalert2';
import * as LZString from 'lz-string';
import { LoadingModalComponent } from '../../shared/components/loading-modal/loading-modal.component';
@Component({
  selector: 'app-bitacora-actividad',
  imports: [
    FormsModule,
    CommonModule,
    AlertaComponent,
    WebcamComponent,
    GaleriaComponent,
    FontAwesomeModule,
    LoadingModalComponent,
  ],
  templateUrl: './bitacora-actividad.component.html',
  styleUrl: './bitacora-actividad.component.scss',
})
export class BitacoraActividadComponent {
  public session = localStorage.getItem('token') || null;
  public catalogos: any = { clientes: [], sitios: [] };
  public cache: any = {
    bitacora: {
      id_bitacora: null,
      tipo_bitacora: 1,
      titulo: '',
      folio_reporte: '',
      proyecto: '',
      responsable: '',
      sitio_id: 0,
      sitio: '',
      cliente_id: 0,
      fecha: this.formatearFecha(new Date()),
      bFinalizado: 0,
    },
    actividades: [],
  };
  public funciones = {
    alerta: {
      bShow: 'false',
      sMessage: '',
      sTipo: 'success',
    },
    bLoad: false,
  };
  isWebcamModalOpen: boolean = false;
  currentFotografias: string[] = []; // Fotografías capturadas actualmente
  currentActivityIndex: number | null = null; // Índice de la actividad actual
  public isLoading = false;

  constructor(
    private library: FaIconLibrary,
    private _bitacoraService: BitacoraService
  ) {
    library.addIcons(faRotate, faCamera, faImages, faCirclePlus);
  }

  ngOnInit() {
    this.cargarBitacora();
  }

  cargarBitacora() {
    if (localStorage.getItem('data_cache') != null) {
      this.cache = JSON.parse(
        LZString.decompress(localStorage.getItem('data_cache') + '')
      );
    }
    console.log(this.cache);
    this._bitacoraService.index().subscribe({
      next: (response) => {
        this.catalogos = response.data;
      },
    });
  }

  async enviarFormularioBitacora() {
    this.funciones.bLoad = true;
    this._bitacoraService
      .enviarFormularioBitacora(this.cache.bitacora)
      .pipe(
        finalize(() => {
          this.funciones.bLoad = false;
        })
      )
      .subscribe({
        next: (response) => {
          if (response.ok) {
            this.funciones.alerta.bShow = 'true';
            this.funciones.alerta.sMessage = response.data;
            this.funciones.alerta.sTipo = 'success';
            setTimeout(() => {
              this.cache.bitacora.titulo =
                this.cache.bitacora.folio_reporte +
                ' - ' +
                this.cache.bitacora.proyecto;
              if (!response.bUpdate) {
                this.cache.bitacora.id_bitacora = response.id;
                this.nuevaActividad();
              }
              this.actualizarCache();
              this.funciones.alerta.bShow = 'false';
            }, 1000);
          } else {
            this.funciones.alerta.bShow = 'true';
            this.funciones.alerta.sMessage = response.message;
            this.funciones.alerta.sTipo = 'danger';
          }
        },
      });
  }

  async enviarFormularioActividad(data: any, index: number) {
    data.bitacora_id = this.cache.bitacora.id_bitacora;
    this.funciones.bLoad = true;
    this._bitacoraService
      .enviarFormularioActividad(data)
      .pipe(
        finalize(() => {
          this.funciones.bLoad = false;
        })
      )
      .subscribe({
        next: (response) => {
          if (response.ok) {
            this.cache.actividades[index].alerta = 'true';
            this.funciones.alerta.sMessage = response.data;
            this.funciones.alerta.sTipo = 'success';
            setTimeout(() => {
              this.cache.actividades[index].titulo =
                this.cache.actividades[index].orden_trabajo;
              if (!response.bUpdate) {
                this.cache.actividades[index].id_actividad = response.id;
                this.nuevaActividad();
              }
              this.cache.actividades[index].alerta = 'false';
              this.actualizarCache();
            }, 2000);
          } else {
            this.cache.actividades[index].alerta = 'true';
            this.funciones.alerta.sMessage = response.message;
            this.funciones.alerta.sTipo = 'danger';
          }
        },
      });
  }

  async recuperarFolio() {
    this._bitacoraService
      .recuperarFolio({
        cliente_id: this.cache.bitacora.cliente_id,
      })
      .subscribe({
        next: (response) => {
          if (response.ok) {
            this.cache.bitacora.folio_reporte = response.data.folio;
            this.actualizarCache();
            return;
          }
          this.funciones.alerta.bShow = 'true';
          this.funciones.alerta.sMessage = response.message;
          this.funciones.alerta.sTipo = 'danger';
        },
      });
  }

  finalizarCaptura() {
    this._bitacoraService
      .finalizarBitacora({
        id_bitacora: this.cache.bitacora.id_bitacora,
      })
      .subscribe({
        next: (response) => {
          if (response.ok) {
            Swal.fire({
              title: 'Has finalizado la captura de tu actividad con exito',
              text: '¿Que deseas hacer?',
              icon: 'success',
              showCancelButton: true,
              cancelButtonText: 'Cerrar',
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Descargar Reporte',
              allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
              allowEscapeKey: false, // Evita que se cierre al presionar ESC
              allowEnterKey: false, // Evita que se active el botón con Enter
            }).then((result) => {
              if (result.isConfirmed) {
                this._bitacoraService
                  .generarReporteBitacora({
                    id_bitacora: this.cache.bitacora.id_bitacora,
                  })
                  .subscribe({
                    next: (response) => {
                      if (response.ok) {
                        // Decodificar el Base64 a un array de bytes
                        const byteCharacters = atob(response.data); // Decodifica el Base64
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                          byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        // Crear un Blob con el array de bytes
                        const blob = new Blob([byteArray], {
                          type: 'application/pdf',
                        });
                        // Generar una URL temporal para el Blob
                        const url = window.URL.createObjectURL(blob);
                        // Abrir la URL en una nueva pestaña
                        window.open(url, '_blank');
                        // Liberar la URL temporal cuando ya no sea necesaria
                        window.URL.revokeObjectURL(url);
                        localStorage.removeItem('data_cache');
                        location.reload();
                      } else {
                        Swal.fire(
                          'Ha ocurrido un problema',
                          'No se ha podido generar el documento, intentelo de nuevo',
                          'warning'
                        );
                      }
                    },
                  });
              } else {
                localStorage.removeItem('data_cache');
                location.reload();
              }
            });
            return;
          }
          Swal.fire('Ha sucedio un problema', response.message, 'warning');
        },
      });
  }

  cerrar() {
    localStorage.removeItem('data_cache');
    window.close();
  }

  nuevaActividad() {
    this.cache.actividades.push({
      id_actividad: 0,
      titulo: '',
      orden_trabajo: '',
      equipo: '',
      observacion: '',
      fotografias: [],
      alerta: 'false',
    });
  }

  abrirInput(index: number) {
    const inputFile = document.getElementById(
      'fileInput' + index
    ) as HTMLInputElement;
    if (inputFile) {
      inputFile.click(); // Simula el clic en el input oculto
    }
  }

  subirImagenes(event: any, index: number) {
    const archivos = event.target.files;
    if (archivos.length > 0) {
      for (let archivo of archivos) {
        const reader = new FileReader();
        reader.onload = (e: any) => {
          // Crear imagen desde el archivo
          const img = new Image();
          img.src = e.target.result;

          img.onload = () => {
            // Crear un canvas para convertir la imagen
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            if (!context) {
              console.error(
                'Error: No se pudo obtener el contexto del canvas.'
              );
              return;
            }

            // Establecer tamaño del canvas igual a la imagen original
            canvas.width = img.width;
            canvas.height = img.height;

            // Dibujar la imagen en el canvas
            context.drawImage(img, 0, 0, img.width, img.height);

            // Convertir a JPEG (sin interlazado)
            const fotoConvertida = canvas.toDataURL('image/jpeg', 0.9); // 90% calidad

            this.isLoading = true;
            this._bitacoraService
              .subirFotoTemp({
                fotografia: fotoConvertida,
              })
              .subscribe({
                next: (response) => {
                  if (response.ok) {
                    this.isLoading = false;
                    this.cache.actividades[index].fotografias.push(
                      response.data
                    );
                    this.actualizarCache();
                  }
                },
              });
          };
        };
        reader.readAsDataURL(archivo);
      }
    }
  }

  //#region [Metodos Privados]
  //Eliminar Foto Event
  deleteFoto(index: number, event: string[]) {
    this.cache.actividades[index].fotografias = event;
    this.actualizarCache();
  }

  setSitio() {
    let sitio = this.catalogos.sitios.filter(
      (x: any) => x.id_sitio == this.cache.bitacora.sitio_id
    );
    this.cache.bitacora.sitio = sitio[0].sitio;
    this.actualizarCache();
  }

  formatearFecha(fecha: Date): string {
    return new Date(fecha.getTime() - fecha.getTimezoneOffset() * 60000)
      .toISOString()
      .split('T')[0];
  }

  saltoLinea(index: any, event: Event) {
    // Obtener el textarea correspondiente
    const textarea = event.target as HTMLTextAreaElement;
    // Obtener el valor actual del textarea
    let texto = this.cache.actividades[index].observacion;
    if (texto.includes(';')) {
      texto = texto.replace(/;/g, '\n');
      this.cache.actividades[index].observacion = texto;
      textarea.value = texto;
    }
  }

  // Abrir la cámara para una actividad específica
  openWebcamForActivity(index: number) {
    this.currentActivityIndex = index;
    this.currentFotografias = this.cache.actividades[index].fotografias || [];
    this.isWebcamModalOpen = true; // Abrir el modal
  }

  // Cuando se capturan fotos, asignarlas a la actividad actual
  onPhotosCaptured(photos: string[]) {
    if (this.currentActivityIndex !== null) {
      this.cache.actividades[this.currentActivityIndex].fotografias = photos;
      this.actualizarCache();
    }
  }

  // Cerrar el modal
  onWebcamModalClosed() {
    this.isWebcamModalOpen = false;
    this.currentActivityIndex = null; // Limpiar el índice de la actividad
  }

  //Almacenar en chache
  actualizarCache() {
    let json = LZString.compress(JSON.stringify(this.cache));
    localStorage.setItem('data_cache', json);
  }
  //#endregion
}
