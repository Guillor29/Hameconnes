import './bootstrap';
import { createApp, h } from 'vue';
import App from './App.vue';

// Import components
import FishingSpotListing from './components/FishingSpotListing.vue';
import FishingSpotDetail from './components/FishingSpotDetail.vue';
import FishSpeciesListing from './components/FishSpeciesListing.vue';
import MapComponent from './components/MapComponent.vue';
import NavigationMenu from './components/NavigationMenu.vue';

// Create a temporary component for fish species detail
// This is a placeholder until a proper component is created
const FishSpeciesDetailPlaceholder = {
  props: ['speciesId'],
  render() {
    return h('div', { class: 'p-8 bg-white rounded-lg shadow-md' }, [
      h('h2', { class: 'text-2xl font-bold mb-4 text-gray-800' }, 'Détails de l\'espèce'),
      h('p', { class: 'mb-4 text-gray-600' }, `ID de l'espèce: ${this.speciesId}`),
      h('p', { class: 'mb-4 text-gray-600' }, 'Cette page est en cours de développement.'),
      h('div', { class: 'mt-6' }, [
        h('a', {
          href: '/fish-species',
          class: 'px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors'
        }, 'Retour à la liste des espèces')
      ])
    ]);
  }
};

// Check if we're on the home page or a specific component page
const appElement = document.getElementById('app');

if (appElement) {
  // Check if the app element has a child component specified
  const fishingSpotListingElement = appElement.querySelector('fishing-spot-listing');
  const fishSpeciesListingElement = appElement.querySelector('fish-species-listing');
  const fishingSpotDetailElement = appElement.querySelector('fishing-spot-detail');
  const fishSpeciesDetailElement = appElement.querySelector('fish-species-detail');

  if (fishingSpotListingElement) {
    // We're on the fishing spots listing page
    const app = createApp(FishingSpotListing);
    app.mount('#app');
  } else if (fishSpeciesListingElement) {
    // We're on the fish species listing page
    const app = createApp(FishSpeciesListing);
    app.mount('#app');
  } else if (fishingSpotDetailElement) {
    // We're on the fishing spot detail page
    const spotId = fishingSpotDetailElement.getAttribute('spot-id');
    const app = createApp(FishingSpotDetail, { spotId: parseInt(spotId) });
    app.mount('#app');
  } else if (fishSpeciesDetailElement) {
    // We're on the fish species detail page
    // Use the placeholder component since FishSpeciesDetail doesn't exist yet
    const speciesId = fishSpeciesDetailElement.getAttribute('species-id');
    const app = createApp(FishSpeciesDetailPlaceholder, { speciesId: parseInt(speciesId) });
    app.mount('#app');
  } else {
    // We're on the home page, use the default App component
    const app = createApp(App);

    // Register components globally for the main app
    app.component('fishing-spot-listing', FishingSpotListing);
    app.component('fishing-spot-detail', FishingSpotDetail);
    app.component('fish-species-listing', FishSpeciesListing);
    app.component('map-component', MapComponent);
    app.component('navigation-menu', NavigationMenu);

    app.mount('#app');
  }
}

// Always register the navigation menu component for use in the layout
const navElement = document.querySelector('navigation-menu');
if (navElement) {
  const navApp = createApp(NavigationMenu);
  navApp.mount('navigation-menu');
}
