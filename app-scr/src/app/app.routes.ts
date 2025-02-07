import { Routes } from '@angular/router';
import { authGuard } from './core/guard/login.guard';
import { unloggedGuard } from './core/guard/unlogin.guard';
import { LoginComponent } from './auth/login/login.component';
import { BitacoraActividadComponent } from './pages/bitacora-actividad/bitacora-actividad.component';

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
        path: 'panel',
        canActivate: [authGuard],
        loadChildren: () => import('./panel-admin/panel-admin.module').then((m) => m.PanelAdminModule)
    },
    {
      path: '**',
      redirectTo: '', // Ruta por defecto o página 404
    }
];