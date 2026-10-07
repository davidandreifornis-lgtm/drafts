import { AppState, getApiBase } from "./state.js";

let _loadingDepth = 0;

export function showGlobalLoading(message) {
  _loadingDepth++;
  const el = document.getElementById("global-loading");
  const txt = document.getElementById("global-loading-text");
  if (txt && message) txt.textContent = message;
  else if (txt && _loadingDepth === 1) txt.textContent = "Processing…";
  if (el) {
    el.classList.remove("hidden");
    el.style.display = "flex";
  }
}

export function hideGlobalLoading() {
  _loadingDepth = Math.max(0, _loadingDepth - 1);
  if (_loadingDepth > 0) return;
  const el = document.getElementById("global-loading");
  if (el) {
    el.classList.add("hidden");
    el.style.display = "none";
  }
}

export function forceHideGlobalLoading() {
  _loadingDepth = 0;
  hideGlobalLoading();
}

export async function apiRequest(path, options = {}) {
  const silent = !!options.silent;
  const loadingMsg = options.loadingMessage || "Processing…";
  if (!silent) showGlobalLoading(loadingMsg);
  try {
    const base = (window.TONER_API_BASE || getApiBase() || "api").replace(/\/$/, "");
    const url = `${base}/${path.replace(/^\//, "")}`;
    console.info("[Toner] API", options.method || "GET", url);
    const opts = {
      credentials: "same-origin",
      headers: { "Content-Type": "application/json", ...(options.headers || {}) },
      ...options
    };
    delete opts.silent;
    delete opts.loadingMessage;
    if (opts.body && typeof opts.body === "object") {
      opts.body = JSON.stringify(opts.body);
    }
    let res;
    try {
      res = await fetch(url, opts);
    } catch (networkErr) {
      const err = new Error("Cannot reach API at " + url + " — is Apache running?");
      err.cause = networkErr;
      throw err;
    }
    let data = null;
    try { data = await res.json(); } catch (_) { data = null; }
    if (!res.ok || (data && data.ok === false)) {
      const err = new Error((data && data.error) || `Request failed (${res.status}) at ${url}`);
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  } finally {
    if (!silent) hideGlobalLoading();
  }
}

Object.assign(window, {
  apiRequest, showGlobalLoading, hideGlobalLoading, forceHideGlobalLoading
});
