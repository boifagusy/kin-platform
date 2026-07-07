// Persistent auth storage — uses both localStorage and cookie for resilience
// against Android Chrome tab-kill clearing localStorage

export function saveAuth(phone, token) {
  localStorage.setItem("kin_phone", phone);
  localStorage.setItem("kin_token", token);
  // Cookie fallback — expires in 30 days
  const expires = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString();
  document.cookie = `kin_phone=${encodeURIComponent(phone)}; expires=${expires}; path=/; SameSite=Strict`;
  document.cookie = `kin_token=${encodeURIComponent(token)}; expires=${expires}; path=/; SameSite=Strict`;
}

export function getPhone() {
  return localStorage.getItem("kin_phone") || getCookie("kin_phone");
}

export function getToken() {
  return localStorage.getItem("kin_token") || getCookie("kin_token");
}

export function clearAuth() {
  localStorage.removeItem("kin_phone");
  localStorage.removeItem("kin_token");
  document.cookie = "kin_phone=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  document.cookie = "kin_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

function getCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
}
