/*


1. Oleellisimmat: Itse messuihin ja vastuuhenkilöihin liittyvät taulut
======================================================================


Kaudet-tauluun tallennetaan alku- ja loppupäivämäärät erikseen
nimettävistä "kausista", kuten "kevät 2016", "syksy 2016", "kevät 2017"

*/

CREATE TABLE kaudet (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  alkupvm date NOT NULL,
  loppupvm date NOT NULL,
  tyyppi varchar(100) DEFAULT NULL,
  teema varchar(100) DEFAULT NULL,
  kommentit text,
  nimi varchar(100) DEFAULT NULL,
  PRIMARY KEY (id)
);


/*

 Messut-taulu kokoaa yhteen kaikki messut kaudesta riippumatta.
 Messut sijoitetaan johonkin tiettyyn kauteen päivämäärän perusteella

*/

CREATE TABLE messut (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  pvm date NOT NULL,
  teema varchar(100) NOT NULL,
  PRIMARY KEY (id)
);


/*
 Vastuut-taulu sisältää informaation esimerkiksi siitä, että
 Messussa, jonka id=4 vastuuta nimeltä "kahvitus" hoitaa henkilö nimeltä "pekka"
 Vastuita voi siis teoriassa olla mitä tahansa, ja kunkin vastuun nimi annetaan joka kerta
 erikseen vastuullinen-kentässä
*/

CREATE TABLE vastuut (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  messu_id int(10) unsigned NOT NULL,
  vastuu varchar(100) DEFAULT NULL,
  vastuullinen varchar(100) DEFAULT NULL,
  kommentit text,
  PRIMARY KEY (id),
  KEY messu_index (messu_id),
  CONSTRAINT vastuut_ibfk_1 FOREIGN KEY (messu_id) REFERENCES messut (id) ON DELETE CASCADE
) ;


/*
 Kommentit seuraavassa taulussa. Tällä hetkellä (9.11.2016) kommentit
 ainoastaan messukohtaisia, eli ne linkitetään messut-tietokantaan
 many-to-one-suhteella messun id:n perusteella.
*/




/*


2. Käyttäjänhallintaan liittyvät taulut
======================================================================

Users-taulussa käyttäjänimet ja salasanat

*/

CREATE TABLE majakka_users (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(20) NOT NULL,
  password char(40) NOT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY username (username)
) ;



/*


3. LAULUIHIN liittyvät taulut
======================================================================



 LAULUT-tauluun (Nimi pitäisi muuttaa johdonmukaisemmaksi)
 tallennetaan se, *mitä lauluja missäkin messussa lauletaan*  
 (ei siis itse laulujen sisältöä)
 TODO: linkkaa tämä suoraan songs-tauluun

*/

CREATE TABLE laulut (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  messu_id int(10) unsigned NOT NULL,
  tyyppi varchar(100) DEFAULT NULL,
  nimi varchar(400) DEFAULT NULL,
  songlink int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY messu_index (messu_id),
  CONSTRAINT laulut_ibfk_1 FOREIGN KEY (messu_id) REFERENCES messut (id) ON DELETE CASCADE
);


/*

 SONGS-taulussa on data itse laulujen sisällöstä.
 Tarkemmin sanottuna tämä taulu sisältää metatiedot kustakin
 laulusta: nimen, potentiaalisesti sanoittajan ja säveltäjän,
 päivämäärän, milloin lisätty kantaan ja mahdollisia muita tietoja,
 kuten esimerkiksi tietoja esitysjärjestyksestä
*/


CREATE TABLE songs (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(99) DEFAULT NULL,
  filename varchar(299) DEFAULT NULL,
  sav varchar(99) DEFAULT NULL,
  san varchar(99) DEFAULT NULL,
  added date DEFAULT NULL,
  PRIMARY KEY (id)
);


/*

 LAULUJEN varsinaiset sanat tallennetaan verses-tauluun, niin
 että jokainen säkeistö on omana rivinään ja linkattu many-to-one-suhteessa
 songs-tietokantaan. Säkeistöjen järjestyksessä luotetaan siihen, että
 ne syötetään kantaan oikeassa järjestyksessä (ensin 1.säkeistö, sitten 2. ym.), jolloin
 id-kentän perusteella voidaan rakentaa laulu oikeassa järjestyksessä.
 versetype-kenttään VOISI tallentaa tiedon siitä, onko kyseessä esim. kertosäe vai tavallinen säkeistö

*/


CREATE TABLE verses (
  id int(11) NOT NULL AUTO_INCREMENT,
  content varchar(2000) DEFAULT NULL,
  versetype varchar(2000) DEFAULT NULL,
  song_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY song_id (song_id),
  CONSTRAINT verses_ibfk_1 FOREIGN KEY (song_id) REFERENCES songs (id)
) ;


