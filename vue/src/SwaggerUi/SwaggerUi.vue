<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    v-if="showTitle"
    :content-title="translate('Swagger_PageTitle')"
  >
    <SwaggerExplorer />
  </ContentBlock>
  <SwaggerExplorer v-else />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';
import SwaggerExplorer from './SwaggerExplorer.vue';

export default defineComponent({
  // ClientWidgetRenderer passes widget metadata (uniqueId, widgetized...) this component does not use
  inheritAttrs: false,
  props: {
    showTitle: Boolean,
  },
  components: {
    ContentBlock,
    SwaggerExplorer,
  },
});
</script>
