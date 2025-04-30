import { Component, EventEmitter, Input, Output, SimpleChanges, TemplateRef, ViewChild } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatListModule } from '@angular/material/list';
import { MatDialog, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';
import { NgIf } from '@angular/common';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';

interface Clima {
  tipo: number,
  climas: Clima[]
};

@Component({
  selector: 'app-clima',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    FormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatListModule,
    MatDialogModule,
    NgIf,
    DragDropModule
  ],
  templateUrl: './clima.component.html',
  styleUrl: './clima.component.scss'
})
export class ClimaComponent {

  @Input() public _climaElement!: Clima;
  @Output() public getClimaElement= new EventEmitter<any[]>(); 
  @Output() validityChanged = new EventEmitter<boolean>();
  @ViewChild('modal') modal!: TemplateRef<any>;

  public climaForm!: FormGroup;
  public climaElement!: FormGroup;
  public dialogRef!: MatDialogRef<any>;

  public unidades = {
    temp: '°C',
    temp_aparente: '°C',
    viento: 'km/h',
    humedad: '%',
    punto_rocio: '°C',
    presion: 'mb'
  };

  constructor(
    private fb : FormBuilder,
    private dialog: MatDialog,
    private snackBar: MatSnackBar,
  ) {
    this.initClima();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['_climaElement']) {
      this.climaElement.patchValue(changes['_climaElement'].currentValue);
    }
  }

  initClima() {
    this.climaElement = this.fb.group({
      tipo: [1],
      climas: this.fb.control(this._climaElement?.climas || [], this.minLengthArray(1))
    })

    this.climaElement.valueChanges.subscribe(() => {
      if(this.climaElement.valid) {
        this.validityChanged.emit(true);
      } else {
        this.validityChanged.emit(false);
      }
    });
  }

  initClimaForm() {
    this.climaForm = this.fb.group({
      hora: ['', Validators.required],
      temp: [null, [Validators.required, Validators.min(-50), Validators.max(60)]],
      temp_aparente: [null, [Validators.min(-50), Validators.max(60)]],
      viento: ['', [Validators.required, Validators.min(0)]],
      humedad: ['', [Validators.required, Validators.min(0), Validators.max(100)]],
      punto_rocio: [null, [Validators.min(-50), Validators.max(60)]],
      presion: ['', [Validators.required, Validators.min(800), Validators.max(1100)]],
      desc: ['']
    });
  }

  openClimaModal(event :  Event, data: any = null, iClima = 0): void {
    event.stopPropagation();
    this.initClimaForm();
    
    if(data != null) {
      this.climaForm.setValue(data);
    }
    this.dialogRef = this.dialog.open(this.modal, {
      maxWidth: '100vw',
      width: '95%',
      panelClass: 'full-screen-modal',
      disableClose: true,
      hasBackdrop: true
    });

    const climaIndex = iClima;

    this.dialogRef.afterClosed().subscribe(result => {
      if (result) {
        const currentArray = this.climaElement.get('climas')?.value || [];
        let newArray;

        if (climaIndex > 0) { // O si usas ID: if (result.id)
          newArray = [...currentArray];
          newArray[climaIndex] = result; // Reemplaza el elemento en el índice
        } 
        // Caso 2: Agregar (si no hay índice/ID)
        else {
          newArray = [...currentArray, result];
        }
        this.climaElement.get('climas')?.setValue(newArray);
        this.getClimaElement.emit(this.climaElement.value);
        this.climaForm.reset();
      }
    });
  }

  onSubmit(): void {
    if (this.climaForm.valid) {
      this.dialogRef.close(this.climaForm.value);
    } else {
      this.climaForm.markAllAsTouched();
    }
  }

  deleteClima(index: number): void {
    const currentArray = this.climaElement.get('climas')?.value || [];
    const newArray = [...currentArray];
    newArray.splice(index, 1);
    this.climaElement.get('climas')?.setValue(newArray);
    this.getClimaElement.emit(this.climaElement.value);
    this.showSnackbar('Item eliminado correctamente');
  }

  private showSnackbar(message: string): void {
    // Necesitarías importar MatSnackBar en tu componente
    this.snackBar.open(message, 'Cerrar', {
      duration: 3000,
      panelClass: ['success-snackbar']
    });
  }

  private minLengthArray(min: number): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value; // Obtiene el array nativo (no FormArray)
      return value && value.length >= min ? null : { minLengthArray: true };
    };
  }

  drop(event: CdkDragDrop<string[]>) {
    const currentArray = this.climaElement.get('climas')?.value || [];
    const newArray = [...currentArray]; // Copia inmutable
    moveItemInArray(newArray, event.previousIndex, event.currentIndex); // Muta la copia
  
    // Actualiza el FormControl con el nuevo array
    this.climaElement.get('climas')?.setValue(newArray);
    this.getClimaElement.emit(this.climaElement.value);
  }
}
