import { ChangeDetectorRef, Component, EventEmitter, Input, Output, ViewChild, ElementRef, AfterViewInit } from '@angular/core';
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
export class WebcamComponent implements AfterViewInit {
  fotografias: any;
  @Input() images: string[] = [];
  @Output() photosEvent = new EventEmitter<string[]>();
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

  // Iniciar la cámara web
  async startWebcam() {
    try {
      this.fotografias = this.images;
      this.iCont = this.fotografias.length;
      this.modal = true;

      // Obtener los dispositivos de video disponibles
      this.devices = (await navigator.mediaDevices.enumerateDevices()).filter(
        (device) => device.kind === 'videoinput'
      );

      // Iniciar con la cámara trasera (o la primera disponible)
      this.currentDeviceId = this.devices[0]?.deviceId || null;
      await this.switchCamera(this.currentDeviceId);
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
          width: { ideal: 1280 }, // Resolución ideal
          height: { ideal: 720 }, // Resolución ideal
          facingMode: deviceId ? undefined : { ideal: 'environment' } // Cámara trasera por defecto
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

  // Voltear la cámara
  async toggleCamera() {
    if (this.devices.length > 1) {
      const currentIndex = this.devices.findIndex(
        (device) => device.deviceId === this.currentDeviceId
      );
      const nextIndex = (currentIndex + 1) % this.devices.length; // Alternar entre cámaras
      this.currentDeviceId = this.devices[nextIndex].deviceId;
      await this.switchCamera(this.currentDeviceId);
    }
  }

  // Detener la cámara web
  stopWebcam() {
    this.modal = false;
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop()); // Detener todas las pistas
    }
    this.iCont = 0;
    this.fotografias = [];
  }

  deleteLastPhoto() {
    this.fotografias = this.fotografias.slice(0, -1);
    this.iCont--;
  }

  capturarFoto() {
    this.isTakingPhoto = true;
    setTimeout(() => {
      this.isTakingPhoto = false;
    }, 500); // Duración de la animación
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    if (this.videoElementRef && this.videoElementRef.nativeElement && context) {
      canvas.width = this.videoElementRef.nativeElement.videoWidth;
      canvas.height = this.videoElementRef.nativeElement.videoHeight;
      context.drawImage(this.videoElementRef.nativeElement, 0, 0, canvas.width, canvas.height);
      const photoUrl = canvas.toDataURL('image/png');
      this.fotografias.push(photoUrl);
      this.cdr.detectChanges();
      this.iCont++;
    }
  }

  // Asegurarse de que el elemento <video> esté listo
  ngAfterViewInit() {
    if (this.videoElementRef && this.videoElementRef.nativeElement) {
      console.log('Elemento <video> listo');
    }
  }
}