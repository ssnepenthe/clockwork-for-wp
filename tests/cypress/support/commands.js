/// <reference types="cypress" />
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
Cypress.Commands.add('cleanMetadata', () => {
    cy.task('getAjaxUrl')
        .then(url => {
            cy.request({
                url,
                qs: {
                    action: 'cfw_coh_clean_metadata'
                }
            })
        });
});

Cypress.Commands.add('createRequests', (qty = 1) => {
    cy.task('getAjaxUrl')
        .then(url => {
            cy.request({
                url,
                qs: {
                    action: 'cfw_coh_request_factory',
                    qty
                }
            })
            .its('body.data');
        });
});
