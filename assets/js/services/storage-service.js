/**
 * Service: storage-service
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { STORAGE_KEYS } from "../data/constants.js";
import { DEMO_APPROVED_TICKETS, DEMO_INITIAL_INVENTORY, DEMO_INITIAL_TRANSACTIONS } from "../data/demo-data.js";

export const StorageService = {
  getInks() {
    // FUTURE BACKEND INTEGRATION:
    // Replace this LocalStorage operation with a Fetch API request
    // to a PHP backend endpoint: GET /api/inventory.php connected to MySQL.
    try {
      const data = localStorage.getItem(STORAGE_KEYS.INKS);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error("StorageService.getInks error:", e);
      return [];
    }
  },

  saveInks(inks) {
    // FUTURE BACKEND INTEGRATION:
    // Replace with Fetch API PUT/POST to /api/inventory.php
    try {
      localStorage.setItem(STORAGE_KEYS.INKS, JSON.stringify(inks));
    } catch (e) {
      console.error("StorageService.saveInks error:", e);
    }
  },

  getTransactions() {
    // FUTURE BACKEND INTEGRATION:
    // Replace with Fetch API GET /api/transactions.php
    try {
      const data = localStorage.getItem(STORAGE_KEYS.TRANSACTIONS);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error("StorageService.getTransactions error:", e);
      return [];
    }
  },

  saveTransactions(transactions) {
    // FUTURE BACKEND INTEGRATION:
    // Replace with Fetch API POST /api/transactions.php
    try {
      localStorage.setItem(STORAGE_KEYS.TRANSACTIONS, JSON.stringify(transactions));
    } catch (e) {
      console.error("StorageService.saveTransactions error:", e);
    }
  },

  getTickets() {
    // FUTURE BACKEND INTEGRATION:
    // Replace LocalStorage ticket lookup with a Fetch API request
    // to the PHP backend: GET /api/tickets.php
    try {
      const data = localStorage.getItem(STORAGE_KEYS.TICKETS);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error("StorageService.getTickets error:", e);
      return [];
    }
  },

  saveTickets(tickets) {
    try {
      localStorage.setItem(STORAGE_KEYS.TICKETS, JSON.stringify(tickets));
    } catch (e) {
      console.error("StorageService.saveTickets error:", e);
    }
  },

  getSettings() {
    try {
      const data = localStorage.getItem(STORAGE_KEYS.SETTINGS);
      return data ? JSON.parse(data) : null;
    } catch (e) {
      return null;
    }
  },

  saveSettings(settings) {
    try {
      localStorage.setItem(STORAGE_KEYS.SETTINGS, JSON.stringify(settings));
    } catch (e) {
      console.error("StorageService.saveSettings error:", e);
    }
  },

  resetAllDemoData() {
    localStorage.setItem(STORAGE_KEYS.INKS, JSON.stringify(DEMO_INITIAL_INVENTORY));
    localStorage.setItem(STORAGE_KEYS.TRANSACTIONS, JSON.stringify(DEMO_INITIAL_TRANSACTIONS));
    localStorage.setItem(STORAGE_KEYS.TICKETS, JSON.stringify(DEMO_APPROVED_TICKETS));
  }
};

// ==========================================
// 4. DATA INITIALIZATION
// ==========================================
export function initializeDataSafely() {
  const existingInks = StorageService.getInks();
  if (!existingInks || existingInks.length === 0) {
    StorageService.saveInks(DEMO_INITIAL_INVENTORY);
  }

  const existingTransactions = StorageService.getTransactions();
  if (!existingTransactions || existingTransactions.length === 0) {
    StorageService.saveTransactions(DEMO_INITIAL_TRANSACTIONS);
  }

  // Always ensure demo tickets exist (merge by reference number)
  // so sample valid tickets keep working even with older localStorage data
  let existingTickets = StorageService.getTickets() || [];
  const byRef = new Map(existingTickets.map(t => [normalizeRefNumber(t.referenceNumber), t]));
  DEMO_APPROVED_TICKETS.forEach(demo => {
    const key = normalizeRefNumber(demo.referenceNumber);
    if (!byRef.has(key)) {
      existingTickets.push(demo);
      byRef.set(key, demo);
    }
  });
  StorageService.saveTickets(existingTickets);

  // Load into AppState
  AppState.inks = StorageService.getInks();
  AppState.transactions = StorageService.getTransactions();
  AppState.tickets = StorageService.getTickets();
}

// ==========================================
// 5. UTILITY FUNCTIONS
// ==========================================

/** Philippine Time (Asia/Manila) — 12-hour clock */






// ==========================================
// 6. ID GENERATION
// ==========================================
export function generateTransactionId() {
  const current = AppState.transactions.length + 1;
  return 'TXN-' + String(current).padStart(5, '0');
}

export function generateInkId() {
  const current = AppState.inks.length + 1;
  return 'INK-' + String(current).padStart(3, '0');
}

Object.assign(window, {
  initializeDataSafely,
  generateTransactionId,
  generateInkId
});
