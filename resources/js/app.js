import './bootstrap';
import { apiRequest, showModal, showToast } from './api.js';

// Exemplo de uso global
window.apiRequest = apiRequest;
window.showModal = showModal;
window.showToast = showToast;
