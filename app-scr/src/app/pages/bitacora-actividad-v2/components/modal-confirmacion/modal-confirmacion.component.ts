import { NgIf } from '@angular/common';
import { Component, Inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-modal-confirmacion',
    imports: [
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    NgIf
  ],
  template: `
    <h2 mat-dialog-title class="d-flex align-items-center gap-2 py-2 px-1" style="font-size: 20px;">
      <mat-icon>info</mat-icon>
      Reporte existente encontrado
    </h2>
    
    <mat-dialog-content>
      <div *ngIf="currentStep === 1">
        <p>Hemos encontrado un reporte previo con estos datos:</p>
        
        <!-- Muestra los datos del encabezado -->
        <div class="data-preview" *ngIf="data">
          <p class="text-truncate"><strong>Tipo:</strong> {{data?.titulo || 'No especificado'}}</p>
          <p class="text-truncate"><strong>Tipo:</strong> {{data?.proyecto || 'No especificado'}}</p>
          <p><strong>Fecha:</strong> {{ formatDate(data.fecha) }}</p>
          <!-- Añade más campos según necesites -->
        </div>
        
        <p class="mt-3">¿Deseas retomar este reporte?</p>
      </div>
      
      <div *ngIf="currentStep === 2">
        <p class="font-weight-bold">¿Estás seguro?</p>
        <p>Al continuar se eliminarán todos los datos no guardados, incluyendo:</p>
        <ul>
          <li>Información del encabezado</li>
          <li>Fotos adjuntadas</li>
          <li>Datos de secciones</li>
        </ul>
      </div>
    </mat-dialog-content>
    
    <mat-dialog-actions align="end">
      <ng-container *ngIf="currentStep === 1">
        <button mat-button color="warn" (click)="nuevoReporte()" >
          No, empezar nuevo
        </button>
        <button mat-raised-button color="primary" (click)="continuarReporte()">
          Sí, continuar
        </button>
      </ng-container>
      
      <ng-container *ngIf="currentStep === 2">
        <button mat-stroked-button (click)="volverAPrimerPaso()">
          <mat-icon>arrow_back</mat-icon> Regresar
        </button>
        <button mat-raised-button color="warn" (click)="confirmarNuevoReporte()">
          <mat-icon>delete</mat-icon> Confirmar
        </button>
      </ng-container>
    </mat-dialog-actions>
  `,
  styleUrls: ['./modal-confirmacion.component.scss']
})
export class ModalConfirmacionComponent {
  currentStep = 1;

  constructor(
    public dialogRef: MatDialogRef<ModalConfirmacionComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {}

  continuarReporte(): void {
    this.dialogRef.close(true);
  }

  nuevoReporte(): void {
    this.currentStep = 2;
  }

  confirmarNuevoReporte(): void {
    this.dialogRef.close(false);
  }

  volverAPrimerPaso(): void {
    this.currentStep = 1;
  }

  formatDate(date: any): string {
    if (!date) return 'No especificada';
    
    try {
      const d = new Date(date);
      return d.toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
    } catch {
      return 'Fecha inválida';
    }
  }
}