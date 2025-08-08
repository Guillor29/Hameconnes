<template>
  <div class="fish-species-listing">
    <div v-if="loading" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      <p class="mt-2 text-gray-600">Chargement des espèces...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Erreur!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-else>
      <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">Espèces de poissons</h2>
        <div class="relative">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Rechercher une espèce..."
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

      <div v-if="filteredSpecies.length === 0" class="text-center py-10">
        <p class="text-gray-600">Aucune espèce trouvée.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="species in filteredSpecies"
          :key="species.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
        >
          <div class="h-48 bg-gray-200 relative">
            <img
              v-if="species.image_path"
              :src="species.image_path"
              :alt="species.name"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <div class="p-4">
            <h3 class="text-xl font-semibold text-gray-800">{{ species.name }}</h3>
            <p v-if="species.scientific_name" class="text-sm text-gray-600 italic mb-2">{{ species.scientific_name }}</p>

            <div class="mt-2 space-y-2">
              <p v-if="species.description" class="text-gray-700 line-clamp-3">{{ species.description }}</p>

              <div v-if="species.habitat" class="flex items-center text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <span>Habitat: {{ species.habitat }}</span>
              </div>

              <div v-if="species.season" class="flex items-center text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Saison: {{ species.season }}</span>
              </div>

              <div class="flex items-center space-x-4 mt-3">
                <div v-if="species.average_length" class="flex items-center text-sm text-gray-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                  </svg>
                  <span>{{ species.average_length }} cm</span>
                </div>

                <div v-if="species.average_weight" class="flex items-center text-sm text-gray-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                  </svg>
                  <span>{{ species.average_weight }} kg</span>
                </div>
              </div>
            </div>

            <button
              class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors duration-300"
              @click="viewSpeciesDetails(species.id)"
            >
              Voir les détails
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FishSpeciesListing',
  data() {
    return {
      species: [],
      loading: true,
      error: null,
      searchQuery: ''
    };
  },
  computed: {
    filteredSpecies() {
      if (!this.searchQuery) {
        return this.species;
      }

      const query = this.searchQuery.toLowerCase();
      return this.species.filter(species => {
        return (
          species.name.toLowerCase().includes(query) ||
          (species.scientific_name && species.scientific_name.toLowerCase().includes(query)) ||
          (species.habitat && species.habitat.toLowerCase().includes(query)) ||
          (species.description && species.description.toLowerCase().includes(query))
        );
      });
    }
  },
  mounted() {
    this.fetchSpecies();
  },
  methods: {
    fetchSpecies() {
      this.loading = true;
      this.error = null;

      fetch('/api/fish-species')
        .then(response => {
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des espèces de poissons');
          }
          return response.json();
        })
        .then(data => {
          this.species = data;
          this.loading = false;
        })
        .catch(error => {
          this.error = error.message;
          this.loading = false;
          console.error('Error fetching fish species:', error);
        });
    },
    viewSpeciesDetails(speciesId) {
      // Navigate to the details page
      window.location.href = `/fish-species/${speciesId}`;
    }
  }
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
