var songlist_g = null;

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

function confirmsubmit(){
    var inputs = document.getElementsByClassName("songeditinput");
    var songnames = [];
    var abort = false;
    for(var i=0;i<inputs.length;i++){
        var thisinput = inputs[i];
        var songname = thisinput.value;
        if(songnames.indexOf(songname)==-1){
            songnames.push(songname);
            var songid = "song_" + songname.replace(/ /g,"_");
            if(document.getElementById(songid)==undefined){
                if(songname==""){
                    //   var conf = window.confirm("Kaikkia lauluja ei ole lisätty. Haluatko jatkaa?");
                    //   if(conf!=true){
                    //       abort = true;
                    //   }
                }
                else{
                    var conf = confirm("Laulua " + songname + " ei löydy tietokannasta. Haluatko silti jatkaa? Jos suinkin mahdollista, lisää laulun sanat painamalla laulun nimen oikealla puolella olevaa linkkiä. Kiitos!");
                    if(conf!=true){
                        abort = true;
                    }
                }
            }
        }
    }
    if(abort != true){
        document.getElementById("sbut").click();
    }
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
    commentdiv.innerHTML = "<span onClick='RemoveWordView();' class='fa-stack fa-lg close-button'> <i class='fa fa-circle fa-stack-2x'></i> <i class='fa fa-times fa-stack-1x fa-inverse'></i></span>";
    //var closebut = TagWithText("a","","boxclose");

    //commentdiv.appendChild(closebut);

    var addremover = false;
    var contrect = document.getElementById('maincontainer').getBoundingClientRect();

    if(document.getElementById(songname)==undefined){
        //Lisää sanat, jos laulua ei löydy
        var title = document.getElementById("editedtitle");
        //Laulun otsikko
        title.textContent = songname.replace("song_","").replace(/_/g," ");

        var worddiv = document.getElementById("editor").cloneNode(true);
        worddiv.className = "cont2";
        var editarea = document.getElementById("editarea");
        document.getElementById("edited_song_name").value=songname;
        worddiv.style.display="block";
        commentdiv.appendChild(worddiv);

    }
    else{
        var worddiv = document.getElementById(songname).cloneNode(true);
        addremover = true;
        worddiv.innerHTML = worddiv.innerHTML.replace(/\n/g,'<br>')
        commentdiv.appendChild(worddiv);
    }

    commentdiv.style.display="block";
    commentdiv.style.marginLeft=contrect.left + 5 + "px";
    //commentdiv.style.width=contrect.width-80 + "px";
}

