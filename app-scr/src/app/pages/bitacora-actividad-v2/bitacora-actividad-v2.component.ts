import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectChange, MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatStepperModule } from '@angular/material/stepper';
import { MatCheckboxModule } from '@angular/material/checkbox';
import {
  AbstractControl,
  FormArray,
  FormBuilder,
  FormControl,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
  ValidationErrors,
  ValidatorFn,
  Validators
} from '@angular/forms';
import html2canvas from 'html2canvas';
import { MatSnackBar } from '@angular/material/snack-bar';
import { BitacoraTypePipe } from '../../core/pipes/bitacora.pipe';
import { BitacoraService } from '../../core/services/bitacora.service';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { animate, state, style, transition, trigger } from '@angular/animations';
import { ClimaComponent } from '../bitacora-actividad/components/clima/clima.component';
import { BitacoraModel } from '../../core/models/biracora.model';

@Component({
  selector: 'app-bitacora-actividad-v2',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatStepperModule,
    MatCheckboxModule,
    BitacoraTypePipe,
    DragDropModule,
    ClimaComponent,
  ],
  templateUrl: './bitacora-actividad-v2.component.html',
  styleUrl: './bitacora-actividad-v2.component.scss',
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

export class BitacoraActividadV2Component implements OnInit {
  public bitacoraForm!: FormGroup;
  public bContenido= false;
  public tipoBitacoraOptions = [
    { value: 1, label: 'MAI', logo: 'MAI.png' },
    { value: 2, label: 'SCE', logo: 'SCE.png' },
    { value: 3, label: 'MPR', logo: 'MPR.png' }
  ];
  public catalogos: any;
  public showPreview = false;
  public selectedIndex = 0;
  public offset = 0;
  public collapsedStates: boolean[] = [];

  constructor(
    private fb: FormBuilder,
    private snackBar: MatSnackBar,
    private _bitacoraService: BitacoraService
  ) {}

  ngOnInit(): void {
    this.getCache();
    this.getCatalogos();
  }

  initForm() {
    this.bitacoraForm = this.fb.group({
      encabezado: this.fb.nonNullable.group({
        tipo_bitacora: [1, Validators.required],
        titulo: ['Reporte diario de trabajo', Validators.required],
        folio_reporte: ['', Validators.required],
        proyecto: ['', Validators.required],
        responsable: ['', Validators.required],
        sitio_id: [null, Validators.required],
        cliente_id: [null, Validators.required],
        fecha: [new Date(), Validators.required],
        bFinalizado: [false]
      }),
      contenido: this.fb.control([], [this.minLengthArray(1)])
    });

    this.bitacoraForm.valueChanges.subscribe(() => {
      this.updateCache();
    });
  }

  getCatalogos() {
    this._bitacoraService.index().subscribe({
      next: (response) => {
        this.catalogos = response.data;
      },
    });
  }

  getEncabezadoControl<T = any>(controlName: string): FormControl<T> {
    return this.bitacoraForm.get(`encabezado.${controlName}`) as FormControl<T>;
  }

  getFolio(event: MatSelectChange): void  {
    let clienteId = this.catalogos.clientes.find((x : any) => x.cliente.toLowerCase() == event.value.toLowerCase())?.id_cliente;
    this._bitacoraService.recuperarFolio({
      cliente_id: clienteId,
    }).subscribe({
      next: (response) => {
        if (response.ok) {
          this.getEncabezadoControl('folio_reporte').setValue(response.data.folio);
          // this.actualizarCache();
          return;
        }
      },
    });
  }

  addClimaItem(): void {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray, { tipo: 1, climas: [] }];
    this.bitacoraForm.get('contenido')?.setValue(newArray);
  }

  addHorarioItem(): void {
    // this.contenido.push(this.fb.group({
    //   tipo: 2,
    //   categoria: ['', Validators.required],
    //   nombre: ['', Validators.required],
    //   inicio: ['', Validators.required],
    //   fin: ['', Validators.required]
    // }));
  }

  addConsumoItem(): void {
    // this.contenido.push(this.fb.group({
    //   tipo: 3,
    //   kit: [''],
    //   color_ral: [''],
    //   wo: [''],
    //   metros_ejec: [''],
    //   equipo: [''],
    //   entregado: ['']
    // }));
  }

  addActividadItem(): void {
    // this.contenido.push(this.fb.group({
    //   tipo: 4,
    //   equipo: ['', Validators.required],
    //   horometro: [''],
    //   no_serie: [''],
    //   modelo: [''],
    //   ot: [''],
    //   descripcion: ['', Validators.required],
    //   fotografias: this.fb.group({
    //     antes: this.fb.array([]),
    //     despues: this.fb.array([])
    //   })
    // }));
  }

  updateValueContenido(index : number, value : any) {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray];
    newArray[index] = value;
    this.bitacoraForm.get('contenido')?.setValue(newArray);
  }

  setValidate(value: any) {
    this.bContenido = value as boolean;
  }

  minLengthArray(min: number): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value; // Obtiene el array nativo (no FormArray)
      return value && value.length >= min ? null : { minLengthArray: true };
    };
  }

  removeItem(index: number): void {
    this.bitacoraForm.get('contenido')?.value.removeAt(index);
  }

  onSubmit(): void {
    if (this.bitacoraForm.valid) {
      // Mostrar loading
      const loading = this.snackBar.open('Guardando bitácora...', '', {
        duration: 3000
      });
      
      console.log('Bitácora guardada:', this.bitacoraForm.value);
      
      // Simular guardado
      setTimeout(() => {
        loading.dismiss();
        this.snackBar.open('Bitácora guardada correctamente', 'Cerrar', {
          duration: 5000,
          panelClass: ['success-snackbar']
        });
        // this.router.navigate(['/bitacoras']); // Redirigir si es necesario
      }, 2000);
    } else {
      this.markFormGroupTouched(this.bitacoraForm);
      this.snackBar.open('Por favor complete todos los campos requeridos', 'Cerrar', {
        duration: 5000,
        panelClass: ['error-snackbar']
      });
      
      // Enfocar el primer campo con error
      const firstError = document.querySelector('.ng-invalid');
      if (firstError) {
        (firstError as HTMLElement).focus();
      }
    }
  }

  private markFormGroupTouched(formGroup: FormGroup | FormArray): void {
    Object.values(formGroup.controls).forEach(control => {
      control.markAsTouched();
      if (control instanceof FormGroup || control instanceof FormArray) {
        this.markFormGroupTouched(control);
      }
    });
  }

  togglePreview(): void {
    this.showPreview = !this.showPreview;
    if (this.showPreview) this.updatePreview();
  }

  updatePreview(): void {
    // lógica de actualización de preview
  }

  async captureRealPreview(): Promise<void> {
    const element = document.getElementById('pdf-full-preview') || new HTMLElement();
    const canvas = await html2canvas(element, {
      scale: 0.3,
      logging: false,
      useCORS: true
    });
    const previewContent = document.querySelector('.preview-content') || new Element();
    previewContent.innerHTML = '';
    previewContent.appendChild(canvas);
  }

  get encabezadoGroup(): AbstractControl {
    return this.bitacoraForm.get('encabezado')!;
  }
  
  get contenidoGroup(): AbstractControl {
    return this.bitacoraForm.get('contenido')!;
  }
  //#region  [Cache]
  getCache() {
    this.initForm();
    let storage = JSON.parse(localStorage.getItem('data-cache') as string);
    if(storage != null && typeof storage == 'object') {
      this.bitacoraForm.setValue(storage as BitacoraModel);
    }
  }

  updateCache() {
    localStorage.setItem('data-cache', JSON.stringify(this.bitacoraForm.value));
  }

  cleanCache() {
    localStorage.removeItem('data-cache');
  }
  //#endregion
  //#region [Drag and drop]
  drop(event: CdkDragDrop<string[]>) {
    moveItemInArray(this.bitacoraForm.get('contenido')?.value, event.previousIndex, event.currentIndex);
    // Si necesitas mantener sincronizado el array subyacente
    this.bitacoraForm.get('contenido')?.value.updateValueAndValidity();
  }
  //#endregion
  //#region [Collapse]
  isCollapsed(index: number): boolean {
    return this.collapsedStates[index] ?? false;
  }
  
  toggleCollapse(index: number): void {
    this.collapsedStates[index] = !this.isCollapsed(index);
  }
  //#endregion
  //#region [Carrusel Tipo Bitacora]
  prevLogo(): void {
    this.selectedIndex = (this.selectedIndex - 1 + this.tipoBitacoraOptions.length) % this.tipoBitacoraOptions.length;
    this.offset = -this.selectedIndex * 100;
    this.updateTipoBitacora();
  }

  nextLogo(): void {
    this.selectedIndex = (this.selectedIndex + 1) % this.tipoBitacoraOptions.length;
    this.offset = -this.selectedIndex * 100;
    this.updateTipoBitacora();
  }

  updateTipoBitacora(): void {
    const selectedValue = this.tipoBitacoraOptions[this.selectedIndex].value;
    this.getEncabezadoControl('tipo_bitacora').setValue(selectedValue);
  }

  getLogoPath(value: number): string {
    const option = this.tipoBitacoraOptions.find(opt => opt.value === value);
    return option ? `logos/${option.logo}` : '';
  }
  //#endregion
}
