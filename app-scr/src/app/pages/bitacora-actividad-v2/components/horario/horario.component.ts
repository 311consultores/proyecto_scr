import { Component, EventEmitter, Input, Output, SimpleChanges, TemplateRef, ViewChild } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import {MatListModule} from '@angular/material/list';
import { MatDialog, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AbstractControl, FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { Horario } from '../../../../core/models/biracora.model';
import { NgIf } from '@angular/common';

interface Horarios {
  tipo: number,
  horarios: Horario[]
};

@Component({
  selector: 'app-horario',
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
  templateUrl: './horario.component.html',
  styleUrl: './horario.component.scss'
})
export class HorarioComponent {

  @Input() public _horarioElement!: Horarios;
  @Output() public getHorarioElement= new EventEmitter<any[]>(); 
  @Output() validityChanged = new EventEmitter<boolean>();
  @ViewChild('modal') modal!: TemplateRef<any>;

  public horarioForm!: FormGroup;
  public horarioElement!: FormGroup;
  public dialogRef!: MatDialogRef<any>;
  public horarioIndex: number = -1;

  constructor(
    private fb : FormBuilder,
    private dialog: MatDialog,
    private snackBar: MatSnackBar,
  ) {
    this.initHorario();
    this.initHorarioForm();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['_horarioElement']) {
      this.horarioElement.patchValue(changes['_horarioElement'].currentValue);
    }
  }

  initHorario() {
    this.horarioElement = this.fb.group({
      tipo: [2],
      horarios: this.fb.control(this._horarioElement?.horarios || [], this.minLengthArray(1))
    })

    this.horarioElement.valueChanges.subscribe(() => {
      if(this.horarioElement.valid) {
        this.validityChanged.emit(true);
      } else {
        this.validityChanged.emit(false);
      }
    });
  }

  initHorarioForm() {
    this.horarioForm = this.fb.group({
      categoria: ['', Validators.required],
      nombre: [null, [Validators.required]],
      hora_inicio: [null, [Validators.required]],
      hora_fin: [null, [Validators.required]],
    });
  }

  onEdit(event :  Event, data: any = null, iHorario = 0): void {
    event.stopPropagation();
    if(data != null) {
      this.horarioForm.setValue(data);
      this.horarioIndex = iHorario;
    }
  }

  onSubmit(): void {
    if (this.horarioForm.valid) {
      let result = this.horarioForm.value;
      if (result) {
        const currentArray = this.horarioElement.get('horarios')?.value || [];
        let newArray;

        if (this.horarioIndex >= 0) { // O si usas ID: if (result.id)
          newArray = [...currentArray];
          newArray[this.horarioIndex] = result; // Reemplaza el elemento en el índice
          this.horarioIndex = -1;
          this.showSnackbar('Horario actualizado correctamente');
        } 
        // Caso 2: Agregar (si no hay índice/ID)
        else {
          newArray = [...currentArray, result];
          this.showSnackbar('Horario añadido correctamente');
        }
        this.horarioElement.get('horarios')?.setValue(newArray);
        this.getHorarioElement.emit(this.horarioElement.value);
        this.horarioForm.reset();
      }
    } else {
      this.horarioForm.markAllAsTouched();
    }
  }

  deleteClima(index: number): void {
    const currentArray = this.horarioElement.get('horarios')?.value || [];
    const newArray = [...currentArray];
    newArray.splice(index, 1);
    this.horarioElement.get('horarios')?.setValue(newArray);
    this.getHorarioElement.emit(this.horarioElement.value);
    this.showSnackbar('Horario eliminado correctamente');
  }

  setFechaFin() {
    const horaInicio = this.horarioForm.get('hora_inicio')?.value; // "HH:mm" format
    
    if (horaInicio) {
      // Crear fecha base (usamos hoy solo como referencia)
      const fechaBase = new Date();
      const [h, m] = horaInicio.split(':');
      fechaBase.setHours(parseInt(h), parseInt(m), 0, 0);
      
      // Sumar 8 horas
      const fechaFin = new Date(fechaBase.getTime() + (8 * 60 * 60 * 1000));
      
      // Formatear a HH:mm
      const horaFin = fechaFin.getHours().toString().padStart(2, '0') + ':' + 
                      fechaFin.getMinutes().toString().padStart(2, '0');
      
      this.horarioForm.get('hora_fin')?.setValue(horaFin);
    }
  }

  drop(event: CdkDragDrop<string[]>) {
    const currentArray = this.horarioElement.get('horarios')?.value || [];
    const newArray = [...currentArray]; // Copia inmutable
    moveItemInArray(newArray, event.previousIndex, event.currentIndex); // Muta la copia
  
    // Actualiza el FormControl con el nuevo array
    this.horarioElement.get('horarios')?.setValue(newArray);
    this.getHorarioElement.emit(this.horarioElement.value);
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
