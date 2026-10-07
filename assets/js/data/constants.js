/**
 * Data: constants
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { getApiBase } from "../core/state.js";

export const API_BASE = getApiBase();

// ==========================================
// 1. CONSTANTS
// ==========================================
export const STORAGE_KEYS = {
  INKS: 'inventoryToners_v2',
  TRANSACTIONS: 'inventoryTransactions_v3',
  TICKETS: 'inventoryTickets_v2',
  SETTINGS: 'inventorySettings'
};

export const STOCK_STATUS = {
  IN_STOCK: 'IN STOCK',
  LOW_STOCK: 'LOW STOCK',
  OUT_OF_STOCK: 'OUT OF STOCK'
};

// Department → Location options for release destination
export const RELEASE_LOCATIONS = [
  { department: 'ACCT', location: 'Acctg Office' },
  { department: 'BD', location: 'BD Office' },
  { department: 'BMS', location: 'General Warehouse' },
  { department: 'LOGISTICS', location: 'Logistics Office' },
  { department: 'PRODUCTION', location: 'Production Office' },
  { department: 'PRODUCTION', location: 'Packaging Office' },
  { department: 'PURCHASING', location: 'Purchasing Office' },
  { department: 'QC', location: 'QC Laboratory' },
  { department: 'LOGISTICS', location: 'PM warehouse' }
];

