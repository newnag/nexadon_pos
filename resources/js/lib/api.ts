import axios from 'axios';
import type { AxiosInstance, AxiosError, InternalAxiosRequestConfig } from 'axios';

// Create axios instance with default config
const api: AxiosInstance = axios.create({
    baseURL: '/api',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true, // Send cookies for CSRF and session
});

// Request interceptor
api.interceptors.request.use(
    (config: InternalAxiosRequestConfig) => {
        // Get CSRF token from meta tag or cookie
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (csrfToken && config.headers) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error: AxiosError) => {
        // Handle common errors
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    // Unauthorized - redirect to login
                    console.error('Unauthorized access. Please log in.');
                    window.location.href = '/login';
                    break;
                case 403:
                    // Forbidden
                    console.error('Access forbidden. You do not have permission.');
                    break;
                case 404:
                    console.error('Resource not found.');
                    break;
                case 422:
                    // Validation error
                    console.error('Validation error:', error.response.data);
                    break;
                case 429:
                    // Too many requests
                    console.error('Too many requests. Please slow down.');
                    break;
                case 500:
                    console.error('Server error. Please try again later.');
                    break;
                default:
                    console.error('An error occurred:', error.message);
            }
        } else if (error.request) {
            // Request was made but no response received
            console.error('No response from server. Please check your connection.');
        } else {
            // Something else happened
            console.error('Error:', error.message);
        }

        return Promise.reject(error);
    }
);

export default api;

// Helper functions for common API calls

export const auth = {
    getCsrfCookie: () => axios.get('/sanctum/csrf-cookie'),
    login: (email: string, password: string) => api.post('/login', { email, password }),
    logout: () => api.post('/logout'),
    getUser: () => api.get('/user'),
};

export const menuItems = {
    getAll: (params?: any) => api.get('/menu-items', { params }),
    getOne: (id: number) => api.get(`/menu-items/${id}`),
    create: (data: any) => api.post('/menu-items', data),
    update: (id: number, data: any) => api.put(`/menu-items/${id}`, data),
    delete: (id: number) => api.delete(`/menu-items/${id}`),
};

export const orders = {
    getActive: () => api.get('/orders/active'),
    getOne: (id: number) => api.get(`/orders/${id}`),
    create: (data: any) => api.post('/orders', data),
    update: (id: number, data: any) => api.put(`/orders/${id}`, data),
};

export const payments = {
    process: (data: any) => api.post('/payments', data),
};
