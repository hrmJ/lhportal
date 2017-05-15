var Nightmare = require('nightmare');
var expect = require('chai').expect; // jshint ignore:line


describe('Basic test suite', function() {

    before(function() {
        console.log("Aloitetaan testit. Kirjaudutaan sisään...");
        this.rootaddress = "lhportal"; // vaihda tarpeen mukaan
        this.nightmare = Nightmare({show:false});
    });

    after(function() {
      console.log("Lopetetaan...");
      // ...
    });

    describe('Test login succeeded', function() {
      it('User sees the header in the top bar as a sign of a succesful login', function(done) {
        this.timeout('10s');
        this.nightmare
            .goto('http://localhost/' + this.rootaddress + '/index.php')
            .type('#username','testusr')
            .type('#password','testpw')
            .click('#loginbutton')
            .wait(1000)
            .evaluate(function () {
                return document.querySelector('#homeli').textContent;
            })
            .end()
            .then(function(header) {
                expect(header).to.equal('Majakkaportaali');
                done();
            })
      });
    });

});

