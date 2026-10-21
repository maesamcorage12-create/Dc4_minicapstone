import React, { createContext, useContext, useEffect, useState } from 'react';
import api from '../services/api';

const AuthContext = createContext(null);

function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const token = localStorage.getItem('tripcraft_token');
        if (token) {
            api.get('/user')
                .then((r) => setUser(r.data))
                .catch(() => {
                    localStorage.removeItem('tripcraft_token');
                    setUser(null);
                })
                .finally(() => setLoading(false));
        } else {
            setLoading(false);
        }
    }, []);

    const login = async (credentials) => {
        const r = await api.post('/login', credentials);
        localStorage.setItem('tripcraft_token', r.data.token);
        setUser(r.data.user);
        return r.data.user;
    };

    const register = async (data) => {
        const r = await api.post('/register', data);
        localStorage.setItem('tripcraft_token', r.data.token);
        setUser(r.data.user);
        return r.data.user;
    };

    const logout = async () => {
        try {
            await api.post('/logout');
        } finally {
            localStorage.removeItem('tripcraft_token');
            setUser(null);
        }
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, register, logout, setUser }}>
            {children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => useContext(AuthContext);

export default AuthProvider;
