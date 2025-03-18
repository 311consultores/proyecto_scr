import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  FaIconLibrary,
  FontAwesomeModule,
} from '@fortawesome/angular-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
import { BitacoraService } from '../../../core/services/bitacora.service';

@Component({
  selector: 'app-galeria',
  imports: [FontAwesomeModule],
  templateUrl: './galeria.component.html',
  styleUrl: './galeria.component.scss',
})
export class GaleriaComponent {
  @Input() images: any = [];
  @Output() photosEvent = new EventEmitter<string[]>();
  currentIndex: number = 0;
  isModalOpen = false; // Estado del modal

  constructor(
    library: FaIconLibrary,
    private _bitacoraService: BitacoraService
  ) {
    library.addIcons(faTrash);
  }

  nextImage(): void {
    this.currentIndex = (this.currentIndex + 1) % this.images.length;
  }

  prevImage(): void {
    this.currentIndex =
      (this.currentIndex - 1 + this.images.length) % this.images.length;
  }

  selectImage(index: number): void {
    this.currentIndex = index;
  }

  eliminarImagen(index: number) {
    this._bitacoraService.eliminarFotoTemp(this.images[index]).subscribe({
      next: (response) => {
        if (response.ok) {
          if (this.currentIndex > 0) {
            this.currentIndex--;
          }
          this.images.splice(index, 1);
          this.photosEvent.emit(this.images);
        }
      },
    });
  }
}
