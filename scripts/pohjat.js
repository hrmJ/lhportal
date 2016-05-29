var i = 0;
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
    var form = document.getElementById('messusyotto')
    var this_input = document.createElement('input');
    var sundays = LaskeMessut();
    this_input.type = 'text';
    var table = new Table();
    table.AddRow(["Sunnuntai","Aihe"],true);
    for (s_idx in sundays){
        var thisday = sundays[s_idx];
        var theme = TextField('theme_' + s_idx, 'regular', '');
        var dateval = TextField('date_' + s_idx, 'hidden', thisday.toISOString().slice(0,10));
        var formatteddate = $.datepicker.formatDate("d.m.yy", thisday);
        table.AddRow([formatteddate,theme,dateval],false);
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


