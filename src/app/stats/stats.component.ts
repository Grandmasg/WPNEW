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

import { Stats, Team, ALC } from '../stats/stats';
import { StatsService } from '../stats/stats.service';

@Component({
  selector: 'app-stats',
  templateUrl: './stats.component.html',
  styleUrls: ['./stats.component.scss']
})
export class StatsComponent implements OnInit {
  Highcharts: typeof Highcharts = Highcharts;
  @HostBinding('class') className = '';
  version: any;
  option = 'Daily';
  offset = 0;
  offset1 = "" ;
  teamname = "-";
  stats: Stats[] = [];
  teams: Team[] = [];
  ALC: ALC[] = [];
  ALCchanged: any;
  ALCadded: any;
  ALCleft: any;
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
  displayedColumns: string[] = ['today', 'Username', 'StatsKeys', 'StatsClicks', 'StatsDownloadMB', 'StatsUploadMB', 'StatsUptimeSeconds', 'StatsPulses'];
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
  chartOptions1: Options = {
    chart: {
      renderTo: 'container',
      type: this.gr,
      height: this.height
    },
    credits: {
      enabled: false
    },
    title: {
      text: 'Daily stats last 21 days'
    },
    series: [{
      type: 'column',
      name: 'Keys',
      data: []
    }, {
      type: 'column',
      name: 'Clicks',
      data: []
    }],
    xAxis: {
      labels: {
          rotation: -60
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
    private statsService: StatsService,
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
    this.PushStats()
  }

  Loading() {
    this.spinner.show();
    this.loading = true;
  }

  Change(id: string) {
    this.Loading();
    this.option = id;
    this.PushStats();
  }

  onSelectTeam(myteamname: string) {
    this.Loading();
    this.paginator.pageIndex = 0;
    this.teamname = myteamname;
    this.stats = this.dataSource.data;
    this.router.navigate(['/s', this.option, this.offset, this.teamname]);
    this.PushStats();
  }

  applyFilter(filterValue: string) {
    this.filterText = filterValue.trim();
    this.dataSource.filter = this.filterText.toLowerCase();
    this.stats = this.dataSource.filteredData;
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
    this.router.navigate(['/s', this.option, this.offset, this.teamname]);
    this.PushStats();
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
      this.router.navigate(['/s', this.option, this.offset, this.teamname]);
      this.PushStats();
    }
  }

  PushStats() {
    this.statsService.mystats(this.option, 'team', this.offset, this.teamname);
    this.getStats();
    this.getTeam();
    this.getALC();
  }

  getTeam(): void {
    this.statsService.getTeam().subscribe(
      data => {
        this.teams = data.data;
      },
      (err) => {
        this.error = err;
      }
    );
  }

  getALC(): void {
    this.ALC = [];
    this.ALCchanged = [];
    this.ALCadded = [];
    this.ALCleft = [];
    this.statsService.getALC().subscribe(
      data => {
        this.ALCchanged = data[`Changed`];
        this.ALCadded = data[`Added`];
        this.ALCleft = data[`Left`];
      },
      (err) => {
        this.error = err;
      }
    );
  }

  getStats(): void {
    this.dataSource.data = [];
    this.dataSource.filter = '';
    this.filterText = '';
    this.stats = [];
    this.str = '';
    this.strGraph = '';
    this.TKeys = this.TClicks = this.TDownload = this.TUpload = this.TUptime = this.TPulses = 0;
    this.statsService.getStats().subscribe(
      data => {
        this.dataSource.data = data.data;
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
        this.stats = this.dataSource.filteredData;
        if (this.option === 'daily') {
          this.dataG = data[`dataG`];
        }

        this.Sum();
        this.Graph();
        if (this.option === 'daily') {
          this.GraphG();
        }
        this.spinner.hide();
        this.loading = false;
      },
      (err) => {
        this.error = err;
      }
    );
  }

  Sum() {
    this.length = this.stats.length;
    this.TKeys = this.stats.reduce((sum, item) => sum + item.StatsKeys, 0);
    this.TClicks = this.stats.reduce((sum, item) => sum + item.StatsClicks, 0);
    this.TDownload = this.stats.reduce((sum, item) => sum + item.StatsDownloadMB, 0);
    this.TUpload = this.stats.reduce((sum, item) => sum + item.StatsUploadMB, 0);
    this.TUptime = this.stats.reduce((sum, item) => sum + item.StatsUptimeSeconds, 0);
    this.TPulses = this.stats.reduce((sum, item) => sum + item.StatsPulses, 0);
  }

  Click() {
    //this.stats = this.dataSource.sortData(this.dataSource.filteredData, this.dataSource.sort!);
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
      this.chartOptions.chart = this.chartOptions1.chart = {
        height: this.height
      };
      this.updateFlag = true;
    }
    if (val === 'line') {
      this.gr = val;
      this.chartOptions.chart = this.chartOptions1.chart = {
        type: 'column',
      };
      this.chartOptions.series[0] = this.chartOptions.series[1] = this.chartOptions1.series[0] = this.chartOptions1.series[1] = {
        type: 'line'
      };
      this.updateFlag = true;
    }
    if (val === 'column') {
      this.gr = val;
      this.chartOptions.chart = this.chartOptions1.chart = {
        type: 'column',
      };
      this.chartOptions.series[0] = this.chartOptions.series[1] = this.chartOptions1.series[0] = this.chartOptions1.series[1] = {
        type: this.gr
      };
      this.updateFlag = true;
    }this.dataG.length
    if (val === 'bar') {
      this.gr = val;
      //this.height = 600;
      this.chartOptions.chart = this.chartOptions1.chart = {
        type: this.gr,
        height: this.height
      };
      this.chartOptions.series[0] = this.chartOptions.series[1] = this.chartOptions1.series[0] = this.chartOptions1.series[1] = {
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
    this.length = this.stats.length;
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
      this.username.push([this.stats[this.i].Username]);
    }
    this.chartOptions.xAxis = {
      categories: this.username
    };
    for (this.i = this.start; this.i < this.end; this.i++) {
      this.keys.push([this.stats[this.i].StatsKeys]);
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
      this.clicks.push([this.stats[this.i].StatsClicks]);
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

  GraphG() {
    this.datum = [];
    this.keysG = [];
    this.clicksG = [];
    this.startG = 0;
    this.endG = this.dataG?.length || 0;
    this.i = 0;
    this.j = 0;
    for (this.i = this.startG; this.i < this.endG; this.i++) {
      if (this.i < this.endG - 1) {
        this.datum.push([this.dataG[this.i].datum]);
      }
    }
    this.chartOptions1.xAxis = {
      categories: this.datum
    };
    for (this.i = this.startG; this.i < this.endG; this.i++) {
      this.j = this.i + 1;
      if (this.j < this.endG) {
        this.Keys1 = this.dataG[this.i].Keys1 - this.dataG[this.j].Keys1;
        this.keysG.push([this.Keys1]);
      }
    }
    this.chartOptions1.series[0] = {
      type: 'column',
      name: 'Keys',
      data: this.keysG,
      dataLabels: {
        enabled: true,
        allowOverlap: true,
        y: -30,
        rotation: -90
      }
    };
    for (this.i = this.startG; this.i < this.endG; this.i++) {
      this.j = this.i + 1;
      if (this.j < this.endG) {
        this.Clicks1 = this.dataG[this.i].Clicks - this.dataG[this.j].Clicks;
        this.clicksG.push([this.Clicks1]);
      }
    }
    this.chartOptions1.series[1] = {
      type: 'column',
      name: 'Clicks',
      data: this.clicksG,
      dataLabels: {
        enabled: true,
        allowOverlap: true,
        y: -30,
        rotation: -90
      }
    };
    this.updateFlag = true;


    this.strGraph = '{"chart": {"valueBgColor": "#4e4e4e", "valueBgAlpha": "100", "caption": "' + this.titleCaseWord(this.option) + ' stats last 21 days", "theme": "DeApen", "showValues": "1", "rotateValues": "1", "labelDisplay": "rotate", ' + '"slantLabel": "1", "valueFontSize": "10"},"categories": [{ "category": [';
    for (this.i = this.startG; this.i < this.endG; this.i++) {
      if (this.i < this.endG - 1) {
        this.strGraph += '{"label": "' + this.dataG[this.i].datum + '"},';
      }
    }
    this.strGraph = this.strGraph.slice(0, -2);
    this.strGraph += ' }] }], "dataset": [{ "seriesname": "Keys", "data": [';
    for (this.i = this.startG; this.i < this.endG; this.i++) {
      this.j = this.i + 1;
      if (this.j < this.endG) {
        this.Keys1 = this.dataG[this.i].Keys1 - this.dataG[this.j].Keys1;
        this.strGraph += '{"value": "' + this.Keys1 + '"},';
      }
    }
    this.strGraph = this.strGraph.slice(0, -2);
    this.strGraph += '}] }, { "seriesname": "Clicks", "data": [';
    this.i = this.j = 0;
    for (this.i = this.startG; this.i < this.endG; this.i++) {
      this.j = this.i + 1;
      if (this.j < this.endG) {
        this.Clicks1 = this.dataG[this.i].Clicks - this.dataG[this.j].Clicks;
        this.strGraph += '{"value": "' + this.Clicks1 + '"},';
      }
    }
    this.strGraph = this.strGraph.slice(0, -2);
    this.strGraph += '}] }] }';
  }

  titleCaseWord(word: string) {
    if (!word) {
      return word;
    }
    return word[0].toUpperCase() + word.substr(1).toLowerCase();
  }

}
