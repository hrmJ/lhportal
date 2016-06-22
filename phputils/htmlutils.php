<?php

class Comment{


    public function __construct($commentdata){
        $cont = new DomEl("div");
        $cont->AddAttribute('class',"comment_container");

        $commentheader = new DomEl("div",'',$cont);
        $commentheader->AddAttribute('class',"comment_header");

        $commentcontent = new DomEl("div",$commentdata["content"],$cont);
        $commentcontent->AddAttribute('class',"comment_content");

        $this->container = $cont;
    }

    public function AddHeader($pvm, $commentator=''){
    
    
    }


}

class HtmlTable{


    public function __construct(){
        $this->element = new DomEl("table");
        $this->head = new DomEl('thead','',$this->element);
        $this->tbody = new DomEl('tbody','',$this->element);
        $this->rows = Array();
    }

    public function AddRow($cells){
        //$cells is an array containing the data to be put in the cells
        $this->rows[] = new Row($this->tbody, $cells);
        return $this->rows[sizeof($this->rows)-1];
    }

}

class Row{

    public function __construct($tbody, $cells){
        //excpets a DomEl Table as the table variable
        $this->element = new DomEl("tr",'',$tbody);
        $this->cells = Array();

        foreach($cells as $cell){
            $this->cells[] =  new DomEl('td',$cell,$this->element);
        }
    }


}

class DomEl{

    public function __construct ($tag,$text="",$parent=Null) {
        //the parent variable is set if the element is nested inside another already created one
        if (isset($parent))
            $this->dom = $parent->dom;
        else
            $this->dom = new DOMDocument('1.0');

        $this->el = $this->dom->createElement($tag,$text);

        if (isset($parent))
            $parent->el->appendChild($this->el);
        else
            $this->dom->appendChild($this->el);
    }

    public function AddAttribute($attr, $value){
        $domAttribute = $this->dom->createAttribute($attr);
        $domAttribute->value = $value;
        $this->el->appendChild($domAttribute);
    }

    public function Show(){
         return $this->dom->saveHTML();
    }

}

function CreateList($li_items){
    $dom = new DOMDocument('1.0');
    $ul = $dom->createElement('ul');
        foreach($li_items as $litext){
            $li = $dom->createElement('li', $litext);
            $ul->appendChild($li);
        }
    $dom->appendChild($ul);
    return $dom->saveHTML();
}


?>
