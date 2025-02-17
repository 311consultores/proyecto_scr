import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faEraser } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-autocomplete',
  standalone: true,
  imports: [
    FormsModule,
    FontAwesomeModule
  ],
  templateUrl: './autocomplete.component.html',
  styleUrl: './autocomplete.component.scss'
})
export class AutocompleteComponent implements OnChanges {
  @Input() titleInp : string = "";
  @Input() data = Array();
  @Output() searchSelected = new EventEmitter<{}>;
  @Output() searchCleaner = new EventEmitter<{}>;
  public search = "";
  public filteredResults = Array();
  public selected = false;

  constructor(
    private library : FaIconLibrary
  ) {
    this.library.addIcons(faEraser);
  }

  ngOnChanges(changes: SimpleChanges): void {
    if(changes['data']) {
      this.filteredResults = this.data;
    }
  }
  // Función para filtrar los resultados
  filterResults() {
    if (this.search.length > 2) {
      this.filteredResults = this.data.filter((option : any) =>
        option.nombre.toLowerCase().includes(this.search.toLowerCase())
      );
    } else {
      this.filteredResults = [];
    }
  }

  // Función para seleccionar un resultado
  selectResult(result: any) {
    this.searchSelected.emit(result);
    this.search = result.nombre;
    this.selected = true;
    this.filteredResults = [];
  }

  clearSearch() {
    if(this.selected) {
      this.searchCleaner.emit();
      this.selected = false;
    } 
    this.search = "";
  }
}
