function FixOut(evt, direction){
    var row = evt.target;
    MouseFix('off', row);
}


function MouseFix(direction, row){
    //Aika monimutkainen prosedyyri, jotta kommentti-ikonien väri olisi oikea hiiren ollessa päällä
   var iconover = false;
   if (row.tagName=='SPAN'){
       var row = row.parentNode;
   }
   if (row.tagName=='I'){
       var row = row.parentNode.parentNode;
       iconover = true;
   }
   var icons = row.getElementsByClassName('fa');
   var icon = icons[0];
   var commentcount = icon.getAttribute('commentcount');
   if(commentcount==0){
       if (direction=='on'){
           icon.style.color='white';
       }
       else{
           icon.style.color='rgb(171, 3, 3)';
       }
   }
   else{
       if (direction=='on'){
           icon.style.color='rgb(171, 3, 3)';
           if(iconover==true){
           }
       }
       else{
           icon.style.color='white';
       }
   }
}

function CommentClick(evt){
    var previous = document.getElementById('ncdiv');
    if (previous !== null){
        ClearContent(previous);
        document.body.removeChild(previous);
    }
    var icon = evt.target;
    var rect = icon.getBoundingClientRect();
    var commentdiv = DomEl('div','ncdiv','commentlist');
    var messuid = icon.getAttribute('messuid');
    var clist = document.getElementById('clist_' + messuid).cloneNode(true);
    commentdiv.appendChild(clist);
    commentdiv.style.left = rect.left + 20 + "px";
    commentdiv.style.top = rect.top + 5 + "px";
    commentdiv.addEventListener('click',RemoveClist,false);
    document.body.appendChild(commentdiv);
}

function RemoveClist(evt){
    var previous = document.getElementById('ncdiv');
    ClearContent(previous);
    document.body.removeChild(previous);
}

function RemoveWordView(evt){
    var previous = document.getElementById('wordview');
    ClearContent(previous);
    previous.style.display="none";
}


function FixOver(evt){
    var row = evt.target;
    MouseFix('on', row);
}

function submitedit(){
   //Tallenna muokattu arvo erikseen
   document.getElementById("editedsong_hidden").value = document.getElementById("editarea").value;
   document.getElementById("sbut").click();
}


function ShowWords(evt){

    var link = evt.target;
    while(link.tagName!=="A"){
        link = link.children[0];
    }


    if(['jklink','pyhalink'].indexOf(link.id)>-1){
        //Erityistapaus: jumalan karitsa ja pyhä
        var sparent = link.parentNode;
        while(sparent.tagName!=="TR"){
            sparent = sparent.parentNode;
        }
        var select = link.parentNode.parentNode.children[1].children[0];
        var songname = select[select.selectedIndex].id.replace("link_","song_");
    }
    else{
        var songname = link.id.replace("link_","song_");
    }
    
    var rect = link.getBoundingClientRect();
    var commentdiv = document.getElementById("wordview");
    ClearContent(commentdiv);

    var addremover = false;
    var contrect = document.getElementById('maincontainer').getBoundingClientRect();
    commentdiv.style.left = contrect.left + 20  + "px" ;

    if(document.getElementById(songname)==undefined){
        commentdiv.removeEventListener('click', RemoveWordView);
        //Lisää sanat, jos laulua ei löydy
        var title = document.getElementById("editedtitle");
        //Laulun otsikko
        title.innerText = songname.replace("song_","").replace(/_/g," ");

        var worddiv = document.getElementById("editor").cloneNode(true);
        var editarea = document.getElementById("editarea");
        document.getElementById("edited_song_name").value=songname;
        worddiv.style.display="block";
        commentdiv.appendChild(worddiv);

        commentdiv.style.top = "5em";
    }
    else{
        var worddiv = document.getElementById(songname).cloneNode(true);
        addremover = true;
        commentdiv.appendChild(worddiv);
        commentdiv.style.top = rect.top + 5 + "px";
    }
    if(addremover==true){
        commentdiv.addEventListener('click',RemoveWordView,false);
    }

    commentdiv.style.display="block";
    if (rect.top>=document.body.clientHeight/3*2){
        //jos 2/3 ylhäältä
        commentdiv.style.top = rect.top - commentdiv.offsetHeight/4*3;
    }
    else if (rect.top>=document.body.clientHeight/2){
        //jos alle puolenvälin
        commentdiv.style.top = rect.top - commentdiv.offsetHeight/2;
    }
    else{
        var fhrect = document.getElementById('firstheader').getBoundingClientRect();
        commentdiv.style.top = fhrect.top + 20 + "px";
    }
}

