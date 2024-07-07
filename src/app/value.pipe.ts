import { Pipe, PipeTransform } from '@angular/core';
import { DatePipe } from '@angular/common';

@Pipe({
  name: 'value',
})
export class ValuePipe implements PipeTransform {
  GB: any;
  TB: any;
  y: any;
  x: any;
  diff: any;
  neg: any;
  pipe: any;
  seconds: any;
  minutes: any;
  hours: any;
  days: any;
  response: any;

  public constructor() {}

  transform(value: any, para: any, para2: any): any {
    if (para === 'bytes') {
      if (value < 0 || value === undefined || value === null) {
        this.neg = -1;
      } else {
        this.neg = 1;
      }
      this.GB = 1024;
      this.TB = 1024 * 1024;
      this.x = value * this.neg;
      if (this.x === 0) {
        return '0 MB';
      } else if (this.x < 1024) {
        return this.x.toFixed(0) * this.neg + ' MB';
      } else if (this.x > this.GB && this.x < this.TB) {
        this.x = this.x / this.GB;
        return this.x.toFixed(2) * this.neg + ' GB';
      } else if (this.x > this.TB) {
        this.x = this.x / this.TB;
        return this.x.toFixed(2) * this.neg + ' TB';
      }
    }

    if (para === 'mi-km') {
      value = Math.round((value * 1.609344)*1000)/1000
      return value
    }

    if (para === 'misdate') {
      this.pipe = new DatePipe('nl-NL');
      if (value === '1000-01-01 00:00:00') {
        return 'No lastpulse found!';
      } else {
        value = this.pipe.transform(value, 'hh:mm:ss dd-MM-yyyy');
      }
      return value;
    }

    if (para === 'rank') {
      this.diff = value - para2;
      if (para2 === null || para2 === '') {
        // return '';
      } else if (value < para2) {
        return '<div class="arrowrank bounceup"><p class="fa fa-arrow-up arrowup"></p></div>' + this.diff * -1;
      } else if (value > para2) {
        return '<div class="arrowrank bouncedown"><p class="fa fa-arrow-down arrowdown"></p></div>' + this.diff;
      } else if (value === para2) {
        return '<div class="arrowrank"><p class="fa fa-ellipsis-h still"></p></div>';
      }
    }

    if (para === 'display') {
      if (value < 0) {
        return '<div class="arrowrank bounceup"><p class="fa fa-arrow-up arrowup"></p></div>' + -1 * value;
      } else if (value > 0) {
        return '<div class="arrowrank bouncedown"><p class="fa fa-arrow-down arrowdown"></p></div>' + value + '</div>';
      } else if (value === 0) {
        return '<div class="arrowrank"><p class="fa fa-ellipsis-h still"></p></div>';
      }
    }

    if (para === 'hms') {
      this.days = Math.trunc(value / (60 * 60 * 24))
      this.hours = Math.trunc((value - (this.days * (60 * 60 * 24))) / (60 * 60));
      this.minutes = Math.trunc((value - (this.days * (60 * 60 * 24)) - (this.hours * (60 * 60))) / 60);
      this.seconds = value - (this.days * (60 * 60 * 24)) - (this.hours * (60 * 60)) - (this.minutes * 60);
      this.response = '';

      if (this.days > 0) {
        this.response = this.response + this.days + 'd';
      }

      if (this.hours > 0) {
        this.response = this.response + this.hours + 'h';
      }

      if (this.minutes > 0) {
        this.response = this.response + this.minutes + 'm';
      }

      if (this.seconds > 0) {
        this.response = this.response + this.seconds + 's';
      } else if (this.seconds === 0) {
        this.response = '0';
      }

      return this.response;
    }
  }
}

@Pipe({
  name: 'unescape',
})
export class UnescapePipe implements PipeTransform {
  transform(value: any, args?: any): any {
    const doc = new DOMParser().parseFromString(value, 'text/html');
    return doc.documentElement.textContent;
  }
}
