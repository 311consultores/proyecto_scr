import { Routes } from '@angular/router';
import { authGuard } from './core/guard/login.guard';
import { unloggedGuard } from './core/guard/unlogin.guard';
import { LoginComponent } from './auth/login/login.component';
import { BitacoraActividadComponent } from './pages/bitacora-actividad/bitacora-actividad.component';
import { BitacoraActividadV2Component } from './pages/bitacora-actividad-v2/bitacora-actividad-v2.component';

export const routes: Routes = [
    {
        path: '',
        canActivate: [unloggedGuard],
        component: LoginComponent
    },
    {
        path: 'bitacora',
        component: BitacoraActividadComponent
    },
    {
        path: 'bitacora-v2',
        component: BitacoraActividadV2Component
    },
    {
        path: 'panel',
        canActivate: [authGuard],
        loadChildren: () => import('./panel-admin/panel-admin.module').then((m) => m.PanelAdminModule)
    },
    {
      path: '**',
      redirectTo: '', // Ruta por defecto o página 404
    }
];