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
 
M.recit = M.recit || {};
M.recit.filter = M.recit.filter || {};
M.recit.filter.autolink = M.recit.filter.autolink || {};

M.recit.filter.autolink.state = {
    loadQRCodes: false,
    loadPopupFeedback: false
}

M.recit.filter.autolink.Popup = class {
    constructor(content, showTitle, showFooter, maxWidth) {
        this.title = null;

        showTitle = (typeof showTitle === 'undefined' ? true : showTitle);
        showFooter = (typeof showFooter === 'undefined' ? false : showFooter);
        maxWidth = (typeof maxWidth === 'undefined' ? true : maxWidth);

        this.modal = document.createElement('div');
        this.modal.classList.add('modal', 'fade');

        if(maxWidth){
            this.modal.classList.add('recitautolink_popup');
        }

        this.dialog = document.createElement('div');
        this.dialog.classList.add('modal-dialog', 'modal-dialog-centered', 'modal-dialog-scrollable');
        this.modal.appendChild(this.dialog);

        let inner = document.createElement('div');
        inner.classList.add('modal-content');
        this.dialog.appendChild(inner);

        if(showTitle){
            let header = document.createElement('div');
            header.classList.add('modal-header');
            inner.appendChild(header);
            this.title = document.createElement('h3');
            let btn = document.createElement('button');
            btn.classList.add('close');
            btn.innerHTML = '<span aria-hidden="true">&times;</span>';
            btn.setAttribute('data-dismiss', 'modal');
            btn.onclick = this.destroy.bind(this);
            header.appendChild(this.title);
            header.appendChild(btn);
        }

        this.body = document.createElement('div');
        this.body.classList.add('modal-body');
        this.body.style.scrollbarWidth = "thin";
        inner.appendChild(this.body);
        if(content !== null){
            this.body.appendChild(content);
        }
        
        if(showFooter){
            this.footer = document.createElement('div');
            this.footer.classList.add('modal-footer');
            inner.appendChild(this.footer);
        }

        document.body.appendChild(this.modal);
        /*$(this.modal).modal({show: true, backdrop: true});

        let that = this;
        $(".modal-backdrop").click(() => $(this.modal).modal('hide'));
        $(this.modal).on('hidden.bs.modal', function (e) {
            that.destroy()
        })*/
        this.modal.classList.add('show');

        this.backdrop = document.createElement('div');
        this.backdrop.classList.add('modal-backdrop', 'fade', 'show');
        this.backdrop.setAttribute('data-backdrop', 'static');
        document.body.appendChild(this.backdrop);
      }

      destroy(){
            this.modal.classList.remove('show');
            this.backdrop.classList.remove('show');
            this.modal.remove();
            this.backdrop.remove();
      }
      
      update(){
        //$(this.modal).modal('handleUpdate');
      }
}

M.recit.filter.autolink.popupIframe = function(url, className){
    let content = document.createElement('iframe');
    content.src = url;
    let popup = new M.recit.filter.autolink.Popup(content, true);
   
    if (className.length > 0){
        popup.modal.classList.add(className);
    }

    content.onload = () => {
        let title = content.contentDocument.title;
        if (title.includes('|')){
            title = title.split('|')[0]; //Only keep activity title, not Moodle name
        }
        popup.title.innerText = title;
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

        //popup.update();
    }
}

M.recit.filter.autolink.popupFeedback = function(content, dismissButton){
    let popup = new M.recit.filter.autolink.Popup(null, false, true, false);

    popup.dialog.classList.add('modal-xl'); 
    popup.body.appendChild(content);     
    popup.footer.appendChild(dismissButton); 

    let el = dismissButton.querySelector('[data-close-modal]')
    if(el){
        el.onclick = popup.destroy.bind(popup);
    }

    popup.update();   
}

M.recit.filter.autolink.loadQRCodes = function(){
    if(M.recit.filter.autolink.state.loadQRCodes){
        return;
    }

    let placeholders = document.querySelectorAll("[data-qrcode-url]");

    for(let item of placeholders){
        let options = {text: item.getAttribute('data-qrcode-url'), width: 256, height: 256};

        if(item.getAttribute('data-width') === '100%'){
            options.width = window.innerWidth;
            options.height = window.innerWidth;

            new QRCode(item, options);

            let img = item.querySelector('img');
            img.style.width = '100%';
        }
        else{
            new QRCode(item, options);
        }

        M.recit.filter.autolink.state.loadQRCodes = true;
    }
}

M.recit.filter.autolink.loadOptionFeedback = function(){
    if( M.recit.filter.autolink.state.loadPopupFeedback){
        return;
    }

    let elList = document.querySelectorAll('div[data-filter-recitactivity="feedback"]');
    for(let el of elList){
        M.recit.filter.autolink.popupFeedback(el.childNodes[0], el.childNodes[1]);
        M.recit.filter.autolink.state.loadPopupFeedback = true;
    }
}

M.recit.filter.autolink.loadLazyOptions = function(){
    M.recit.filter.autolink.loadQRCodes();
    M.recit.filter.autolink.loadOptionFeedback();
}

document.addEventListener('DOMContentLoaded', function(){ 
    M.recit.filter.autolink.loadLazyOptions();
}, false);