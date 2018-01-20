<?php
/**
 *
 * Sisältöä lataava olio
 *
 * @param string $html merkkijono, joka voidaan tulostaa ajax-datan syöttämiseksi suoraan html-muodossa
 *
 */
class Loader{

    protected $html = "";

    /**
     * @param string $path polku tietokantakonfiguraatioon
     */
    public function __construct($path){
        $this->con = new DbCon($path);
        return $this;
    }


    /**
     *
     * Tulostaa datan json-muodossa
     *
     */
    public function OutputData(){
        echo json_encode($this->data);
    }

    /**
     *
     * Palauttaa datan html-muodossa
     *
     * @return string html-muotoinen data syötettäväksi sivulle.
     *
     */
    public function GetHtml(){
        return $this->html;
    }

}

/**
 * Messujen tietojen lataaja
 */
class ServiceLoader extends Loader{
    /**
     *
     *
     * @param string $path polku tietokantakonfiguraatioon
     *
     */
        public function __construct($path){
        parent::__construct($path);
        #$this->title = $title;
        $this->GetCurrentSeason();
    }


    /**
     *
     * Aseta polku templates-kansioon
     *
     * @param string $path polku templates-kansioon
     *
     */
    public function SetTemplatePath($path){
        $this->templatepath = $path;
    }

    /**
     *
     * Hakee id-attribuutilla märiteltyyn messuun kaikki siihen määritellyt
     * esityselementit. Luo itse diat templeittien perusteella.
     *
     * @param int $id  sen messun id, jota käsitellään
     *
     **/
    function LoadSegments($id){
        //Kokeile, onko tätä kyseistä messua varten muutettu messurakennetta
        $slots = $this->con->q("SELECT id, slot_name, slot_type, slot_number, content_id, addedclass FROM service_specific_presentation_structure WHERE service_id = :sid ORDER by slot_number",Array("sid"=>$id),"all");
        if(!$slots){
            //Jos ei, käytä yleistä rakennetta
            $slots = $this->con->q("SELECT id, slot_name, slot_type, slot_number, content_id, addedclass FROM presentation_structure ORDER by slot_number",Array(),"all");
        }
        foreach($slots as $key=>$slot){
            switch($slot["slot_type"]){
                case "songsegment":
                    $details = $this->con->q("SELECT id, songdescription, restrictedto, singlename, multiname FROM songsegments WHERE id = :cid",Array("cid"=>$slot["content_id"]),"row");
                    $titles = $this->con->q("SELECT song_title FROM servicesongs WHERE service_id = :id AND songtype = :stype ORDER BY multisong_position",Array("id"=>$id,"stype"=>$details["singlename"]),"all_flat");
                    foreach($titles as $title){
                        $songdata = $this->con->q("SELECT title, composer, lyrics, verses FROM songdata WHERE title = :title",Array("title"=>$title),"row");
                        $slide = new Song($this->templatepath);
                        $slide->SetTitle($songdata["title"])->SetComposer($songdata["composer"])->SetLyrics($songdata["lyrics"])->SetVerses($songdata["verses"]);
                    }
                    break;
                case "infosegment":
                    $details = $this->con->q("SELECT id, maintext, header, genheader, subgenheader, imgname, imgposition FROM infosegments WHERE id = :cid",Array("cid"=>$slot["content_id"]),"row");
                    $slide = new Infoslide($this->templatepath);
                    $slide->SetImg($details["imgname"],$details["imgposition"])
                          ->SetServiceheader($details["genheader"])->SetServiceTheme($details["subgenheader"])->SetSlideHeader($details["header"])->SetText($details["maintext"])->SetSegmentName($slot["slot_name"]);
                    break;
            }

            //Varmista, että ensimmäinen dia on oletuksena ensimmäinen näytettävä
            if($key==0)
                $slide->Set("iscurrent","current");
            else
                $slide->Set("iscurrent","");

            //Lisää vielä tieto mahdollisesta tarkentavasta css-luokasta. Näiden perusteella dioja voidaan jaotella esimerkiksi eri messun osien mukaisesti
            $slide->SetAddedClass(str_replace(".","",$slot["addedclass"]));

            $this->html .= "{$slide->Output()}\n\n";
        }
    }

