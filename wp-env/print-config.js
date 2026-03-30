#!/usr/bin/env node

(async () => {
    const path = require('path');

    const {loadConfig} = require('@wordpress/env/lib/config');
    const wpEnvConfig = await loadConfig(path.resolve(__dirname, '..'));

    console.log(JSON.stringify(wpEnvConfig));
})();
