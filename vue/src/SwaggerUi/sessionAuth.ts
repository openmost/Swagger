/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface SwaggerRequest {
  url: string;
  method?: string;
  headers?: Record<string, string>;
  body?: unknown;
}

function hasAuthorizationHeader(headers: Record<string, string>): boolean {
  return Object.keys(headers).some((name) => name.toLowerCase() === 'authorization');
}

/**
 * Authenticates a "Try it out" request with the session of the logged in user, the same way the
 * Matomo UI calls the API. A token entered with the Authorize button always takes precedence.
 */
export function addSessionAuth(request: SwaggerRequest, tokenAuth: string): SwaggerRequest {
  const headers = request.headers || {};
  if (!tokenAuth || hasAuthorizationHeader(headers)) {
    return request;
  }

  const sessionParams: Record<string, string> = {
    token_auth: tokenAuth,
    force_api_session: '1',
  };

  if (typeof request.body === 'string') {
    const params = new URLSearchParams(request.body);
    Object.entries(sessionParams).forEach(([name, value]) => params.set(name, value));
    request.body = params.toString();
  } else if (request.body instanceof FormData) {
    Object.entries(sessionParams).forEach(([name, value]) => (request.body as FormData).set(name, value));
  } else if (request.body instanceof URLSearchParams) {
    Object.entries(sessionParams).forEach(([name, value]) => (request.body as URLSearchParams).set(name, value));
  } else {
    request.body = new URLSearchParams(sessionParams).toString();
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
  }

  request.headers = headers;

  return request;
}
