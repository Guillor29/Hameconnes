<template>
  <div class="fishing-spot-detail">
    <div v-if="loading" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      <p class="mt-2 text-gray-600">Chargement du spot de pêche...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Erreur!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-else-if="spot" class="bg-white rounded-lg shadow-md overflow-hidden">
      <div class="md:flex">
        <!-- Map section -->
        <div class="md:w-1/2 h-64 md:h-auto relative">
          <div id="detail-map" class="w-full h-full"></div>
        </div>

        <!-- Details section -->
        <div class="md:w-1/2 p-6">
          <div class="flex justify-between items-start">
            <h1 class="text-2xl font-bold text-gray-900">{{ spot.name }}</h1>
            <span
              v-if="spot.water_type"
              class="px-3 py-1 text-xs font-semibold rounded-full"
              :class="{
                'bg-blue-100 text-blue-800': spot.water_type === 'river',
                'bg-indigo-100 text-indigo-800': spot.water_type === 'lake',
                'bg-cyan-100 text-cyan-800': spot.water_type === 'sea',
                'bg-gray-100 text-gray-800': !['river', 'lake', 'sea'].includes(spot.water_type)
              }"
            >
              {{ getWaterTypeLabel(spot.water_type) }}
            </span>
          </div>

          <div class="mt-4 space-y-4">
            <p v-if="spot.description" class="text-gray-700">{{ spot.description }}</p>

            <div v-if="spot.access_type" class="flex items-center text-sm text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <span>Accès: {{ getAccessTypeLabel(spot.access_type) }}</span>
            </div>

            <div class="flex items-center text-sm text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>Coordonnées: {{ spot.latitude }}, {{ spot.longitude }}</span>
            </div>

            <div v-if="spot.tips" class="mt-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Conseils de pêche</h3>
              <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200">
                <p class="text-gray-700">{{ spot.tips }}</p>
              </div>
            </div>

            <div class="mt-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Prises récentes</h3>
              <div v-if="catches.length === 0" class="text-gray-500 italic">
                Aucune prise enregistrée pour ce spot.
              </div>
              <div v-else class="space-y-3">
                <div
                  v-for="fishCatch in catches"
                  :key="fishCatch.id"
                  class="bg-gray-50 p-3 rounded-md border border-gray-200 flex items-center"
                >
                  <div v-if="fishCatch.photo_path" class="w-16 h-16 mr-3 rounded-md overflow-hidden">
                    <img :src="fishCatch.photo_path" :alt="fishCatch.fish_species.name" class="w-full h-full object-cover">
                  </div>
                  <div v-else class="w-16 h-16 mr-3 bg-gray-200 rounded-md flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-800">{{ fishCatch.fish_species.name }}</div>
                    <div class="text-sm text-gray-600">
                      <span v-if="fishCatch.weight">{{ fishCatch.weight }} kg</span>
                      <span v-if="fishCatch.weight && fishCatch.length"> · </span>
                      <span v-if="fishCatch.length">{{ fishCatch.length }} cm</span>
                      <span> · {{ formatDate(fishCatch.date_caught) }}</span>
                    </div>
                    <div v-if="fishCatch.bait_used" class="text-xs text-gray-500 mt-1">
                      Appât: {{ fishCatch.bait_used }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 flex space-x-4">
            <button
              class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors duration-300 flex items-center justify-center"
              @click="recordCatch"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Enregistrer une prise
            </button>
            <button
              class="flex-1 bg-white hover:bg-gray-50 text-gray-700 py-2 px-4 rounded-md border border-gray-300 transition-colors duration-300 flex items-center justify-center"
              @click="goBack"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Retour
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

export default {
  name: 'FishingSpotDetail',
  props: {
    spotId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      spot: null,
      catches: [],
      loading: true,
      error: null,
      map: null
    };
  },
  mounted() {
    this.fetchSpotDetails();
  },
  methods: {
    fetchSpotDetails() {
      this.loading = true;
      this.error = null;

      // Fetch fishing spot details
      fetch(`/api/fishing-spots/${this.spotId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des détails du spot de pêche');
          }
          return response.json();
        })
        .then(data => {
          this.spot = data;
          this.loading = false;
          this.initializeMap();
          this.fetchCatches();
        })
        .catch(error => {
          this.error = error.message;
          this.loading = false;
          console.error('Error fetching fishing spot details:', error);
        });
    },

    fetchCatches() {
      // Fetch catches for this fishing spot
      fetch(`/api/fishing-spots/${this.spotId}/catches`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des prises');
          }
          return response.json();
        })
        .then(data => {
          this.catches = data;
        })
        .catch(error => {
          console.error('Error fetching catches:', error);
        });
    },

    initializeMap() {
      if (!this.spot) return;

      // Use the global Mapbox token set in bootstrap.js

      // Set the Mapbox access token
      mapboxgl.accessToken = window.MAPBOX_TOKEN;

      this.map = new mapboxgl.Map({
        container: 'detail-map',
        style: 'mapbox://styles/mapbox/outdoors-v12',
        center: [this.spot.longitude, this.spot.latitude],
        zoom: 14
      });

      // Add navigation controls
      this.map.addControl(new mapboxgl.NavigationControl());

      // Add marker for the fishing spot
      new mapboxgl.Marker()
        .setLngLat([this.spot.longitude, this.spot.latitude])
        .addTo(this.map);
    },

    getWaterTypeLabel(waterType) {
      const labels = {
        'river': 'Rivière',
        'lake': 'Lac',
        'sea': 'Mer',
        'pond': 'Étang',
        'reservoir': 'Réservoir'
      };

      return labels[waterType] || waterType;
    },

    getAccessTypeLabel(accessType) {
      const labels = {
        'public': 'Public',
        'private': 'Privé',
        'boat': 'Bateau requis',
        'shore': 'Depuis la rive'
      };

      return labels[accessType] || accessType;
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      }).format(date);
    },

    recordCatch() {
      // This would typically navigate to a form to record a new catch
      console.log('Record catch for spot ID:', this.spotId);
      // Example: this.$router.push(`/fishing-spots/${this.spotId}/record-catch`);
    },

    goBack() {
      // This would typically navigate back to the fishing spots list
      console.log('Go back to fishing spots list');
      // Example: this.$router.push('/fishing-spots');
    }
  }
};
</script>

<style scoped>
.fishing-spot-detail {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
