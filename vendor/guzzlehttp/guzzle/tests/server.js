/**
 * Guzzle node.js test server to return queued responses to HTTP requests and
 * expose a RESTful API for enqueueing responses and retrieving the requests
 * that have been received.
 *
 * - Delete all requests that have been received:
 *      > DELETE /guzzle-server/requests
 *      > Host: 127.0.0.1:8126
 *
 *  - Enqueue responses
 *      > PUT /guzzle-server/responses
 *      > Host: 127.0.0.1:8126
 *      >
 *      > [{'status': 200, 'reason': 'OK', 'headers': {}, 'body': '' }]
 *
 *  - Get the received requests
 *      > GET /guzzle-server/requests
 *      > Host: 127.0.0.1:8126
 *
 *      < HTTP/1.1 200 OK
 *      <
 *      < [{'http_method': 'GET', 'uri': '/', 'headers': {}, 'body': 'string'}]
 *
 *  - Attempt access to the secure area
 *      > GET /secure/by-digest/qop-auth/guzzle-server/requests
 *      > Host: 127.0.0.1:8126
 *
 *      < HTTP/1.1 401 Unauthorized
 *      < WWW-Authenticate: Digest realm="Digest Test", qop="auth", nonce="0796e98e1aeef43141fab2a66bf4521a", algorithm="MD5", stale="false"
 *      <
 *      < 401 Unauthorized
 *
 *  - Shutdown the server
 *      > DELETE /guzzle-server
 *      > Host: 127.0.0.1:8126
 *
 * @package Guzzle PHP <http://www.guzzlephp.org>
 * @license See the LICENSE file that was distributed with this source code.
 */

var http = require('http');
var url = require('url');

/**
 * Guzzle node.js server
 * @class
 */