function UpdateLyrics(evt){
    if(evt.parentElement !== undefined){
        var td = evt.parentElement;
    }
    else{
        var td = evt.target;
    }
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
        link.textContent = "Lisää sanat";
    }
    else{
        link.textContent = "Katso sanoja";
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

    if(sel[sel.selectedIndex].textContent == "Jokin muu"){
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
        if(element.children[0]!==undefined){
            //Jos soulssa jo tekstikenttä, ota *sen* arvo muistiin
            if(element.children[0].tagName == 'INPUT'){
                var text = element.children[0].value;
            }
        
        }
        else{
            var text = element.textContent;
        }

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



function ExpandComment(el){
    if (el.style.height == '' || el.style.height == '2em'){
        el.style.height = '8em';

        var form = document.getElementById('commentform');
        var from = TagWithText('input','','');
        from.type = "text";
        from.name = "commentator";
        from.setAttribute('Placeholder','Nimesi');
        document.getElementById('themechooser').appendChild(from);

        var commentsubmit = DomEl('input','cmsub','sbutton');
        commentsubmit.type = 'submit';
        commentsubmit.name = 'cmsub';
        commentsubmit.value = 'Lisää';
        form.appendChild(TagParent('div',[commentsubmit],'commentadder'));

        document.getElementById('themechooser').style.display='block';

    }
}


function RemoveComment(commentid){
   var conf = window.confirm('Oletko aivan varma, että haluat poistaa kommentin? Tätä ei voi perua!');
   if(conf==true){
       document.getElementById('deleted_comment_id').value = commentid;
       document.getElementById('deleted_comment_id').value = commentid;
        document.getElementById('submit_comment_edits').click();
   }
}

function EditComment(commentid){
    var content = document.getElementById("commentcontent_" + commentid);
    if(content.getElementsByTagName("TEXTAREA").length == 0){
        // jos ei vielä aloitettu muokkausta
        var textarea = TagWithText("textarea",content.textContent,"commenttext");
        textarea.id = "editedcomment";
        textarea.name = "editedcomment";
        textarea.style.height = content.offsetHeight - 10 + "px";
        content.textContent = "";
        content.appendChild(textarea);

        var select = document.getElementById('commentthemes').cloneNode(true);
        content.appendChild(TagParent('div',[select],'selcont'));


       for(var idx = 0;idx<select.length;idx++) {
           var opt = select[idx];
            if(opt.value == document.getElementById("ctheme_" + commentid).textContent) {
                select.selectedIndex = idx;
                break;
            }
        }

        var commentsubmit = DomEl('input','cmeditsub','seditbutton');
        commentsubmit.type = 'submit';
        commentsubmit.name = 'cmeditsub';
        commentsubmit.value = 'Tallenna muutokset';
        content.appendChild(TagParent('div',[commentsubmit],''));
        document.getElementById('edited_comment_id').value=commentid;
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
        songswitch.textContent = "Näytä messun laulut" ;
    }
    else{
        element.style.display = "block";
        songswitch.textContent = "Piilota laulut" ;
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
                td.textContent = tdcontent;
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


function RemoveWsSong(type){
    var rows = document.getElementById(type + "table").getElementsByTagName('TR');
    if(rows.length>1){
        document.getElementById('removed_type').value = type;
        // Tarkista, mitä on jo tallennettuna
        var memo = document.getElementById(type + 'table_memo');
        var memorows = memo.getElementsByTagName('TR');
        //var lastmemoval = memorows[memorows.length-1].getElementsByTagName('input')[0].value;
        //vertaa nykyiseen näkymään
        //var lastviewval = rows[rows.length-1].getElementsByTagName('input')[0].value;
        if(memorows.length==rows.length){
            // Jos halutaan postaa jo tallennettuja muutoksia
            document.getElementById('removed_ws_sub').click();
        }
        else if(memorows.length < rows.length){
            // Jos poistetaan tallentamattomia
            document.getElementById(type + "table").removeChild(rows[rows.length-1]);
        }
    }
}

function AddWsSong(type){
    var table = document.getElementById(type + "table");
    var row = document.createElement('tr');
    var left = document.createElement('td');
    var right = document.createElement('td');
    var lyricslinkcell = document.createElement('td');
    var allws = table.getElementsByClassName("editable" + type);

    left.className = "left";
    right.className = "right";
    lyricslinkcell.className = "lyricslinkcell";

    var this_input = document.createElement('input');
    this_input.type = 'text';
    this_input.className = 'linestyle songeditinput editable' + type;
    this_input.addEventListener('focusout',UpdateLyrics,false);

    var this_link = document.createElement('a');
    this_link.textContent = "Katso sanoja";
    this_link.className = "lyricslink";
    this_link.addEventListener('click',ShowWords,false);
    

    left.textContent = type + " " + (allws.length + 1);
    this_input.name = type + "_" + (allws.length + 1);
    this_input.id = type + "_" + (allws.length + 1);
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

TagWithText = function(tagname, tagtext, tagclass){
    var tag = document.createElement(tagname);
    tag.textContent = tagtext;
    tag.className = tagclass;
    return tag;
}

TagParent = function(tagname, childlist, classname, tagid){
    var tag = document.createElement(tagname);
    tag.className = classname;
    for (child_idx in childlist){
        tag.appendChild(childlist[child_idx]);
    }
    if (tagid!==undefined){
        tag.id = tagid;
    
    }
    return tag;
}

function EditWords(songname){
    var div = document.getElementById(songname);
    var ps = div.getElementsByTagName('P');
    var oldtext = "";
    for(var idx = 1;idx<ps.length;idx++){
        var p = ps[idx];
        //hack.. ->
        p.innerHTML = p.innerHTML.replace(/<br>/g,'\n');
        oldtext += "\n\n" + p.textContent;
    }
    oldtext = oldtext.trim();


    var titlecont = div.getElementsByTagName('H3');
    var title = titlecont[0].cloneNode(true);
    
    var section = document.getElementById('songlistsection');
    if (section.style.height == '' || section.style.height == '0px'){
        var wordview = document.getElementById("wordview");
        wordview.innerHTML = "<span onClick='RemoveWordView();' class='fa-stack fa-lg close-button'> <i class='fa fa-circle fa-stack-2x'></i> <i class='fa fa-times fa-stack-1x fa-inverse'></i></span>";
    }
    else{
        // Jos muokataan laululistanäkymässä
        var wordview = document.getElementById("listeditwords");
        ClearContent(wordview);
    }
    wordview.appendChild(title);
    var etextarea = TagWithText("textarea",oldtext,"earea");
    etextarea.id = "editedoldwords";
    wordview.appendChild(etextarea);
    var but = TagWithText("input","","");
    but.setAttribute("type","button");
    but.value = "Tallenna";
    but.addEventListener("click",SendEditedWords,false);
    wordview.appendChild(TagParent("p",[but]));

    //Tallenna laulun id
    var idspans = div.getElementsByTagName('SPAN');
    document.getElementById('editedsongid').value = idspans[0].textContent;
}

function SendEditedWords(){
    var editfield = document.getElementById("editedoldwords");
    var sendfield = document.getElementById("edited_existing_text");
    sendfield.value = editfield.value;
    var but = document.getElementById("edited_existing_button");
    but.click();
}

function CheckFilter(){
    var input = document.getElementById("songfilterinput");
    songlist_g.PrintList(input.value);
}

function ViewSongList(){
    if (songlist_g == null){
        songlist_g = new SongList();
    }
    var section = document.getElementById('songlistsection');
    var h = window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight||0;
    if(h<700){
        //pienet laitteet
        var newheight = h;
    }
    else{
        var newheight = (h - document.getElementById('leftbanner').offsetHeight) / 3 * 2;
    }
    section.style.marginTop = document.getElementById('leftbanner').offsetHeight;
    if (section.style.height == '' || section.style.height == '0px'){
        section.style.height= newheight + "px";
        document.getElementById('songlistcontainer').style.display = 'block';
        document.getElementById('songlistdiv').style.display = 'block';
        document.getElementById('searchtools').style.display = 'block';
        document.getElementById('laululista_launcher').style.background = 'cadetblue';
        songlist_g.PrintList("all");
        var cdiv = document.getElementById('songfilterinput').focus();
    }
    else{
        document.getElementById('laululista_launcher').style.background = 'none';
        document.getElementById('songlistcontainer').style.display = 'none';
        section.style.height = "0px";
    }
    var cdiv = document.getElementById('songcontrols');
    cdiv.style.display = 'none';
}

function BackToSongList(){
    //stupid??
    ViewSongList();
    ViewSongList();
}

function UseSong(evt){
    var li = evt.target;
    //save the song name for future use
    document.getElementById('pickedlistsong').value = li.textContent;

    //hide songlist
    var sdiv = document.getElementById('songlistdiv');
    var searchcontrols = document.getElementById('searchtools');
    sdiv.style.display = "none";
    searchcontrols.style.display = 'none';

    //show controls
    var cdiv = document.getElementById('songcontrols');
    cdiv.style.display = 'block';

    //Insert information and controls
    var wcont = document.getElementById('listeditwords');
    var songname = "song_" + li.textContent.replace(/ /g,'_');
    var worddiv = document.getElementById(songname).cloneNode(true);
    worddiv.innerHTML = worddiv.innerHTML.replace(/\n/g,'<br>')

    ClearContent(wcont);

    var link = TagWithText("a","<< Takaisin listaan","");
    link.href = "javascript:void(0)";
    link.addEventListener('click',BackToSongList,false);
    wcont.appendChild(TagParent("p",[link],""));

    wcont.appendChild(worddiv);

    //Add functionality
    var appliedsongs = document.getElementsByClassName('left');
    var functions = [];
    var usedroles = [];
    for(var idx =0; idx<appliedsongs.length; idx++){
        var song = appliedsongs[idx];
        if(usedroles.indexOf(song.textContent)==-1 && ['Jumalan karitsa','Pyhä-hymni'].indexOf(song.textContent)==-1){
            var thisli = TagWithText("div",song.textContent,"");
            thisli.addEventListener('click',AssignRole,false);
            functions.push(thisli);
            usedroles.push(song.textContent);
        }
    }
    var functiondiv = document.getElementById("songpanel");
    ClearContent(functiondiv);
    var cont = TagParent("div",functions,"");
    cont.id = "panelcont";
    functiondiv.appendChild(cont);

}

function SongElement(spanel){
    this.verses =  [];
    var songdiv = spanel.nextElementSibling;
    var verses = songdiv.getElementsByTagName('P');
    for (var idx = 0;idx<verses.length;idx++){
        var verse = verses[idx];
        this.verses.push(verse.cloneNode(true));
    }
}

function AssignRole(evt){
    var li = evt.target;
    id = li.textContent;
    if(li.textContent.indexOf("Ylistyslaulu") > -1 || li.textContent.indexOf("Ehtoollislaulu") > -1){
        var id = id.replace(/ /g,'_');
    }
    document.getElementById(id).value = document.getElementById('pickedlistsong').value ;
    ViewSongList();
    UpdateLyrics(document.getElementById(id));
}

function SongList(){
    //an object containing all the names of the songs
    //these can be ordered, search etc
    //TODO make the songnames themselves OBJECTS with properties
    //like composer, theme etc
    this.order='';
    this.namelist = function(thisobj){
        var songnames = document.getElementsByClassName('songtitleentry');
        var namelist = [];
        //another list containing more information
        thisobj.objlist = {};
        for (var idx=0;idx<songnames.length; idx++) {
            var songtitle = songnames[idx].textContent;
            var li = TagWithText("li",songtitle,"");
            li.addEventListener('click',UseSong,false);
            li.setAttribute('title','Klikkaa valitaksesi laululle tehtävä');
            namelist.push(li);
            //save a more detailed object indexed by the song name
            thisobj.objlist[songtitle.replace(/ /g,'_')] = new SongElement(songnames[idx]);
        }
        return namelist;
    }(this);

    this.PrintList = function(pattern){
            var filtered = [];
            var section = document.getElementById('songlistsection');
            if (pattern=="all"){
                filtered = this.namelist;
            }
            else{
                //
                for (var idx=0;idx<this.namelist.length; idx++) {
                    var name = this.namelist[idx];
                    if(name.textContent.toLowerCase().indexOf(pattern.toLowerCase()) != -1){
                        filtered.push(name);
                    }
                }
            }
            var listdiv = document.getElementById('songlistdiv');
            ClearContent(listdiv);
            listdiv.appendChild(TagParent("ul",filtered,""));
        };
    }

