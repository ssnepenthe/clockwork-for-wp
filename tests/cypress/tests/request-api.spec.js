/// <reference types="cypress" />

describe('Request API', () => {

    it('Does not require authentication by default', () => {

        cy.createRequests(1)
            .then(([request]) => {
                cy.request(`/__clockwork/${request.id}`)
                    .its('status')
                    .should('equal', 200);
            });

    });

    context('Request by ID (GET /__clockwork/{id})', () => {

        it('Serves id requests', () => {

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request(`/__clockwork/${request.id}`)
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body.id).to.equal(request.id);
                        });
                });

        });

        it('Serves "latest" requests', () => {

            cy.createRequests(2)
                .then(([one, two]) => {
                    cy.request('/__clockwork/latest')
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body.id).to.equal(two.id);
                        });
                });

        });

    });

    context.skip('Extended request by ID (GET /__clockwork/{id}/extended)', () => {

        // @todo Custom data source for testing request extension.

    });

    context('Requests by ID with direction (GET /__clockwork/{id}/{direction}[/{count}])', () => {

        it('Serves "next" requests', () => {

            // @todo There seems to be a flaw in the Clockwork FileStorage->next() logic...
            //       For now we compensate by making three requests and ignoring the first.
            //       Need to revisit after the release of v6.0.
            // 		 https://github.com/itsgoingd/clockwork/issues/511.
            cy.createRequests(3)
                .then(([one, two, three]) => {
                    cy.request(`/__clockwork/${two.id}/next`)
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body[0].id).to.equal(three.id);
                        });
                });

        });

        it('Serves "previous" requests', () => {

            cy.createRequests(2)
                .then(([one, two]) => {
                    cy.request(`/__clockwork/${two.id}/previous`)
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body[0].id).to.equal(one.id);
                        });
                });

        });

        it('Handles "count" argument', () => {

            // @todo Should we test that the default returns up to 10 results?
            cy.createRequests(5)
                .then(([one, two, three, four, five]) => {
                    cy.request(`/__clockwork/${three.id}/next`)
                        .its('body')
                        .should('have.length', 2);

                    cy.request(`/__clockwork/${three.id}/next/1`)
                        .its('body')
                        .should('have.length', 1);

                    cy.request(`/__clockwork/${three.id}/previous`)
                        .its('body')
                        .should('have.length', 2);

                    cy.request(`/__clockwork/${three.id}/previous/1`)
                        .its('body')
                        .should('have.length', 1);
                });

        });
    });

    context('Update request (PUT /__clockwork/{id})', () => {

        const config = { collect_client_metrics: 1 };

        it('Returns 404 response for invalid request ID', () => {

                const fakeId = '123-456-789';

                cy.request({
                    url: `/__clockwork/${fakeId}`,
                    method: 'PUT',
                    qs: config,
                    failOnStatusCode: false,
                })
                    .its('status')
                    .should('equal', 404);

        });

        it('Returns 403 response for invalid update token', () => {

            const fakeToken = 'abc123';

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request({
                        url: `/__clockwork/${request.id}`,
                        method: 'PUT',
                        qs: config,
                        body: { _token: fakeToken },
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 403);
                });

        });

        it('Allows "clientMetrics" and "webVitals" data to be updated', () => {

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request(`/__clockwork/${request.id}`)
                        .should(response => {
                            expect(response.body.clientMetrics).to.be.an('array').that.is.empty;
                            expect(response.body.webVitals).to.be.an('array').that.is.empty;
                        });

                    cy.request({
                        url: `/__clockwork/${request.id}`,
                        method: 'PUT',
                        qs: config,
                        body: {
                            _token: request.updateToken,
                            clientMetrics: { connection: 5, waiting: 10 },
                            webVitals: { lcp: 5, fcp: 10 },
                        },
                    });

                    cy.request(`/__clockwork/${request.id}`)
                        .should(response => {
                            expect(response.body.clientMetrics).to.be.an('object').with.keys('connection', 'waiting');
                            expect(response.body.webVitals).to.be.an('object').with.keys('lcp', 'fcp');
                        });
                });

        });

        it('Only allows "clientMetrics" and "webVitals" data to be updated', () => {

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request(`/__clockwork/${request.id}`)
                        .its('body.url')
                        .should('be.null');

                    cy.request({
                        url: `/__clockwork/${request.id}`,
                        method: 'PUT',
                        qs: config,
                        body: {
                            _token: request.updateToken,
                            url: 'https://www.example.com'
                        },
                    });

                    cy.request(`/__clockwork/${request.id}`)
                        .its('body.url')
                        .should('be.null');
                });

        });

    });

    context('Filters on singular endpoint', () => {

        it('Handles "except" filter', () => {

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request(`/__clockwork/${request.id}`)
                        .then(response => {
                            expect(response.body).to.have.property('id');
                            expect(response.body).to.have.property('version');
                        });

                    cy.request(`/__clockwork/${request.id}?except=id`)
                        .then(response => {
                            expect(response.body).to.not.have.property('id');
                            expect(response.body).to.have.property('version');
                        });

                    cy.request(`/__clockwork/${request.id}?except=id,version`)
                        .then(response => {
                            expect(response.body).to.not.have.property('id');
                            expect(response.body).to.not.have.property('version');
                        });
                });

        });

        it('Handles "only" filter', () => {

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request(`/__clockwork/${request.id}`)
                        .then(response => {
                            expect(response.body).to.have.property('id');
                            expect(response.body).to.have.property('version');
                            expect(response.body).to.have.property('type');
                        });

                    cy.request(`/__clockwork/${request.id}?only=id`)
                        .then(response => {
                            expect(response.body).to.have.property('id');
                            expect(response.body).to.not.have.property('version');
                            expect(response.body).to.not.have.property('type');
                        });

                    cy.request(`/__clockwork/${request.id}?only=id,version`)
                        .then(response => {
                            expect(response.body).to.have.property('id');
                            expect(response.body).to.have.property('version');
                            expect(response.body).to.not.have.property('type');
                        });
                });

        });

        it('Favors "only" filter over "except" filter', () => {

            cy.createRequests(1)
                .then(([request]) => {
                    cy.request(`/__clockwork/${request.id}?only=id&except=id`)
                        .its('body')
                        .should('have.property', 'id');
                });

        });

    });

    context('Filters on list endpoint', () => {

        it('Handles "except" filter', () => {

            cy.createRequests(2)
                .then(([one, two]) => {
                    cy.request(`/__clockwork/${two.id}/previous`)
                        .its('body.0')
                        .should('have.property', 'id');

                    cy.request(`/__clockwork/${two.id}/previous?except=id`)
                        .its('body.0')
                        .should('not.have.a.property', 'id');
                });

        });

        it('Handles "only" filter', () => {
            cy.createRequests(2)
                .then(([one, two]) => {
                    cy.request(`/__clockwork/${two.id}/previous`)
                        .its('body.0')
                        .should('have.property', 'id');

                    cy.request(`/__clockwork/${two.id}/previous?only=version`)
                        .its('body.0')
                        .should('not.have.a.property', 'id');
                });
        });

    });

    context('Auth required (simple auth driver)', () => {

        const password = 'nothing-to-see-here-folks';
        const config = {
            authentication: {
                enabled: 1,
                drivers: {
                    simple: {
                        config: { password },
                    },
                },
            },
        };

        it('Returns 403 response when not authenticated', () => {

            cy.createRequests(2)
                .then(([one, two]) => {
                    cy.request({
                        url: `/__clockwork/${two.id}`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 403);

                    cy.request({
                        url: '/__clockwork/latest',
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 403);

                    cy.request({
                        url: `/__clockwork/${two.id}/extended`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 403);

                    cy.request({
                        url: `/__clockwork/${two.id}/previous`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 403);

                    cy.request({
                        url: `/__clockwork/${two.id}/previous/1`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 403);
                });

        });

        it('Serves all API requests when authenticated', () => {

            cy.request({
                url: '/__clockwork/auth',
                method: 'POST',
                qs: config,
                body: { password }
            })
                .its('body.token')
                .as('token');

            cy.createRequests(2)
                .then(function([one, two]) {
                    cy.request({
                        url: `/__clockwork/${two.id}`,
                        qs: config,
                        headers: { 'x-clockwork-auth': this.token },
                    })
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body.id).to.equal(two.id);
                        });

                    cy.request({
                        url: '/__clockwork/latest',
                        qs: config,
                        headers: { 'x-clockwork-auth': this.token },
                    })
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body.id).to.equal(two.id);
                        });

                    cy.request({
                        url: `/__clockwork/${two.id}/extended`,
                        qs: config,
                        headers: { 'x-clockwork-auth': this.token },
                    })
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body.id).to.equal(two.id);
                        });

                    cy.request({
                        url: `/__clockwork/${two.id}/previous`,
                        qs: config,
                        headers: { 'x-clockwork-auth': this.token },
                    })
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body[0].id).to.equal(one.id);
                        });

                    cy.request({
                        url: `/__clockwork/${two.id}/previous/1`,
                        qs: config,
                        headers: { 'x-clockwork-auth': this.token },
                    })
                        .should(response => {
                            expect(response.status).to.equal(200);
                            expect(response.body[0].id).to.equal(one.id);
                        });
                });

        });

    });

    context('Clockwork disabled', () => {

        const config = { enable: 0 };

        it('Returns 404 response', () => {

            cy.createRequests(2)
                .then(([one, two]) => {
                    cy.request({
                        url: `/__clockwork/${two.id}`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);

                    cy.request({
                        url: '/__clockwork/latest',
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);

                    cy.request({
                        url: `/__clockwork/${two.id}/extended`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);

                    cy.request({
                        url: `/__clockwork/${two.id}/previous`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);

                    cy.request({
                        url: `/__clockwork/${two.id}/previous/1`,
                        qs: config,
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);

                    cy.request({
                        url: `/__clockwork/${two.id}`,
                        method: 'PUT',
                        qs: { collect_client_metrics: 1, ...config },
                        failOnStatusCode: false,
                    })
                        .its('status')
                        .should('equal', 404);
                });

        });

    });

});
