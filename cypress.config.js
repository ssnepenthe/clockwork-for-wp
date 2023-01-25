const { defineConfig } = require('cypress')

module.exports = defineConfig({
  downloadsFolder: 'tests/cypress/downloads',
  fixturesFolder: 'tests/cypress/fixtures',
  screenshotsFolder: 'tests/cypress/screenshots',
  videosFolder: 'tests/cypress/videos',
  e2e: {
    async setupNodeEvents(on, config) {
      let baseUrl = 'http://localhost:8889/';

      const { readConfig } = require('@wordpress/env/lib/config');
      const wpEnvConfig = await readConfig('.wp-env.json');

      if (wpEnvConfig) {
        baseUrl = wpEnvConfig.env.tests.config.WP_SITEURL;
      }

      config.baseUrl = baseUrl;

      require('./tests/cypress/plugins/index.js')(on, config);

      return config;
    },
    specPattern: 'tests/cypress/tests/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'tests/cypress/support/index.js',
  },
})
