import qs from 'qs';

// passing config as qs in visit results in request with query like ?requests=%5Bobject%20Object%5D
// @see https://github.com/cypress-io/cypress/issues/19407
const urlWithQuery = (url, query) => `${url}?${qs.stringify(query)}`;

const authUrl = '/__clockwork/auth';

const authUrlWithQuery = (query) => urlWithQuery(authUrl, query);

export {
    urlWithQuery,
    authUrl,
    authUrlWithQuery
};
