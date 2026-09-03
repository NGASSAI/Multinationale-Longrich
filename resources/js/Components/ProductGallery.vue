<template>
  <div class="w-full">
    <!-- Image principale -->
    <div
      class="relative aspect-square w-full overflow-hidden rounded bg-sage cursor-zoom-in group"
      @click="openLightbox(activeIndex)"
    >
      <img
        :src="images[activeIndex]?.path"
        :alt="productName"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
      />
      <div class="absolute bottom-3 right-3 bg-charcoal/70 text-ivory text-xs px-3 py-1.5 rounded flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
        Agrandir
      </div>
    </div>

    <!-- Bande de miniatures : défilement horizontal sur mobile, grille sur desktop -->
    <div
      v-if="images.length > 1"
      class="mt-3 flex gap-2 overflow-x-auto pb-1 sm:grid sm:grid-cols-5 sm:overflow-visible"
      style="scrollbar-width: thin;"
    >
      <button
        v-for="(image, index) in images"
        :key="image.id ?? index"
        @click="activeIndex = index"
        class="relative flex-shrink-0 w-16 h-16 sm:w-full sm:h-auto sm:aspect-square rounded overflow-hidden border-2 transition-colors"
        :class="index === activeIndex ? 'border-gold' : 'border-transparent opacity-70 hover:opacity-100'"
      >
        <img :src="image.path" :alt="`${productName} - vue ${index + 1}`" class="h-full w-full object-cover" />
      </button>
    </div>

    <!-- Lightbox plein écran -->
    <Teleport to="body">
      <div
        v-if="lightboxOpen"
        class="fixed inset-0 z-50 bg-charcoal/95 flex items-center justify-center animate-fade-in"
        @click.self="closeLightbox"
        @keydown.esc="closeLightbox"
        @keydown.left="prev"
        @keydown.right="next"
        tabindex="0"
        ref="lightboxRef"
      >
        <button
          @click="closeLightbox"
          class="absolute top-4 right-4 sm:top-6 sm:right-6 text-ivory/80 hover:text-ivory transition-colors z-10"
          aria-label="Fermer"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <button
          v-if="images.length > 1"
          @click="prev"
          class="absolute left-2 sm:left-6 text-ivory/70 hover:text-ivory transition-colors p-2"
          aria-label="Image précédente"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <div
          class="max-w-4xl max-h-[85vh] w-full mx-4 sm:mx-16"
          @touchstart="onTouchStart"
          @touchend="onTouchEnd"
        >
          <img
            :src="images[activeIndex]?.path"
            :alt="productName"
            class="w-full h-full max-h-[85vh] object-contain select-none"
            draggable="false"
          />
          <p class="text-center text-ivory/60 text-sm mt-3">
            {{ activeIndex + 1 }} / {{ images.length }}
          </p>
        </div>

        <button
          v-if="images.length > 1"
          @click="next"
          class="absolute right-2 sm:right-6 text-ivory/70 hover:text-ivory transition-colors p-2"
          aria-label="Image suivante"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from "vue";

const props = defineProps({
  images: { type: Array, required: true }, // [{ id, path }]
  productName: { type: String, default: "" },
});

const activeIndex = ref(0);
const lightboxOpen = ref(false);
const lightboxRef = ref(null);
let touchStartX = 0;

function openLightbox(index) {
  activeIndex.value = index;
  lightboxOpen.value = true;
  document.body.style.overflow = "hidden";
  nextTick(() => lightboxRef.value?.focus());
}

function closeLightbox() {
  lightboxOpen.value = false;
  document.body.style.overflow = "";
}

function next() {
  activeIndex.value = (activeIndex.value + 1) % props.images.length;
}

function prev() {
  activeIndex.value = (activeIndex.value - 1 + props.images.length) % props.images.length;
}

function onTouchStart(e) {
  touchStartX = e.changedTouches[0].screenX;
}

function onTouchEnd(e) {
  const touchEndX = e.changedTouches[0].screenX;
  const diff = touchStartX - touchEndX;

  if (Math.abs(diff) > 50) {
    diff > 0 ? next() : prev();
  }
}

watch(() => props.images, () => {
  activeIndex.value = 0;
});
</script>
