import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})

export class XmlService {
  baseUrl = environment.url;
  get: string = "";
  get1: string = "";
  offset: number = 0;
  teamname: string = "";
  xml: any;
  teams: any;

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

  getXml(): Observable<any> {
    return this.http
      .get(this.baseUrl + '/' + this.get + '1', {
        headers: this.headers,
        params: {
          get: this.get,
          Offset: this.offset,
          Team: this.teamname,
        },
        responseType: 'text'
      })
      .pipe(
        map((res) => {
          this.xml = res;
          return this.xml;
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
