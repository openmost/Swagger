/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export type OpenApiSpec = Record<string, unknown>;

interface PropertySchema {
  type?: string|string[];
  example?: unknown;
  default?: unknown;
}

interface Operation {
  requestBody?: {
    content?: Record<string, { schema?: { properties?: Record<string, PropertySchema> } }>;
  };
}

const FORM_MEDIA_TYPE = 'application/x-www-form-urlencoded';

/**
 * Returns a copy of the specification for Swagger UI only. Swagger UI prefills every form field without
 * an example or a default with a sample value ("string", 0, the first enum value) and sends it with the
 * request, which breaks most API calls. An empty example keeps these fields empty, so only the values the
 * user fills in are sent. The downloaded specification is left untouched.
 */
export function withEmptyFormSamples(spec: OpenApiSpec): OpenApiSpec {
  const copy = JSON.parse(JSON.stringify(spec)) as OpenApiSpec;
  const paths = (copy.paths || {}) as Record<string, Record<string, Operation>>;

  Object.values(paths).forEach((pathItem) => {
    Object.values(pathItem).forEach((operation) => {
      const properties = operation?.requestBody?.content?.[FORM_MEDIA_TYPE]?.schema?.properties || {};

      Object.values(properties).forEach((property) => {
        if ('example' in property || 'default' in property) {
          return;
        }

        // Swagger UI parses the sample of array fields as JSON, an empty string would throw
        property.example = property.type === 'array' ? [] : '';
      });
    });
  });

  return copy;
}
