import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-panel-admin',
  imports: [
    RouterOutlet,
    RouterLink,
    RouterLinkActive
  ],
  templateUrl: './panel-admin.component.html',
  styleUrl: './panel-admin.component.scss'
})
export class PanelAdminComponent {
  bSidebar= false;
  bShowMenu= true;
  currentTime?: string;
  private timer: any;

  constructor() {}

  ngOnInit() {
    this.configReloj();
  }

  ngOnDestroy() {
    clearInterval(this.timer);
  }

  configReloj() {
    this.updateTime();
    this.timer = setInterval(() => this.updateTime(), 1000);
  }

  configMovil() {
    const width = window.innerWidth;
    this.bSidebar = width > 768;
    this.bShowMenu= !this.bSidebar;
  }

  toggleSidebar() {
    this.bSidebar = !this.bSidebar; // Alternar el estado
  }

  updateTime() {
    const now = new Date();
    this.currentTime = (now.getDay() +"/"+ now.getMonth() +"/"+ now.getFullYear()) +" - "+ now.toLocaleTimeString(); // Cambia el formato según tus necesidades
  }

  logOut() {
    localStorage.removeItem("token");
    location.reload();
  }
}
