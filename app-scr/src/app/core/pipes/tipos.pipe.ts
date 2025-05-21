import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'tiposPipe',
  standalone: true // Si estás usando standalone components
})
export class TiposPipe implements PipeTransform {
  transform(value: number | string | null | undefined): string {
    if (value === null || value === undefined) {
      return 'No especificado';
    }

    const numericValue = typeof value === 'string' ? parseInt(value, 10) : value;

    switch (numericValue) {
      case 1:
        return 'MAI';
      case 2:
        return 'SCE';
      case 3:
        return 'MPR';
      default:
        return `Tipo desconocido (${value})`;
    }
  }
}