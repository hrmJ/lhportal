
function SelectMessu (evt){
    var thisid = evt.target.getAttribute('id');
    var messuid = thisid.substring(thisid.indexOf('_')+1);
    var params = {"messuid":messuid,
                  "teema": evt.target.getAttribute('teema'),
                  "pvm": evt.target.getAttribute('pvm')};
    paramlist = "";
    for (var param_name in params){
        if (paramlist !== ""){
            paramlist += "&";
        }
        paramlist += param_name + "=" + params[param_name];
    }
    window.location.search = paramlist;
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
        var hidden_date = TextField('pvm_' + s_idx, 'hidden', formatteddate);
        window.alert(formatted_date);
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

function DomEl(eltype, id='',classname=''){
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
