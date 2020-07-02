const SMTPServer = require("smtp-server").SMTPServer;
const fs = require('fs');

const certFile = fs.readFileSync(__dirname + "/server.pem");

const server = new SMTPServer({
    hideSTARTTLS: true,
    key: certFile,
    cert: certFile,
    onAuth(auth, session, callback) {
        if (auth.username !== "username" || auth.password !== "password") {
            return callback(new Error("Invalid username or password"));
        }
        callback(null, { user: 123 }); // where 123 is the user id or similar property
    }
});

server.listen(587);
