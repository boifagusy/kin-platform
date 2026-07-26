const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8001/api/v1';

const buildHeaders = (extra = {}) => {
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...extra,
  };
  const token = localStorage.getItem('kin_token');
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  return headers;
};

const handleFetchResponse = async (response) => {
  const contentType = response.headers.get('content-type');
  const isJson = contentType && contentType.includes('application/json');

  if (!response.ok) {
    if (isJson) {
      const error = await response.json();
      throw new Error(error.message || error.error || `HTTP ${response.status}`);
    }
    throw new Error(`HTTP ${response.status}`);
  }

  if (isJson) {
    return await response.json();
  }
  return response;
};

export async function get(endpoint, options = {}) {
  const response = await fetch(`${API_BASE}${endpoint}`, {
    method: 'GET',
    headers: buildHeaders(options.headers),
    ...options,
  });
  return handleFetchResponse(response);
}

export async function post(endpoint, body = null, options = {}) {
  const response = await fetch(`${API_BASE}${endpoint}`, {
    method: 'POST',
    headers: buildHeaders(options.headers),
    body: body instanceof FormData ? body : JSON.stringify(body),
    ...options,
  });
  return handleFetchResponse(response);
}

export async function put(endpoint, body = null, options = {}) {
  const response = await fetch(`${API_BASE}${endpoint}`, {
    method: 'PUT',
    headers: buildHeaders(options.headers),
    body: JSON.stringify(body),
    ...options,
  });
  return handleFetchResponse(response);
}

export async function patch(endpoint, body = null, options = {}) {
  const response = await fetch(`${API_BASE}${endpoint}`, {
    method: 'PATCH',
    headers: buildHeaders(options.headers),
    body: JSON.stringify(body),
    ...options,
  });
  return handleFetchResponse(response);
}

export async function httpDelete(endpoint, options = {}) {
  const response = await fetch(`${API_BASE}${endpoint}`, {
    method: 'DELETE',
    headers: buildHeaders(options.headers),
    ...options,
  });
  return handleFetchResponse(response);
}

export const platformClient = {
  get,
  post,
  put,
  patch,
  delete: httpDelete,
};

export default platformClient;

export { httpDelete as delete };
