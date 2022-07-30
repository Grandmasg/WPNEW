import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})

export class OverallService {
  baseUrl = environment.url;
  get: string = "";
  get1: string = "";
  offset: number = 0;
  teamname: string = "";
  overall: any;
  teams: any;
  error = '';
  date = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
  today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());

  constructor(private http: HttpClient) {}

  headers = new HttpHeaders({
    'Content-Type': 'application/json',
    responseType: 'text',
  });

  myoverall(varget = "", varget1 = "", varoffset = 0, varteamname = "") {
    this.get = varget;
    this.get1 = varget1;
    this.offset = varoffset;
    this.teamname = varteamname;
  }

  getOverall(): Observable<any> {
    return this.http.get(this.baseUrl + '/' + this.get + '1', {
        headers: this.headers,
        params: {
          get: this.get,
          Offset: this.offset,
          Team: this.teamname,
        },
      })
      .pipe(
        map((res) => {
          this.overall = res;
          return this.overall;
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

}
