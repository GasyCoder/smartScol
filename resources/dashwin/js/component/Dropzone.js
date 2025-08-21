import { Dropzone } from "dropzone";
Dropzone.autoDiscover = false;
export default function UploadZone(selector) {
    let elm = document.querySelectorAll(selector);
    if( elm != 'undefined' && elm != null ){
      elm.forEach(item => {
        let itemId = item.id;
        let maxFiles = item.dataset.maxFiles ? parseInt(item.dataset.maxFiles) : null;
        let maxFilesize = item.dataset.maxFilesize ? parseInt(item.dataset.maxFilesize) : 256;
        let acceptedFiles = item.dataset.acceptedFiles ? item.dataset.acceptedFiles : null;

        //add styling Class 
        item.classList.add('dropzone');
        
        let myDropzone = new Dropzone(`#${itemId}`,{
          url: "image",
          maxFilesize: maxFilesize,
          maxFiles: maxFiles,
          acceptedFiles: acceptedFiles
        });
      })
    }
};