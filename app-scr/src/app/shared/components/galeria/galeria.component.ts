import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
import { BitacoraService } from '../../../core/services/bitacora.service';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-galeria',
  templateUrl: './galeria.component.html',
  styleUrls: ['./galeria.component.scss'],
  standalone: true,
  imports: [
    FontAwesomeModule,
    NgIf
  ],
})
export class GaleriaComponent {
  @Input() images: any[] = [];
  @Output() photosEvent = new EventEmitter<string[]>();

  isModalOpen: boolean = false;
  currentIndex: number = 0;

  constructor(
    library: FaIconLibrary,
    private _bitacoraService: BitacoraService
  ) {
    library.addIcons(faTrash);
  }

  openModal(index: number = 0): void {
    this.currentIndex = index;
    this.isModalOpen = true;
  }

  closeModal(): void {
    this.isModalOpen = false;
  }

  nextImage(): void {
    this.currentIndex = (this.currentIndex + 1) % this.images.length;
  }

  prevImage(): void {
    this.currentIndex =
      (this.currentIndex - 1 + this.images.length) % this.images.length;
  }

  eliminarImagen(index: number): void {
    this._bitacoraService.eliminarFotoTemp(this.images[index]).subscribe({
      next: (response) => {
        if (response.ok) {
          this.images.splice(index, 1);
          if (this.currentIndex >= this.images.length) {
            this.currentIndex = this.images.length - 1;
          }
          this.photosEvent.emit(this.images);
        }
      },
    });
  }
}
