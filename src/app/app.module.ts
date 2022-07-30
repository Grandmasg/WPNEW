import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';

import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatTableModule } from '@angular/material/table';
import { MatSortModule } from '@angular/material/sort';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatInputModule } from '@angular/material/input';
import { ReactiveFormsModule } from '@angular/forms';
import { MatNativeDateModule } from '@angular/material/core';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { HighlightSearchPipe } from './highlightable-search.pipe';
import { ClipboardModule } from 'ngx-clipboard';
import { ValuePipe } from './value.pipe';
import { UnescapePipe } from './value.pipe';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { NgxSpinnerModule } from "ngx-spinner";
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { StatsComponent } from './stats/stats.component';
import { OverallComponent } from './overall/overall.component';
import { XmlComponent } from './xml/xml.component';

import { registerLocaleData } from '@angular/common';
import localeNl from '@angular/common/locales/nl';
registerLocaleData(localeNl, 'nl');

import { NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';
import { NgbDateNLParserFormatter } from './ngb-date-nl-parser-formatter';

import { HighchartsChartModule } from 'highcharts-angular';
@NgModule({
  declarations: [
    AppComponent,
    StatsComponent,
    OverallComponent,
    XmlComponent,
    ValuePipe,
    HighlightSearchPipe,
    UnescapePipe
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    AppRoutingModule,
    HttpClientModule,
    ClipboardModule,
    NgxSpinnerModule,
    FontAwesomeModule,
    NgbModule,
    FormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatDatepickerModule,
    MatTableModule,
    MatSortModule,
    MatPaginatorModule,
    MatInputModule,
    MatNativeDateModule,
    ReactiveFormsModule,
    HighchartsChartModule
  ],
  providers:  [
    {provide: NgbDateParserFormatter, useClass: NgbDateNLParserFormatter},
    HighlightSearchPipe],
  bootstrap: [AppComponent]
})
export class AppModule { }
