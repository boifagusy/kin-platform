// Centralized API configuration
// All screens should import API_BASE from here instead of redefining it.
export const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1";

// Helper for building auth headers consistently.
export function authHeaders(extra = {}) {
  return {
    "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
    "Accept": "application/json",
    ...extra,
  };
}