function UpdateLyrics(evt){
    var td = evt.target;
    if(td.tagName=="SELECT"){
        //Jos Jumalan karitsa tai Pyhä
        return 0;
    }
    if (td.tagName=="INPUT"){
        td = td.parentElement;
    }
    //var tr = td.parentNode;
    var newname = td.parentElement.children[1].children[0].value;
    var linkid = "link_" + newname.replace(/ /g,"_");
    var songid = "song_" + newname.replace(/ /g,"_");
    var link = td.parentElement.children[2].children[0];
    link.id = linkid;
    if(document.getElementById(songid)==undefined){
        link.innerText = "Lisää sanat";
    }
    else{
        link.innerText = "Katso sanoja";
    }
}

function UpdateLit(type){

    if(type=="Jumalan karitsa"){
        var sel = document.getElementById('Jumalan_karitsa_select');
        var version = sel[sel.selectedIndex].id.replace(/link_/g,'');
        document.getElementById('jumalan_karitsa').value=version;
        console.log(document.getElementById('jumalan_karitsa').value);
    }
    else{
        var sel = document.getElementById('Pyhä-hymni_select');
        var version = sel[sel.selectedIndex].id.replace(/link_/g,'');
        document.getElementById('pyhä-hymni').value=version;
        console.log(document.getElementById('pyhä-hymni').value);
    }

    if(sel[sel.selectedIndex].innerText == "Jokin muu"){
        console.log("...");
        var this_input = document.createElement('input');
        this_input.className = "liteditinput";
        this_input.name = "new_" + type.replace(/ /g,'_');
        sel.parentNode.appendChild(this_input);
        $( ".liteditinput" ).autocomplete({ source: songnames });
    }
    else{
        for(var idx=0;idx<sel.parentNode.children.length;idx++){
            var thischild = sel.parentNode.children[idx];
            if(thischild.tagName=="INPUT")
            {
                sel.parentNode.removeChild(thischild);
            }
        }
    }
}

function SelectMessu (evt){
    var td = evt.target;
    if (td.tagName=='I'){
        //Jos klikattu kommentti-ikonia
        return 0;
    }
    if (td.tagName=='SPAN'){
        td = evt.target.parentNode;
    }
    if (td.hasAttribute('id')){
        var thisid = td.getAttribute('id');
        var messuid = thisid.substring(thisid.indexOf('_')+1);
        var params = {"messuid":messuid,
                      "teema": td.getAttribute('teema'),
                      "pvm": td.getAttribute('pvm')};
        paramlist = "";
        for (var param_name in params){
            if (paramlist !== ""){
                paramlist += "&";
            }
            paramlist += param_name + "=" + params[param_name];
        }
        window.location.search = paramlist;
    }
}

function ChangeSongPvm (evt){
    var pvmlist = evt.target;
    var pvm = pvmlist[pvmlist.selectedIndex].text;
    window.location.search = "messupvm=" + pvm;
}

function SelectVastuu (evt){
    var vastuulist = evt.target;
    var vastuu = vastuulist[vastuulist.selectedIndex].text;
    window.location.search = "vastuu=" + vastuu;
}

function edit (evt){
    var element = evt.target;
    if (element.tagName == 'TD') {
        //VAIN jos kyseessä ei jo ole tekstikenttä
        //
        if (element.className.indexOf('left') > -1){
            //Hyväksy myös vasemmanpuoleisen solun klikkaukset
            element = element.parentElement.children[1];
        }

        var text = element.textContent;
        var id_and_name = 'anonymous';
        if (element.hasAttribute("name")){
            id_and_name = element.getAttribute("name");
        }
        else if(element.children[0].hasAttribute("name")){
            id_and_name = element.children[0].getAttribute("name");
        }
        ClearContent(element);
        element.appendChild(TextField(id_and_name, 'linestyle', text));
        //Viereisen solun sisällä oleva tekstikenttä fokusoidaan:
        element.parentElement.children[1].children[0].focus()
    }
    else if (element.tagName == 'H3'){
        var text = element.textContent;
        ClearContent(element);
        element.appendChild(TextField('messutheme', 'linestyle', text));
    }
}

