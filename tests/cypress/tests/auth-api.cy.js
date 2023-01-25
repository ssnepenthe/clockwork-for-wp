/// <reference types="cypress" />

import { authUrl, authUrlWithQuery } from '../support/utils';

describe('Auth API (POST /__clockwork/auth)', () => {

    context('Null auth driver (default configuration)', () => {

        it('Always returns true auth token', () => {

            cy.request('POST', authUrl)
                .should(response => {
                    expect(response.status).to.equal(200);
                    expect(response.body.token).to.be.true;
                });

        });

    });

    context('Simple auth driver', () => {

        const password = 'nothing-to-see-here-folks';
        const config = {
            authentication: {
                enabled: 1,
                drivers: {
                    simple: { password },
                },
            },
        };

        it('Handles form requests', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                body: { password },
                form: true,
            })
                .its('status')
                .should('equal', 200);

        });

        it('Handles form requests with incorrect credentials', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                body: { password: 'wrong-password' },
                form: true,
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 403);

        });

        it('Handles form requests with no credentials', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                form: true,
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 403);

        });

        it('Handles JSON requests', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                body: { password },
            })
                .its('status')
                .should('equal', 200);

        });

        it('Handles JSON requests with incorrect credentials', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                body: { password: 'wrong-password' },
                failOnStatusCode: false
            })
                .its('status')
                .should('equal', 403);

        });

        it('Handles JSON requests with no credentials', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 403);

        });

        it('Handles JSON requests with invalid JSON body', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                body: '{"password":"nothing-to-see-here-folks"}}',
                headers: {
                    'content-type': 'application/json',
                },
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 403);

        });

    });

    context('Clockwork disabled', () => {

        const config = {
            enable: 0,
        };

        it('Returns 404 response', () => {

            cy.request({
                url: authUrlWithQuery(config),
                method: 'POST',
                failOnStatusCode: false
            })
                .its('status')
                .should('equal', 404);

        });

    });

});
