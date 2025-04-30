import { Component, EventEmitter, Input, Output, TemplateRef, ViewChild } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { MatDialogRef } from '@angular/material/dialog';
import { Consumo } from '../../../../core/models/biracora.model';

interface Consumos {
  tipo : number,
  consumos : Consumo[]
}

@Component({
  selector: 'app-consumo',
  standalone: true,
  imports: [],
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

  
}
