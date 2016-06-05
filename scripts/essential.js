
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


