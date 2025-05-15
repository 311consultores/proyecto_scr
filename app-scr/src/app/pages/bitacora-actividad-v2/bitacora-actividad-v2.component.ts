import { ChangeDetectorRef, Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
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
import { TiposPipe } from '../../core/pipes/tipos.pipe';
import { BitacoraService } from '../../core/services/bitacora.service';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { animate, state, style, transition, trigger } from '@angular/animations';
import { BitacoraModel } from '../../core/models/biracora.model';
import { ClimaComponent } from './components/clima/clima.component';
import { HorarioComponent } from './components/horario/horario.component';
import { ConsumoComponent } from './components/consumo/consumo.component';
import { ActividadComponent } from './components/actividad/actividad.component';
import { MatDialog, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { ModalConfirmacionComponent } from './components/modal-confirmacion/modal-confirmacion.component';
import { PrevisualizadorPdfComponent } from './components/previsualizador-pdf/previsualizador-pdf.component';

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
    HorarioComponent,
    ConsumoComponent,
    ActividadComponent,
    MatDialogModule,
    PrevisualizadorPdfComponent
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
  public encabezadoForm!: FormGroup;
  public bContenido= false;
  public tipoBitacoraOptions = [
    { value: 1, label: 'MAI', logo: 'MAI.png' },
    { value: 2, label: 'SCE', logo: 'SCE.png' },
    { value: 3, label: 'MPR', logo: 'MPR.png' }
  ];
  public catalogos: any;
  public selectedIndex = 0;
  public offset = 0;
  public openedCollapseIndex: number | null = null;
  
  // Modal Encabezado
  @ViewChild('modal') modal!: TemplateRef<any>;
  public dialogRef!: MatDialogRef<any>;

  // Preview
  public showPreview = false;
  public touchStartY = 0;

  constructor(
    private fb: FormBuilder,
    private snackBar: MatSnackBar,
    private _bitacoraService: BitacoraService,
    private cdr: ChangeDetectorRef,
    private dialog: MatDialog,
  ) {}

  ngOnInit(): void {
    this.initBitacora();
    this.getCatalogos();
  }

  initBitacora() {
    this.initForm();
    let storage = JSON.parse(localStorage.getItem('data-cache') as string);
    if(storage != null && typeof storage == 'object') {
      const dialogRef = this.dialog.open(ModalConfirmacionComponent, {
        data: storage.encabezado,
        width: '500px',
        disableClose: true
      });

      dialogRef.afterClosed().subscribe(result => {
        if (result === true) {
          // Continuar con el reporte existente
          this.setCache(storage);
          this.initializeSelectedIndex();
        } else if (result === false) {
          // Comenzar nuevo reporte
          localStorage.removeItem('data-cache');
        }
      });
    }    
  }

  initForm() {
    this.bitacoraForm = this.fb.group({
      encabezado: this.fb.nonNullable.group({
        tipo_bitacora: [1, Validators.required],
        titulo: ['', Validators.required],
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

  initEncabezado() {
    this.encabezadoForm = this.fb.group({
      titulo: ["", [Validators.required, Validators.minLength(3)]]
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

  addItem(type : number, titulo= ""): void {
    let json = null;
    switch(type) {
      case 1: json = { tipo: type, climas: [], valido: false}; break;
      case 2: json = { tipo: type, horarios: [], valido: false}; break;
      case 3: json = { tipo: type, consumos: [], valido: false}; break;
      case 4: json = { tipo: type, titulo, data: {}, valido: false}; break;
    }
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray, json];
    this.bitacoraForm.get('contenido')?.setValue(newArray);
    this.openedCollapseIndex = currentArray.length;
  }

  updateValueContenido(index : number, value : any) {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray];
    newArray[index] = value;
    this.bitacoraForm.get('contenido')?.setValue(newArray);
    this.cdr.detectChanges();
  }

  setValidate(index: number, value: any) {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray];
    newArray[index].valido = value;
    this.bitacoraForm.get('contenido')?.setValue(newArray);
    this.cdr.detectChanges();
  }

  minLengthArray(min: number): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value; // Obtiene el array nativo (no FormArray)
      return value && value.length >= min ? null : { minLengthArray: true };
    };
  }

  removeItem(index: number): void {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray];
    newArray.splice(index, 1);
    this.bitacoraForm.get('contenido')?.setValue(newArray);
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

  get isNextDisabled(): boolean {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    return currentArray.find((x : any) => x.valido == false) || currentArray.length == 0;
  }

  private markFormGroupTouched(formGroup: FormGroup | FormArray): void {
    Object.values(formGroup.controls).forEach(control => {
      control.markAsTouched();
      if (control instanceof FormGroup || control instanceof FormArray) {
        this.markFormGroupTouched(control);
      }
    });
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

  openModalEncabezado() {
    this.initEncabezado();

    this.dialogRef = this.dialog.open(this.modal, {
      maxWidth: '100vw',
      width: '95%',
      panelClass: 'full-screen-modal',
      disableClose: true,
      hasBackdrop: true
    });

    this.dialogRef.afterClosed().subscribe(result => {
      if (result) {
        this.addItem(4, result.titulo);
        // const currentArray = this.consumoElement.get('consumos')?.value || [];
        // let newArray;

        // if (consumoIndex >= 0) { // O si usas ID: if (result.id)
        //   newArray = [...currentArray];
        //   newArray[consumoIndex] = result; // Reemplaza el elemento en el índice
        // } 
        // // Caso 2: Agregar (si no hay índice/ID)
        // else {
        //   newArray = [...currentArray, result];
        // }
        // this.consumoElement.get('consumos')?.setValue(newArray);
        // this.getConsumoElement.emit(this.consumoElement.value);
        // this.consumoForm.reset();
      }
    });
  }

  onEncabezadoSubmit(): void {
    if (this.encabezadoForm.valid) {
      this.dialogRef.close(this.encabezadoForm.value);
    } else {
      this.encabezadoForm.markAllAsTouched();
    }
  }

  //#region  [Cache]
  setCache(storage : any) {
    this.bitacoraForm.setValue(storage as BitacoraModel);
    const contenidoLength = this.bitacoraForm.get('contenido')?.value?.length || 0;
    this.openedCollapseIndex = contenidoLength > 0 ? contenidoLength - 1 : null;
  }

  updateCache() {
    localStorage.setItem('data-cache', JSON.stringify(this.bitacoraForm.value));
  }

  cleanCache() {
    localStorage.removeItem('data-cache');
  }

  getTotalPorTipo(): {tipo: string, cantidad: number}[] {
    const items = this.bitacoraForm.get('contenido')?.value || [];
    const conteo: {[key: string]: number} = {}; // Tipo explícito

    items.forEach((item : any) => {
      const tipo = item.tipo;
      conteo[tipo] = (conteo[tipo] || 0) + 1;
    });

    return Object.keys(conteo).map(tipo => ({
      tipo: tipo,
      cantidad: conteo[tipo]
    }));
  }
  //#endregion
  //#region [Drag and drop]
  drop(event: CdkDragDrop<string[]>) {
    const currentArray = this.bitacoraForm.get('contenido')?.value || [];
    const newArray = [...currentArray]; // Copia inmutable
    moveItemInArray(newArray, event.previousIndex, event.currentIndex); // Muta la copia
  
    // Actualiza el FormControl con el nuevo array
    this.bitacoraForm.get('contenido')?.setValue(newArray);
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
  //#region [Carrusel Tipo Bitacora]
  private initializeSelectedIndex(): void {
    // Intenta recuperar el índice guardado
    let savedIndex = this.getEncabezadoControl('tipo_bitacora').value;
    this.selectedIndex = parseInt(savedIndex, 10) -1;
    
    // Asegúrate de que el índice esté dentro de los límites
    if (this.selectedIndex > this.tipoBitacoraOptions.length) {
      this.selectedIndex = 0;
    }
    
    this.offset = -this.selectedIndex * 100;
  }

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
    console.log(selectedValue);
    this.getEncabezadoControl('tipo_bitacora').setValue(selectedValue);
  }

  getLogoPath(value: number): string {
    const option = this.tipoBitacoraOptions.find(opt => opt.value === value);
    return option ? `logos/${option.logo}` : '';
  }
  //#endregion
  //#region [Preview]
  togglePreview() {
    this.showPreview = !this.showPreview;
    const footer = document.getElementById('stepFooter');
    if (footer) {
      footer.classList.toggle('expanded', this.showPreview);
    }
  }
  
  handleTouchStart(event: TouchEvent) {
    this.touchStartY = event.touches[0].clientY;
  }
  
  handleTouchEnd(event: TouchEvent) {
    const touchEndY = event.changedTouches[0].clientY;
    const deltaY = this.touchStartY - touchEndY;
    
    // Si el desplazamiento hacia arriba es significativo
    if (deltaY > 30 && !this.showPreview) {
      this.togglePreview();
    } 
    // Si el desplazamiento hacia abajo es significativo y está expandido
    else if (deltaY < -30 && this.showPreview) {
      this.togglePreview();
    }
  }
  //#endregion
}