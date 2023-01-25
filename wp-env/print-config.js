#!/usr/bin/env node

(async () => {
    const {readConfig} = require('@wordpress/env/lib/config');
    const wpEnvConfig = await readConfig('.wp-env.json');

    console.log(JSON.stringify(wpEnvConfig));
})();
