import { Component, Input, SimpleChanges } from '@angular/core';
import { BitacoraService } from '../../../../core/services/bitacora.service';
import { PdfViewerModule } from 'ng2-pdf-viewer';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-previsualizador-pdf',
  standalone: true,
  imports: [
    PdfViewerModule,
    MatIconModule
  ],
  templateUrl: './previsualizador-pdf.component.html',
  styleUrl: './previsualizador-pdf.component.scss'
})
export class PrevisualizadorPdfComponent {

  @Input() bPreview: boolean = false;
  @Input() bitacora!: any;
  pdfSrc: string | undefined;
  zoom = 1;

  constructor(
  private _bitService : BitacoraService
  ) {}

  ngOnChanges(changes: SimpleChanges) {
    if (changes['bPreview']) {
      this.getPreview(changes['bPreview'].currentValue);
    }
  }

  getPreview(band : boolean) {
    if(band) {
      this._bitService.getPreview(this.bitacora).subscribe({
        next: (res : any) => {
          if(res.ok) {
            this.pdfSrc = this.base64ToPdfDataUrl(res.data);
          }
        }
      })
    }
  }

  downloadPdf() {
    if (this.pdfSrc) {
      const link = document.createElement('a');
      const base64 = this.pdfSrc.split(',')[1]; // quitar el encabezado data:application/pdf
      const byteCharacters = atob(base64);
      const byteNumbers = new Array(byteCharacters.length).fill(0).map((_, i) => byteCharacters.charCodeAt(i));
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: 'application/pdf' });
      const url = URL.createObjectURL(blob);
  
      link.href = url;
      link.download = `BIT-${this.bitacora.encabezado.folio_reporte}.pdf`;
      link.click();
  
      URL.revokeObjectURL(url); // liberar memoria
    }
  }

  base64ToPdfDataUrl(base64: string): string {
    return 'data:application/pdf;base64,' + base64;
  }
}
