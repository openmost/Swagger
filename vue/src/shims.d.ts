/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare module 'swagger-ui-dist/swagger-ui-es-bundle.js' {
  const SwaggerUIBundle: (options: Record<string, unknown>) => unknown;
  export default SwaggerUIBundle;
}

declare module '*.css?inline' {
  const css: string;
  export default css;
}
