const SMTPServer = require("smtp-server").SMTPServer;
const fs = require('fs');

const certFile = fs.readFileSync(__dirname + "/server.pem");

const server = new SMTPServer({
    secure: true,
    key: certFile,
    cert: certFile,
    onAuth(auth, session, callback) {
        if (auth.username !== "username" || auth.password !== "password") {
            return callback(new Error("Invalid username or password"));
        }
        callback(null, { user: 123 }); // where 123 is the user id or similar property
    },
    onMailFrom(address, session, callback) {
        if (address.address !== "from@example.com") {
            return callback(
                new Error("Only from@example.com is allowed to send mail")
            );
        }
        return callback(); // Accept the address
    },
    onRcptTo(address, session, callback) {
        if (address.address !== "to@example.com") {
            return callback(
                new Error("Only to@example.com is allowed to receive mail")
            );
        }
        return callback(); // Accept the address
    }
});



server.listen(4650);
