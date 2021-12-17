/// <reference types="cypress" />

import './commands'

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
