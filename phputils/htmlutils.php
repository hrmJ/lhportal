<?php

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