function SwitchSeason(direction){
    if(direction == 'seuraava'){
        var kausi = "next";
    }
    else{
        var kausi = "previous";
    }
    document.getElementById('kausi_input').value = kausi;
    document.getElementById('seasonsubmit').click();
}

function AddComment(){
    if (document.getElementById("cm1") == null){
        var form = document.getElementById('commentform');

        var commentdiv = DomEl('div','ncdiv','newcomment');
        var commentarea = DomEl('textarea','cm1','commenttext');
        var commentsubmit = DomEl('input','cmsub','sbutton');
        commentsubmit.type = 'submit';
        commentsubmit.name = 'cmsub';
        commentsubmit.value = 'Lisää';
        commentarea.name = 'newcomment_text';

        commentdiv.appendChild(commentarea);
        commentdiv.appendChild(commentsubmit);
        form.appendChild(commentdiv);
    }
}

function ClearContent(myNode){
    //Remove child nodes,
    //see also http://stackoverflow.com/questions/3955229/remove-all-child-elements-of-a-dom-node-in-javascript
    while (myNode.firstChild) {
        myNode.removeChild(myNode.firstChild);
    }
}


function getURLParameter(name) {
    //Credits to:
    //http://stackoverflow.com/questions/11582512/how-to-get-url-parameters-with-javascript
    //
  return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
}

function ShowSongList(){
    var element = document.getElementById('songdiv');
    var songswitch = document.getElementById('songswitch');
    if(element.style.display=="block"){
        element.style.display = "none";
        songswitch.innerText = "Näytä messun laulut" ;
    }
    else{
        element.style.display = "block";
        songswitch.innerText = "Piilota laulut" ;
    }
}

function ShowSettings(){
    var element = document.getElementById('menu');
    var bannerheight = document.getElementById('leftbanner').offsetHeight;
    element.style.top = bannerheight;
    if(element.style.display=="block"){
        element.style.display = "none";

    }
    else{
        element.style.display = "block";
    }
}

function NewSeason(){
    slist = document.getElementById('seasonlist');
    cont = document.getElementById('slistcont');
    if (slist.value == 'Lisää uusi kausi' & document.getElementById('snamelab')==null) { 
        lab = new DomEl('span','snamelab','');
        lab.textContent = "Anna nimi uudelle kaudelle: ";
        cont.appendChild(lab);
        lab2 = new DomEl('span','sname','');
        lab2.appendChild(TextField('newsname', 'regular', ''));
        cont.appendChild(lab2);
    }
    else{
        if (document.getElementById("snamelab") != null){
                document.getElementById("snamelab").outerHTML='';
                document.getElementById("sname").outerHTML='';
            }
    }
}

function LaskeMessut(){
    first = new Date(document.getElementById('first').value);
    last = new Date(document.getElementById('last').value);
    var sundays = [];
    var date  = first;
    while (date <= last) {
        sundays.push(new Date(date));
        date.setDate( date.getDate() + 7 );
    }

    var x = 0;
    return sundays;
}

function CreateInputs(){
    var form = document.getElementById('syottodiv');
    var this_input = document.createElement('input');
    var sundays = LaskeMessut();
    this_input.type = 'text';
    var table = new Table();
    var messufields = ["Sunnuntai","Aihe"];
    var vastuufields = ["Saarnateksti","Liturgi","Saarna","Juonto","Bändi","Sanailija","Pyhis","Klubi","Ehtoollisavustaja","Diat","Miksaus"];
    table.AddRow(messufields.concat(vastuufields),true);
    for (var s_idx in sundays){
        var thisday = sundays[s_idx];

        var month = thisday.getMonth() + 1; //months from 1-12
        var day = thisday.getDate();
        var year = thisday.getFullYear();
        var formatted_date = year + "-" + month + "-" + day;

        //erikseen messukentät
        var hidden_date = TextField('pvm_' + s_idx, 'hidden', formatted_date);
        //teemalla on erityinen kentän nimi, siksi se erikseen taulukon ensimmäiseksi
        var inputs = [TextField('teema_' + s_idx, 'regular', '')];
        //ja kaikki tarvittavat vastuukentät
        for (var v_idx in vastuufields){
            var vastuu = vastuufields[v_idx];
            inputs.push(TextField(vastuu + "_" + s_idx, 'regular', ''));
        }
        var formatteddate = $.datepicker.formatDate("d.m.yy", thisday);
        table.AddRow([formatteddate].concat(inputs).concat([hidden_date]),false);
        //table.AddRow([formatteddate].concat(messu_inputs).concat(vastuu_inputs),false);
    }
    //table.appendChild(this_input);
    form.appendChild(table.table);
    document.getElementById('s1').className='visible';

}

