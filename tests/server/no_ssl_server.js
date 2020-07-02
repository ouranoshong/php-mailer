const SMTPServer = require("smtp-server").SMTPServer;
const fs = require('fs');

const server = new SMTPServer({
    secure: false,
    disabledCommands: ['STARTTLS'],
    onAuth(auth, session, callback) {
        if (auth.username !== "username" || auth.password !== "password") {
            return callback(new Error("Invalid username or password"));
        }
        callback(null, { user: 123 }); // where 123 is the user id or similar property
    }
});


server.listen(2500);
