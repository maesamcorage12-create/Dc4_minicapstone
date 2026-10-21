import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('tripcraft_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export function errorMessage(error) {
    return (
        error?.response?.data?.message ||
        Object.values(error?.response?.data?.errors || {}).flat()[0] ||
        error?.message ||
        'Something went wrong. Please try again.'
    );
}

export default api;
