// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   filter_recitautolink
 * @copyright 2019 RÉCIT 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
 
var recit = recit || {};
recit.filter = recit.filter || {};
recit.filter.autolink = recit.filter.autolink || {};

recit.filter.autolink.Popup = class {
    constructor(content) {
        this.popup = document.createElement('div');
        this.popup.classList.add('autolink_popup-overlay');
        this.popup.onclick = this.destroy.bind(this);
        let inner = document.createElement('div');
        inner.classList.add('autolink_popup');
        inner.appendChild(content);
        this.popup.appendChild(inner)
        document.body.appendChild(this.popup)
        content.onload = () => {
            if (content.contentWindow.document.querySelector('#page-wrapper')){
                content.contentWindow.document.querySelector('#page-wrapper').style.marginTop = '0'; //remove margin from page wrapper
                content.contentWindow.document.querySelector('nav').style.display = 'none'; //remove navbar
                content.contentWindow.document.querySelector('header').style.display = 'none'; //remove course header
                if (content.contentWindow.document.querySelector('#sidepreopen-control')) content.contentWindow.document.querySelector('#sidepreopen-control').style.display = 'none'; //remove sidebar icon drawer
                content.contentWindow.document.querySelector('#top-footer1').style.display = 'none'; //remove footer
            }
            content.style.height = content.contentWindow.document.documentElement.scrollHeight + 'px'; //adjust iframe to page height
        }
      }
      destroy(){
          this.popup.remove();
      }
}

recit.filter.autolink.popupIframe = function(url){
    let iframe = document.createElement('iframe');
    iframe.src = url;
    new recit.filter.autolink.Popup(iframe);
}
