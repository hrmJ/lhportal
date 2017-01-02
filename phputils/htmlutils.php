<?php

class Comment{


    public function __construct($commentdata){

        $outercont  = new DomEl("div");
        $outercont->AddAttribute('class',"outer_comment_container");

        //$persondiv = new DomEl("div","",$outercont);
        //$persondiv->AddAttribute('class','commentator');
        //$cdate = strtotime($commentdata["comment_time"]);
        //$bolded = new DomEl("span",$commentdata["commentator"] . " kommentoi " . date('j.n.Y',$cdate) . ": ", $persondiv);

        $cont = new DomEl("div","",$outercont);
        $cont->AddAttribute('class',"comment_container");
        $cont->AddChild(FormatCommentHeader($commentdata));

        $commentcontent = new DomEl("div",$commentdata["content"],$cont);
        $commentcontent->AddAttribute('class',"comment_content");
        $commentcontent->AddAttribute("id","commentcontent_" . $commentdata["id"]);


        $this->container = $cont;
    }


}

function FormatCommentHeader($commentdata){
    $cont = new DomEl("div");
    $cont->AddAttribute('class',"comment_header");

    $infocont = new DomEl("div",$commentdata["commentator"],$cont);
    $cdate = strtotime($commentdata["comment_time"]);
    $infocont2 = new DomEl("div",date('j.n.Y',$cdate) ,$cont);
    $infocont3 = new DomEl("div",$commentdata["theme"] ,$cont);
    $infocont3->AddAttribute("id","ctheme_" . $commentdata["id"]);

    $linkcont = new DomEl("div", "", $cont);
    $editlink = new DomEl("a","Muokkaa",$linkcont);
    $editlink->AddAttribute("id","editcomment_" . $commentdata["id"]);
    $editlink->AddAttribute("onClick","EditComment(" . $commentdata["id"] . ");");
    $removelink = new DomEl("a","Poista",$linkcont);
    $removelink->AddAttribute("onClick","RemoveComment(" . $commentdata["id"] . ");");

    return $cont;
}

class HtmlTable{

    public function __construct($parent=Null){
        if (isset($parent)){
            $this->element = new DomEl("table","",$parent);
        }
        else{
            $this->element = new DomEl("table");
        }
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

class CommentList{
    public function __construct ($par, $comments, $messuid) {
            $this->list = new DomEl("ul"," ",$par);
            $this->list->AddAttribute("id","clist_" . $messuid);
            foreach($comments as $comment){
                $li = new DomEl("li",$comment['content'],$this->list);
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

        $this->el = $this->dom->createElement($tag,htmlspecialchars($text));

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

    public function AddChild($el){
        #ini_set('display_errors', 1);
        #ini_set('display_startup_errors', 1);
        #error_reporting(E_ALL);
        $el->el = $this->dom->importNode($el->el, true);
        $this->el->appendChild($el->el);
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
