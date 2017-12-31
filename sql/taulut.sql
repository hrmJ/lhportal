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
  info varchar(9999),
  kolehtikohde varchar(99) DEFAULT NULL,
  kolehtia_keratty decimal(60,2) DEFAULT 0,
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


CREATE TABLE liturgiset (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  songtype varchar(100) DEFAULT NULL,
  songname varchar(400) DEFAULT NULL,
  name varchar(400) DEFAULT NULL,
  PRIMARY KEY (id)
);


CREATE TABLE kolehtitavoitteet (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  kohde varchar(100) DEFAULT NULL,
  tavoite varchar(100) DEFAULT NULL,
  tavoitemaara decimal(60,2) DEFAULT 0,
  PRIMARY KEY (id)
);

--CREATE UNIQUE INDEX idx_liturgiset ON liturgiset(name);

INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Jumalan karitsa', 'jk_v1', 'Versio 1 (Riemumessusta)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Jumalan karitsa', 'jk_v2', 'Versio 2 (Rantatalo = Oi Jumalan karitsa)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Jumalan karitsa', 'jk_v3', 'Versio 3 (2. sävelmäsarja)') ON DUPLICATE KEY UPDATE name=name;


INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v1', 'Versio 1 (Perus)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v2', 'Versio 2 (Pyhä Kuningas)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v3', 'Versio 3 (Olet pyhä)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v4', 'Versio 4 (Pyhä yksi yhteinen 1)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v5', 'Versio 4 (Pyhä yksi yhteinen 2)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v6', 'Versio 6 (Virsi 134)') ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `liturgiset` (`songtype`, `songname`, `name`) VALUES ('Pyhä-hymni', 'pyh_v7', 'Versio 7 (Halleluja, kaikkivaltias hallitsee)') ON DUPLICATE KEY UPDATE name=name;

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


/*
 
Seuraava taulu messun kulun näkymiseksi erillisellä sivulla

*/


CREATE TABLE messukulku (
  id int(11) NOT NULL AUTO_INCREMENT,
  entry varchar(300) DEFAULT NULL,
  messu_id int(11),
  entrytype varchar(100) DEFAULT NULL,
  typeidentifier varchar(100) DEFAULT NULL,
  iscurrent boolean DEFAULT FALSE,
  PRIMARY KEY (id)
) ;

/*
 
Lokien tallentamiseksi portaalin tapahtumista:

*/


CREATE TABLE logs (
  id int(20) NOT NULL AUTO_INCREMENT,
  time DATETIME DEFAULT NULL,
  event varchar(200) DEFAULT NULL,
  PRIMARY KEY (id)
) ;



/*  
 
Soittajapankin taulut 
 
*/



CREATE TABLE soittajat (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  nimi varchar(100) NOT NULL,
  puhelin varchar(100) NOT NULL,
  email varchar(9999),
  PRIMARY KEY (id)
);


/*
 Vastuut-taulu sisältää informaation esimerkiksi siitä, että
 Messussa, jonka id=4 vastuuta nimeltä "kahvitus" hoitaa henkilö nimeltä "pekka"
 Vastuita voi siis teoriassa olla mitä tahansa, ja kunkin vastuun nimi annetaan joka kerta
 erikseen vastuullinen-kentässä
*/

CREATE TABLE soittimet (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  soittaja_id int(10) unsigned NOT NULL,
  soitin varchar(100) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY soittaja_index (soittaja_id),
  CONSTRAINT soittimet_ibfk_1 FOREIGN KEY (soittaja_id) REFERENCES soittajat (id)
) ;


CREATE TABLE puhujat (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  nimi varchar(100) NOT NULL,
  puhelin varchar(100) NOT NULL,
  email varchar(9999),
  PRIMARY KEY (id)
);


/*
 Vastuut-taulu sisältää informaation esimerkiksi siitä, että
 Messussa, jonka id=4 vastuuta nimeltä "kahvitus" hoitaa henkilö nimeltä "pekka"
 Vastuita voi siis teoriassa olla mitä tahansa, ja kunkin vastuun nimi annetaan joka kerta
 erikseen vastuullinen-kentässä
*/

CREATE TABLE puheenaiheet (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  puhuja_id int(10) unsigned NOT NULL,
  puheenaihe varchar(100) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY puhuja_index (puhuja_id),
  CONSTRAINT puheenaiheet_ibfk_1 FOREIGN KEY (puhuja_id) REFERENCES puhujat (id)
) ;

