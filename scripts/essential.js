
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
    window.location.search += paramlist;
}

function SelectVastuu (evt){
    var vastuulist = evt.target;
    var vastuu = vastuulist[vastuulist.selectedIndex].text
    window.location.search = "vastuu=" + vastuu;
}

function edit (evt){
    var element = evt.target;
    if (element.tagName == 'TD') {
        //VAIN jos kyseessä ei jo ole tekstikenttä
        var text = element.textContent;
        var id_and_name = 'anonymous';
        if (element.hasAttribute("name")){
            id_and_name = element.getAttribute("name");
        }
        ClearContent(element);
        element.appendChild(TextField(id_and_name, 'linestyle', text));
    }
}


function AddComment(){
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

function ClearContent(myNode){
    //Remove child nodes,
    //see also http://stackoverflow.com/questions/3955229/remove-all-child-elements-of-a-dom-node-in-javascript
    while (myNode.firstChild) {
        myNode.removeChild(myNode.firstChild);
    }
}


