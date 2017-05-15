var Nightmare = require('nightmare');
var expect = require('chai').expect; // jshint ignore:line

describe('Basic test suite', function() {

    before(function() {
        console.log("Aloitetaan testit. Kirjaudutaan sisään...");
        this.timeout('30s');
        this.nightmare = Nightmare({show:false});
        this.rootaddress = "lhportal"; // vaihda tarpeen mukaan
    });

    after(function() {
      console.log("Lopetetaan...");
      // ...
    });

    describe('Test login page opens', function() {
      it('User sees the title and the version number', function(done) {
        this.nightmare
          .goto('http://localhost/' + rootaddress + '/index.php')
          .evaluate(function () {
            return document.title;
          })
          .end()
          .then(function(title) {
            expect(title).to.equal('Majakkaportaali 0.1');
            done();
          })
      });
    });

});

