import { ChangeDetectorRef, Component, EventEmitter, Input, Output, ViewChild, ElementRef, AfterViewInit, OnChanges, SimpleChanges } from '@angular/core';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faCamera, faCheck, faXmark, faCircleXmark, faCameraRotate } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-webcam',
  imports: [
    FontAwesomeModule
  ],
  templateUrl: './webcam.component.html',
  styleUrl: './webcam.component.scss'
})
export class WebcamComponent implements OnChanges {
  fotografias: any;
  @Input() isModalOpen: boolean = false;
  @Input() images: string[] = [];
  @Output() photosEvent = new EventEmitter<string[]>();
  @Output() modalClosed = new EventEmitter<void>();
  videoStream: MediaStream | null = null; // Almacena el stream de video
  @ViewChild('videoElement') videoElementRef!: ElementRef<HTMLVideoElement>; // Referencia al elemento <video>
  modal = false;
  isTakingPhoto = false;
  iCont = 0;
  currentDeviceId: string | null = null; // Almacena el ID del dispositivo actual
  devices: MediaDeviceInfo[] = []; // Lista de dispositivos de video disponibles

  constructor(
    library: FaIconLibrary,
    private cdr: ChangeDetectorRef
  ) {
    library.addIcons(faCamera, faCheck, faXmark, faCircleXmark, faCameraRotate);
  }

  m_inicia_captura() {
    this.startWebcam();
  }

  m_finaliza_captura() {
    this.photosEvent.emit(this.fotografias);
    this.stopWebcam();
  }

  async startWebcam() {
    try {
      this.fotografias = this.images;
      this.iCont = this.fotografias.length;
  
      // Iniciar con la cámara trasera
      const constraints: MediaStreamConstraints = {
        video: {
          aspectRatio: 9 / 16,
          width: { min: 1280, ideal: 1920 }, // Resolución mínima e ideal
          height: { min: 720, ideal: 1080 }, // Resolución mínima e ideal
          facingMode: 'environment'
        },
        audio: false,
      };
  
      this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
  
      // Asignar el stream al elemento <video>
      if (this.videoElementRef && this.videoElementRef.nativeElement) {
        this.videoElementRef.nativeElement.srcObject = this.videoStream;
      }
    } catch (error) {
      console.error('Error al acceder a la cámara:', error);
    }
  }

  // Cambiar entre cámaras
  async switchCamera(deviceId: string | null) {
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop()); // Detener el stream actual
    }

    try {
      const constraints: MediaStreamConstraints = {
        video: {
          deviceId: deviceId ? { exact: deviceId } : undefined, // Usar el dispositivo específico
          aspectRatio: 9 / 16, // Forzar relación de aspecto vertical
          facingMode: 'environment' // Cámara trasera por defecto
        },
        audio: false,
      };

      this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);

      // Asignar el nuevo stream al elemento <video>
      if (this.videoElementRef && this.videoElementRef.nativeElement) {
        this.videoElementRef.nativeElement.srcObject = this.videoStream;
      }
    } catch (error) {
      console.error('Error al cambiar de cámara:', error);
    }
  }

  // Detener la cámara web
  stopWebcam() {
    this.isModalOpen = false;
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop()); // Detener todas las pistas
    }
    this.iCont = 0;
    this.fotografias = [];
    this.modalClosed.emit();
  }

  deleteLastPhoto() {
    this.fotografias = this.fotografias.slice(0, -1);
    this.iCont--;
  }

  capturarFoto() {
    if (!this.videoElementRef?.nativeElement) return;
  
    const video = this.videoElementRef.nativeElement;
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
  
    if (!context) {
      console.error("Error: No se pudo obtener el contexto del canvas.");
      return;
    }
  
    // Obtener dimensiones originales del video
    const videoWidth = video.videoWidth || video.clientWidth;
    const videoHeight = video.videoHeight || video.clientHeight;
  
    if (!videoWidth || !videoHeight) {
      console.error("Error: No se pudo obtener el tamaño del video.");
      return;
    }
  
    // Establecer tamaño del canvas con las dimensiones originales
    canvas.width = videoWidth;
    canvas.height = videoHeight;
  
    // Dibujar la imagen original en el canvas
    context.drawImage(video, 0, 0, videoWidth, videoHeight);
  
    // Convertir canvas a imagen en formato JPEG
    try {
      const photoUrl = canvas.toDataURL('image/jpeg', 0.9); // Calidad del 90%
      this.fotografias.push(photoUrl);
      this.iCont++;
      this.cdr.detectChanges();
    } catch (error) {
      console.error("Error al generar la imagen:", error);
    }
  }
  
  // Método que se ejecuta cuando cambia una propiedad de entrada
  ngOnChanges(changes: SimpleChanges) {
    if (changes['isModalOpen']) {
        if (this.isModalOpen) {
            this.startWebcam(); // Iniciar la cámara si el modal se abre
        } else {
            this.stopWebcam(); // Detener la cámara si el modal se cierra
        }
    }
  }
}