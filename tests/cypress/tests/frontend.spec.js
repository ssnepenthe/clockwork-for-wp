/// <reference types="cypress" />

const base64url = require('base64url');
const qs = require('qs');

describe('Frontend', () => {

    context('Default Configuration', () => {

        it('Adds clockwork ID header to responses', () => {

            cy.request('/')
                .its('headers.x-clockwork-id')
                .should('match', /^\d{10,}-\d{4}-\d+$/);

        });

        it.skip('Adds clockwork version header to responses', () => {

            // @todo store version in ctx along with ajaxurl?

        });

        it('Stores request data', () => {

            cy.request('/')
                .its('headers.x-clockwork-id')
                .then(id => {
                    cy.hasRequest(id)
                        .should('be.true');
                });

        });

        it('Is disabled by default for clockwork API requests', () => {

            cy.request('/__clockwork/app')
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it.skip('Is disabled by default for OPTIONS requests', () => {

            // @todo NGINX on VVV is currently blocking OPTIONS requests.
            cy.request({
                url: '/',
                method: 'OPTIONS',
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

    });

    context('Additional Headers', () => {

        const config = {
            requests: {
                except: [base64url('sample-page')],
            },
            headers: {
                Apples: 'Bananas',
                Cats: 'Dogs',
            },
        };

        it('Sends additional headers', () => {

            cy.request({
                url: '/',
                qs: config,
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.have.property('x-clockwork-header-apples');
                    expect(headers['x-clockwork-header-apples']).to.equal('Bananas');
                    expect(headers).to.have.property('x-clockwork-header-cats');
                    expect(headers['x-clockwork-header-cats']).to.equal('Dogs');
                });

        });

        it('Does not send additional headers on filtered requests', () => {

            cy.request({
                url: '/sample-page/',
                qs: config,
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-header-apples');
                    expect(headers).to.not.have.property('x-clockwork-header-cats');
                });

        });

        it('Does not send additional headers when clockwork is disabled', () => {

            cy.request({
                url: '/',
                qs: {enable: 0, ...config},
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-header-apples');
                    expect(headers).to.not.have.property('x-clockwork-header-cats');
                });

        });

    });

    context('Collecting Data Always, Clockwork Disabled', () => {

        const config = {
            enable: 0,
            collect_data_always: 1,
        };

        it('Does not send clockwork headers', () => {

            cy.request({
                url: '/',
                qs: config,
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it('Continues to store request data', () => {

            cy.visit({
                url: '/',
                qs: config,
            })
                .get('[data-cy="request-id"]')
                .invoke('text')
                .then(id => {
                    // File exists.
                    cy.hasRequest(id)
                        .should('be.true');

                    // Not available when disabled but collecting data always.
                    cy.request({
                        url: `/__clockwork/${id}`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);

                    // Available again when re-enabled.
                    cy.request(`/__clockwork/${id}`)
                        .its('body.id')
                        .should('equal', id);

                });

        });

    });

    context('Clockwork Disabled', () => {

        const config = {enable: 0};

        it('Does not send clockwork headers', () => {

            cy.request({
                url: '/',
                qs: config,
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it('Does not store request data', () => {

            cy.visit({
                url: '/',
                qs: config,
            })
                .get('[data-cy="request-id"]')
                .invoke('text')
                .then(id => {
                    // File does not exist.
                    cy.hasRequest(id)
                        .should('be.false');

                });

        });

    });

    context.skip('Except Preflight Requests Disabled', () => {

        const config = {
            requests: {
                except_preflight: false,
            }
        }

        it('Sends clockwork headers for eligible OPTIONS requests', () => {

            // @todo Nginx on VVV seems to be blocking OPTIONS request completely.

        });

    });

    context('Filtered URIs', () => {

        const config = {
            requests: {
                except: [base64url('sample-page')],
            }
        };

        it('Does not send clockwork headers for filtered URIs', () => {

            cy.request({
                url: '/',
                qs: config,
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.have.property('x-clockwork-id');
                    expect(headers).to.have.property('x-clockwork-version');
                });

            cy.request({
                url: '/sample-page/',
                qs: config,
            })
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it('Does not store request data for filtered URIs', () => {

            // passing config as qs in visit results in request to
            // http://one.wordpress.test/sample-page/?requests=%5Bobject%20Object%5D
            // @see https://github.com/cypress-io/cypress/issues/19407
            cy.visit('/sample-page/?' + qs.stringify(config))
                .get('[data-cy="request-id"]')
                .invoke('text')
                .then(id => {
                    // File does not exist.
                    cy.hasRequest(id)
                        .should('be.false');

                });

        });

    });

});
