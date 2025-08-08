<template>
  <nav class="bg-blue-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <span class="text-xl font-bold">Les Hameçonnés 🎣</span>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <a href="#" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-900 text-white">
                Accueil
              </a>
              <a href="/fishing-spots" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
                Spots de pêche
              </a>
              <a href="/fish-species" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
                Espèces de poissons
              </a>
              <a href="#" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
                Mes prises
              </a>
              <a href="#" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
                Équipement
              </a>
            </div>
          </div>
        </div>
        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">
            <button class="bg-blue-700 p-1 rounded-full text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white">
              <span class="sr-only">Voir les notifications</span>
              <!-- Heroicon name: bell -->
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>

            <!-- Profile dropdown -->
            <div class="ml-3 relative">
              <div>
                <button @click="toggleProfileDropdown" class="max-w-xs bg-blue-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white">
                  <span class="sr-only">Ouvrir le menu utilisateur</span>
                  <svg class="h-8 w-8 rounded-full" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </button>
              </div>
              <!-- Dropdown menu -->
              <div v-if="profileDropdownOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Mon profil
                </a>
                <a href="/profile/password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Changer mot de passe
                </a>
                <template v-if="isAdmin">
                  <div class="border-t border-gray-100"></div>
                  <a href="/admin/users" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Gestion des utilisateurs
                  </a>
                </template>
                <div class="border-t border-gray-100"></div>
                <form method="POST" action="/logout">
                  <input type="hidden" name="_token" :value="csrfToken">
                  <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Déconnexion
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="-mr-2 flex md:hidden">
          <!-- Mobile menu button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="bg-blue-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white">
            <span class="sr-only">Ouvrir le menu principal</span>
            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div v-if="mobileMenuOpen" class="md:hidden">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <a href="#" class="block px-3 py-2 rounded-md text-base font-medium bg-blue-900 text-white">
          Accueil
        </a>
        <a href="/fishing-spots" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
          Spots de pêche
        </a>
        <a href="/fish-species" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
          Espèces de poissons
        </a>
        <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
          Mes prises
        </a>
        <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
          Équipement
        </a>
      </div>
      <div class="pt-4 pb-3 border-t border-blue-700">
        <div class="flex items-center px-5">
          <div class="flex-shrink-0">
            <svg class="h-10 w-10 rounded-full" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div class="ml-3">
            <div class="text-base font-medium leading-none text-white">{{ userName }}</div>
            <div class="text-sm font-medium leading-none text-gray-400">{{ userEmail }}</div>
          </div>
        </div>
        <div class="mt-3 px-2 space-y-1">
          <a href="/profile" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
            Mon profil
          </a>
          <a href="/profile/password" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
            Changer mot de passe
          </a>
          <template v-if="isAdmin">
            <a href="/admin/users" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
              Gestion des utilisateurs
            </a>
          </template>
          <form method="POST" action="/logout">
            <input type="hidden" name="_token" :value="csrfToken">
            <button type="submit" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-700 hover:text-white">
              Déconnexion
            </button>
          </form>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'NavigationMenu',
  data() {
    return {
      mobileMenuOpen: false,
      profileDropdownOpen: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      user: null,
      loading: true
    }
  },
  computed: {
    isAdmin() {
      return this.user && this.user.isAdmin;
    },
    userName() {
      return this.user ? this.user.name : 'Utilisateur';
    },
    userEmail() {
      return this.user ? this.user.email : 'utilisateur@exemple.com';
    }
  },
  methods: {
    toggleProfileDropdown() {
      this.profileDropdownOpen = !this.profileDropdownOpen;
    },
    closeProfileDropdown() {
      this.profileDropdownOpen = false;
    },
    fetchCurrentUser() {
      this.loading = true;
      fetch('/api/user')
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to fetch user data');
          }
          return response.json();
        })
        .then(data => {
          this.user = data;
          this.loading = false;
        })
        .catch(error => {
          console.error('Error fetching user data:', error);
          this.loading = false;
        });
    }
  },
  mounted() {
    // Fetch current user data
    this.fetchCurrentUser();

    // Add click outside listener to close dropdown
    document.addEventListener('click', (event) => {
      const dropdown = this.$el.querySelector('.ml-3.relative');
      if (dropdown && !dropdown.contains(event.target)) {
        this.closeProfileDropdown();
      }
    });
  },
  beforeUnmount() {
    // Remove click outside listener
    document.removeEventListener('click', this.closeProfileDropdown);
  }
}
</script>