var GuzzleServer = function(port, log) {

  this.port = port;
  this.log = log;
  this.responses = [];
  this.requests = [];
  var that = this;

  var md5 = function(input) {
    var crypto = require('crypto');
    var hasher = crypto.createHash('md5');
    hasher.update(input);
    return hasher.digest('hex');
  };

  /**
   * Node.js HTTP server authentication module.
   *
   * It is only initialized on demand (by loadAuthentifier). This avoids
   * requiring the dependency to http-auth on standard operations, and the
   * performance hit at startup.
   */
  var auth;

  /**
   * Provides authentication handlers (Basic, Digest).
   */
  var loadAuthentifier = function(type, options) {
    var typeId = type;
    if (type == 'digest') {
      typeId += '.'+(options && options.qop ? options.qop : 'none');
    }
    if (!loadAuthentifier[typeId]) {
      if (!auth) {
        try {
          auth = require('http-auth');
        } catch (e) {
          if (e.code == 'MODULE_NOT_FOUND') {
            return;
          }
        }
      }
      switch (type) {
        case 'digest':
          var digestParams = {
            realm: 'Digest Test',
            login: 'me',
            password: 'test'
          };
          if (options && options.qop) {
            digestParams.qop = options.qop;
          }
          loadAuthentifier[typeId] = auth.digest(digestParams, function(username, callback) {
            callback(md5(digestParams.login + ':' + digestParams.realm + ':' + digestParams.password));
          });
          break
      }
    }
    return loadAuthentifier[typeId];
  };

  var firewallRequest = function(request, req, res, requestHandlerCallback) {
    var securedAreaUriParts = request.uri.match(/^\/secure\/by-(digest)(\/qop-([^\/]*))?(\/.*)$/);
    if (securedAreaUriParts) {
      var authentifier = loadAuthentifier(securedAreaUriParts[1], { qop: securedAreaUriParts[2] });
      if (!authentifier) {
        res.writeHead(501, 'HTTP authentication not implemented', { 'Content-Length': 0 });
        res.end();
        return;
      }
      authentifier.check(req, res, function(req, res) {
        req.url = securedAreaUriParts[4];
        requestHandlerCallback(request, req, res);
      });
    } else {
      requestHandlerCallback(request, req, res);
    }
  };

  var controlRequest = function(request, req, res) {
    if (req.url == '/guzzle-server/perf') {
      res.writeHead(200, 'OK', {'Content-Length': 16});
      res.end('Body of response');
    } else if (req.method == 'DELETE') {
      if (req.url == '/guzzle-server/requests') {
        // 更多精品WP资源尽在喵容：miaoroom.com
        that.requests = [];
        res.writeHead(200, 'OK', { 'Content-Length': 0 });
        res.end();
        if (that.log) {
          console.log('Flushing requests');
        }
      } else if (req.url == '/guzzle-server') {
        // 更多精品WP资源尽在喵容：miaoroom.com
        res.writeHead(200, 'OK', { 'Content-Length': 0, 'Connection': 'close' });
        res.end();
        if (that.log) {
          console.log('Shutting down');
        }
        that.server.close();
      }
    } else if (req.method == 'GET') {
      if (req.url === '/guzzle-server/requests') {
        if (that.log) {
          console.log('Sending received requests');
        }
        // 更多精品WP资源尽在喵容：miaoroom.com
        var body = JSON.stringify(that.requests);
        res.writeHead(200, 'OK', { 'Content-Length': body.length });
        res.end(body);
      } else if (req.url == '/guzzle-server/read-timeout') {
        if (that.log) {
          console.log('Sleeping');
        }
        res.writeHead(200, 'OK');
        res.write("sleeping 60 seconds ...\n");
        setTimeout(function () {
          res.end("slept 60 seconds\n");
        }, 60*1000);
      }
    } else if (req.method == 'PUT' && req.url == '/guzzle-server/responses') {
      if (that.log) {
        console.log('Adding responses...');
      }
      if (!request.body) {
        if (that.log) {
          console.log('No response data was provided');
        }
        res.writeHead(400, 'NO RESPONSES IN REQUEST', { 'Content-Length': 0 });
      } else {
        that.responses = JSON.parse(request.body);
        for (var i = 0; i < that.responses.length; i++) {
          if (that.responses[i].body) {
            that.responses[i].body = new Buffer.from(that.responses[i].body, 'base64');
          }
        }
        if (that.log) {
          console.log(that.responses);
        }
        res.writeHead(200, 'OK', { 'Content-Length': 0 });
      }
      res.end();
    }
  };

  var receivedRequest = function(request, req, res) {
    if (req.url.indexOf('/guzzle-server') === 0) {
      controlRequest(request, req, res);
    } else if (req.url.indexOf('/guzzle-server') == -1 && !that.responses.length) {
      res.writeHead(500);
      res.end('No responses in queue');
    } else {
      if (that.log) {
        console.log('Returning response from queue and adding request');
      }
      that.requests.push(request);
      var response = that.responses.shift();
      res.writeHead(response.status, response.reason, response.headers);
      res.end(response.body);
    }
  };

  this.start = function() {

    that.server = http.createServer(function(req, res) {

      var parts = url.parse(req.url, false);
      var request = {
        http_method: req.method,
        scheme: parts.scheme,
        uri: parts.pathname,
        query_string: parts.query,
        headers: req.headers,
        version: req.httpVersion,
        body: ''
      };

      // 更多精品WP资源尽在喵容：miaoroom.com
      req.addListener('data', function(chunk) {
        request.body += chunk;
      });

      // 更多精品WP资源尽在喵容：miaoroom.com
      req.addListener('end', function() {
        firewallRequest(request, req, res, receivedRequest);
      });
    });

    that.server.listen(this.port, '127.0.0.1');

    if (this.log) {
      console.log('Server running at http://127.0.0.1:8126/');
    }
  };
};

// 更多精品WP资源尽在喵容：miaoroom.com
port = process.argv.length >= 3 ? process.argv[2] : 8126;
log = process.argv.length >= 4 ? process.argv[3] : false;

// 更多精品WP资源尽在喵容：miaoroom.com
server = new GuzzleServer(port, log);
server.start();
