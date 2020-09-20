import paths from '../paths';
import path from 'path';
import fs from 'fs';
import ini from 'ini';

// singleton cache in module memory so it doesn't need to be recreated betweem
// requests is a performance improvement.
let _instance = null;

/**
 * Provides access to the apps ini settings.
 *
 * @author Oliver Lillie
 */
class Config {

    /**
     * Creates a static instance of the Config reader.
     *
     * @return {*}
     */
    static instance() {
        if(_instance !== null) {
            return _instance;
        }

        _instance = ini.parse(
            fs.readFileSync(
                path.join(
                    paths.ETC,
                    'backend.ini'
                ),
                'utf-8'
            )
        );

        return _instance;
    }
}

module.exports = Config;