var songlist_g = null;
var globalservicelist = null;


/**
 *
 * Jquery ui:n selectmenu-pluginin muokkaus niin, että
 * mahdollista valita myös tekstikenttä.
 *
 */
 $.widget("custom.select_withtext", $.ui.selectmenu, 
     { 
         _renderItem: function( ul, item ) {
            if(item.label=="Uusi tavoite"){
                //TODO: abstract this, so that these options can be set appropriately and don't have to be hard coded.
                var $input = $("<input type='text' placeholder='Uusi tavoite...'>");
                $input.on("keydown",function(){
                    var $div = $(this).parents(".other-option");
                    if ($div.find("button").length==0){
                        $("<button>Lisää</button>")
                            .click(function(){
                                //Lisää äsken lisätty uusi arvo KAIKKIIN tällä sivulla oleviin select-elementteihin, joissa addedclass-nimi
                                var newval = $(this).parents(".other-option").find("input").val();
                                //TODO: make this not depend on the select tag's name
                                $("<option value='" + newval + "'> " + newval + "</option>")
                                    .insertBefore($("select[name='kolehti_tavoite']").find("option:last-child"));
                                $("select[name='kolehti_tavoite']").each(function(){
                                    try{
                                        $(this).select_withtext("refresh");
                                    }
                                    catch(e){
                                        $(this).select_withtext();
                                    }
                                });
                            })
                            .appendTo($div);
                    }
                });
            }
             else if(item.label=="Jokin muu"){
                var $input = $("<input type='text' placeholder='Jokin muu...'>")
                var self = this;
                var thisitem = item;
                $input.autocomplete( {
                    source: function(request, response){ $.getJSON(loaderpath + "/songtitles.php",{songname:request.term,fullname:"no"},response);},
                    minLength: 2,
                    select: function(event,input){
                        $(self.element).find("[value='Jokin muu']").before("<option>" +  input.item.value +"</option>");
                        self.refresh();
                    },
                });
            }

            var wrapper = (["Uusi tavoite","Jokin muu"].indexOf(item.label)>-1 ? $("<div class='other-option'>").append($input) : $("<div>").text(item.label));

            return $("<li>").append(wrapper).appendTo(ul);
        },
        open: function( event ) {

            var self = this;
            $.each(this.menuItems,function(idx,el){
                if($(el).hasClass("other-option")){
                    //Siivoa tekstikenttään liittyvät tapahtumat
                    $(el).unbind('mousedown');
                    $(el).unbind('keydown');
                    $(el).unbind('click');
                    $(el).click(function(){return false;});
                    $(el).bind("keydown", function(event){});
                    $(el).bind('mousedown', function() {
                        //Fokus pitää asettaa erikseen
                        $(this).find('input:eq(0)').focus();
                    });
                }
            });
            if ( this.options.disabled ) {
                return;
            }

            // If this is the first time the menu is being opened, render the items
            if ( !this._rendered ) {
                this._refreshMenu();
            } else {

                // Menu clears focus on close, reset focus to selected item
                this._removeClass( this.menu.find( ".ui-state-active" ), null, "ui-state-active" );
                this.menuInstance.focus( null, this._getSelectedItem() );
            }

            // If there are no options, don't open the menu
            if ( !this.menuItems.length ) {
                return;
            }

            this.isOpen = true;
            this._toggleAttr();
            this._resizeMenu();
            this._position();

            this._on( this.document, this._documentClick );

            this._trigger( "open", event );
        },


	_drawMenu: function() {
		var that = this;

		// Create menu
		this.menu = $( "<ul>", {
			"aria-hidden": "true",
			"aria-labelledby": this.ids.button,
			id: this.ids.menu
		} );

		// Wrap menu
		this.menuWrap = $( "<div>" ).append( this.menu );
		this._addClass( this.menuWrap, "ui-selectmenu-menu", "ui-front" );
		this.menuWrap.appendTo( this._appendTo() );

		// Initialize menu widget
		this.menuInstance = this.menu
			.menu( {
				classes: {
					"ui-menu": "ui-corner-bottom"
				},
				role: "listbox",
				select: function( event, ui ) {
                    console.log("sel");
					event.preventDefault();

					// Support: IE8
					// If the item was selected via a click, the text selection
					// will be destroyed in IE
					that._setSelection();

                    if(ui.item.data( "ui-selectmenu-item" ).label!=="Jokin muu"){
                        that._select( ui.item.data( "ui-selectmenu-item" ), event );
                    }
                    else{
                        $(event.target).find
                    }
				},
				focus: function( event, ui ) {
					var item = ui.item.data( "ui-selectmenu-item" );

					// Prevent inital focus from firing and check if its a newly focused item
					if ( that.focusIndex != null && item.index !== that.focusIndex ) {
						that._trigger( "focus", event, { item: item } );
						if ( !that.isOpen ) {
							that._select( item, event );
						}
					}
					that.focusIndex = item.index;

					that.button.attr( "aria-activedescendant",
						that.menuItems.eq( item.index ).attr( "id" ) );
				}
			} )
			.menu( "instance" );


		// Don't close the menu on mouseleave
		this.menuInstance._off( this.menu, "mouseleave" );

		// Cancel the menu's collapseAll on document click
		this.menuInstance._closeOnDocumentClick = function() {
			return false;
		};

		// Selects often contain empty items, but never contain dividers
		this.menuInstance._isDivider = function() {
			return false;
		};

        this.menuInstance._keydown = function(){
            //Poistetaan jquery ui:n menuun liittyvät näppäimistötapahtumat, jotta tekstikentässä voisi kirjoittaa rauhassa
        };


	},

     }
);

