var HttpConnectProxy = require('proxy-test-server/lib/server');
var proxy = new HttpConnectProxy();

proxy.listen(9999, function () {
    // console.log('PROXY Server Listening');
});

proxy.on('connect', function (port, host, socket) {
    // var time = new Date().toISOString().substr(0, 19).replace('T', '');
    // console.log('[%s] From %s to %s:%s', time, socket.remoteAddress, host, port);
});
