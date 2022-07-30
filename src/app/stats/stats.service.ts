import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})

export class StatsService {
  baseUrl = environment.url;
  get: string = "";
  get1: string = "";
  offset: number = 0;
  teamname: string = "";
  stats: any;
  teams: any;
  ALC: any;
  error = '';
  date = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
  today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());

  constructor(private http: HttpClient) {}

  headers = new HttpHeaders({
    'Content-Type': 'application/json',
    responseType: 'text',
  });

  mystats(varget = "", varget1 = "", varoffset = 0, varteamname = "") {
    this.get = varget;
    this.get1 = varget1;
    this.offset = varoffset;
    this.teamname = varteamname;
  }

  getStats(): Observable<any> {
    return this.http.get(this.baseUrl + '/' + this.get, {
        headers: this.headers,
        params: {
          get: this.get,
          Offset: this.offset,
          Team: this.teamname,
        },
      })
      .pipe(
        map((res) => {
          this.stats = res;
          return this.stats;
        })
      );
  }

  getTeam(): Observable<any> {
    return this.http
      .get(this.baseUrl + '/' + this.get1, {
        headers: this.headers,
        params: {
          get: this.get1,
          Offset: this.offset,
          Team: this.teamname,
        },
      })
      .pipe(
        map((res) => {
          this.teams = res;
          return this.teams;
        })
      );
  }

  getALC(): Observable<any> {
    return this.http
      .get(this.baseUrl + '/' + this.get + 'ALC', {
        headers: this.headers,
        params: {
          get: this.get1,
          Offset: this.offset,
          Team: this.teamname,
        },
      })
      .pipe(
        map((res) => {
          this.ALC = res;
          return this.ALC;
        })
      );
  }
}
