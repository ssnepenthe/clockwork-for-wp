/// <reference types="cypress" />

Cypress.Commands.add('cleanRequests', () => {
    cy.task('getTestContext', null, {log: false})
        .then(context => {
            cy.request({
                url: context.ajaxUrl,
                qs: {
                    action: 'cfwth_clean_requests'
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
                    action: 'cfwth_create_requests',
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
                    action: 'cfwth_request_by_id',
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
                    action: 'cfwth_request_by_id',
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
                    action: 'cfwth_set_config',
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
                    action: 'cfwth_reset_config',
                },
                log: false,
            });

            Cypress.log({
                name: 'resetConfig',
                message: '',
            });
        });
});