    /**
     * Hakee kaikkien nykyisessä messukaudessa olevien messujen päivämäärät
     **/
    function LoadServiceDates(){
        $data = $this->con->q("SELECT id, servicedate FROM services WHERE servicedate BETWEEN :startdate AND :enddate ORDER BY servicedate ",Array("startdate"=>$this->season["startdate"],"enddate"=>$this->season["enddate"]),"all");
        $this->data = Array();
        foreach($data as $val){
            $this->data[] = Array("date" => FormatDate($val["servicedate"]), "id" =>  $val["id"]);
        }
    }


    /**
     * Hakee kaikki vastuuhenkilöt nykyisestä messusta (vain nopeaa esikatselua varten)
     *
     * @param integer id käsiteltävän messun id
     *
     **/
    function LoadResponsibles($id){
        $this->data =  $this->con->q("SELECT responsibility, responsible FROM responsibilities WHERE service_id = :sid", Array("sid"=>$id),"all");
    }

    /**
     * Hakee kaikki laulut nykyisestä messusta
     *
     * @param integer id käsiteltävän messun id
     *
     **/
    function LoadSongs($id){
        $this->data =  $this->con->q("SELECT song_title FROM servicesongs WHERE service_id = :sid ORDER by multisong_position, id", Array("sid"=>$id),"all_flat");
    }
    
    /**
     *
     * Valitsee sen messukauden, joka on lähinnä nykyistä päivämäärää.
     * Yrittää ensin löytää kauden, jonka sisälle nykyinen päivä osuu.
     * Tämän jälkeen yrittää hakea ensimmäisen kauden tulevaisuudesta.
     * Jos tämäkin epäonnistuu, hakee lähimmän kauden menneisyydestä.
     *
     * 
     * @param DbCon $con yhteys tietokantaan
     *
     * @return  array  Taulukon, jossa on ilmaistu messukauden alku- ja loppupäivät.
     *
     */
    function GetCurrentSeason(){
        $date = date('Y-m-d');
        $season = $this->con->q("SELECT id, name, startdate, enddate FROM seasons WHERE startdate <=:date AND enddate >=:date ORDER BY startdate", Array("date"=>$date),"row");
        #Jos nykyinen pvm ei osu mihinkään kauteen
        if(!$season) #1: ota seuraava kausi tulevaisuudesta
            $season = $this->con->q("SELECT id, name, startdate, enddate FROM seasons WHERE startdate >=:date ORDER BY startdate", Array("date"=>$date),"row");
        if(!$season) #2: ota edellinen kausi menneisyydestä
            $season = $this->con->q("SELECT id, name, startdate, enddate FROM seasons WHERE enddate <=:date ORDER BY enddate DESC", Array("date"=>$date),"row");
        $this->season = $season;
        return $this;
    }
}



/**
 * Laulujen nimien ja sanojen lataaja
 */
class SongLoader extends Loader{

    /**
     * @param string $path polku tietokantakonfiguraatioon
     * @param string $testament ot / nt - kumpi testamentti
     */
        public function __construct($title,$path){
        $this->title = $title;
        parent::__construct($path);
    }

    /**
     * Hakee kaikkien laulujen nimet tietokannasta ja tulostaa ne filtteröitynä
     * laulun nimen tai jonkin sen sisältämän merkkijonon mukaan.
     */
    public function LoadTitles(){
        $this->data = $this->con->q("SELECT title FROM songdata WHERE title LIKE :giventitle ORDER by title",Array("giventitle"=>"%{$this->title}%"),"all_flat");
    }


    /**
     * Hakee laulun säkeistöt nimen perusteella.
     */
    public function LoadContent(){
        $row = $this->con->q("SELECT title, verses FROM songdata WHERE title = :giventitle ORDER by title",Array("giventitle"=>$this->title),"row");
        $this->data = Array("title"=>$row["title"],"verses"=>$row["verses"]);
    }

}


/**
 * Lataa raamatuntekstejä ja jakeiden osoitteita tietokannasta.
 *
 */
class BibleLoader extends Loader{

    /**
     * @param string $path polku tietokantakonfiguraatioon
     * @param string $testament ot / nt - kumpi testamentti
     */
        public function __construct($testament,$path){
        $this->testament = "verses_{$testament}_fi";
        parent::__construct($path);
    }

