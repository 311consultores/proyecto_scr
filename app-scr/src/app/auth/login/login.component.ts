import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { FormGroup, FormBuilder, Validators, ReactiveFormsModule } from '@angular/forms';
import { finalize, of } from 'rxjs';

@Component({
  selector: 'app-login',
  imports: [
    CommonModule,
    ReactiveFormsModule
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  loading = false;
  form: FormGroup;
  passwordTextType!: boolean;
  year$ = of(new Date().getFullYear());
  alertError = {
    message: "",
    show: false
  };

  constructor(
    private fb: FormBuilder,
    private _authService: AuthService,
    private _router: Router
  ) {
    this.form = this.fb.group({
      user: ['', [Validators.required]],
      password: ['', [Validators.required, Validators.maxLength(10)]]
    });
  }

  togglePasswordTextType() {
    this.passwordTextType = !this.passwordTextType;
  }

  async onSubmit(e: Event) {
    e.preventDefault();
    if (this.form.invalid) {
      console.log(this.form.errors);
      this.alertError.show = true;
      this.alertError.message = "*Llene los campos antes de continuar*";
      return;
    }

    this.alertError.show = true;
    this.loading = true;
    await this._authService.login(this.form.value)
    .pipe(
      finalize(() => {
        this.loading = false;
      })
    )
    .subscribe({
      next: (response) => {
        if(response.ok) {
          localStorage.setItem("token",response.data);
          this._router.navigate(["/panel"]);
          return;
        }
        this.alertError = {
          message : response.data,
          show: true
        };
      },
      error: (error) => {
        this.alertError = {
          message : "El servicio no se encuentra disponible",
          show: true
        };
      }
    });
  }
}
