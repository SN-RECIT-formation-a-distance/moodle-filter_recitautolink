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
        let modal = document.createElement('div');
        modal.classList.add('modal', 'fade', 'autolink_popup');
        let inner2 = document.createElement('div');
        inner2.classList.add('modal-dialog');
        modal.appendChild(inner2);
        let inner = document.createElement('div');
        inner.classList.add('modal-content');
        inner2.appendChild(inner);

        let header = document.createElement('div');
        header.classList.add('modal-header');
        inner.appendChild(header);
        let btn = document.createElement('button');
        btn.classList.add('close');
        btn.innerHTML = '<span aria-hidden="true">&times;</span>';
        btn.setAttribute('data-dismiss', 'modal');
        header.appendChild(btn);
        
        let body = document.createElement('div');
        body.classList.add('modal-body');
        inner.appendChild(body);
        body.appendChild(content);
        
        document.body.appendChild(modal);
        this.popup = modal;
        $(modal).modal({show: true, backdrop: true});
        let that = this;
        $(".modal-backdrop").click(() => $(this.popup).modal('hide'));
        $(modal).on('hidden.bs.modal', function (e) {
            that.destroy()
        })
      }
      destroy(){
          this.popup.remove();
      }
      update(){
        $(this.popup).modal('handleUpdate');
      }
}

recit.filter.autolink.popupIframe = function(url){
    let content = document.createElement('iframe');
    content.src = url;
    let popup = new recit.filter.autolink.Popup(content);
    let selectors_to_hide = [
        'nav', //navbar
        'header', //Course header
        '.activity-nav', //activity nav
        '.activity-navigation', //activity nav
        '.activity-title-container', //activity title
        '#sidepreopen-control', //sidebar drawer
        '#nav-drawer', //drawer
        '#top-footer1', //footer
        '#page-footer', //footer
    ]
    content.onload = () => {
        if (content.contentWindow.document.querySelector('#page-wrapper')){
            content.contentWindow.document.querySelector('#page-wrapper').style.marginTop = '0'; //remove margin from page wrapper
            content.contentWindow.document.body.classList.remove('drawer-open-left'); //This class adds margin left on Boost
            for (let i = 0; i < selectors_to_hide.length; i++){
                if (content.contentWindow.document.querySelector(selectors_to_hide[i])) 
                    content.contentWindow.document.querySelector(selectors_to_hide[i]).style.display = 'none';
            }
        }
        //content.style.height = content.contentWindow.document.documentElement.scrollHeight + 'px'; //adjust iframe to page height
        popup.update();
    }
}