    /**
     * Lataa kirjojen nimet ko. testamentissa
     */
    public function LoadBookNames(){
        $this->data = $this->con->q("SELECT DISTINCT q.book FROM (SELECT  book, id FROM {$this->testament} order by id) as q",Array(),"all_flat");
    }

    /**
     * Lataa  lukujen määrä
     *
     * @param string $bookname kirja, jonka luvut ladataan
     *
     */
    public function LoadChapters($bookname){
        $this->data = $this->con->q("SELECT DISTINCT q.chapterno FROM (SELECT chapterno, id FROM {$this->testament} WHERE book = :bookname order by id) as q",Array("bookname"=>$bookname),"all_flat");
    }

    /**
     * Lataa  jakeiden määrä
     *
     * @param string $bookname kirja, jonka luvut ladataan
     * @param string $chapterno luku, jonka jakeet ladataan
     *
     */
    public function LoadVerses($bookname, $chapterno){
        $this->data = $this->con->q("SELECT DISTINCT q.verseno FROM (SELECT verseno, id from {$this->testament} WHERE book = :bookname and chapterno = :chapterno order by id) as q",Array("bookname"=>$bookname,"chapterno"=>$chapterno),"all_flat");
    }

    /**
     * Lataa jakeet käyttäjän märittämältä väliltä
     *
     * @param array $start Taulukko muotoa (book,chapter,verse)
     * @param array $end Taulukko muotoa (book,chapter,verse)
     *
     */
    public function LoadBibleVerses($start, $end){
        $this->data = $this->con->q("SELECT content FROM {$this->testament} WHERE id BETWEEN 
                (SELECT id FROM {$this->testament} WHERE book = :startbook AND chapterno = :startchapter AND verseno = :startverse)
                AND
                (SELECT id FROM {$this->testament} WHERE book = :endbook AND chapterno = :endchapter AND verseno = :endverse)",
                Array("startbook"=>$start[0],"startchapter"=>$start[1],"startverse"=>$start[2],
                      "endbook"=>$end[0],"endchapter"=>$end[1],"endverse"=>$end[2]),"all_flat");
        return $this;
    }

}


/**
 * Lataa tietoa siitä, mitä kuvia yms. on saatavilla lokaalisti. 
 *
 */
class AssetLoader extends Loader{

    /**
     * Lataa saatavilla olevat taustakuvat, kuvat yms.
     *
     * @param string $asset_type mistä tietokannan taulukosta haetaan eli minkä tyyppisestä sisällöstä on kyse
     */
    public function LoadAssetNames($asset_type){
        $this->data = $this->con->q("SELECT DISTINCT filename FROM {$asset_type} order by filename",Array(),"all_flat");
    }

    /**
     * Lataa lisätietoja saatavilla olevista kuvista tms.
     *
     * @param string $asset_type mistä tietokannan taulukosta haetaan eli minkä tyyppisestä sisällöstä on kyse
     */
    public function LoadAssetDescription($asset_type, $filename){
        $this->data = $this->con->q("SELECT description FROM {$asset_type} where filename = :fn",Array("fn"=>$filename),"all_flat");
    }

}


/**
 * Lataa  tietokannasta oletustyylit ja käyttäjän tallentamat lisätyylit
 *
 */
class StyleLoader extends Loader{

