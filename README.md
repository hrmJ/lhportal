# Majakkaportaali

Projektin tarkoitus tehdä seurakuntavastuiden jakamisesta ja viestinnästä helpompaa. 

# Yleistietoja

Portaalin data sijaitsee MySQL-tietokannassa, käyttöliittymä on toteutettu
simppeleillä web-tekniikoilla (css, html, php, js).

# Kehitysympäristön pystyttäminen

Tässä perustiedot siitä, miten projektin saa käyntiin.

##  Testityökalut

Perusideologiana projektin kehittämisessä on testata koodia mahdollisimman tehokkaasti.
Tämän toteuttamiseksi voidaan käyttää seuraavia työkaluja.

### Node js

Asennus esim. Ubuntussa:

    apt-get install nodejs npm node

Node pitää mahdollisesti päivittää uusimpaan versioon:

    sudo npm cache clean -f
    sudo npm install -g n
    sudo n stable

### Nightmare

Selainpohjainen testaus on toteutettu Nightmare + mocha + chai -yhdistelmällä

    npm install nightmare
    npm install mocha


## Testaaminen

Aja `npm test` projektin juurikansiossa.


## SQL-taulujen luonti

ks. sql/taulut.sql

# HUOM!

Tällä hetkellä diojen luonti palvelimella on siirtymävaiheessa, niin että 
esitystekniikka on toteutettu symlinkkeinä htmlslides-Projektin kansiosta 
(ks. pres-kansio tässä projektissa).
