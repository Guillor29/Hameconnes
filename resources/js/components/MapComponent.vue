<template>
  <div>
    <div id="map" class="map-container"></div>

    <div class="map-controls mt-3 inline-flex items-center space-x-2">
      <!-- Zoom in button -->
      <button
        @click="zoomIn"
        class="p-2 bg-white rounded-md shadow-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Zoom in"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
      </button>

      <!-- Zoom out button -->
      <button
        @click="zoomOut"
        class="p-2 bg-white rounded-md shadow-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Zoom out"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
        </svg>
      </button>

      <!-- Center button -->
      <button
        @click="centerOnUserLocation"
        class="p-2 bg-white rounded-md shadow-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Center on my location"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
        </svg>
      </button>

      <!-- Style selector dropdown -->
      <div class="relative">
        <button
          @click="toggleDropdown"
          class="flex items-center justify-between w-40 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <span>{{ getCurrentStyleName() }}</span>
          <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
        <div
          v-if="dropdownOpen"
          class="absolute bottom-full mb-1 z-10 w-40 bg-white rounded-md shadow-lg border border-gray-200 overflow-hidden"
        >
          <div class="py-1">
            <a
              v-for="style in mapStyles"
              :key="style.id"
              href="#"
              @click.prevent="changeMapStyle(style.url)"
              class="block px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors duration-150"
              :class="{ 'bg-blue-100 font-medium': currentStyle === style.url }"
            >
              {{ style.name }}
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for adding new fishing spot -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ajouter un nouveau spot de pêche</h3>

        <form @submit.prevent="saveFishingSpot">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input
              type="text"
              id="name"
              v-model="newSpot.name"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
          </div>

          <div class="mb-4">
            <label for="water_type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <div class="flex items-center space-x-2">
              <select
                id="water_type"
                v-model="newSpot.water_type"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="type in waterTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
              <button
                type="button"
                @click="showAddWaterTypeForm = true"
                class="p-2 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                title="Ajouter un nouveau type"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>

            <!-- Form for adding new water type -->
            <div v-if="showAddWaterTypeForm" class="mt-2 p-3 bg-gray-50 rounded-md border border-gray-200">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Ajouter un nouveau type</h4>
              <div class="flex flex-col space-y-2">
                <input
                  type="text"
                  v-model="newWaterType.value"
                  placeholder="Identifiant (ex: canal)"
                  class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
                <input
                  type="text"
                  v-model="newWaterType.label"
                  placeholder="Nom affiché (ex: Canal)"
                  class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
                <div class="flex justify-end space-x-2">
                  <button
                    type="button"
                    @click="showAddWaterTypeForm = false"
                    class="px-3 py-1 text-xs border border-gray-300 rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Annuler
                  </button>
                  <button
                    type="button"
                    @click="addWaterType"
                    class="px-3 py-1 text-xs border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Ajouter
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              id="description"
              v-model="newSpot.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div class="mb-4">
            <label for="tips" class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
            <textarea
              id="tips"
              v-model="newSpot.tips"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div class="mb-4">
            <div class="flex items-center justify-between mb-1">
              <label class="block text-sm font-medium text-gray-700">Espèces de poissons</label>
              <button
                type="button"
                @click="showAddFishSpeciesForm = true"
                class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Ajouter une espèce
              </button>
            </div>

            <div v-if="fishSpecies.length === 0" class="text-sm text-gray-500 italic mb-2">
              Aucune espèce disponible. Veuillez en ajouter une.
            </div>

            <div v-else class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-2 bg-white">
              <div
                v-for="species in fishSpecies"
                :key="species.id"
                class="flex items-center mb-1"
              >
                <input
                  type="checkbox"
                  :id="'species-' + species.id"
                  :value="species.id"
                  v-model="newSpot.fish_species_ids"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label :for="'species-' + species.id" class="ml-2 block text-sm text-gray-700">
                  {{ species.name }}
                  <span v-if="species.scientific_name" class="text-xs text-gray-500 italic">
                    ({{ species.scientific_name }})
                  </span>
                </label>
              </div>
            </div>

            <!-- Form for adding new fish species -->
            <div v-if="showAddFishSpeciesForm" class="mt-2 p-3 bg-gray-50 rounded-md border border-gray-200">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Ajouter une nouvelle espèce</h4>
              <div class="flex flex-col space-y-2">
                <input
                  type="text"
                  v-model="newFishSpecies.name"
                  placeholder="Nom (ex: Bar)"
                  class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
                <input
                  type="text"
                  v-model="newFishSpecies.scientific_name"
                  placeholder="Nom scientifique (optionnel)"
                  class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
                <textarea
                  v-model="newFishSpecies.description"
                  placeholder="Description (optionnel)"
                  rows="2"
                  class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                ></textarea>
                <div class="flex justify-end space-x-2">
                  <button
                    type="button"
                    @click="showAddFishSpeciesForm = false"
                    class="px-3 py-1 text-xs border border-gray-300 rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Annuler
                  </button>
                  <button
                    type="button"
                    @click="addFishSpecies"
                    class="px-3 py-1 text-xs border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Ajouter
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="text-xs text-gray-500 mb-4">
            Coordonnées: {{ newSpot.latitude }}, {{ newSpot.longitude }}
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="isSaving"
            >
              {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

export default {
  name: 'MapComponent',
  data() {
    return {
      map: null,
      markers: [],
      fishingSpots: [],
      fishSpecies: [],
      waterTypes: [
        { value: 'sea', label: 'Mer' },
        { value: 'river', label: 'Rivière' },
        { value: 'lake', label: 'Lac' },
        { value: 'pond', label: 'Étang' },
        { value: 'reservoir', label: 'Réservoir' }
      ],
      dropdownOpen: false,
      showModal: false,
      isSaving: false,
      showAddWaterTypeForm: false,
      showAddFishSpeciesForm: false,
      newWaterType: { value: '', label: '' },
      newFishSpecies: { name: '', scientific_name: '', description: '' },
      newSpot: {
        name: '',
        water_type: 'sea',
        description: '',
        tips: '',
        latitude: 0,
        longitude: 0,
        fish_species_ids: [],
        user_id: 1 // Default user ID, should be replaced with authenticated user ID in production
      },
      currentStyle: 'mapbox://styles/mapbox/satellite-streets-v12',
      mapStyles: [
        { id: 'streets', name: 'Rues', url: 'mapbox://styles/mapbox/streets-v12' },
        { id: 'outdoors', name: 'Extérieur', url: 'mapbox://styles/mapbox/outdoors-v12' },
        { id: 'light', name: 'Clair', url: 'mapbox://styles/mapbox/light-v11' },
        { id: 'dark', name: 'Sombre', url: 'mapbox://styles/mapbox/dark-v11' },
        { id: 'satellite', name: 'Satellite', url: 'mapbox://styles/mapbox/satellite-v9' },
        { id: 'satellite-streets', name: 'Satellite avec noms', url: 'mapbox://styles/mapbox/satellite-streets-v12' }
      ]
    };
  },
  mounted() {
    this.initializeMap();
    this.loadFishingSpots();
    this.loadFishSpecies();

    // Add click outside listener
    document.addEventListener('click', this.handleClickOutside);
  },

  beforeUnmount() {
    // Remove click outside listener
    document.removeEventListener('click', this.handleClickOutside);
  },

  methods: {
    initializeMap() {
      // Use the Mapbox access token from environment variables
      mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN;

      this.map = new mapboxgl.Map({
        container: 'map',
        style: this.currentStyle, // Default to satellite with streets
        center: [-4.0, 48.6], // Center on northern Finistère
        zoom: 8.8 // Slightly less pronounced zoom for regional view
      });

      // Add navigation controls
      this.map.addControl(new mapboxgl.NavigationControl());

      // Add geolocate control
      this.map.addControl(
        new mapboxgl.GeolocateControl({
          positionOptions: {
            enableHighAccuracy: true
          },
          trackUserLocation: true
        })
      );

      // Add click event to add new fishing spots
      this.map.on('click', (e) => {
        this.handleMapClick(e);
      });
    },

    loadFishingSpots() {
      // Fetch fishing spots from API
      fetch('/api/fishing-spots')
        .then(response => response.json())
        .then(data => {
          this.fishingSpots = data;
          this.addMarkersToMap();
        })
        .catch(error => {
          console.error('Error loading fishing spots:', error);
        });
    },

    loadFishSpecies() {
      // Fetch fish species from API
      fetch('/api/fish-species')
        .then(response => response.json())
        .then(data => {
          this.fishSpecies = data;
        })
        .catch(error => {
          console.error('Error loading fish species:', error);
        });
    },

    addMarkersToMap() {
      // Clear existing markers
      this.markers.forEach(marker => marker.remove());
      this.markers = [];

      // Add markers for each fishing spot
      this.fishingSpots.forEach(spot => {
        this.addMarkerForSpot(spot);
      });
    },

    handleMapClick(e) {
      // Set the coordinates in the newSpot object
      this.newSpot.longitude = e.lngLat.lng;
      this.newSpot.latitude = e.lngLat.lat;

      // Reset other form fields
      this.newSpot.name = '';
      this.newSpot.water_type = 'sea';
      this.newSpot.description = '';
      this.newSpot.tips = '';
      this.newSpot.fish_species_ids = [];

      // Reset form states
      this.showAddWaterTypeForm = false;
      this.showAddFishSpeciesForm = false;

      // Open the modal
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.showAddWaterTypeForm = false;
      this.showAddFishSpeciesForm = false;
    },

    addWaterType() {
      // Validate inputs
      if (!this.newWaterType.value || !this.newWaterType.label) {
        alert('Veuillez remplir tous les champs pour ajouter un nouveau type.');
        return;
      }

      // Check if value already exists
      if (this.waterTypes.some(type => type.value === this.newWaterType.value)) {
        alert('Un type avec cet identifiant existe déjà.');
        return;
      }

      // Add new water type to the array
      this.waterTypes.push({
        value: this.newWaterType.value,
        label: this.newWaterType.label
      });

      // Set the new water type as selected
      this.newSpot.water_type = this.newWaterType.value;

      // Reset form
      this.newWaterType = { value: '', label: '' };
      this.showAddWaterTypeForm = false;
    },

    addFishSpecies() {
      // Validate inputs
      if (!this.newFishSpecies.name) {
        alert('Le nom de l\'espèce est obligatoire.');
        return;
      }

      // Set saving state
      this.isSaving = true;

      // Create a copy of the newFishSpecies object to send to the API
      const speciesData = { ...this.newFishSpecies };

      // Send the data to the API
      fetch('/api/fish-species', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(speciesData)
      })
      .then(response => {
        if (!response.ok) {
          // If it's a validation error (422), parse the response to get the specific errors
          if (response.status === 422) {
            return response.json().then(data => {
              throw new Error(Object.values(data.errors).flat().join('\n'));
            });
          }
          throw new Error('Erreur lors de l\'enregistrement de l\'espèce');
        }
        return response.json();
      })
      .then(data => {
        // Add the new species to the fishSpecies array
        this.fishSpecies.push(data);

        // Select the new species
        this.newSpot.fish_species_ids.push(data.id);

        // Reset form
        this.newFishSpecies = { name: '', scientific_name: '', description: '' };
        this.showAddFishSpeciesForm = false;

        // Show success message
        alert('Espèce ajoutée avec succès!');
      })
      .catch(error => {
        console.error('Error saving fish species:', error);
        alert('Erreur lors de l\'enregistrement de l\'espèce: ' + error.message);
      })
      .finally(() => {
        // Reset saving state
        this.isSaving = false;
      });
    },

    saveFishingSpot() {
      // Set saving state
      this.isSaving = true;

      // Validate required fields
      if (!this.newSpot.name) {
        alert('Le nom du spot est obligatoire.');
        this.isSaving = false;
        return;
      }

      // Create a copy of the newSpot object to send to the API
      const spotData = { ...this.newSpot };

      // Log the data being sent (for debugging)
      console.log('Saving fishing spot with data:', spotData);

      // Send the data to the API
      fetch('/api/fishing-spots', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(spotData)
      })
      .then(response => {
        if (!response.ok) {
          // If it's a validation error (422), parse the response to get the specific errors
          if (response.status === 422) {
            return response.json().then(data => {
              throw new Error(Object.values(data.errors).flat().join('\n'));
            });
          }
          throw new Error('Erreur lors de l\'enregistrement du spot de pêche');
        }
        return response.json();
      })
      .then(data => {
        // Add the new spot to the fishingSpots array
        this.fishingSpots.push(data);

        // Add a marker for the new spot
        this.addMarkerForSpot(data);

        // Close the modal
        this.closeModal();

        // Show success message
        alert('Spot de pêche enregistré avec succès!');
      })
      .catch(error => {
        console.error('Error saving fishing spot:', error);
        alert('Erreur lors de l\'enregistrement du spot de pêche: ' + error.message);
      })
      .finally(() => {
        // Reset saving state
        this.isSaving = false;
      });
    },

    addMarkerForSpot(spot) {
      const popup = new mapboxgl.Popup({ offset: 25 })
        .setHTML(`
          <h3>${spot.name}</h3>
          <p>${spot.description || 'No description'}</p>
          <p>Type: ${this.getWaterTypeLabel(spot.water_type) || 'Not specified'}</p>
          <a href="/fishing-spots/${spot.id}">View details</a>
        `);

      const marker = new mapboxgl.Marker()
        .setLngLat([spot.longitude, spot.latitude])
        .setPopup(popup)
        .addTo(this.map);

      this.markers.push(marker);
    },

    getWaterTypeLabel(waterType) {
      const labels = {
        'sea': 'Mer',
        'river': 'Rivière',
        'lake': 'Lac',
        'pond': 'Étang',
        'reservoir': 'Réservoir'
      };

      return labels[waterType] || waterType;
    },

    changeMapStyle(styleUrl) {
      // Update current style
      this.currentStyle = styleUrl;

      // Apply the new style to the map
      this.map.setStyle(styleUrl);

      // When the style is loaded, re-add the markers
      this.map.once('style.load', () => {
        this.addMarkersToMap();
      });

      // Close the dropdown after selection
      this.dropdownOpen = false;
    },

    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },

    getCurrentStyleName() {
      const style = this.mapStyles.find(style => style.url === this.currentStyle);
      return style ? style.name : 'Sélectionner un style';
    },

    handleClickOutside(event) {
      const dropdown = document.querySelector('.map-controls .relative');
      if (dropdown && !dropdown.contains(event.target)) {
        this.dropdownOpen = false;
      }
    },

    zoomIn() {
      if (this.map) {
        this.map.zoomIn();
      }
    },

    zoomOut() {
      if (this.map) {
        this.map.zoomOut();
      }
    },

    centerOnUserLocation() {
      if (this.map) {
        // Try to get user's location
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(
            (position) => {
              // Success callback
              this.map.flyTo({
                center: [position.coords.longitude, position.coords.latitude],
                zoom: 14,
                essential: true
              });
            },
            (error) => {
              // Error callback
              console.error('Error getting user location:', error);
              alert('Impossible de récupérer votre position. Veuillez vérifier vos paramètres de localisation.');
            },
            {
              enableHighAccuracy: true,
              timeout: 5000,
              maximumAge: 0
            }
          );
        } else {
          alert('La géolocalisation n\'est pas prise en charge par votre navigateur.');
        }
      }
    }
  }
};
</script>

<style scoped>
.map-container {
  width: 100%;
  height: 600px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.map-controls {
  display: inline-flex;
  justify-content: center;
  padding: 8px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin: 0 auto;
  width: auto;
}

button {
  font-weight: 500;
  border: none;
  cursor: pointer;
}

button:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
