import { Component, Input, OnChanges, SimpleChanges, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-alerta',
  imports: [],
  templateUrl: './alerta.component.html',
  styleUrl: './alerta.component.scss'
})
export class AlertaComponent implements OnChanges {

  @Input() message : string = '';
  @Input() type: string = '';
  @Input() isVisible: string = "";
  @Output() updateAlert = new EventEmitter<string>();

  mostrar = false;

  constructor() { }

  // Método para detectar cambios en las propiedades @Input
  ngOnChanges(changes: SimpleChanges) {
    if (changes['isVisible']) {
      // Actualizar la propiedad interna cuando isVisible cambia
      this.mostrar = changes['isVisible'].currentValue === "true";
    }
  }

  closeAlert() {
    this.mostrar = false;
    this.updateAlert.emit(this.mostrar+"");
  }
}
