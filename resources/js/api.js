import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Response interceptor for error handling
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Server responded with error status
            const message = error.response.data.message || 'An error occurred';
            console.error('API Error:', message, error.response.data);
        } else if (error.request) {
            // Request made but no response received
            console.error('Network Error:', error.request);
        } else {
            // Error in request setup
            console.error('Request Error:', error.message);
        }
        return Promise.reject(error);
    }
);

// Fund API methods
export const fundApi = {
    list(filters = {}) {
        return api.get('/funds', { params: filters });
    },
    get(id) {
        return api.get(`/funds/${id}`);
    },
    create(data) {
        return api.post('/funds', data);
    },
    update(id, data) {
        return api.put(`/funds/${id}`, data);
    },
    delete(id) {
        return api.delete(`/funds/${id}`);
    }
};

// Fund Manager API methods
export const fundManagerApi = {
    list() {
        return api.get('/fund-managers');
    },
    create(data) {
        return api.post('/fund-managers', data);
    },
    delete(id) {
        return api.delete(`/fund-managers/${id}`);
    }
};

// Company API methods
export const companyApi = {
    list() {
        return api.get('/companies');
    },
    create(data) {
        return api.post('/companies', data);
    },
    delete(id) {
        return api.delete(`/companies/${id}`);
    }
};

// Duplicate Warning API methods
export const duplicateWarningApi = {
    list() {
        return api.get('/duplicate-warnings');
    }
};

export default api;
