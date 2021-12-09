/// <reference types="cypress" />

beforeEach(() => {
    cy.cleanMetadata();
});

after(() => {
    cy.cleanMetadata();
});

describe('API - Default Configuration', () => {
    it('Does not require authentication', () => {
        cy.createRequests(1)
            .then(ids => {
                cy.request(`/__clockwork/${ids[0]}`)
                .its('status')
                .should('equal', 200);
            })
    });

    it('Return true auth token', () => {
        cy.request('POST', '/__clockwork/auth')
            .should(response => {
                expect(response.status).to.equal(200);
                expect(response.body.token).to.be.true;
            });
    });

    it('Serves id requests', () => {
        cy.createRequests(1)
            .then(ids => {
                cy.request(`/__clockwork/${ids[0]}`)
                    .should(response => {
                        expect(response.status).to.equal(200);
                        expect(response.body.id).to.equal(ids[0]);
                    });
            });
    });

    it('Serves "latest" requests', () => {
        cy.createRequests(2)
            .then(ids => {
                cy.request('/__clockwork/latest')
                    .should(response => {
                        expect(response.status).to.equal(200);
                        expect(response.body.id).to.equal(ids[1]);
                    });
            });
    });

    it('Serves "next" requests', () => {
        // @todo There seems to be a flaw in the Clockwork FileStorage next and previous logic...
		// 		 We are getting one fewer response than expected.
		// 		 For now we compensate by making this third request.
		// 		 Possibly related to https://github.com/itsgoingd/clockwork/issues/510.
        cy.createRequests(3)
            .then(ids => {
                cy.request(`/__clockwork/${ids[1]}/next`)
                    .should(response => {
                        expect(response.status).to.equal(200);
                        expect(response.body[0].id).to.equal(ids[2]);
                    });
            });
    });

    it('Serves "previous" requests', () => {
        // @todo There seems to be a flaw in the Clockwork FileStorage next and previous logic...
		// 		 We are getting one fewer response than expected.
		// 		 For now we compensate by making this third request.
		// 		 Possibly related to https://github.com/itsgoingd/clockwork/issues/510.
        cy.createRequests(3)
            .then(ids => {
                cy.request(`/__clockwork/${ids[2]}/previous`)
                    .should(response => {
                        expect(response.status).to.equal(200);
                        expect(response.body[0].id).to.equal(ids[0]);
                        expect(response.body[1].id).to.equal(ids[1]);
                    });
            });
    });

    it('Handles "count" argument', () => {
        cy.createRequests(5)
            .then(ids => {
                cy.request(`/__clockwork/${ids[2]}/next`)
                    .its('body')
                    .should('have.length', 2);

                cy.request(`/__clockwork/${ids[2]}/next/1`)
                    .its('body')
                    .should('have.length', 1);

                cy.request(`/__clockwork/${ids[2]}/previous`)
                    .its('body')
                    .should('have.length', 2);

                cy.request(`/__clockwork/${ids[2]}/previous/1`)
                    .its('body')
                    .should('have.length', 1);
            });
    });

    it('Handles "except" filter', () => {
        cy.createRequests(2)
            .then(ids => {
                // Single endpoint.
                cy.request(`/__clockwork/${ids[1]}`)
                    .then(response => {
                        expect(response.body).to.have.property('id');
                        expect(response.body).to.have.property('version');
                    });

                cy.request(`/__clockwork/${ids[1]}?except=id`)
                    .then(response => {
                        expect(response.body).to.not.have.property('id');
                        expect(response.body).to.have.property('version');
                    });

                cy.request(`/__clockwork/${ids[1]}?except=id,version`)
                    .then(response => {
                        expect(response.body).to.not.have.property('id');
                        expect(response.body).to.not.have.property('version');
                    });

                // List endpoint.
                cy.request(`/__clockwork/${ids[1]}/previous`)
                    .its('body.0')
                    .should('have.property', 'id');

                cy.request(`/__clockwork/${ids[1]}/previous?except=id`)
                    .its('body.0')
                    .should('not.have.a.property', 'id');
            });
    });

    it('Handles "only" filter', () => {
        cy.createRequests(2)
            .then(ids => {
                // Single endpoint.
                cy.request(`/__clockwork/${ids[1]}`)
                    .then(response => {
                        expect(response.body).to.have.property('id');
                        expect(response.body).to.have.property('version');
                        expect(response.body).to.have.property('type');
                    });

                cy.request(`/__clockwork/${ids[1]}?only=id`)
                    .then(response => {
                        expect(response.body).to.have.property('id');
                        expect(response.body).to.not.have.property('version');
                        expect(response.body).to.not.have.property('type');
                    });

                cy.request(`/__clockwork/${ids[1]}?only=id,version`)
                    .then(response => {
                        expect(response.body).to.have.property('id');
                        expect(response.body).to.have.property('version');
                        expect(response.body).to.not.have.property('type');
                    });

                // List endpoint.
                cy.request(`/__clockwork/${ids[1]}/previous`)
                    .its('body.0')
                    .should('have.property', 'id');

                cy.request(`/__clockwork/${ids[1]}/previous?only=version`)
                    .its('body.0')
                    .should('not.have.a.property', 'id');

                // Only takes precedence over except.
                cy.request(`/__clockwork/${ids[1]}?only=id&except=id`)
                    .its('body')
                    .should('have.property', 'id');
            });
    });

    it.skip('Extends requests', () => {
    });
});
