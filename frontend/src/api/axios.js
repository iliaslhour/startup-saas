import axios from "axios"

const instance = axios.create({
  baseURL: "http://localhost:8000/api",
})

instance.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token")

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  config.headers.Accept = "application/json"

  return config
})

export default instance