/// <reference types="cypress" />

beforeEach(() => {
    cy.cleanMetadata();
});

after(() => {
    cy.cleanMetadata();
});

describe('API - Disabled Configuration', () => {
    it('Does not respond on any API routes', () => {
        cy.createRequests(3)
            .then(ids => {
                // Three requests exist, subsequent requests will be made with API disabled.

                // Auth route.
                cy.request({
                    url: '/__clockwork/auth',
                    method: 'POST',
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // ID extended route.
                cy.request({
                    url: `/__clockwork/${ids[1]}/extended`,
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // Latest extended route.
                cy.request({
                    url: '/__clockwork/latest/extended',
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // ID update route.
                cy.request({
                    url: `/__clockwork/${ids[1]}`,
                    method: 'PUT',
                    qs: {
                        enable: 0,
                        collect_client_metrics: 1,
                    },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // ID route.
                cy.request({
                    url: `/__clockwork/${ids[1]}`,
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // Latest route.
                cy.request({
                    url: '/__clockwork/latest',
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // Next route.
                cy.request({
                    url: `/__clockwork/${ids[1]}/next`,
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // Next with count route.
                cy.request({
                    url: `/__clockwork/${ids[1]}/next/1`,
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // Previous route.
                cy.request({
                    url: `/__clockwork/${ids[2]}/previous`,
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);

                // Previous with count route.
                cy.request({
                    url: `/__clockwork/${ids[2]}/previous/1`,
                    qs: { enable: 0 },
                    failOnStatusCode: false
                })
                    .its('status')
                    .should('equal', 404);
            });
    });
});
