import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { PanelAdminComponent } from './panel-admin.component';
import { BitacoraComponent } from './bitacora/bitacora.component';
import { InspeccionComponent } from './inspeccion/inspeccion.component';

const routes: Routes = [
  {
    path: '',
    component: PanelAdminComponent,
    children: [
      {
        path: '',
        component: BitacoraComponent
      },
      {
        path: 'inspeccion',
        component: InspeccionComponent
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PanelAdminRoutingModule { }
