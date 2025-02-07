import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service'; 
import { inject } from '@angular/core';
import { environment } from '../../../environments/environment';

export const unloggedGuard: CanActivateFn = (route, state) => {
  const router : Router = inject(Router);
  const authService : AuthService = inject(AuthService);

  if(authService.isLoggedIn()) {
    router.navigate([environment.urlInicio])
    return false;
  }
  return true;
};