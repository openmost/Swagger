<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="swaggerExplorer">
    <p class="swaggerExplorer-intro">{{ translate('Swagger_Intro') }}</p>
    <div class="swaggerExplorer-toolbar">
      <div class="swaggerExplorer-session">
        <label>
          <input
            type="checkbox"
            v-model="useSession"
          />
          <span>{{ translate('Swagger_UseSession') }}</span>
        </label>
        <p class="form-help">{{ translate('Swagger_UseSessionHelp') }}</p>
      </div>
      <button
        type="button"
        class="btn"
        :disabled="!spec"
        @click="downloadSpec"
      >
        {{ translate('Swagger_DownloadSpec') }}
      </button>
    </div>
    <ActivityIndicator
      :loading="loading"
      :loading-message="translate('Swagger_LoadingSpec')"
    />
    <Alert
      v-if="errorMessage"
      severity="danger"
    >
      {{ errorMessage }}
    </Alert>
    <div
      ref="host"
      class="swaggerExplorer-host"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  Alert,
  translate,
} from 'CoreHome';
import SwaggerUIBundle from 'swagger-ui-dist/swagger-ui-es-bundle.js';
import swaggerUiCss from 'swagger-ui-dist/swagger-ui.css?inline';
import { addSessionAuth, SwaggerRequest } from './sessionAuth';
import { OpenApiSpec, withEmptyFormSamples } from './swaggerUiSpec';

export interface SwaggerExplorerState {
  loading: boolean;
  errorMessage: string;
  spec: OpenApiSpec|null;
  useSession: boolean;
}

// Swagger UI is rendered in a shadow root: its stylesheet must not leak into Matomo, and the
// Matomo stylesheets (inputs, tables, headings) must not break Swagger UI
const SHADOW_STYLES = `
:host { display: block; }
.swagger-ui .wrapper { max-width: none; padding: 0; }
.swagger-ui .information-container .info { margin: 16px 0 8px; }
.swagger-ui .scheme-container { background: transparent; box-shadow: none; margin: 0; padding: 8px 0 16px; }
.dark-mode .swagger-ui { background: transparent; }
`;

function isDarkMode(): boolean {
  return document.documentElement.getAttribute('data-theme-mode') === 'dark';
}

export default defineComponent({
  components: {
    ActivityIndicator,
    Alert,
  },
  data(): SwaggerExplorerState {
    return {
      loading: false,
      errorMessage: '',
      spec: null,
      useSession: true,
    };
  },
  mounted() {
    this.loadSpec();
  },
  methods: {
    async loadSpec() {
      this.loading = true;
      this.errorMessage = '';

      try {
        const spec = await AjaxHelper.fetch<OpenApiSpec>(
          { method: 'Swagger.getOpenApi' },
          { createErrorNotification: false },
        );
        this.spec = markRaw(spec);
        this.renderSwaggerUi(spec);
      } catch (e) {
        this.errorMessage = (e as Error)?.message || translate('General_ErrorRequest', '', '');
      } finally {
        this.loading = false;
      }
    },
    renderSwaggerUi(spec: OpenApiSpec) {
      const host = this.$refs.host as HTMLElement;
      const shadowRoot = host.shadowRoot || host.attachShadow({ mode: 'open' });

      const style = document.createElement('style');
      style.textContent = `${swaggerUiCss}${SHADOW_STYLES}`;

      const themeWrapper = document.createElement('div');
      themeWrapper.className = isDarkMode() ? 'dark-mode' : '';
      const mountPoint = document.createElement('div');
      themeWrapper.appendChild(mountPoint);

      shadowRoot.replaceChildren(style, themeWrapper);

      SwaggerUIBundle({
        spec: withEmptyFormSamples(spec),
        domNode: mountPoint,
        deepLinking: false,
        docExpansion: 'none',
        defaultModelsExpandDepth: -1,
        displayRequestDuration: true,
        filter: true,
        validatorUrl: null,
        requestInterceptor: (request: SwaggerRequest) => (
          this.useSession ? addSessionAuth(request, window.piwik.token_auth) : request
        ),
      });
    },
    downloadSpec() {
      if (!this.spec) {
        return;
      }

      const blob = new Blob([JSON.stringify(this.spec, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'matomo-openapi.json';
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    },
  },
});
</script>

<style lang="less" scoped>
.swaggerExplorer-intro {
  margin-bottom: 8px;
}

.swaggerExplorer-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.swaggerExplorer-session {
  flex: 1 1 320px;
}
</style>
