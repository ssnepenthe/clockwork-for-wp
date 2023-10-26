/// <reference types="cypress" />

import base64url from 'base64url';
import { urlWithQuery } from '../support/utils';

describe('Frontend', () => {

    context('Default Configuration', () => {

        it('Adds clockwork ID header to responses', () => {

            cy.request('/')
                .its('headers.x-clockwork-id')
                .should('match', /^\d{10,}-\d{4}-\d+$/);

        });

        it('Adds clockwork version header to responses', () => {

            cy.task('getTestContext')
                .then(context => {
                    cy.request('/')
                        .its('headers.x-clockwork-version')
                        .should('equal', context.clockworkVersion);
                });

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

            cy.request(urlWithQuery('/', config))
                .its('headers')
                .should(headers => {
                    expect(headers).to.have.property('x-clockwork-header-apples');
                    expect(headers['x-clockwork-header-apples']).to.equal('Bananas');
                    expect(headers).to.have.property('x-clockwork-header-cats');
                    expect(headers['x-clockwork-header-cats']).to.equal('Dogs');
                });

        });

        it('Does not send additional headers on filtered requests', () => {

            cy.request(urlWithQuery('/sample-page/', config))
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-header-apples');
                    expect(headers).to.not.have.property('x-clockwork-header-cats');
                });

        });

        it('Does not send additional headers when clockwork is disabled', () => {

            cy.request(urlWithQuery('/', {enable: 0, ...config}))
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

            cy.request(urlWithQuery('/', config))
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it('Continues to store request data', () => {

            cy.visit(urlWithQuery('/', config))
                .get('[data-cy="request-id"]')
                .invoke('text')
                .then(id => {
                    // File exists.
                    cy.hasRequest(id)
                        .should('be.true');

                    // Not available when disabled but collecting data always.
                    cy.request({
                        url: urlWithQuery(`/__clockwork/${id}`, config),
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

            cy.request(urlWithQuery('/', config))
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it('Does not store request data', () => {

            cy.visit(urlWithQuery('/', config))
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

            cy.request(urlWithQuery('/', config))
                .its('headers')
                .should(headers => {
                    expect(headers).to.have.property('x-clockwork-id');
                    expect(headers).to.have.property('x-clockwork-version');
                });

            cy.request(urlWithQuery('/sample-page/', config))
                .its('headers')
                .should(headers => {
                    expect(headers).to.not.have.property('x-clockwork-id');
                    expect(headers).to.not.have.property('x-clockwork-version');
                });

        });

        it('Does not store request data for filtered URIs', () => {

            cy.visit(urlWithQuery('/sample-page/', config))
                .get('[data-cy="request-id"]')
                .invoke('text')
                .then(id => {
                    // File does not exist.
                    cy.hasRequest(id)
                        .should('be.false');

                });

        });

    });

    context('Toolbar', () => {

        const config = { toolbar: true };

        it('Does not show the toolbar by default', () => {

            cy.visit('/');

            cy.wait(500);

            cy.get('.clockwork-toolbar')
                .should('not.exist');

        });

        it('Shows the toolbar on the site front end', () => {

            cy.visit(urlWithQuery('/', config))
                .get('.clockwork-toolbar');

        });

        it('Shows the toolbar on wp-login', () => {

            cy.visit(urlWithQuery('/wp-login.php', config))
                .get('.clockwork-toolbar');

        });

        it('Shows the toolbar on wp-admin', () => {

            cy.visit('/wp-login.php');

            // @todo Without wait this is flaky - Need to investigate further.
            // Sometimes it will only type a portion of the username like "min" or just "n".
            cy.wait(500);

            cy.get('#user_login').type('admin');
            cy.get('#user_pass').type('password');
            cy.get('#wp-submit').click();

            cy.visit(urlWithQuery('/wp-admin/', config))
                .get('.clockwork-toolbar');

        });

    });

});
