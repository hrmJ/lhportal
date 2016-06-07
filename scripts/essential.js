
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
    var text = element.textContent;
    ClearContent(element);
    element.appendChild(TextField('test', 'linestyle', text));
}


function ClearContent(myNode){
    //Remove child nodes,
    //see also http://stackoverflow.com/questions/3955229/remove-all-child-elements-of-a-dom-node-in-javascript
    while (myNode.firstChild) {
        myNode.removeChild(myNode.firstChild);
    }
}
