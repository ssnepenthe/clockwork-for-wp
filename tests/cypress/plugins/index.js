/// <reference types="cypress" />

let testContext;

/**
 * @type {Cypress.PluginConfig}
 */
// eslint-disable-next-line no-unused-vars
module.exports = (on, config) => {

    on('task', {

      getTestContext() {
        return testContext;
      },

      setTestContext(context) {
        testContext = context;

        return null;
      },

  });

}
