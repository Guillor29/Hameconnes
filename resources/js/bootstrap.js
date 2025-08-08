import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global Mapbox token configuration
// This ensures the token is always available, even if the environment variable is not properly passed
window.MAPBOX_TOKEN = import.meta.env.VITE_MAPBOX_TOKEN || 'pk.eyJ1IjoiZ3VpbGxvciIsImEiOiJjazI0d25kZncyNnU5M2NtdmphaDR0bHcwIn0.XyxG_qYLs_RrQOwkFFomQg';

// Log the token for debugging
console.log('Global MAPBOX_TOKEN set:', window.MAPBOX_TOKEN);
