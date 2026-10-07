export const APP_TIMEZONE = "Asia/Manila";

export function escapeHTML(str) {
  if (typeof str !== "string") return String(str ?? "");
  return str.replace(/[&<>'"]/g, (tag) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    "'": "&#39;",
    '"': "&quot;"
  }[tag] || tag));
}

export function toManilaDate(input) {
  if (input == null || input === "") return null;
  let s = String(input).trim();
  if (/^\d{4}-\d{2}-\d{2}$/.test(s)) s += "T00:00:00+08:00";
  else if (/^\d{4}-\d{2}-\d{2} /.test(s)) s = s.replace(" ", "T") + "+08:00";
  const d = new Date(s);
  return Number.isNaN(d.getTime()) ? null : d;
}

export function formatDate(dateStr) {
  if (!dateStr) return "—";
  try {
    const d = toManilaDate(dateStr);
    if (!d) return String(dateStr);
    return d.toLocaleDateString("en-PH", {
      timeZone: APP_TIMEZONE,
      year: "numeric",
      month: "short",
      day: "numeric"
    });
  } catch (_) {
    return String(dateStr);
  }
}

export function formatDateTime(dateStr) {
  if (!dateStr) return "—";
  try {
    const d = toManilaDate(dateStr);
    if (!d) return String(dateStr);
    return d.toLocaleString("en-PH", {
      timeZone: APP_TIMEZONE,
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit"
    });
  } catch (_) {
    return String(dateStr);
  }
}

export function normalizeRefNumber(ref) {
  return String(ref || "").trim().toUpperCase();
}

export function phTodayYmd() {
  const parts = new Intl.DateTimeFormat("en-CA", {
    timeZone: APP_TIMEZONE,
    year: "numeric",
    month: "2-digit",
    day: "2-digit"
  }).formatToParts(new Date());
  const y = parts.find((p) => p.type === "year")?.value;
  const m = parts.find((p) => p.type === "month")?.value;
  const d = parts.find((p) => p.type === "day")?.value;
  return `${y}-${m}-${d}`;
}

// window bridges during migration
Object.assign(window, {
  escapeHTML, formatDate, formatDateTime, normalizeRefNumber, toManilaDate, APP_TIMEZONE, phTodayYmd
});
