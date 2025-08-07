<template>
  <div class="min-h-screen bg-gray-100">
    <NavigationMenu />

    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Carte des spots de pêche</h2>
          <p class="text-gray-600 mb-4">Explorez les meilleurs spots de pêche ou ajoutez les vôtres en cliquant sur la carte.</p>
          <MapComponent />
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import MapComponent from './components/MapComponent.vue';
import NavigationMenu from './components/NavigationMenu.vue';

export default {
  name: 'App',
  components: {
    MapComponent,
    NavigationMenu
  }
}
</script>
