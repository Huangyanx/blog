var obj=null;
var As=document.getElementById('nav').getElementsByTagName('a');
obj = As[0];
for(i=1;i<As.length;i++){
    if(As[i].getAttribute('href')===location.pathname ) {
        obj=As[i]; break;
    }
}

//console.log(window.location.href.indexOf(As[i].href)>=0);
obj.id='nav_current';
