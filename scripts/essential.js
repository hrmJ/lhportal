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
    document.body.removeChild(previous);
}


function FixOver(evt){
    var row = evt.target;
    MouseFix('on', row);
}

function submitedit(){
    document.getElementById("sbut").click();
}

function ShowWords(evt){
    var previous = document.getElementById('wordview');
    if (previous !== null){
        //ClearContent(previous);
        document.body.removeChild(previous);
    }
    var link = evt.target;
    var songname = link.id.replace("link_","song_");
    var rect = link.getBoundingClientRect();
    var commentdiv = DomEl('div','wordview','commentlist');
    //Lisää sanat
    var addremover = false;
    if(document.getElementById(songname)==undefined){
        var worddiv = document.getElementById("editor").cloneNode(true);
        var editarea = document.getElementById("editarea");
        document.getElementById("edited_song_name").value=songname;
        worddiv.style.display="block";
    }
    else{
        var worddiv = document.getElementById(songname).cloneNode(true);
        addremover = true;
    }
    commentdiv.appendChild(worddiv);
    commentdiv.style.left = rect.left - 300 + "px";
    commentdiv.style.top = rect.top + 5 + "px";
    if(addremover==true){
        commentdiv.addEventListener('click',RemoveWordView,false);
    }


    document.body.appendChild(commentdiv);
}

function UpdateLyrics(evt){
    var td = evt.target;
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
    if (child.tagName == 'A'){
        child.click();
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
    left.className = "left";
    right.className = "right";

    var this_input = document.createElement('input');
    this_input.type = 'text';
    this_input.className = 'linestyle songeditinput editable' + type;
    var allws = document.getElementsByClassName("editable" + type);

    left.innerText = type + " " + (allws.length + 1);
    this_input.name = type + "_" + (allws.length + 1);
    right.appendChild(this_input);

    row.appendChild(left);
    row.appendChild(right);
    table.appendChild(row);
    //Liitä dynaamisestikin luotuun elementtiin autocomp
    $( ".songeditinput" ).autocomplete({ source: songnames });
}