function AddNewService(){
    if (globalservicelist==null){
        globalservicelist = new DynamicList("addedservices","servicelistparent");
    }
    globalservicelist.AddNewPvm();
}


function ShowExtraInfo(){
     document.getElementById("extrainfospan").style.display = "inline";
}




function DynamicList(grandpaid,listparentid){

    //Luo lista
    var grandpa = document.getElementById(grandpaid);
    var listparent = TagWithText("ul","","");
    listparent.id = listparentid;
    grandpa.appendChild(listparent);
    this.listparent = document.getElementById(listparentid);

    this.AddNewPvm = function(){
        var pvm = this.AddInput("Päivämäärä","dateinput","pvm_");
        var name = this.AddInput("Messun aihe","","teema_");
        var li = TagParent("li",[pvm, name],"");
        this.listparent.appendChild(li);
        $(".dateinput").datepicker(); 
    };

    this.AddInput = function(placeholder,thisclass, thistype){
        var input = TagWithText("input","",thisclass);
        input.setAttribute("Placeholder", placeholder);
        input.setAttribute("name",thistype + (this.listparent.children.length+1));
        return input;
    };

}


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
        songname = songname.replace(/ /g, "_");
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
    if (element.tagName == 'TD' || element.tagName == "SPAN") {
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
        else if(element.children[0]!==undefined){
            if(element.children[0].hasAttribute("name")){
                id_and_name = element.children[0].getAttribute("name");
            }
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

function EditVastuuNames (ask){
    if(ask==true){
        var confirmed = window.confirm("Oletko varma? Tämä poistaa kaikki valitut vastuut.");
        if(confirmed==true){
            document.getElementById("remover").click();
        }
    }
    else{
        document.getElementById("remover").click();
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

function CheckPlayerFilter(input, filtertype){
    var table = document.getElementById("playertable");
    var rows = table.getElementsByTagName("tr");
    for(var i=0;i<rows.length;i++){
        var row = rows[i];
        var thisname = row.getElementsByClassName(filtertype)[0].textContent;
        if(thisname.indexOf(input.value)==-1){
            //jos rivi ei sisällä tätä merkkijonoa soittajan nimessä
            row.style.display = "none";
        }
        else{
            row.style.display = "table-row";
        }
    }
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



function EditInstrument(){
    var section = document.getElementById("instrumentadder_edit");
    if (section.style.height == '' || section.style.height == '0px'){
        section.style.background = 'rgba(63, 57, 57, 0.48)';
        section.style.height= '3em';
        document.getElementById('instrumentaddparagraph_edit').style.display = 'block';
        //section.style.marginTop = "-" + section.offsetHeight + "px";
    }
    else{
        document.getElementById('instrumentaddparagraph_edit').style.display = 'none';
        section.style.height = "0px";
        section.style.background = 'none';
    }
}

function AddInstrument(){
    var section = document.getElementById("instrumentadder");
    if (section.style.height == '' || section.style.height == '0px'){
        section.style.background = 'rgba(63, 57, 57, 0.48)';
        section.style.height= '3em';
        document.getElementById('instrumentaddparagraph').style.display = 'block';
        //section.style.marginTop = "-" + section.offsetHeight + "px";
    }
    else{
        document.getElementById('instrumentaddparagraph').style.display = 'none';
        section.style.height = "0px";
        section.style.background = 'none';
    }
}


function ConfirmInstrEdit(){
    var instruments = document.getElementById("editrowsection").getElementsByClassName("instrbadge");
    var instrname = document.getElementById("instrumentname_edit").value;
    //varmista, ettei samaa soitinta kahdesti
    for (var i=0;i<instruments.length;i++){
        var instrument = instruments[i];
        if(instrname==instrument.textContent){
            return 0;
        }
    }
    var newinstr = TagWithText("div",instrname,"instrbadge");
    newinstr.addEventListener('click',RemoveInstrument_edit,false);
    document.getElementById("addedinstruments_edit").appendChild(newinstr);
    document.getElementById("edit_repertoire").value = document.getElementById("edit_repertoire").value  + instrname + ";";
}

function RemoveInstrument(evt){
    var instr = evt.target.textContent;
    var conf = confirm("Haluatko varmasti poistaa soittimen?");
    if(conf!=true){
        return 0;
    }
    var repertoire =  document.getElementById("repertoire").value.split(";");
    var keptinstr = [];
    for(var i=0;i<repertoire.length;i++){
        if(repertoire[i]!=instr){
            keptinstr.push(repertoire[i]);
        }
    }
    document.getElementById("repertoire").value=keptinstr.join(";");
    evt.target.outerHTML="";
}

function RemovePlayer(){
    document.getElementById("playerdeleted").value="true";
    var conf = window.confirm("Oletko varma, että haluat poistaa soittajan?");
    if(conf==true){
        document.getElementById("playerdeleted").value="true";
        document.getElementById("savechanges").click();
    }
}

function RemoveInstrument_edit(evt){
    var instr = evt.target.textContent;
    var conf = confirm("Haluatko varmasti poistaa soittimen?");
    if(conf!=true){
        return 0;
    }
    var repertoire =  document.getElementById("edit_repertoire").value.split(";");
    var keptinstr = [];
    for(var i=0;i<repertoire.length;i++){
        if(repertoire[i]!=instr){
            keptinstr.push(repertoire[i]);
        }
    }
    if(keptinstr.length>0){
        document.getElementById("edit_repertoire").value=keptinstr.join(";");
    }
    else{
        document.getElementById("edit_repertoire").value="";
    }
    evt.target.outerHTML="";
}

function ConfirmInstrAdd(){
    var defaulttext = document.getElementById("emptyinstrumentspan");
    if(defaulttext!==undefined){
        defaulttext.innerHTML="";
    }
    var instruments = document.getElementsByClassName("instrbadge");
    var instrname = document.getElementById("instrumentname").value;
    for (var i=0;i<instruments.length;i++){
        var instrument = instruments[i];
        if(instrname==instrument.textContent){
            return 0;
        }
    }
    var newinstr = TagWithText("div",instrname,"instrbadge");
    newinstr.addEventListener('click',RemoveInstrument,false);
    document.getElementById("addedinstruments").appendChild(newinstr);
    document.getElementById("repertoire").value = document.getElementById("repertoire").value  + instrname + ";";
}

function ShowPlayerAdder(){
    var section = document.getElementById("addplayersec");
    if (section.style.height == '' || section.style.height == '0px'){
        section.style.height= '25em';
        section.style.background = "white";
        document.getElementById("addplayerheader").style.background = "white";
    }
    else{
        section.style.height = "0px";
        section.style.background = "none";
        document.getElementById("addplayerheader").style.background = "none";
    }
}


function CloseRowEdit(){
    document.getElementById("editrowsection").style.display="none";
}


function EditRow(row){
    document.getElementById("editrowsection").style.display="block";
    //Tyhjennä mahdolliset edellisen muokkauksen soittimet
    document.getElementById("addedinstruments_edit").innerHTML="";
    document.getElementById("edit_repertoire").value = "";
    //Muistiin tieto siitä, kuka kyseessä (3, koska tyyppiä id_nro)
    document.getElementById("player_id").value = row.id.substring(3);
    var fieldnames = ["playername","phone","email"];
    for(var i=0;i<fieldnames.length;i++){
        document.getElementById("edit_" + fieldnames[i]).value = row.getElementsByClassName(fieldnames[i])[0].textContent;
    }
    var instrumentstring = row.getElementsByClassName("instruments")[0].textContent;
    var instruments = instrumentstring.split(",");
    for(var i=0;i<instruments.length;i++){
        var instrument = instruments[i].trim();
        var newinstr = TagWithText("div",instrument,"instrbadge");
        newinstr.addEventListener('click',RemoveInstrument_edit,false);
        document.getElementById("addedinstruments_edit").appendChild(newinstr);
        document.getElementById("edit_repertoire").value = document.getElementById("edit_repertoire").value  + instrument + ";";
    }
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

$(document).ready(function(){
    // Häckäillään kolehti-infon syötöt jqueryllä
    // Alkaa huomata, että intressit ovat uuden portaalin kehittämisessä...

    /**
     *
     * Päivittää tiedon johonkin kohteeseen kerätystä kokonaismäärästä kolehtia.
     *
     */
    function UpdateKolehtiTavoite(fetchkohde){
       var kohde = (!fetchkohde ? $("[name='kolehtikohde']").val() : "from_db");
       $.getJSON("ajax/get_kolehti.php",{"messu_id":$("[name='messu_id_comments']").val(),"kohde":kohde,"fallback":$("[name='kolehtikohde']").val()},function(data){
           var $select = $("<select name='kolehti_tavoite'>");
           var target_goal = Number();
           var kohde = String();
           if(fetchkohde){
               var tavoite = String();
           }
           $.each(data,function(idx, el){
               var selected = (el.selected ? " selected " : "");
               $select.append("<option "  + selected + "value='"+el.tavoite+"'>"+el.tavoite+" (yhteensä kerätty "+el.amount+"€)</option>");
               target_goal = parseFloat(el.goal);
               if(!kohde){
                   kohde = el.kohde;
               }
           });
           $select.append("<option>Uusi tavoite</option>");
           $("#tarkempitavoite").html("").append($select);
           //Luo ui-selectemnu lisävalintamahdollisuudella ja lisää oikea select-tapahtuma
           $select.select_withtext({select:function(){UpdateTavoiteMaara()}});
           $("[name='kolehtikohde']").selectmenu("refresh");
           if(["Myanmar","Kimbilio"].indexOf(kohde)){
               $("[name='kolehtikohde']").val(kohde).selectmenu("refresh");
           }
           else{
               $("[name='kolehtikohde']").val("Myanmar").selectmenu("refresh");
           }
           UpdateTavoiteMaara();
       });
    }

    /**
     *
     * Hakee tähän kohteeseen liittyvän kokonaistavoitesumman
     * ja syöttää sen tekstikenttään.
     *
     */
    function UpdateTavoiteMaara(){
       var params = {"goal":$("[name='kolehti_tavoite']").val(),"kohde":$("[name='kolehtikohde']").val()};
       $.getJSON("ajax/get_kolehti.php",params,function(data){
           $("[name='total_goal']").val(data.tavoitemaara);
           $("[name='kolehti_description']").val(data.kuvaus);
           //Hack??
           $("[name='kolehti_tavoite']").select_withtext("destroy");
           $("[name='kolehti_tavoite']").select_withtext({select:function(){UpdateTavoiteMaara()}});
       });
    }

    UpdateKolehtiTavoite(true);

    $("[name='kolehtikohde']").selectmenu();
    $("[name='kolehtikohde']").on("selectmenuchange",function(){
        //Päivitä tallennetut tavoitteet ja kokonaismäärät aina, kun kolehtikohdetta tai -tavoitetta muutettu.
        UpdateKolehtiTavoite();
    });

    $.getJSON("ajax/get_kolehti.php",{"just_amount":true,"messu_id":$("[name='messu_id_comments']").val()},
        function(data){
            $("[name='kolehti_amount']").val(data);
    });

    $("#save_kolehti").click(function(){
        var button = $(this);
        $.post("ajax/update_kolehti.php",
            {"id":$("input[name='messu_id_comments']").val(),
            "keratty":$("[name='kolehti_amount']").val().replace(",","."),
            "kohde":$("[name='kolehtikohde']").val(),
            "kuvaus":$("[name='kolehti_description']").val(),
            "tavoite":$("[name='kolehti_tavoite']").val(),
            "total_goal":$("[name='total_goal']").val(),
            },
            /**
             *
             * Näytä ilmoitus päivityksestä ja piilota 2 sekunnin kuluttua
             *
             */
            function(data){
                $("<div class='tempdiv'>Kolehtitiedot päivitetty.</div>").prependTo(button.parent());
                $(".tempdiv").fadeIn("slow");
                setTimeout(function(){
                    $(".tempdiv").fadeOut("slow");
                },2000);
            UpdateKolehtiTavoite();
        });
    });

    $(".unhider").click(function(){
        $(this).parents("div").next().slideToggle();

    });

})
