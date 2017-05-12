var Nightmare = require('nightmare');
var expect = require('chai').expect; // jshint ignore:line


describe('Test login page opens', function() {
  it('User sees the title and the version number', function(done) {
    var nightmare = Nightmare({show:false})
    nightmare
      .goto('http://localhost/majakkaportaali/index.php')
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

