import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';

// Import components
import FishingSpotListing from './components/FishingSpotListing.vue';
import FishingSpotDetail from './components/FishingSpotDetail.vue';
import FishSpeciesListing from './components/FishSpeciesListing.vue';
import MapComponent from './components/MapComponent.vue';
import NavigationMenu from './components/NavigationMenu.vue';

// Create app instance
const app = createApp(App);

// Register components globally
app.component('fishing-spot-listing', FishingSpotListing);
app.component('fishing-spot-detail', FishingSpotDetail);
app.component('fish-species-listing', FishSpeciesListing);
app.component('map-component', MapComponent);
app.component('navigation-menu', NavigationMenu);

// Mount the app
app.mount('#app');
