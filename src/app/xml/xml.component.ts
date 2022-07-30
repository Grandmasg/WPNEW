import { environment } from '../../environments/environment';
import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { trigger, state, style, animate, transition, query, group } from '@angular/animations';
import { NgxSpinnerService } from 'ngx-spinner';
import { FormGroup, FormControl } from '@angular/forms';
import { Title } from '@angular/platform-browser';

import { Xml, Team } from '../xml/xml';
import { XmlService } from '../xml/xml.service';

@Component({
  selector: 'app-xml',
  templateUrl: './xml.component.html',
  styleUrls: ['./xml.component.scss'],
  animations: [
    trigger('CopyVisible', [
      state('false', style({
        'opacity': '0',
      })),
      state('true', style({
        'opacity': '1',
      })),
      transition(
        'false => true',
        animate('2500ms ease-out')
      ),
      transition(
        'true => false',
        animate('2500ms ease-in')
      ),
    ]),
  ]
})
export class XmlComponent implements OnInit {
  xml: any;
  version: any;
  option = 'Daily';
  offset = 0;
  offset1 = "" ;
  teamname = "-";
  teams: Team[] = [];
  Xml: Xml[] = [];
  filterText = "";
  date = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
  date2: any;
  date3: any;
  today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
  DateForm!: FormGroup;
  loading = false;
  oneDay: number = 0;
  copy: string = "";
  currentState = false;
  Changeloading: any;
  error: string = "";

  constructor(
    private xmlService: XmlService,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private titleService: Title,
  ) { }

  model = {
    year: this.today.getFullYear(),
    month: this.today.getMonth() + 1,
    day: this.today.getDate(),
  };

  ngOnInit(): void {
    console.log(this.router.url);
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
    this.Loading();
    this.version = environment.appName + ' - v' + environment.appVersion; // environment.appVersion;
    this.titleService.setTitle(this.version);
    this.route.queryParams.subscribe(params => {
      this.option = this.route.snapshot.paramMap.get('option') || '';
      this.offset1 = this.route.snapshot.paramMap.get('offset') || '';
      this.offset = Number(this.offset1);
      this.teamname = this.route.snapshot.paramMap.get('team') || '';
    });
    this.date3 = new Date(this.today.getTime() + this.offset * 86400000);
    this.date2 = this.date3.getTimezoneOffset();
    if (this.date2 === -60) { this.date = new Date(this.today.getTime() + ((this.offset * 86400000) + 3600000)); } else { this.date = new Date(this.today.getTime() + (this.offset * 86400000)); }
    this.DateForm = new FormGroup({
      date: new FormControl(this.date),
    });
    this.model = {
      year: this.date.getFullYear(),
      month: this.date.getMonth() + 1,
      day: this.date.getDate(),
    };
    this.PushXml()
  }

  Loading() {
    this.spinner.show();
    this.loading = true;
  }

  Change(id: string) {
    this.Loading();
    this.option = id;
    this.PushXml();
  }

  onSelectTeam(myteamname: string) {
    this.Loading();
    //this.paginator.pageIndex = 0;
    this.teamname = myteamname;
    //this.overall = this.dataSource.data;
    this.router.navigate(['/x', this.option, this.offset, this.teamname]);
    this.PushXml();
  }

  applyFilter(filterValue: string) {
    this.filterText = filterValue.trim();
    //this.dataSource.filter = this.filterText.toLowerCase();
    //this.stats = this.dataSource.filteredData;
    //this.Sum();
    //this.str = '';
    //this.Graph();
  }

  select(val:string) {
    this.Loading();
    if (val === 'today') {
      this.offset = 0;
    }
    if (val === 'yesterday') {
      this.offset = -1;
    }
    if (val === 'back') {
      this.offset = +this.offset - 1;
    }
    if (val === 'forward') {
      this.offset = +this.offset + 1;
    }
    this.date3 = new Date(this.today.getTime() + this.offset * 86400000);
    this.date2 = this.date3.getTimezoneOffset();
    if (this.date2 === -60) { this.date = new Date(this.today.getTime() + (this.offset * 86400000) + 3600000); } else { this.date = new Date(this.today.getTime() + (this.offset * 86400000)); }
    this.DateForm = new FormGroup({
      date: new FormControl(this.date),
    });
    this.model = {
      year: this.date.getFullYear(),
      month: this.date.getMonth() + 1,
      day: this.date.getDate(),
    };
    this.router.navigate(['/x', this.option, this.offset, this.teamname]);
    this.PushXml();
  }

  onSelectDate(date: any) {
    if (date != null) {
      this.Loading();
      if (typeof date !== 'object') {
        this.date = new Date(date.replace(/(\d{2})-(\d{2})-(\d{4})/, '$2/$1/$3'));
        this.model = {
          year: this.date.getFullYear(),
          month: this.date.getMonth() + 1,
          day: this.date.getDate(),
        };
      }
      this.date = new Date(date.month + '/' + date.day + '/' + date.year);
      this.oneDay = 24 * 60 * 60 * 1000;
      this.offset = -1 * Math.round((this.today.getTime() - this.date.getTime()) / this.oneDay);
      this.router.navigate(['/x', this.option, this.offset, this.teamname]);
      //this.PushOverall();
    }
  }

  click() {
    this.copy = 'Xml copied to clipboard!';
    this.currentState = true;
    this.delay(5000).then(any=>{
      this.currentState = false;
    });
    this.delay(10000).then(any=>{
      this.copy = '';
    });
  }

  async delay(ms: number) {
    await new Promise(resolve => setTimeout(()=>resolve(1), ms)); //.then(()=>console.log("fired"))
  }

  PushXml() {
    this.xmlService.mystats(this.option, 'team', this.offset, this.teamname);
    this.getXml();
    this.getTeam();
  }

  getXml(): void {
    this.xmlService.getXml().subscribe(
      data => {
        this.xml = data;
        this.spinner.hide();
        this.loading = false;
      },
      (err) => {
        this.error = err;
      }
    );

    // this.xmlService.getAll();
    /*this.xmlService.getAll().subscribe(
      res => {
        this.xml = res;
        this.spinner.hide();
        this.loading = false;
        this.Changeloading = 'spinneroff';
      },
      err => {
        this.error = err;
      }
    );*/
  }

  getTeam(): void {
    this.xmlService.getTeam().subscribe(
      data => {
        this.teams = data.data;
      },
      (err) => {
        this.error = err;
      }
    );
  }

}
