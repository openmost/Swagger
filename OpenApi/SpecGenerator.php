<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Swagger\OpenApi;

use Piwik\API\DocumentationGenerator;
use Piwik\API\NoDefaultValue;
use Piwik\API\Proxy;
use Piwik\Common;
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\Version;

/**
 * Builds the OpenAPI 3.1 document describing the Reporting API methods of the activated plugins.
 */
class SpecGenerator
{
    private const OPENAPI_VERSION = '3.1.0';

    private const SUMMARY_MAX_LENGTH = 120;

    private const RESPONSE_FORMATS = ['json', 'xml', 'csv', 'tsv', 'html', 'rss', 'original'];

    private const FLAG_VALUES = ['0', '1'];

    public function generate(): array
    {
        // Instantiating the generator registers the API class of every loaded plugin in the proxy
        $documentationGenerator = new DocumentationGenerator();
        $proxy = Proxy::getInstance();

        $tags = [];
        $paths = [];

        foreach ($proxy->getMetadata() as $className => $classMetadata) {
            $module = $proxy->getModuleNameFromClassName($className);
            $modulePaths = [];

            foreach ($classMetadata as $action => $methodMetadata) {
                $action = (string) $action;
                if (
                    !is_array($methodMetadata)
                    || !isset($methodMetadata['parameters'])
                    || $this->isDeclaredByBaseApi($className, $action)
                ) {
                    continue;
                }

                $modulePaths['/index.php?method=' . $module . '.' . $action] = [
                    'post' => $this->buildOperation($documentationGenerator, $className, $module, $action, $methodMetadata),
                ];
            }

            if (empty($modulePaths)) {
                continue;
            }

            $tags[] = $this->buildTag($module, $classMetadata['__documentation'] ?? null);
            $paths += $modulePaths;
        }

        return [
            'openapi' => self::OPENAPI_VERSION,
            'info' => $this->buildInfo(),
            'externalDocs' => [
                'description' => 'Matomo Reporting API reference',
                'url' => 'https://developer.matomo.org/api-reference/reporting-api',
            ],
            'servers' => $this->buildServers(),
            'tags' => $tags,
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'description' => 'A Matomo API token, created under Administration > Personal > Security.',
                    ],
                ],
            ],
            'security' => [
                ['BearerAuth' => []],
            ],
        ];
    }

    /**
     * Public helpers of the base API class (e.g. usesAutoSanitizeInputParams) are not API methods
     */
    private function isDeclaredByBaseApi(string $className, string $action): bool
    {
        try {
            $declaringClass = (new \ReflectionMethod($className, $action))->getDeclaringClass()->getName();
        } catch (\ReflectionException $e) {
            return false;
        }

        return $declaringClass === \Piwik\Plugin\API::class;
    }

    private function buildInfo(): array
    {
        return [
            'title' => 'Matomo Reporting API',
            'summary' => 'Reporting API of this Matomo installation',
            'description' => 'Generated from the plugins activated on this Matomo. Every method is called with a POST request on `index.php`. '
                . 'Errors are returned with a 200 status code and a `result: error` body in the requested format.',
            'version' => Version::VERSION,
            'contact' => [
                'name' => 'Openmost',
                'url' => 'https://openmost.com/matomo/extensions/swagger',
                'email' => 'ronan@openmost.com',
            ],
            'license' => [
                'name' => 'GPL v3 or later',
                'identifier' => 'GPL-3.0-or-later',
            ],
        ];
    }

    private function buildServers(): array
    {
        // The current URL keeps the host the user is browsing, so "Try it out" requests stay same-origin
        $url = Common::isPhpCliMode() ? SettingsPiwik::getPiwikUrl() : Url::getCurrentUrlWithoutFileName();
        $url = rtrim((string) $url, '/');

        return [
            [
                'url' => $url !== '' ? $url : '/',
                'description' => 'This Matomo',
            ],
        ];
    }

    private function buildTag(string $module, $documentation): array
    {
        $tag = ['name' => $module];

        if (is_string($documentation)) {
            $description = trim(html_entity_decode(strip_tags($documentation), ENT_QUOTES));
            if ($description !== '') {
                $tag['description'] = $description;
            }
        }

        $homepage = $this->getPluginHomepage($module);
        if ($homepage !== '') {
            $tag['externalDocs'] = [
                'description' => 'Plugin homepage',
                'url' => $homepage,
            ];
        }

        return $tag;
    }

    private function getPluginHomepage(string $module): string
    {
        $pluginManager = Manager::getInstance();
        if (!$pluginManager->isPluginLoaded($module)) {
            return '';
        }

        $homepage = $pluginManager->getLoadedPlugin($module)->getInformation()['homepage'] ?? '';

        return is_string($homepage) && filter_var($homepage, FILTER_VALIDATE_URL) ? $homepage : '';
    }

    private function buildOperation(
        DocumentationGenerator $documentationGenerator,
        string $className,
        string $module,
        string $action,
        array $methodMetadata
    ): array {
        $method = $module . '.' . $action;
        [$summary, $description] = $this->readDocComment($className, $action);

        $parameters = $methodMetadata['parameters'];
        $mediaType = $this->buildRequestBody($parameters, $this->isReportMethod($action, $parameters));

        $example = $this->buildExample($documentationGenerator, $className, $action, $mediaType['schema']['properties']);
        if ($example !== null) {
            $mediaType['example'] = $example;
        }

        $operation = [
            'summary' => $summary !== '' ? $summary : $method,
            'operationId' => $module . '_' . $action,
            'tags' => [$module],
            'requestBody' => [
                'required' => true,
                'content' => [
                    // The API reads its parameters from the query string and form fields only, a JSON body is ignored
                    'application/x-www-form-urlencoded' => $mediaType,
                ],
            ],
            'responses' => [
                '200' => $this->buildSuccessResponse(),
            ],
        ];

        if ($description !== '' && $description !== $summary) {
            $operation['description'] = $description;
        }

        if (!empty($methodMetadata['isDeprecated'])) {
            $operation['deprecated'] = true;
        }

        return $operation;
    }

    /**
     * @return array{0: string, 1: string} summary and description
     */
    private function readDocComment(string $className, string $action): array
    {
        try {
            $docComment = (new \ReflectionMethod($className, $action))->getDocComment();
        } catch (\ReflectionException $e) {
            return ['', ''];
        }

        if (!is_string($docComment)) {
            return ['', ''];
        }

        $lines = [];
        foreach (preg_split('/\R/', $docComment) ?: [] as $line) {
            $line = (string) preg_replace(['#^\s*(/\*\*|\*/|\*)\s?#', '#\s*\*/\s*$#'], '', $line);
            if (str_starts_with(ltrim($line), '@')) {
                break;
            }
            $lines[] = rtrim($line);
        }

        $description = trim(html_entity_decode(strip_tags(implode("\n", $lines)), ENT_QUOTES));
        if ($description === '') {
            return ['', ''];
        }

        $firstParagraph = (preg_split('/\n\s*\n/', $description) ?: [$description])[0];
        $summary = trim((string) preg_replace('/\s+/', ' ', $firstParagraph));

        if (mb_strlen($summary) > self::SUMMARY_MAX_LENGTH) {
            $sentenceEnd = mb_strpos($summary, '. ');
            $summary = $sentenceEnd !== false && $sentenceEnd < self::SUMMARY_MAX_LENGTH
                ? mb_substr($summary, 0, $sentenceEnd + 1)
                : mb_substr($summary, 0, self::SUMMARY_MAX_LENGTH - 3) . '...';
        }

        return [$summary, $description];
    }

    private function isReportMethod(string $action, array $parameters): bool
    {
        return str_starts_with($action, 'get')
            && array_key_exists('period', $parameters)
            && array_key_exists('date', $parameters);
    }

    private function buildRequestBody(array $parameters, bool $isReportMethod): array
    {
        $properties = [
            'module' => [
                'type' => 'string',
                'enum' => ['API'],
                'default' => 'API',
                'description' => 'Always `API` for Reporting API requests.',
            ],
            'format' => [
                'type' => 'string',
                'enum' => self::RESPONSE_FORMATS,
                'default' => 'json',
                'description' => 'Response format.',
            ],
        ];
        $required = ['module', 'format'];
        $encoding = [];

        foreach ($parameters as $name => $config) {
            $name = (string) $name;
            if (str_starts_with($name, '_') || !is_array($config)) {
                continue;
            }

            $schema = $this->buildParameterSchema($name, $config);
            $field = $name;

            if ($schema['type'] === 'array') {
                // PHP only reads repeated form fields as an array when their name ends with []
                $field = $name . '[]';
                $encoding[$field] = ['style' => 'form', 'explode' => true];
            }

            $properties[$field] = $schema;

            if (($config['default'] ?? null) instanceof NoDefaultValue) {
                $required[] = $field;
            }
        }

        if ($isReportMethod) {
            $properties += $this->getReportParameters();
        }

        $mediaType = [
            'schema' => [
                'type' => 'object',
                'required' => $required,
                'properties' => $properties,
            ],
        ];

        if (!empty($encoding)) {
            $mediaType['encoding'] = $encoding;
        }

        return $mediaType;
    }

    private function buildParameterSchema(string $name, array $config): array
    {
        $phpType = is_string($config['type'] ?? null) ? strtolower($config['type']) : null;
        $default = $config['default'] ?? null;
        $hasDefault = $default !== null && !($default instanceof NoDefaultValue);

        $schema = $this->getKnownParameterSchema($name, $phpType)
            ?? $this->mapPhpType($phpType, $hasDefault ? $default : null);

        if ($hasDefault && !array_key_exists('default', $schema)) {
            $coercedDefault = $this->coerceDefault($schema, $default);
            if ($coercedDefault !== null) {
                $schema['default'] = $coercedDefault;
            }
        }

        return $schema;
    }

    private function mapPhpType(?string $phpType, $default): array
    {
        switch ($phpType) {
            case 'int':
                return ['type' => 'integer'];
            case 'float':
                return ['type' => 'number'];
            case 'bool':
                return ['type' => 'boolean'];
            case 'array':
                return ['type' => 'array', 'items' => ['type' => 'string']];
        }

        if ($phpType === null) {
            // Untyped parameters receive the raw request string: "0" is falsy for PHP while "false" is not.
            // A false default often only means "not set" for a string (e.g. columns), so only true marks a flag
            if ($default === true) {
                return ['type' => 'string', 'enum' => self::FLAG_VALUES];
            }
            if (is_int($default)) {
                return ['type' => 'integer'];
            }
            if (is_array($default)) {
                return ['type' => 'array', 'items' => ['type' => 'string']];
            }
        }

        return ['type' => 'string'];
    }

    private function getKnownParameterSchema(string $name, ?string $phpType): ?array
    {
        switch ($name) {
            case 'period':
                return [
                    'type' => 'string',
                    'enum' => ['day', 'week', 'month', 'year', 'range'],
                    'description' => 'Period of the report. Use `range` together with a date range.',
                    'example' => 'day',
                ];
            case 'date':
                return [
                    'type' => 'string',
                    'description' => 'A date (`YYYY-MM-DD`), a keyword (`today`, `yesterday`), `lastN` / `previousN`, or a range (`YYYY-MM-DD,YYYY-MM-DD`).',
                    'example' => 'yesterday',
                ];
            case 'idSite':
            case 'idsite':
            case 'idSites':
                if ($phpType === 'int') {
                    return [
                        'type' => 'integer',
                        'minimum' => 1,
                        'description' => 'Website ID.',
                        'example' => 1,
                    ];
                }

                return [
                    'type' => 'string',
                    'pattern' => '^(all|[0-9]+(,[0-9]+)*)$',
                    'description' => 'Website ID, a comma separated list of website IDs, or `all`.',
                    'example' => '1',
                ];
            case 'segment':
                return [
                    'type' => 'string',
                    'description' => 'Segment definition, for example `browserCode==FF;countryCode==fr`. See https://developer.matomo.org/api-reference/reporting-api-segmentation',
                ];
            case 'language':
                return [
                    'type' => 'string',
                    'pattern' => '^[a-z]{2,3}([-_][A-Za-z]{2,4})?$',
                    'description' => 'Language code, for example `en` or `pt-br`.',
                ];
        }

        return null;
    }

    /**
     * Generic parameters applied by Matomo to every report, see
     * https://developer.matomo.org/api-reference/reporting-api#optional-api-parameters
     */
    private function getReportParameters(): array
    {
        return [
            'filter_limit' => [
                'type' => 'integer',
                'description' => 'Maximum number of rows to return (100 by default). Use `-1` to return all rows.',
            ],
            'filter_offset' => [
                'type' => 'integer',
                'description' => 'Number of rows to skip.',
            ],
            'filter_sort_column' => [
                'type' => 'string',
                'description' => 'Column to sort the rows by, for example `nb_visits`.',
            ],
            'filter_sort_order' => [
                'type' => 'string',
                'enum' => ['desc', 'asc'],
                'description' => 'Sort order.',
            ],
            'filter_pattern' => [
                'type' => 'string',
                'description' => 'Only keep the rows whose label matches this regular expression.',
            ],
            'showColumns' => [
                'type' => 'string',
                'description' => 'Comma separated list of the only columns to return.',
            ],
            'hideColumns' => [
                'type' => 'string',
                'description' => 'Comma separated list of columns to remove.',
            ],
            'flat' => [
                'type' => 'string',
                'enum' => self::FLAG_VALUES,
                'description' => 'Set to `1` to flatten the subtables into a single table.',
            ],
            'format_metrics' => [
                'type' => 'string',
                'enum' => ['bestFormat', '0', '1'],
                'description' => 'Set to `1` to format metrics (percentages, durations, money), `0` to return raw values.',
            ],
            'percent_of_total' => [
                'type' => 'string',
                'enum' => self::FLAG_VALUES,
                'description' => 'Set to `0` to remove the `{metric}_percent_of_total` columns.',
            ],
        ];
    }

    private function coerceDefault(array $schema, $default)
    {
        if (is_object($default)) {
            return null;
        }

        if (isset($schema['enum'])) {
            $value = is_bool($default) ? ($default ? '1' : '0') : (is_scalar($default) ? (string) $default : null);

            return in_array($value, $schema['enum'], true) ? $value : null;
        }

        switch ($schema['type']) {
            case 'integer':
                return is_numeric($default) ? (int) $default : null;
            case 'number':
                return is_numeric($default) ? (float) $default : null;
            case 'boolean':
                return is_bool($default) ? $default : null;
            case 'array':
                $values = is_array($default) ? array_values(array_filter($default, 'is_scalar')) : [];

                return empty($values) ? null : array_map('strval', $values);
        }

        if (is_bool($default)) {
            return $default ? '1' : null;
        }

        return is_scalar($default) && (string) $default !== '' ? (string) $default : null;
    }

    private function buildExample(
        DocumentationGenerator $documentationGenerator,
        string $className,
        string $action,
        array $properties
    ): ?array {
        try {
            $exampleUrl = $documentationGenerator->getExampleUrl($className, $action);
        } catch (\Throwable $e) {
            return null;
        }

        if (!is_string($exampleUrl) || $exampleUrl === '') {
            return null;
        }

        parse_str(ltrim(html_entity_decode($exampleUrl, ENT_QUOTES), '?'), $params);

        // Keep the example valid against the schema, internal parameters (e.g. _hideImplementationData) are not documented
        $example = [];
        foreach (array_intersect_key($params, $properties) as $name => $value) {
            $example[$name] = $this->coerceExampleValue($properties[$name], $value);
        }
        $example['module'] = 'API';
        $example['format'] = 'json';

        return $example;
    }

    /**
     * Example URLs only carry strings, cast them to the type documented by the schema
     */
    private function coerceExampleValue(array $schema, $value)
    {
        if (!is_string($value)) {
            return $value;
        }

        switch ($schema['type']) {
            case 'integer':
                return is_numeric($value) ? (int) $value : $value;
            case 'number':
                return is_numeric($value) ? (float) $value : $value;
            case 'boolean':
                return in_array(strtolower($value), ['1', 'true'], true);
        }

        return $value;
    }

    private function buildSuccessResponse(): array
    {
        return [
            'description' => 'The content type depends on the `format` parameter.',
            'content' => [
                'application/json' => ['schema' => ['type' => ['object', 'array']]],
                'application/xml' => ['schema' => ['type' => 'string']],
                'text/csv' => ['schema' => ['type' => 'string']],
                'text/tab-separated-values' => ['schema' => ['type' => 'string']],
                'text/html' => ['schema' => ['type' => 'string']],
                'application/rss+xml' => ['schema' => ['type' => 'string']],
                'text/plain' => [
                    'schema' => [
                        'type' => 'string',
                        'description' => 'Returned with `format=original`: the serialized PHP value.',
                    ],
                ],
            ],
        ];
    }
}
