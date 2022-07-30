import { environment } from '../../environments/environment';
import { Component, HostBinding, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { FormGroup, FormControl } from '@angular/forms';
import { MatTableDataSource } from '@angular/material/table';
import { MatSort } from '@angular/material/sort';
import { MatPaginator } from '@angular/material/paginator';
import { Title } from '@angular/platform-browser';
import { DomSanitizer } from '@angular/platform-browser';
import * as Highcharts from 'highcharts';
import { Options } from 'highcharts';
require('highcharts/themes/dark-unica')(Highcharts);
declare var require: any;

import { Overall, Team } from '../overall/overall';
import { OverallService } from '../overall/overall.service';

@Component({
  selector: 'app-overall',
  templateUrl: './overall.component.html',
  styleUrls: ['./overall.component.scss']
})
export class OverallComponent implements OnInit {
  Highcharts: typeof Highcharts = Highcharts;
  @HostBinding('class') className = '';
  version: any;
  option = 'Daily';
  offset = 0;
  offset1 = "" ;
  teamname = "-";
  overall: Overall[] = [];
  teams: Team[] = [];
  error: string = "";
  loading = false;
  Changeloading: any;
  date = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
  date2: any;
  date3: any;
  today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
  DateForm!: FormGroup;
  oneDay: number;
  filterText = "";
  dataSource = new MatTableDataSource<any>();
  str: any;
  strGraph: any;
  TKeys: number = 0;
  TClicks: number = 0;
  TDownload: number = 0;
  TUpload: number = 0;
  TUptime: number = 0;
  TPulses: number = 0;
  length: number = 0;
  User: string = '';
  UserURL: string = '';
  displayedColumns: string[] = ['today', 'Username', 'Keys1', 'Clicks', 'DownloadMB', 'UploadMB', 'UptimeSeconds', 'Pulses'];
  pageSize = 25;
  pageIndex = 0;
  dataG: any;
  height = 600;
  updateFlag = false;
  gr: any = 'column';
  myFormattedDate: any;
  MS: Array<any> = [];
  CQ: Array<any> = [];
  username: Array<any> = [];
  keys: Array<any> = [];
  data = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];
  clicks: Array<any> = [];
  datum: Array<any> = [];
  keysG: Array<any> = [];
  clicksG: Array<any> = [];
  start: number = 0;
  end: number = 0;
  i: number = 0;
  j: number = 0;
  title: string = '';
  startG: number = 0;
  endG: number = 0;
  Keys1: any;
  Clicks1: any;
  stringob: any;
  chartOptions: Options = {
    chart: {
      renderTo: 'container',
      type: this.gr,
      height: this.height
    },
    credits: {
      enabled: false
    },
    title: {
      text: this.option + ' stats'
    },
    subtitle: {
      text: ''
    },
    series: [{
      type: 'column',
      name: 'Keys',
      data: [],
      dataLabels: {
        enabled: true
      }
    }, {
      type: 'column',
      name: 'Clicks',
      data: []
    }],
    xAxis: {
      labels: {
          rotation: -45
      }
    },
    yAxis: {
      title: {
          text: ''
      }
    },
    accessibility: {
      enabled: false
    }
  };


  @ViewChild(MatPaginator, { static: true }) paginator!: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort!: MatSort;

  constructor(
    private overallService: OverallService,
    private route: ActivatedRoute,
    private router: Router,
    public sanitizer: DomSanitizer,
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
    this.PushOverall()
  }

  Loading() {
    this.spinner.show();
    this.loading = true;
  }

  Change(id: string) {
    this.Loading();
    this.option = id;
    this.PushOverall();
  }

  onSelectTeam(myteamname: string) {
    this.Loading();
    this.paginator.pageIndex = 0;
    this.teamname = myteamname;
    this.overall = this.dataSource.data;
    this.router.navigate(['/o', this.option, this.offset, this.teamname]);
    this.PushOverall();
  }

  applyFilter(filterValue: string) {
    this.filterText = filterValue.trim();
    this.dataSource.filter = this.filterText.toLowerCase();
    this.overall = this.dataSource.filteredData;
    this.Sum();
    this.str = '';
    this.Graph();
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
    this.router.navigate(['/o', this.option, this.offset, this.teamname]);
    this.PushOverall();
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
      this.router.navigate(['/o', this.option, this.offset, this.teamname]);
      this.PushOverall();
    }
  }

  PushOverall() {
    this.overallService.myoverall(this.option, 'team', this.offset, this.teamname);
    this.getoverall();
    this.getTeam();
  }

  getnr(val:string, val1:string) {
    if (val1 === null) {
      val1 = '-';
    }
    return 'Today: ' + val + '\nYesterday: ' + val1;
  }

  getTeam(): void {
    this.overallService.getTeam().subscribe(
      data => {
        this.teams = data.data;
      },
      (err) => {
        this.error = err;
      }
    );
  }

  getoverall(): void {
    this.dataSource.data = [];
    this.dataSource.filter = '';
    this.filterText = '';
    this.overall = [];
    this.str = '';
    this.strGraph = '';
    this.TKeys = this.TClicks = this.TDownload = this.TUpload = this.TUptime = this.TPulses = 0;
    this.overallService.getOverall().subscribe(
      data => {
        this.dataSource.data = data.data;
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
        this.overall = this.dataSource.filteredData;
        this.Sum();
        this.Graph();
        this.spinner.hide();
        this.loading = false;
        this.Changeloading = 'spinneroff';
      },
      err => {
        this.error = err;
      }
    );
  }

  Sum() {
    this.length = this.overall.length;
    for (this.i = 0; this.i < this.length; this.i++) {
      this.TKeys = this.overall.reduce((sum, item) => sum + item.Keys1, 0);
      this.TClicks = this.overall.reduce((sum, item) => sum + item.Clicks, 0);
      this.TDownload = this.overall.reduce((sum, item) => sum + item.DownloadMB, 0);
      this.TUpload = this.overall.reduce((sum, item) => sum + item.UploadMB, 0);
      this.TUptime = this.overall.reduce((sum, item) => sum + item.UptimeSeconds, 0);
      this.TPulses = this.overall.reduce((sum, item) => sum + item.Pulses, 0);
    }
  }

  Click() {
    //this.overall = this.dataSource.sortData(this.dataSource.filteredData, this.dataSource.sort!);
    //this.dataSource.sort = this.sort;
    this.str = '';
    this.Graph();
  }

  ClickUser(ID = '', Username = '') {
    this.UserURL = 'https://whatpulse.org/stats/users/' + ID + '/';
    this.User = Username;
  }

  ClickCancel() {
    this.UserURL = '';
    this.User = '';
  }

  public handlePage() {
    this.pageIndex = this.paginator.pageIndex;
    this.pageSize = this.paginator.pageSize;
    this.str = '';
    this.Graph();
  }

  ReloadGraph(val: any) {
    if (/*val === 400 || */val === 600 || val === 800) {
      this.height = val;
      this.chartOptions.chart = {
        height: this.height
      };
      this.updateFlag = true;
    }
    if (val === 'line') {
      this.gr = val;
      this.chartOptions.chart = {
        type: 'column',
      };
      this.chartOptions.series[0] = this.chartOptions.series[1] = {
        type: 'line'
      };
      this.updateFlag = true;
    }
    if (val === 'column') {
      this.gr = val;
      this.chartOptions.chart = {
        type: 'column',
      };
      this.chartOptions.series[0] = this.chartOptions.series[1] = {
        type: this.gr
      };
      this.updateFlag = true;
    }this.dataG.length
    if (val === 'bar') {
      this.gr = val;
      //this.height = 600;
      this.chartOptions.chart = {
        type: this.gr,
        height: this.height
      };
      this.chartOptions.series[0] = this.chartOptions.series[1] = {
        type: 'column'
      };
      this.updateFlag = true;
    }
  }

  Graph() {
    this.MS = [];
    this.CQ = [];
    this.username = [];
    this.keys = [];
    this.clicks = [];
    this.length = this.overall.length;
    this.start = this.pageIndex * this.pageSize;
    this.end = this.pageIndex * this.pageSize + this.pageSize;
    this.i = 0;
    if (this.end > this.length) {
      this.end = this.length;
    }
    this.myFormattedDate = ('0' + this.date.getDate()).slice(-2) + '-' + ('0' + (this.date.getMonth() + 1)).slice(-2) + '-' + this.date.getFullYear();
    if (this.option !== '') { this.title = this.option + ' stats'; }
    this.chartOptions.title = {
      text: this.title
    };
    this.chartOptions.subtitle = {
      text: 'Day: ' + this.myFormattedDate
    };
    for (this.i = this.start; this.i < this.end; this.i++) {
      this.username.push([this.overall[this.i].Username]);
    }
    this.chartOptions.xAxis = {
      categories: this.username
    };
    for (this.i = this.start; this.i < this.end; this.i++) {
      this.keys.push([this.overall[this.i].Keys1]);
    }
    this.chartOptions.series[0] = {
      type: 'column',
      name: 'Keys',
      data: this.keys,
      dataLabels: {
        enabled: true,
        allowOverlap: true,
        y: -30,
        rotation: -90
      }
    }
    for (this.i = this.start; this.i < this.end; this.i++) {
      this.clicks.push([this.overall[this.i].Clicks]);
    }
    this.chartOptions.series[1] = {
      type: 'column',
      name: 'Clicks',
      data: this.clicks,
      dataLabels: {
        enabled: true,
        allowOverlap: true,
        y: -30,
        rotation: -90
      }
    };
    this.updateFlag = true;
  }


  titleCaseWord(word: string) {
    if (!word) {
      return word;
    }
    return word[0].toUpperCase() + word.substr(1).toLowerCase();
  }

}
