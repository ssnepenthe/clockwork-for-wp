/// <reference types="cypress" />
// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Alternatively you can use CommonJS syntax:
// require('./commands')

before(() => {

    cy.visit({
        url: '/?enable=0',
        log: false,
    })
    .get('[data-cy="test-context"]', {log: false})
    .invoke({log: false}, 'text')
    .then(context => {
        let parsedContext = JSON.parse(context);

        cy.task('setTestContext', parsedContext, {log: false})

        Cypress.log({
            name: 'setTestContext',
            message: '',
            consoleProps: () => ({context: parsedContext}),
        });
    });

    cy.resetConfig();

});

beforeEach(() => cy.cleanRequests());

after(() => {

    cy.resetConfig();
    cy.cleanRequests()

});
