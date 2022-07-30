import { NgModule } from '@angular/core';
import { ExtraOptions, RouterModule, Routes } from '@angular/router';

import { StatsComponent } from './stats/stats.component';
import { OverallComponent } from './overall/overall.component';
import { XmlComponent } from './xml/xml.component';

const routes: Routes = [
  { path: '', redirectTo: '/s/daily/0/-', pathMatch: 'full' },
  {
    path: 's/:option/:offset/:team',
    pathMatch: 'full',
    component: StatsComponent,
  },
  {
    path: 'o/:option/:offset/:team',
    pathMatch: 'full',
    component: OverallComponent,
  },
  {
    path: 'x/:option/:offset/:team',
    pathMatch: 'full',
    component: XmlComponent,
  },
  { path: '**', redirectTo: '/s/daily/0/-' },
  { path: '404', redirectTo: '/s/daily/0/-' },
];

const config: ExtraOptions = {
  useHash: true,
  // enableTracing: true,
  scrollPositionRestoration: 'enabled',
  onSameUrlNavigation: 'reload',
  relativeLinkResolution: 'legacy'
};

@NgModule({
  imports: [RouterModule.forRoot(routes, config)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
