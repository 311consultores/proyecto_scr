import { CommonModule, NgIf } from '@angular/common';
import { Component, EventEmitter, Input, Output, SimpleChanges } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { GaleriaComponent } from '../../../../shared/components/galeria/galeria.component';
import { animate, state, style, transition, trigger } from '@angular/animations';
import { Actividad } from '../../../../core/models/biracora.model';
import { BitacoraService } from '../../../../core/services/bitacora.service';
import { WebcamComponent } from '../../../../shared/components/webcam/webcam.component';
import { QuillModule } from 'ngx-quill';

interface ActividadElement {
  tipo : number,
  titulo: string,
  data: Actividad,
  valido: boolean
}
@Component({
  selector: 'app-actividad',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    FormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatDatepickerModule,
    MatCheckboxModule,
    GaleriaComponent,
    WebcamComponent,
    QuillModule
  ],
  templateUrl: './actividad.component.html',
  styleUrl: './actividad.component.scss',
  animations: [
    trigger('collapse', [
      state('collapsed', style({
        height: '0',
        overflow: 'hidden',
        opacity: '0'
      })),
      state('expanded', style({
        height: '*',
        overflow: 'hidden',
        opacity: '1'
      })),
      transition('expanded <=> collapsed', animate('300ms ease-in-out'))
    ])
  ]
})
export class ActividadComponent {

  @Input() _actividad!: ActividadElement;
  @Output() public getActividadElement= new EventEmitter<any>();

  public actividadForm!: FormGroup;

  
  public openedCollapseIndex: number | null = null;  
  public isWebcamModalOpen: boolean = false;
  public tipo_foto = 0;
  public currentFotografias: string[] = []; // Fotografías capturadas actualmente

  constructor(
    private fb : FormBuilder,
    private _bitacoraService: BitacoraService,
    private snackBar: MatSnackBar,
  ) {
    this.initActividad();
  }

  ngOnInit() {
    this.setupConditionalValidation();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['_actividad']) {
      let data = (changes['_actividad'].currentValue as ActividadElement).data;
      this.actividadForm.patchValue(data);
    }
  }

  initActividad() {
    this.actividadForm = this.fb.group({
      equipo: [null],
      horas_funcion: [null, Validators.required],
      no_economico: [null, Validators.required],
      no_serie: [null, Validators.required],
      modelo: [null, Validators.required],
      bEvidencia: [false],
      fotos_ant: [[]],
      fotos_des: [[]],
      descripcion: [null],
      recomendacion: [null],
      bFinalizo: [true],
      motivo: [null]
    });

    this.actividadForm.valueChanges.subscribe((result) => {
      this._actividad.data = result;
      if(this.actividadForm.valid) {
        this._actividad.valido = true;
      } else {
        this._actividad.valido = false;
      }
      this.getActividadElement.emit(this._actividad);
    });
  }

  setupConditionalValidation() {
    const recomendacionControl = this.actividadForm.get('motivo');
    const bFinalizoControl = this.actividadForm.get('bFinalizo');

    bFinalizoControl?.valueChanges.subscribe((isFinalizado: boolean) => {
      if (!isFinalizado) {
        // Si bFinalizo es FALSE, hacer requerido el campo
        recomendacionControl?.setValidators([Validators.required]);
      } else {
        // Si bFinalizo es TRUE, quitar validador
        recomendacionControl?.clearValidators();
      }
      recomendacionControl?.updateValueAndValidity();
    });
  }

  subirImagenes(event: any, tipo: number) {
    const archivos = event.target.files;
    if (archivos.length > 0) {
      for (let archivo of archivos) {
        const reader = new FileReader();

        reader.onload = (e: any) => {
          const img = new Image();
          img.src = e.target.result;

          img.onload = () => {
            const fixedWidth = 640;
            const fixedHeight = 480;

            const canvas = document.createElement('canvas');
            canvas.width = fixedWidth;
            canvas.height = fixedHeight;

            const context = canvas.getContext('2d');
            if (!context) {
              console.error('Error: No se pudo obtener el contexto del canvas.');
              return;
            }

            // Escalar proporcionalmente tipo "contain"
            const ratio = Math.min(fixedWidth / img.width, fixedHeight / img.height);
            const scaledWidth = img.width * ratio;
            const scaledHeight = img.height * ratio;

            const offsetX = (fixedWidth - scaledWidth) / 2;
            const offsetY = (fixedHeight - scaledHeight) / 2;

            context.drawImage(img, offsetX, offsetY, scaledWidth, scaledHeight);

            const fotoConvertida = canvas.toDataURL('image/png', 0.9);

            const loading = this.snackBar.open('Guardando las imágenes...', '', {
              duration: 6000,
            });

            this._bitacoraService
              .subirFotoTemp({ fotografia: fotoConvertida })
              .subscribe({
                next: (response) => {
                  if (response.ok) {
                    loading.dismiss();

                    const fotos =
                      tipo === 1
                        ? this.actividadForm.get('fotos_ant')?.value || []
                        : this.actividadForm.get('fotos_des')?.value || [];

                    const newArray = [...fotos, response.data];

                    tipo === 1
                      ? this.actividadForm.get('fotos_ant')?.setValue(newArray)
                      : this.actividadForm.get('fotos_des')?.setValue(newArray);
                  }
                },
              });
          };
        };

        reader.readAsDataURL(archivo);
      }
    }
  }

  deletePhoto(event: any, tipo: number) {
    tipo == 1 ? 
      this.actividadForm.get('fotos_ant')?.setValue(event) :
      this.actividadForm.get('fotos_des')?.setValue(event);
    this.snackBar.open('Imagen eliminada...', '', {
      duration: 3000
    });
  }
  //#region [Camera]
  
  // Abrir la cámara para una actividad específica
  openWebcamForActivity(tipo: number) {
    this.isWebcamModalOpen = true; // Abrir el modal
    this.tipo_foto = tipo;
  }

  // Cuando se capturan fotos, asignarlas a la actividad actual
  onPhotosCaptured(photos: string[]) {
      this.tipo_foto == 1 ? 
      this.actividadForm.get('fotos_ant')?.setValue(photos) :
      this.actividadForm.get('fotos_des')?.setValue(photos);
  }

  // Cerrar el modal
  onWebcamModalClosed() {
    this.isWebcamModalOpen = false;
  }
  //#endregion
  //#region [Collapse]
  isCollapsed(index: number): boolean {
    return this.openedCollapseIndex !== index;
  }
  
  toggleCollapse(index: number): void {
    if (this.openedCollapseIndex === index) {
      this.openedCollapseIndex = null; // Cierra el collapse si ya está abierto
    } else {
      this.openedCollapseIndex = index; // Abre el nuevo collapse y cierra cualquier otro
    }
  }
  //#endregion
}
