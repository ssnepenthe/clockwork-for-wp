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
Cypress.Commands.add('cleanRequests', () => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfw_coh_clean_metadata'
                },
                log: false,
            });

            Cypress.log({
                name: 'cleanRequests',
                message: '',
            });
        });
});

Cypress.Commands.add('createRequests', (qty = 1) => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfw_coh_request_factory',
                    qty,
                    log: false
                }
            })
            .its('body.data');
        });
});

Cypress.Commands.add('getRequestById', id => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfw_coh_metadata_by_id',
                    id,
                    log: false,
                }
            })
            .its('body.data');
        });
});

Cypress.Commands.add('hasRequest', id => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfw_coh_metadata_by_id',
                    id,
                    log: false,
                }
            })
                .its('body.success');
        });
});

Cypress.Commands.add('setConfig', config => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfw_coh_set_config',
                    config,
                },
                log: false,
            });

            Cypress.log({
                name: 'setConfig',
                message: '',
                consoleProps: () => ({config}),
            });
        });
});

Cypress.Commands.add('resetConfig', () => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfw_coh_reset_config',
                },
                log: false,
            });

            Cypress.log({
                name: 'resetConfig',
                message: '',
            });
        });
});
