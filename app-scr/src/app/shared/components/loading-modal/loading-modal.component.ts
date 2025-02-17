import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-loading-modal',
  imports: [],
  templateUrl: './loading-modal.component.html',
  styleUrl: './loading-modal.component.scss'
})
export class LoadingModalComponent {
  @Input() isLoading: boolean = false; // Controla la visibilidad del modal
}
