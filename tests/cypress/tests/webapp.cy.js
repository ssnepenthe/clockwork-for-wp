/// <reference types="cypress" />

import { urlWithQuery } from '../support/utils';

// @todo Differentiate between installed web app and web app served by WP.
describe('Clockwork Webapp', () => {

    context('Default configuration', () => {

        it('Serves index', () => {

            cy.visit('/__clockwork/app')
                .contains('Performance');

        });

        it('Serves images', () => {

            cy.request('/__clockwork/img/icons/apple-touch-icon-60x60.png')
                .should(response => {
                    expect(response.status).to.equal(200);
                    expect(response.headers['content-type']).to.equal('image/png');
                });

        });

        it('Serves scripts', () => {

            cy.visit('/__clockwork/app')
                .get('script[src^="js/app"]')
                .invoke('attr', 'src')
                .as('scriptPath');

            cy.get('@scriptPath')
                .then(path => {
                    cy.request(`/__clockwork/${path}`)
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.headers['content-type']).to.contain('application/javascript');
                        });
                });

        });

        it('Serves styles', () => {

            cy.visit('/__clockwork/app')
                .get('link[rel="stylesheet"][href^="css/app"]')
                .invoke('attr', 'href')
                .as('stylePath');

            cy.get('@stylePath')
                .then(path => {
                    cy.request(`/__clockwork/${path}`)
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.headers['content-type']).to.contain('text/css');
                        });
                });

        });

        it('Provides a shortcut redirect', () => {

            cy.visit('/__clockwork')
                .url()
                .should('match', /__clockwork\/app$/);

        });

        it('Prevents trailing-slash redirects', () => {

            cy.visit('/__clockwork/app')
                .url()
                .should('match', /__clockwork\/app$/);

            cy.visit('/__clockwork/index.html')
                .url()
                .should('match', /__clockwork\/index\.html$/);

        });

        it('Returns 404 response for invalid files', () => {

            cy.request({
                url: '/__clockwork/nope.html',
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

            cy.request({
                url: '/__clockwork/nope.json',
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

            cy.request({
                url: '/__clockwork/css/nope.css',
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

            cy.request({
                url: '/__clockwork/img/nope.png',
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

            cy.request({
                url: '/__clockwork/js/nope.js',
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

    });

    context('Auth required (simple auth driver)', () => {

        const password = 'nothing-to-see-here-folks';
        const config ={
            authentication: {
                enabled: 1,
                drivers: {
                    simple: { password },
                },
            },
        };

        before(() => {
            cy.setConfig(config);
        });

        after(() => {
            cy.resetConfig();
        });

        it('Is locked by default', () => {

            cy.visit('/__clockwork/app');

            cy.get('input[type="password"]')
                .should('be.visible');

        });

        it('Stays locked with incorrect credentials', () => {

            cy.intercept('/__clockwork/auth').as('auth');

            cy.visit('/__clockwork/app');

            cy.get('.application > .modal-backdrop a.header-close').click();

            cy.get('input[type="password"]')
                .type('wrong-password{enter}');

            cy.wait('@auth');

            cy.contains('Authentication failed.')
                .should('be.visible');

        });

        it('Unlocks with correct credentials', () => {

            cy.intercept('/__clockwork/auth').as('auth');

            cy.visit('/__clockwork/app');

            cy.get('.application > .modal-backdrop a.header-close').click();

            cy.get('input[type="password"]')
                .type(`${password}{enter}`);

            cy.wait('@auth');

            cy.get('input[type="password"]')
                .should('not.be.visible');

        });

    });

    context('Web disabled', () => {

        const config = { web: 0 };

        it('Returns 404 response for app requests', () => {

            cy.request({
                url: urlWithQuery('/__clockwork/app', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

        it('Returns 404 for asset requests', () => {

            cy.request({
                url: urlWithQuery('/__clockwork/manifest.json', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

            cy.request({
                url: urlWithQuery('/__clockwork/img/icons/apple-touch-icon-60x60.png', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

        it('Does not provide shortcut redirect', () => {

            cy.request({
                url: urlWithQuery('/__clockwork', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

        it.skip('Does not prevent trailing-slash redirects', () => {

            // @todo Is this even really testable?

        });

    });

    context('Clockwork disabled', () => {

        const config = { enable: 0 };

        it('Returns 404 response for app requests', () => {

            cy.request({
                url: urlWithQuery('/__clockwork/app', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

        it('Returns 404 for asset requests', () => {

            cy.request({
                url: urlWithQuery('/__clockwork/manifest.json', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

            cy.request({
                url: urlWithQuery('/__clockwork/img/icons/apple-touch-icon-60x60.png', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

        it('Does not provide shortcut redirect', () => {

            cy.request({
                url: urlWithQuery('/__clockwork', config),
                failOnStatusCode: false,
            })
                .its('status')
                .should('equal', 404);

        });

        it.skip('Does not prevent trailing-slash redirects', () => {

            // @todo Is this even really testable?

        });

    });

});
