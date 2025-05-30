import { Component, EventEmitter, Input, Output, SimpleChanges, TemplateRef, ViewChild } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { MatDialog, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { Consumo } from '../../../../core/models/biracora.model';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import {MatListModule} from '@angular/material/list';
import { MatInputModule } from '@angular/material/input';
import { NgIf } from '@angular/common';

interface Consumos {
  tipo : number,
  consumos : Consumo[]
}

@Component({
  selector: 'app-consumo',
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
    DragDropModule,
    NgIf
  ],
  templateUrl: './consumo.component.html',
  styleUrl: './consumo.component.scss'
})

export class ConsumoComponent {

  @Input() public _consumoElement!: Consumos;
  @Output() public getConsumoElement= new EventEmitter<any[]>(); 
  @Output() validityChanged = new EventEmitter<boolean>();
  @ViewChild('modal') modal!: TemplateRef<any>;

  public consumoForm!: FormGroup;
  public consumoElement!: FormGroup;
  public dialogRef!: MatDialogRef<any>;
  public consumoIndex: number = -1;

  constructor(
    private fb : FormBuilder,
    private dialog: MatDialog,
    private snackBar: MatSnackBar,
  ) {
    this.initConsumo();
    this.initConsumoForm();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['_consumoElement']) {
      this.consumoElement.patchValue(changes['_consumoElement'].currentValue);
    }
  }

  initConsumo() {
    this.consumoElement = this.fb.group({
      tipo: [2],
      consumos: this.fb.control(this._consumoElement?.consumos || [], this.minLengthArray(1))
    })

    this.consumoElement.valueChanges.subscribe(() => {
      if(this.consumoElement.valid) {
        this.validityChanged.emit(true);
      } else {
        this.validityChanged.emit(false);
      }
    });
  }

  initConsumoForm() {
    this.consumoForm = this.fb.group({
      descripcion: ['', Validators.required],
      unidad_medida: [null, [Validators.required]],
      cantidad: [null, [Validators.required]]
    });
  }

  onEdit(event :  Event, data: any = null, iConsumo = -1): void {
    event.stopPropagation();
    this.initConsumoForm();
    
    if(data != null) {
      this.consumoForm.setValue(data);
      this.consumoIndex = iConsumo;
    }
  }

  onSubmit(): void {
    if (this.consumoForm.valid) {
      let result = this.consumoForm.value;
      if (result) {
        const currentArray = this.consumoElement.get('consumos')?.value || [];
        let newArray;

        if (this.consumoIndex >= 0) { // O si usas ID: if (result.id)
          newArray = [...currentArray];
          newArray[this.consumoIndex] = result; // Reemplaza el elemento en el índice
          this.consumoIndex = -1;
          this.showSnackbar('Consumo actualizado correctamente');
        } 
        // Caso 2: Agregar (si no hay índice/ID)
        else {
          newArray = [...currentArray, result];
          this.showSnackbar('Consumo añadido correctamente');
        }
        this.consumoElement.get('consumos')?.setValue(newArray);
        this.getConsumoElement.emit(this.consumoElement.value);
        this.consumoForm.reset();
      }
    } else {
      this.consumoForm.markAllAsTouched();
    }
  }

  deleteConsumo(index: number): void {
    const currentArray = this.consumoElement.get('consumos')?.value || [];
    const newArray = [...currentArray];
    newArray.splice(index, 1);
    this.consumoElement.get('consumos')?.setValue(newArray);
    this.getConsumoElement.emit(this.consumoElement.value);
    this.showSnackbar('Consumo eliminado correctamente');
  }

  drop(event: CdkDragDrop<string[]>) {
    const currentArray = this.consumoElement.get('consumos')?.value || [];
    const newArray = [...currentArray]; // Copia inmutable
    moveItemInArray(newArray, event.previousIndex, event.currentIndex); // Muta la copia
  
    // Actualiza el FormControl con el nuevo array
    this.consumoElement.get('consumos')?.setValue(newArray);
    this.getConsumoElement.emit(this.consumoElement.value);
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
}
