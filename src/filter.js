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
 * This filter must be put before Auto-linking with Manage Filters to work properly.
 *
 * @package    filter_recitactivity
 * @copyright  2019 RECIT
 * @license    {@link http://www.gnu.org/licenses/gpl-3.0.html} GNU GPL v3 or later
 */
 
var recit = recit || {};
recit.filter = recit.filter || {};
recit.filter.autolink = recit.filter.autolink || {};

recit.filter.autolink.Popup = class {
    constructor(content, showTitle, showFooter, maxWidth) {        
        showTitle = (typeof showTitle === 'undefined' ? true : showTitle);
        showFooter = (typeof showFooter === 'undefined' ? false : showFooter);
        maxWidth = (typeof maxWidth === 'undefined' ? true : maxWidth);

        let modal = document.createElement('div');
        modal.classList.add('modal', 'fade');

        if(maxWidth){
            modal.classList.add('recitautolink_popup');
        }

        let inner2 = document.createElement('div');
        inner2.classList.add('modal-dialog', 'modal-dialog-centered');
        modal.appendChild(inner2);

        let inner = document.createElement('div');
        inner.classList.add('modal-content');
        inner2.appendChild(inner);

        if(showTitle){
            let header = document.createElement('div');
            header.classList.add('modal-header');
            inner.appendChild(header);
            this.title = document.createElement('h3');
            let btn = document.createElement('button');
            btn.classList.add('close');
            btn.innerHTML = '<span aria-hidden="true">&times;</span>';
            btn.setAttribute('data-dismiss', 'modal');
            header.appendChild(this.title);
            header.appendChild(btn);
        }

        this.body = document.createElement('div');
        this.body.classList.add('modal-body');
        inner.appendChild(this.body);
        if(content !== null){
            this.body.appendChild(content);
        }
        
        if(showFooter){
            this.footer = document.createElement('div');
            this.footer.classList.add('modal-footer');
            inner.appendChild(this.footer);
        }

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

recit.filter.autolink.popupIframe = function(url, className){
    let content = document.createElement('iframe');
    content.src = url;
    let popup = new recit.filter.autolink.Popup(content, true);
    if (className.length > 0){
        popup.popup.classList.add(className);
    }
    content.onload = () => {
        popup.title.innerText = content.contentDocument.title;
        if (!content.contentWindow.document.querySelector('iframe')){
            content.contentWindow.document.querySelector('html').style.height = 'auto'; //adjust body height to content instead of 100%
            content.style.height = content.contentWindow.document.documentElement.offsetHeight + 'px'; //adjust iframe to page height
        }
        else{
            // in case h5p (iframe within iframe within another iframe) that keep refreshing screen endless because of the scrollbar
            let css = "::-webkit-scrollbar {width: 0; background: transparent;}";
            let style = document.createElement("style");
            // / Remove scrollbar space and  just make scrollbar invisible 
            style.setAttribute('type', 'text/css'); 
            style.appendChild(document.createTextNode(css));
            content.contentWindow.document.head.appendChild(style);
        }

        popup.update();
    }
}

