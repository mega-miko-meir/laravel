import axios from "axios";
import { configs } from "eslint-plugin-react-refresh";

const axiosClient = axios.create({
    baseURL: "http://localhost:8000/",
    // baseURL: "http://127.0.0.1:8000/",
    withCredentials: true,
    headers: {
        "Content-Type": "application/json",
    },
});

axiosClient.interceptors.request.use((config) => {
    const token = localStorage.getItem("ACCESS_TOKEN"); // Adjust the key name as needed
    config.headers.Authorization = `Bearer ${token}`;
    return config;
});

axiosClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            // Handle unauthorized access, e.g., redirect to login
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);

export default axiosClient;
