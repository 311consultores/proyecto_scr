import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { inject } from '@angular/core';
import { environment } from '../../../environments/environment';


export const authGuard: CanActivateFn = (route, state) => {
  const router : Router = inject(Router);
  const authService : AuthService = inject(AuthService);

  if(!authService.isLoggedIn()) {
    router.navigate(["/"])
    return false;
  }
  return true;
};