import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'bitacoraType',
  standalone: true // Si estás usando standalone components
})
export class BitacoraTypePipe implements PipeTransform {
  transform(value: number | string | null | undefined): string {
    if (value === null || value === undefined) {
      return 'No especificado';
    }

    const numericValue = typeof value === 'string' ? parseInt(value, 10) : value;

    switch (numericValue) {
      case 1:
        return 'Clima';
      case 2:
        return 'Horarios';
      case 3:
        return 'Consumos';
      case 4:
        return 'Actividad';
      default:
        return `Tipo desconocido (${value})`;
    }
  }
}