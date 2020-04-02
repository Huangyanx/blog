/*
 filedrag.js - HTML5 File Drag & Drop demonstration
 Featured on SitePoint.com
 Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
 */
(function() {

    // getElementById
    function $id(id) {
        return document.getElementById(id);
    }
    //判断是否是中文
    function isChinese(temp)
    {
        var re = new RegExp("[\\u4E00-\\u9FFF]+","g");
        if(!re.test(temp)) return false ;
        return true ;
    }
    //转码为Unicode
    var GB2312UnicodeConverter={
        ToUnicode:function(str){
            return escape(str).toLocaleLowerCase().replace(/%u/gi,'\\u');
        }
        ,ToGB2312:function(str){
            return unescape(str.replace(/\\u/gi,'%u'));
        }
    };




    // file drag hover
    function FileDragHover(e) {
        e.stopPropagation();
        e.preventDefault();
        e.target.className = (e.type == "dragover" ? "hover" : "");
    }


    // file selection
    function FileSelectHandler(e) {
        //hide drag div
        var filedrag = $id("filedrag");
        var browse = $id("browse-files");
//		filedrag.style.display = "none";

        // cancel event and hover styling
        FileDragHover(e);

        var files = e.target.files || e.dataTransfer.files;
        //console.log(files);

        // process all File objects  只上传一个文件，可能是中文问题  中文转Unicode
        //无法修改对象的值  重新用另外变量赋值
        for (var i = 0, f; f = files[i]; i++) {
            if(isChinese(f.name)){
                var name=GB2312UnicodeConverter.ToUnicode(f.name);
                var k={};
                k.name=name;
                k.lastModified=f.lastModified;
                k.lastModifiedDate=f.lastModifiedDate;
                k.size=f.size;
                k.type=f.type;
                k.webkitRelativePath=f.webkitRelativePath;
                //console.log(k);
                if(f.type!='')
                    UploadFile(k,callback);
                    //si_createlist(k);

            }else {
                if(f.type!='')
                   UploadFile(f,callback);
                //si_createlist(f);
            }

        }
        function callback() {
                if((this.readyState ==4) && this.status==200 ){
                    var rtn= JSON.parse(this.responseText);
                    //console.log(rtn);
                    if (rtn.status && rtn.name!=="undefined"){
                        $id("upfilename").value=rtn.name;
                        //console.log(rtn.name);
                    } else if(rtn.status==3){
                        $id("upfilename").value='';
                    }

        }

    }

    // upload JPEG files
    function UploadFile(file,callback) {
        // following line is not necessary: prevents running on SitePoint servers
        if (location.host.indexOf("sitepointstatic") >= 0) return

        var xhr = new XMLHttpRequest();
        if (xhr.upload) {
            xhr.onreadystatechange = callback;

            };
            var name= $id("upfilename").value||'';
            var action= $id("type")?$id("type").value:"upload";
            xhr.open("POST", "index.php?c=back&a="+action+"&name="+name+"&fname="+file.webkitRelativePath, true);
            xhr.setRequestHeader("X-FILENAME", file.name);
            xhr.send(file);

        }

    }

    //folder handler
    function FileFolderHandler(){

    }


    // initialize
    function Init() {

        var folderInput = $id("folderInput");

        // file select
       // fileselect.addEventListener("change", FileSelectHandler, false);
        //folder upload
        folderInput.addEventListener("change",FileSelectHandler,false);

        // is XHR2 available?
        var xhr = new XMLHttpRequest();
        if (xhr.upload) {

            // file drop
           // filedrag.addEventListener("dragover", FileDragHover, false);
            //filedrag.addEventListener("dragleave", FileDragHover, false);
            //filedrag.addEventListener("drop", FileSelectHandler, false);
            //bodytest.addEventListener("dragover",showdragdiv,false);
//			filedrag.style.display = "block";

        }

    }

    // call initialization file
    if (window.File && window.FileList && window.FileReader) {
        Init();
    }
    function showdragdiv(){
        var filedrag = $id("filedrag");
        filedrag.style.display = "block";
    }

})();