function Table(){
    this.table = document.createElement('table');
    this.table.appendChild(document.createElement('thead'));
    this.table.appendChild(document.createElement('tbody'));
    this.rows = [];
    this.AddRow = function(tds, header){
        var row = document.createElement('tr');
        for (tdi in tds){
            var tdcontent = tds[tdi];
            var td = document.createElement('td');
            if (typeof tdcontent == 'object'){                 
                td.appendChild(tdcontent);
            }
            else{
                td.innerText = tdcontent;
            }
            row.appendChild(td);
        }
        if (header){
            this.table.children[0].appendChild(row);
        }
        else{
            this.table.children[1].appendChild(row);
        }
    };
}

function TextField(id, cssclass, value){
    var input = document.createElement('input');
    input.type = 'text';
    input.id = id;
    input.name = id;
    input.className = cssclass;
    input.value = value;
    return input;
}

function DomEl(eltype, id, classname){
    if (typeof id == undefined){
        id='';
    }
    if (typeof classname == undefined){
        classname='';
    }
    thisel = document.createElement(eltype);
    thisel.id = id;
    thisel.className = classname;
    return thisel;
}


function MenuClick(event){
    var child = event.target.children[0];
    if(child!==undefined){
        if (child.tagName == 'A'){
            child.click();
        }
    }
}

function AddSaveButton(){
    var form = document.getElementById('themeupdater');
    if (document.getElementById("themesub") == null){
        var submit = DomEl('input','themesub','smallsub');
        submit.type = 'submit';
        submit.name = 'themesubmit';
        submit.value = 'Vaihda teema';
        form.appendChild(submit);
    }
}

function AddWsSong(type){
    var table = document.getElementById(type + "table");
    var row = document.createElement('tr');
    var left = document.createElement('td');
    var right = document.createElement('td');
    var lyricslinkcell = document.createElement('td');
    var allws = document.getElementsByClassName("editable" + type);

    left.className = "left";
    right.className = "right";
    lyricslinkcell.className = "lyricslinkcell";

    var this_input = document.createElement('input');
    this_input.type = 'text';
    this_input.className = 'linestyle songeditinput editable' + type;
    this_input.addEventListener('focusout',UpdateLyrics,false);

    var this_link = document.createElement('a');
    this_link.innerText = "Katso sanoja";
    this_link.className = "lyricslink";
    this_link.addEventListener('click',ShowWords,false);
    

    left.innerText = type + " " + (allws.length + 1);
    this_input.name = type + "_" + (allws.length + 1);
    right.appendChild(this_input);
    lyricslinkcell.appendChild(this_link);

    row.appendChild(left);
    row.appendChild(right);
    row.appendChild(lyricslinkcell);
    table.appendChild(row);
    //Liitä dynaamisestikin luotuun elementtiin autocomp
    $( ".songeditinput" ).autocomplete({ source: songnames });
}

function CreateSlides(messu_id){
    window.open('pres/diat.php?id=' + messu_id);
}

function MoreSongInfo(){
    var div = document.getElementById('help');
    var link = document.getElementById('infolink');
    if (div.style.height == 'auto'){
        div.style.opacity = '0';
        div.style.height = '0';
        link.textContent = 'Lue pikaohjeet';
    }
    else{
        div.style.opacity = '1';
        div.style.height = 'auto';
        link.textContent = '';
    }
}

function ViewMaintenance(li){
    var rect = li.getBoundingClientRect();
    var submenu = document.getElementById("maintenancelist");
    if (submenu.style.display=='block'){
        submenu.style.display = "none";
    }
    else{
        submenu.style.left = rect.left + li.offsetWidth + 1 +  "px";
        submenu.style.top = rect.top + "px";
        submenu.style.display = "block";
    }
}

