import createError from "http-errors";
import express from "express";
import cookieParser from "cookie-parser";
import logger from "morgan";
import path from "path";
import indexRouter from "./routes/index";
import packagesRouter from "./routes/packages";
import paths from "./paths";
import errorhandler from "errorhandler";
import cors from "cors";
import helmet from "helmet";
import JsonPayloadError from "./classes/JsonPayloadError";
import Config from "./classes/Config";

const app = express();

// whilst typically for a production app NODE_ENV would not be set by an ini
// setting, due to the fact the ini is shared between node and php this is a
// workaround to get both systems working of the same config setting.
process.env.NODE_ENV = Config.instance().environment;

const isProduction = process.env.NODE_ENV === 'production';
if (!isProduction) {
    app.use(errorhandler());
}

app.use(helmet());
app.use(cors());
app.use(logger('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(express.static(path.join(paths.ROOT, 'public')));

// force disable etas
app.disable('etag');
app.get(
    '/*',
    function(reqest, response, next){
        response.set({
            'Cache-Control': 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' : 'no-cache',
            'Expires': 'Tue, 25 Mar 2003 05:00:00 GMT',
            'Last-Modified': (new Date()).toUTCString()
        });
        next();
    }
);

app.use('/', indexRouter);
app.use('/packages', packagesRouter);

app.use('/assets/', express.static(path.join(paths.ETC, 'vue/dist/assets')));

/// catch 404 and forward to error handler
app.use(
    function(request, response, next) {
        const err = new Error('Not Found');
        err.status = 404;
        next(err);
    }
);

// development error handler
// will print stacktrace
if (!isProduction) {
    app.use(
        function(error, request, response, next) {
            console.log(error);
            console.log(error.stack);

            const payload = new JsonPayloadError(error.message, 'trace');
            payload.meta = {
                status: error.status,
                stack: error.stack.split('\n')
            };

            response.status(error.status || 500);
            response.json(
                payload.toObject()
            );
        }
    );
}

// production error handler
// no stacktraces leaked to user
app.use(
    function(error, request, response, next) {
        const payload = new JsonPayloadError(error.message, 'trace');
        payload.meta = {
            error
        };

        response.status(error.status || 500);
        response.json(
            payload.toObject()
        );
    }
);

console.log('App loaded in ' + process.env.NODE_ENV.toUpperCase() + ' mode.');

module.exports = app;
