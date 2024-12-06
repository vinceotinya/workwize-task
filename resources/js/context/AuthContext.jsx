import { useNavigate } from 'react-router-dom';
import axiosClient from '../utils/axios.js';
import { createContext, useContext, useEffect, useRef, useState } from 'react';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [token, setToken] = useState(null);
  const [user, setUser] = useState(null);
  const login = async ({ email, password }) => {
    try {
      const res = await axiosClient.post('/auth/login', { email, password })
      setToken(res.data.data.access_token);
    } catch (e) {
      alert(e.message)
    }
  };

  const logout = () => {
    setToken(null);
  };

  const isFirstRender = useRef(true);
  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
    } else {
      localStorage.setItem('token', token || '');
    }

    axiosClient.defaults.headers.common = {
      Authorization: `Bearer ${token}`,
    }
    if (token) {
      (async () => {
        const me = (await axiosClient.get('/auth/me')).data.data;
        setUser(me);
      })()
    } else {
      setUser(null);
    }
  }, [token]);

  useEffect(() => {
    const token = localStorage.getItem('token');
    setToken(token || null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, isAuthenticated: !!token, login, logout, setToken }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);