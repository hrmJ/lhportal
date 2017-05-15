var Nightmare = require('nightmare');
var expect = require('chai').expect; // jshint ignore:line
var assert = require('chai').assert; 


describe('Basic test suite', function() {

    before(function() {
        console.log("Aloitetaan testit. Kirjaudutaan sisään...");
        //this.rootaddress = "lhportal"; // vaihda tarpeen mukaan
        this.rootaddress = "majakkaportaali"; // vaihda tarpeen mukaan
        this.nightmare = Nightmare({show:false});
        //this.nightmare = Nightmare({show:true});
    });

    after(function() {
        this.nightmare.end();
        console.log("Lopetetaan...");
      // ...
    });

    describe('Try logging in', function() {
      it('User sees the header in the top bar as a sign of a succesful login', function(done) {
        this.timeout('10s');
        this.nightmare
            .goto('http://localhost/' + this.rootaddress + '/index.php')
            .exists("#username")
            .type('#username','testusr')
            .type('#password','testpw')
            .click('#loginbutton')
            .wait(1000)
            .evaluate(function () {
                return document.querySelector('#homeli').textContent;
            })
            .then(function(header) {
                expect(header).to.equal('Majakkaportaali');
                done();
            }).catch(done);
      });
    });

    describe('Newsfeed tests', function() {
        describe('', function() {
          it('The desktop user sees the newsfeed', function(done) {
            this.timeout('2s');
            this.nightmare
                .goto('http://localhost/' + this.rootaddress + '/index.php')
                .exists("#newsfeed")
                .then(function (elementExists) {
                    assert.isOk(elementExists,"not ok");
                    done();
                })
                .catch(done);
          });
        });
    });

    describe.skip('Tests for inserting songs', function() {
      it('Users follows the link in the top menu and ends up at songs.php', function(done) {
        this.timeout('10s');
        this.nightmare
            .click('#homeli')
            .click('#songphplink')
            .wait(4000)
            .evaluate(function () {
                return document.querySelector('#mainheader').textContent;
            })
            .then(function(header) {
                expect(header).to.equal('Majakkamessun laulut');
                done();
            })
      });
    });

});

