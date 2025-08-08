<template>
  <div class="fishing-spot-listing">
    <div v-if="loading" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      <p class="mt-2 text-gray-600">Chargement des spots de pêche...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Erreur!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-else>
      <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">Spots de pêche</h2>
        <div class="relative">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Rechercher un spot..."
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <div v-if="filteredSpots.length === 0" class="text-center py-10">
        <p class="text-gray-600">Aucun spot de pêche trouvé.</p>
      </div>

      <div v-else class="overflow-hidden bg-white shadow-md sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nom
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Espèces
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Dernière modification
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Par
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="spot in filteredSpots" :key="spot.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ spot.name }}</div>
                <div class="text-xs text-gray-500">
                  {{ spot.latitude.toFixed(4) }}, {{ spot.longitude.toFixed(4) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800': spot.water_type === 'river',
                    'bg-indigo-100 text-indigo-800': spot.water_type === 'lake',
                    'bg-cyan-100 text-cyan-800': spot.water_type === 'sea',
                    'bg-green-100 text-green-800': spot.water_type === 'pond',
                    'bg-yellow-100 text-yellow-800': spot.water_type === 'reservoir',
                    'bg-gray-100 text-gray-800': !['river', 'lake', 'sea', 'pond', 'reservoir'].includes(spot.water_type)
                  }">
                  {{ getWaterTypeLabel(spot.water_type) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <span v-if="spot.fishSpecies && spot.fishSpecies.length > 0">
                    <span v-for="(species, index) in spot.fishSpecies.slice(0, 3)" :key="species.id" class="inline-block">
                      {{ species.name }}{{ index < Math.min(spot.fishSpecies.length, 3) - 1 ? ', ' : '' }}
                    </span>
                    <span v-if="spot.fishSpecies.length > 3" class="text-gray-500">
                      +{{ spot.fishSpecies.length - 3 }} autres
                    </span>
                  </span>
                  <span v-else class="text-gray-500 italic">Aucune espèce</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(spot.updated_at) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ spot.user ? spot.user.name : 'Inconnu' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="viewSpotDetails(spot.id)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Détails
                </button>
                <button
                  @click="viewOnMap(spot.id)"
                  class="text-green-600 hover:text-green-900"
                >
                  Carte
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FishingSpotListing',
  data() {
    return {
      spots: [],
      loading: true,
      error: null,
      searchQuery: ''
    };
  },
  computed: {
    filteredSpots() {
      if (!this.searchQuery) {
        return this.spots;
      }

      const query = this.searchQuery.toLowerCase();
      return this.spots.filter(spot => {
        return (
          spot.name.toLowerCase().includes(query) ||
          this.getWaterTypeLabel(spot.water_type).toLowerCase().includes(query) ||
          (spot.description && spot.description.toLowerCase().includes(query)) ||
          (spot.user && spot.user.name.toLowerCase().includes(query)) ||
          (spot.fishSpecies && spot.fishSpecies.some(species => species.name.toLowerCase().includes(query)))
        );
      });
    }
  },
  mounted() {
    this.fetchSpots();
  },
  methods: {
    fetchSpots() {
      this.loading = true;
      this.error = null;

      fetch('/api/fishing-spots')
        .then(response => {
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des spots de pêche');
          }
          return response.json();
        })
        .then(data => {
          this.spots = data;
          this.loading = false;
        })
        .catch(error => {
          this.error = error.message;
          this.loading = false;
          console.error('Error fetching fishing spots:', error);
        });
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
    formatDate(dateString) {
      if (!dateString) return 'Date inconnue';

      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    viewSpotDetails(spotId) {
      // Navigate to the details page
      window.location.href = `/fishing-spots/${spotId}`;
    },
    viewOnMap(spotId) {
      // Navigate to the map view and focus on this spot
      window.location.href = `/#spot=${spotId}`;
    }
  }
};
</script>
