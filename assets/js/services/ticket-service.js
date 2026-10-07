/**
 * Service: ticket-service
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { normalizeRefNumber } from "../core/format.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { DEMO_APPROVED_TICKETS } from "../data/demo-data.js";
import { pushNotification, renderAlerts } from "../layout/notifications-panel.js";
import { openDuplicateModal } from "../modals/duplicate.js";
import { StorageService, generateInkId, generateTransactionId } from "./storage-service.js";
import { renderCharts, renderDashboard, renderReports } from "../views/dashboard.js";
import { renderInventory } from "../views/inventory.js";
import { renderTransactions } from "../views/transactions.js";

// ==========================================
// 11. RECEIVE DELIVERY (TicketService Inbound)
// ==========================================
export const TicketService = {
  getTickets() {
    return StorageService.getTickets();
  },

  saveTickets(tickets) {
    StorageService.saveTickets(tickets);
  },

  findByReferenceNumber(refNumber) {
    // FUTURE BACKEND INTEGRATION:
    // Replace LocalStorage ticket lookup with a Fetch API request
    // to the PHP backend: GET /api/tickets.php?ref=${encodeURIComponent(refNumber)}
    const normalized = normalizeRefNumber(refNumber);
    const tickets = (AppState.tickets && AppState.tickets.length)
      ? AppState.tickets
      : (StorageService.getTickets() || []);
    let found = tickets.find(t => normalizeRefNumber(t.referenceNumber) === normalized);
    // Fallback to hardcoded demo tickets so samples always work
    if (!found) {
      found = DEMO_APPROVED_TICKETS.find(t => normalizeRefNumber(t.referenceNumber) === normalized) || null;
    }
    return found || null;
  },

  isAlreadyProcessed(refNumber) {
    const normalized = normalizeRefNumber(refNumber);
    const txns = StorageService.getTransactions();
    return txns.some(t => normalizeRefNumber(t.referenceNumber) === normalized);
  },

  processDeliveryTicket(ticket) {
    if (!ticket || !ticket.referenceNumber) {
      showToast('Missing delivery reference.', 'error');
      return false;
    }

    const ref = normalizeRefNumber(ticket.referenceNumber);

    // Step 2: Check for duplicate execution
    if (this.isAlreadyProcessed(ref)) {
      openDuplicateModal(ref, 'DELIVERY');
      return false;
    }

    // Step 3: Loop through every ticket item and increase inventory stock
    const inks = [...AppState.inks];
    const newTxns = [];
    const executionTime = new Date().toISOString();

    ticket.items.forEach(item => {
      let matchedInk = inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());

      // If SKU doesn't exist, create it from ticket metadata
      if (!matchedInk) {
        matchedInk = {
          id: generateInkId(),
          inkCode: item.inkCode,
          brand: item.brand || 'Generic',
          printerModel: item.printerModel || 'Compatible Model',
          color: item.color || 'Black',
          serialNumbers: [],
          quantity: 0,
          reorderLevel: 5,
          supplier: ticket.supplier || 'Standard Supplier',
          location: 'Main Storage',
          createdAt: executionTime,
          updatedAt: executionTime
        };
        inks.push(matchedInk);
      }

      // Quantity calculation: New Stock = Current + Ticket Qty
      const prevQty = Number(matchedInk.quantity) || 0;
      const addQty = Number(item.quantity) || 0;
      matchedInk.quantity = prevQty + addQty;
      matchedInk.updatedAt = executionTime;

      // Track serial numbers
      if (item.serialNumber) {
        matchedInk.serialNumbers = matchedInk.serialNumbers || [];
        if (!matchedInk.serialNumbers.includes(item.serialNumber)) {
          matchedInk.serialNumbers.push(item.serialNumber);
        }
      }

      // Create one transaction record per item while maintaining the same Reference Number
      const txnRecord = {
        id: generateTransactionId() + '-' + Math.random().toString(36).substring(2, 5).toUpperCase(),
        type: 'RECEIVED',
        referenceNumber: ticket.referenceNumber,
        inkId: matchedInk.id,
        inkCode: matchedInk.inkCode,
        serialNumber: item.serialNumber || (matchedInk.serialNumbers[0] || 'N/A'),
        brand: matchedInk.brand,
        color: matchedInk.color,
        quantity: addQty,
        date: ticket.date || executionTime.split('T')[0],
        supplier: ticket.supplier || 'N/A',
        givenTo: '',
        department: 'Main Storage',
        purpose: 'Stock delivery',
        status: 'APPROVED',
        createdAt: executionTime
      };

      newTxns.push(txnRecord);
    });

    // Step 4: Persist inventory and transactions
    StorageService.saveInks(inks);
    AppState.inks = inks;

    const allTxns = [...AppState.transactions, ...newTxns];
    StorageService.saveTransactions(allTxns);
    AppState.transactions = allTxns;

    // Step 5: Refresh UI and show feedback
    renderDashboard();
    renderInventory();
    renderTransactions();
    renderReports();
    renderCharts();
    renderAlerts();

    showToast(`Delivery ${ticket.referenceNumber} processed successfully! Stock incremented.`, 'success');
    pushNotification('success', 'Delivery Received', `Ticket ${ticket.referenceNumber} processed. Stock incremented.`, { source: 'action', referenceNumber: ticket.referenceNumber, action: { type: 'delivery' } });
    return true;
  },

  processReleaseTicket(ticket) {
    if (!ticket || !ticket.referenceNumber) {
      showToast('Missing issuance reference.', 'error');
      return false;
    }

    const ref = normalizeRefNumber(ticket.referenceNumber);

    // Step 2: Check for duplicate execution
    if (this.isAlreadyProcessed(ref)) {
      openDuplicateModal(ref, 'RELEASE');
      return false;
    }

    // Step 3: ATOMIC PRE-VALIDATION: Check available stock for ALL items first!
    const inks = [...AppState.inks];
    let validationFailed = false;
    let failureDetail = '';

    // Use primary item quantity (supports multi-unit issuance)
    const items = ticket.items && ticket.items.length ? ticket.items : [{ inkCode: 'UNKNOWN', quantity: 1 }];
    const primary = items[0];
    const requestedQty = Math.max(1, Number(primary.quantity) || 1);
    {
      const matched = inks.find(i => i.inkCode.toUpperCase() === (primary.inkCode || '').toUpperCase());
      const available = matched ? (Number(matched.quantity) || 0) : 0;
      const requested = requestedQty;

      if (!matched || available < requested) {
        validationFailed = true;
        failureDetail = `${primary.inkCode}: Need ${requested} unit(s), but only ${available} available in stock.`;
      }
    }

    if (validationFailed) {
      showToast(`Unable to process release: Insufficient stock. (${failureDetail})`, 'error');
      return false;
    }

    // Step 4: Atomic Execution — 1 ticket = 1 toner
    const newTxns = [];
    const executionTime = new Date().toISOString();
    const releaseItems = (ticket.items && ticket.items.length) ? [ticket.items[0]] : [];

    releaseItems.forEach(item => {
      const matched = inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
      const reqQty = Math.max(1, Number(item.quantity) || 1);

      // Stock Decrement by requested quantity
      matched.quantity = (Number(matched.quantity) || 0) - reqQty;
      matched.updatedAt = executionTime;

      // Release serial number from active pool
      if (item.serialNumber && Array.isArray(matched.serialNumbers)) {
        matched.serialNumbers = matched.serialNumbers.filter(sn => sn !== item.serialNumber);
      }

      // Record transaction
      const txnRecord = {
        id: generateTransactionId() + '-' + Math.random().toString(36).substring(2, 5).toUpperCase(),
        type: 'RELEASED',
        referenceNumber: ticket.referenceNumber,
        inkId: matched.id,
        inkCode: matched.inkCode,
        serialNumber: item.serialNumber || 'N/A',
        brand: matched.brand,
        color: matched.color,
        quantity: reqQty,
        date: ticket.date || executionTime.split('T')[0],
        supplier: '',
        givenTo: ticket.givenTo || 'Authorized Staff',
        department: (AppState.selectedReleaseDepartment || ticket.department || 'General Department'),
        location: (AppState.selectedReleaseLocation || ''),
        purpose: ticket.purpose || 'Toner Release',
        status: 'APPROVED',
        createdAt: executionTime
      };

      newTxns.push(txnRecord);
    });

    // Step 5: Save updated state
    StorageService.saveInks(inks);
    AppState.inks = inks;

    const allTxns = [...AppState.transactions, ...newTxns];
    StorageService.saveTransactions(allTxns);
    AppState.transactions = allTxns;

    // Step 6: Refresh UI
    renderDashboard();
    renderInventory();
    renderTransactions();
    renderReports();
    renderCharts();
    renderAlerts();

    showToast(`Toner release ${ticket.referenceNumber} processed successfully! Stock decremented.`, 'success');
    pushNotification('success', 'Toner Released', `Ticket ${ticket.referenceNumber} processed. Toner stock decremented.`, { source: 'action', referenceNumber: ticket.referenceNumber, action: { type: 'issue' } });
    return true;
  },

  /**
   * Flag a completed issuance ticket as defective return.
   * Does NOT restock usable inventory.
   */
  processDefectiveReturn(issuanceRef, notes) {
    const ref = normalizeRefNumber(issuanceRef);
    if (!ref) {
      showToast('Enter an issuance ticket number.', 'warning');
      return false;
    }

    // Must already be a processed RELEASE
    const released = AppState.transactions.filter(
      t => t.type === 'RELEASED' && normalizeRefNumber(t.referenceNumber) === ref
    );
    if (released.length === 0) {
      showToast('No completed issuance found for this ticket.', 'error');
      return false;
    }

    // Already flagged?
    const already = AppState.transactions.some(
      t => t.type === 'DEFECTIVE' && normalizeRefNumber(t.referenceNumber) === ref
    );
    if (already) {
      showToast('This issuance was already flagged as defective.', 'warning');
      return false;
    }

    const primary = released[0];
    const executionTime = new Date().toISOString();

    // Mark original release rows
    const updated = AppState.transactions.map(t => {
      if (t.type === 'RELEASED' && normalizeRefNumber(t.referenceNumber) === ref) {
        return { ...t, defective: true, defectiveAt: executionTime, defectiveNotes: notes || '' };
      }
      return t;
    });

    const defTxn = {
      id: (typeof generateTransactionId === 'function' ? generateTransactionId() : ('TXN-' + Date.now())) + '-DEF',
      type: 'DEFECTIVE',
      referenceNumber: primary.referenceNumber,
      inkId: primary.inkId || '',
      inkCode: primary.inkCode,
      serialNumber: primary.serialNumber || 'N/A',
      brand: primary.brand || '',
      color: primary.color || '',
      quantity: Math.max(1, Number(primary.quantity) || 1),
      date: executionTime.split('T')[0],
      supplier: '',
      givenTo: primary.givenTo || '',
      department: primary.department || '',
      location: primary.location || '',
      purpose: notes || 'Defective return',
      status: 'DEFECTIVE',
      defective: true,
      createdAt: executionTime
    };

    const allTxns = [...updated, defTxn];
    StorageService.saveTransactions(allTxns);
    AppState.transactions = allTxns;

    renderDashboard();
    renderInventory();
    renderTransactions();
    renderCharts();
    renderAlerts();

    showToast(`Issuance ${ref} flagged as defective.`, 'success');
    pushNotification('warning', 'Defective Return', `Ticket ${ref} (${primary.inkCode}) marked defective.`, { source: 'action', referenceNumber: ref, inkCode: primary.inkCode, action: { type: 'issue' } });
    return true;
  }
};