    /**
     * Lataa kaikki tyylitietokannassa olevat tyylitiedot tulostettavaksi html-muodossa
     *
     * @param Array $classes luokat, jotka esityksessä ovat käytössä.
     * @param string $stylesheet mahdollisesti haettavan tallennetun tyylisetin nimi
     *
     */
    public function LoadAllStyles($classes,$stylesheet="default"){
        //ota talteen tässä stylesheetissä valmiiksi tuetut luokat
        $classnames = $this->con->q("SELECT distinct classname FROM styles WHERE stylesheet = :sheet AND classname <> 'sample'",Array("sheet"=>$stylesheet),"all_flat");
        $text_tags = Array("article","h1","h2","h3","p"); 
        $css_style_blocks = "";
        foreach($classnames as $classname){
            //Ensin muut kuin tägeittäin asetellut tyylit
            $attrs = implode($this->con->q("SELECT CONCAT(attr, ': ', value, ';') AS line FROM styles WHERE stylesheet = :sheet and classname = :cname and tagname = :tname ",
                Array("tname"=>'', "cname"=>$classname,"sheet"=>$stylesheet),"all_flat"),"\n    ");
            $css_style_blocks .= "\n\n$classname {\n    {$attrs}\n}";
            //Sitten tägeittäin (=tekstitasoittain: p, h1, h2 jne... mutta myös article)
            foreach($text_tags as $tag){
                $attrs = implode($this->con->q("SELECT CONCAT(attr, ': ', value, ';') AS line FROM styles WHERE stylesheet = :sheet and classname = :cname and tagname = :tname ",
                    Array("tname"=>$tag, "cname"=>$classname,"sheet"=>$stylesheet),"all_flat"),"\n    ");
                $css_style_blocks .= "\n\n$classname $tag {\n    {$attrs}\n}";
            }
        }
        $this->html = $css_style_blocks;
    }

    /**
     * Lataa kaikki tyylitietokannassa olevat tyylitiedot ja yhdistä
     * taulukoksi, jonka avulla voidaan nopeasti vertailla, sitä, onko jotakin
     * tyyliä muutettu.
     *
     * @param Array $classes luokat, jotka esityksessä ovat käytössä.
     * @param string $stylesheet mahdollisesti haettavan tallennetun tyylisetin nimi
     *
     */
    public function LoadAllStylesAsArrayOfStrings($stylesheet="default"){
        //ota talteen tässä stylesheetissä valmiiksi tuetut luokat
        $this->data = $this->con->q("SELECT CONCAT(classname, ';', tagname, ';', attr, ';', value) AS line FROM styles WHERE stylesheet = :sheet AND classname <> 'sample'",Array("sheet"=>$stylesheet),"all_flat");
    }

    /**
     * Listaa kaikki tietokantaan tallennetut tyylipohjat
     *
     */
    public function LoadAllStyleSheets(){
        $this->data = $this->con->q("SELECT DISTINCT stylesheet FROM styles ORDER by stylesheet",Array(),"all_flat");
    }

    /**
     * Päivitä koko tyylitietokanta yhden stylesheetin osalta
     *
     * @param Array $all_rows taulukko, jonka jokainen solu on yksi päivitettävä tietokannan sarake
     * @param string $sheet tallennettavan tyylin nimi
     *
     */
    public function UpdateStyles($all_rows, $sheet){
        foreach($all_rows as $key => $row){
            if(key_exists("attr",$row)){
                $this->con->q("UPDATE styles SET value = :v WHERE attr = :a AND stylesheet = :s AND classname = :cn AND tagname = :tn",
                    Array("a"=>$row["attr"],"v"=>$row["value"],"s"=>$sheet,"cn"=>$row["classname"],"tn"=>$row["tagname"]),"none");
            }
        }
    }

}

/**
 *
 * Muokkaa päivämäärän suomalaiseen esitysmuotoon.
 *
 * @param date $date päivämäärä, joka halutaan muuttaa
 * @return string merkkijonomuotoinen muokattu päivämäärä
 *
 */
function FormatDate($date){
    $date_arr = ParseMonthDayYear($date);
    return $date_arr["day"] . "." . $date_arr["month"] . "." . $date_arr["year"];
}


/**
 *
 * Erottele kuukausi, vuosi ja päivä Päivämääräoliosta.
 *
 * @param date $date Päivämäärä, joka jäsennetään.
 * @return array taulukko, jossa päivä, kuukausi ja vuosi on eroteltu ja siistitty
 *
 **/
function ParseMonthDayYear($date){
    $month = substr($date, 5,2);
    $year = substr($date, 0,4);
    $day = substr($date, 8,2);
    return Array("day"=>RemoveZero($day), "month"=>RemoveZero($month), "year"=>$year);
}


/**
 *
 * Poistaa nollat yksittäisistä numeroista, niin että 05 -> 5
 * 
 * @param string $input
 * @return string siistitty numero
 *
 **/
function RemoveZero($input){
    if(substr($input,0,1)=="0")
        $input  = substr($input,1,1);
    return $input;
}

?>
