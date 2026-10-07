/**
 * Modal: release
 * Split out of the former app-logic.js — behavior unchanged.
 */
import { apiRequest } from "../core/api.js";
import { escapeHTML, formatDate, normalizeRefNumber } from "../core/format.js";
import { navigateTo } from "../core/router.js";
import { AppState } from "../core/state.js";
import { showToast } from "../core/toast.js";
import { RELEASE_LOCATIONS } from "../data/constants.js";
import { updateSidebarUser } from "../layout/sidebar.js";
import { closeLocationsModal, saveLocationRow } from "./locations.js";
import { openReceiveModal, searchAndDisplayDeliveryModal } from "./receive.js";
import { closeReleaseDetailModal } from "./release-detail.js";
import { resolveDepartmentCode } from "../services/stock-utils.js";
import { TicketService } from "../services/ticket-service.js";
import { populateTonerSelect } from "../views/receive.js";
import { recordManualIssuance } from "../views/release.js";

export async function openReleaseModal() {
  document.getElementById('release-step-form')?.classList.remove('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  populateTonerSelect('modal-rel-toner', true);
  await loadReleaseLocations();
  await loadAdminUsersForIssuance();
  populateDeptSelect();
  populateIssuedBySelect();
  const ref = document.getElementById('modal-rel-ref');
  const dept = document.getElementById('modal-rel-dept');
  const date = document.getElementById('modal-rel-date');
  const hint = document.getElementById('modal-rel-stock-hint');
  const hasYieldEl = document.getElementById('modal-rel-has-yield');
  if (hasYieldEl) hasYieldEl.checked = false;
  const yieldWrap = document.getElementById('modal-rel-yield-wrap');
  if (yieldWrap) yieldWrap.classList.add('hidden');
  const yieldEl = document.getElementById('modal-rel-yield');
  const printerEl = document.getElementById('modal-rel-printer');
  if (ref) ref.value = '';
  if (dept) dept.value = '';
  if (date) date.value = new Date().toISOString().split('T')[0];
  if (hint) hint.textContent = '';
  if (yieldEl) yieldEl.value = '';
  if (printerEl) printerEl.value = '';
  const qtyEl = document.getElementById('modal-rel-qty');
  if (qtyEl) qtyEl.value = '1';
  fillLocationsForDept('', 'modal-rel-location', 'm-rel-location-auto', 'm-rel-location-auto-text');
  const modal = document.getElementById('modal-release');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  setTimeout(() => ref?.focus(), 80);
}

export function closeReleaseModal() {
  const modal = document.getElementById('modal-release');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.activeReleaseTicket = null;
}

export function resetReleaseModal() {
  document.getElementById('release-step-search')?.classList.remove('hidden');
  document.getElementById('release-step-error')?.classList.add('hidden');
  document.getElementById('release-step-preview')?.classList.add('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  AppState.activeReleaseTicket = null;
  AppState.selectedReleaseLocation = '';
  AppState.selectedReleaseDepartment = '';
  const loc = document.getElementById('modal-rel-location');
  if (loc) loc.value = '';
}

export function populateReleaseLocationsForDepartment(ticketDept) {
  const select = document.getElementById('modal-rel-location');
  const hint = document.getElementById('m-rel-location-hint');
  const autoBox = document.getElementById('m-rel-location-auto');
  const autoText = document.getElementById('m-rel-location-auto-text');
  const help = document.getElementById('m-rel-location-help');
  if (!select) return;

  const code = resolveDepartmentCode(ticketDept);
  select.innerHTML = '<option value="">— Select location —</option>';
  select.classList.remove('hidden');
  if (autoBox) autoBox.classList.add('hidden');
  if (hint) {
    hint.classList.add('hidden');
    hint.classList.remove('text-rose-600');
  }

  let options = [];
  if (code) {
    options = RELEASE_LOCATIONS.filter(r => r.department === code);
  }

  if (options.length === 0) {
    if (hint) {
      hint.textContent = ticketDept
        ? `No mapped locations for department “${ticketDept}”. Check the ticket department or master list.`
        : 'Ticket has no department. Cannot select a location.';
      hint.classList.remove('hidden');
      hint.classList.add('text-rose-600');
    }
    select.disabled = true;
    select.value = '';
    if (help) help.classList.remove('hidden');
    return;
  }

  // Only one office for this department → auto-apply, hide dropdown
  if (options.length === 1) {
    const only = options[0];
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = only.location;
    opt.setAttribute('data-dept', only.department);
    opt.textContent = `${only.department} — ${only.location}`;
    select.appendChild(opt);
    select.value = only.location;
    select.classList.add('hidden');
    select.disabled = false;
    if (autoBox) autoBox.classList.remove('hidden');
    if (autoText) autoText.textContent = `${only.department} — ${only.location}`;
    if (help) help.classList.add('hidden');
    if (hint) hint.classList.add('hidden');
    AppState.selectedReleaseLocation = only.location;
    AppState.selectedReleaseDepartment = only.department;
    return;
  }

  // Multiple locations → show dropdown to choose
  options.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.location;
    opt.setAttribute('data-dept', r.department);
    opt.textContent = `${r.department} — ${r.location}`;
    select.appendChild(opt);
  });
  select.disabled = false;
  select.classList.remove('hidden');
  if (autoBox) autoBox.classList.add('hidden');
  if (help) {
    help.textContent = `Select where the toner will be issued for ${code}.`;
    help.classList.remove('hidden');
  }
  if (hint) {
    hint.textContent = `${options.length} locations for ${code}. Choose one.`;
    hint.classList.remove('hidden', 'text-rose-600');
  }
  AppState.selectedReleaseLocation = '';
  AppState.selectedReleaseDepartment = code || '';
}

export function showReleaseError(ref, title, desc) {
  const errRef = document.getElementById('release-error-ref');
  const errTitle = document.getElementById('release-error-title');
  const errDesc = document.getElementById('release-error-desc');
  if (errRef) errRef.textContent = ref || '—';
  if (errTitle) errTitle.textContent = title || 'Ticket Not Found';
  if (errDesc) errDesc.textContent = desc || 'No approved release ticket matches this reference.';

  document.getElementById('release-step-search')?.classList.remove('hidden');
  document.getElementById('release-step-error')?.classList.remove('hidden');
  document.getElementById('release-step-preview')?.classList.add('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  AppState.activeReleaseTicket = null;

  document.getElementById('release-step-error')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

export function searchAndDisplayReleaseModal(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a release reference number.', 'warning');
    return;
  }

  if (TicketService.isAlreadyProcessed(normalized)) {
    showReleaseError(
      normalized,
      'Ticket Already Processed',
      'This release ticket was already executed. Duplicate stock movements are blocked.'
    );
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  // Not found, wrong type, or not approved → error, no process button
  if (!ticket || ticket.type !== 'RELEASE' || ticket.status !== 'APPROVED') {
    showReleaseError(
      normalized,
      'Ticket Not Found',
      'This ticket was not found or is not approved yet. Confirm & Process is unavailable.'
    );
    return;
  }

  AppState.activeReleaseTicket = ticket;

  document.getElementById('m-rel-ref').textContent = ticket.referenceNumber;
  document.getElementById('m-rel-date').textContent = formatDate(ticket.date);
  document.getElementById('m-rel-dept').textContent = ticket.department || 'General';
  document.getElementById('m-rel-givento').textContent = ticket.givenTo || 'Staff';
  document.getElementById('m-rel-purpose').textContent = ticket.purpose || '';

  // Only show locations that belong to this ticket's department
  populateReleaseLocationsForDepartment(ticket.department);

  let totalQty = 0;
  let hasInsufficient = false;
  const tbody = document.getElementById('m-rel-tbody');
  // 1 ticket = 1 toner: only show primary item at quantity 1
  const displayItems = (ticket.items && ticket.items.length) ? [ticket.items[0]] : [];
  tbody.innerHTML = displayItems.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const available = matched ? (Number(matched.quantity) || 0) : 0;
    const req = 1;
    const after = available - req;
    totalQty += req;
    const isShort = after < 0;
    if (isShort) hasInsufficient = true;
    const statusBadge = isShort
      ? '<span class="text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-700">Insufficient</span>'
      : '<span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">OK</span>';
    return `<tr class="${isShort ? 'bg-rose-50' : ''}">
      <td class="px-3 py-2 font-mono font-semibold">${escapeHTML(item.inkCode)}</td>
      <td class="px-3 py-2">${escapeHTML(item.brand || matched?.brand || '—')}</td>
      <td class="px-3 py-2 text-right font-mono">${available}</td>
      <td class="px-3 py-2 text-right font-bold font-mono text-emerald-600">-${req}</td>
      <td class="px-3 py-2 text-right font-mono ${isShort ? 'text-rose-600 font-bold' : ''}">${after}</td>
      <td class="px-3 py-2 text-center">${statusBadge}</td>
    </tr>`;
  }).join('');

  document.getElementById('m-rel-total-items').textContent = 1;
  document.getElementById('m-rel-total-qty').textContent = 1;

  const errorBox = document.getElementById('m-rel-stock-error');
  const badge = document.getElementById('m-rel-validation-badge');
  const processBtn = document.getElementById('btn-modal-process-rel');
  const tryAnotherBtn = document.getElementById('btn-release-try-another');
  const validatedBanner = document.getElementById('m-rel-validated-banner');

  if (hasInsufficient) {
    if (errorBox) {
      errorBox.textContent = 'Insufficient stock for one or more items. Cannot process this release.';
      errorBox.classList.remove('hidden');
    }
    if (badge) {
      badge.textContent = 'Stock Insufficient';
      badge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-rose-100 text-rose-800';
    }
    // Hide Confirm & Process — show Try another ticket instead
    if (processBtn) processBtn.classList.add('hidden');
    if (tryAnotherBtn) tryAnotherBtn.classList.remove('hidden');
    if (validatedBanner) {
      validatedBanner.className = 'flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800';
      validatedBanner.innerHTML = `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div><div class="text-sm font-bold">Cannot Process — Stock Insufficient</div>
        <div class="text-xs opacity-80">Inventory must be replenished before this release can be executed.</div></div>`;
    }
    // Preview panel border red
    const preview = document.getElementById('release-step-preview');
    if (preview) {
      preview.classList.remove('border-emerald-200');
      preview.classList.add('border-rose-200');
    }
  } else {
    if (errorBox) errorBox.classList.add('hidden');
    if (badge) {
      badge.textContent = 'Stock Verified';
      badge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-emerald-100 text-emerald-800';
    }
    if (processBtn) {
      processBtn.classList.remove('hidden');
      processBtn.disabled = false;
    }
    if (tryAnotherBtn) tryAnotherBtn.classList.add('hidden');
    if (validatedBanner) {
      validatedBanner.className = 'flex items-center gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800';
      validatedBanner.innerHTML = `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div><div class="text-sm font-bold">Ticket Validated</div>
        <div class="text-xs opacity-80">Approved release ticket found. Review stock impact below, then confirm to decrement inventory.</div></div>`;
    }
    const preview = document.getElementById('release-step-preview');
    if (preview) {
      preview.classList.remove('border-rose-200');
      preview.classList.add('border-emerald-200');
    }
  }

  document.getElementById('release-step-search')?.classList.remove('hidden');
  document.getElementById('release-step-error')?.classList.add('hidden');
  document.getElementById('release-step-preview')?.classList.remove('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  document.getElementById('release-step-preview')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ---------- Locations (editable dept / location / printer) ----------
export async function loadReleaseLocations() {
  try {
    const data = await apiRequest('locations.php');
    AppState.releaseLocations = data.locations || [];
  } catch (e) {
    console.warn('[Toner] locations load failed, using static list', e);
    AppState.releaseLocations = (typeof RELEASE_LOCATIONS !== 'undefined' ? RELEASE_LOCATIONS : []).map((r, i) => ({
      id: -(i + 1),
      department: r.department,
      location: r.location,
      printerName: r.printerName || '',
      isActive: true
    }));
  }
}

export async function loadAdminUsersForIssuance() {
  try {
    const data = await apiRequest('users.php');
    AppState.adminUsers = (data.users || []).filter(u => u.isActive !== false);
  } catch (e) {
    AppState.adminUsers = [];
  }
  try {
    const me = await apiRequest('me.php', { silent: true });
    AppState.currentUser = me;
  } catch (e) {
    AppState.currentUser = { username: 'admin', fullName: 'Admin' };
  }
  updateSidebarUser();
}

export function populateDeptSelect() {
  const sel = document.getElementById('modal-rel-dept');
  if (!sel) return;
  const current = sel.value;
  const depts = [...new Set((AppState.releaseLocations || []).map(r => (r.department || '').toUpperCase()).filter(Boolean))].sort();
  sel.innerHTML = '<option value="">— Select department —</option>' +
    depts.map(d => `<option value="${escapeHTML(d)}">${escapeHTML(d)}</option>`).join('');
  if (current && depts.includes(current)) sel.value = current;
}

export function populateIssuedBySelect() {
  const sel = document.getElementById('modal-rel-issued-by');
  const recorded = document.getElementById('modal-rel-recorded-by');
  if (recorded) {
    const me = AppState.currentUser;
    recorded.value = me ? (me.fullName ? `${me.fullName} (${me.username})` : (me.username || '')) : '';
  }
  if (!sel) return;
  const users = AppState.adminUsers || [];
  const meUser = AppState.currentUser?.username || '';
  sel.innerHTML = '<option value="">— Select admin —</option>' +
    users.map(u => {
      const label = u.fullName ? `${u.fullName} (${u.username})` : u.username;
      return `<option value="${escapeHTML(u.username)}">${escapeHTML(label)}</option>`;
    }).join('');
  if (meUser) sel.value = meUser;
}

export function fillLocationsForDept(deptCode, selectId, autoWrapId, autoTextId) {
  const select = document.getElementById(selectId);
  const autoBox = document.getElementById(autoWrapId);
  const autoText = document.getElementById(autoTextId);
  const printerEl = document.getElementById('modal-rel-printer');
  if (!select) return;
  select.innerHTML = '<option value="">— Select location —</option>';
  select.classList.remove('hidden');
  if (autoBox) autoBox.classList.add('hidden');
  if (printerEl) printerEl.value = '';
  const code = (deptCode || '').toUpperCase();
  const options = (AppState.releaseLocations || []).filter(r => (r.department || '').toUpperCase() === code);
  if (!code) {
    select.innerHTML = '<option value="">— Select department first —</option>';
    return;
  }
  if (options.length === 0) {
    select.innerHTML = '<option value="">— No locations — add under Locations & Suppliers —</option>';
    return;
  }
  if (options.length === 1) {
    const only = options[0];
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = only.location;
    opt.textContent = only.location;
    opt.dataset.printer = only.printerName || '';
    select.appendChild(opt);
    select.value = only.location;
    select.classList.add('hidden');
    if (autoBox) autoBox.classList.remove('hidden');
    if (autoText) autoText.textContent = only.department + ' — ' + only.location;
    if (printerEl) printerEl.value = only.printerName || '—';
    return;
  }
  options.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.location;
    opt.textContent = r.location;
    opt.dataset.printer = r.printerName || '';
    select.appendChild(opt);
  });
}

export function onReleaseLocationChange() {
  const locSel = document.getElementById('modal-rel-location');
  const printerEl = document.getElementById('modal-rel-printer');
  if (!locSel || !printerEl) return;
  const opt = locSel.options[locSel.selectedIndex];
  printerEl.value = (opt && opt.dataset.printer) ? opt.dataset.printer : '—';
}

export function initReleaseModalListeners() {
  const quickRelease = document.getElementById('quick-btn-release');

  if (quickRelease) quickRelease.addEventListener('click', openReleaseModal);

  // ---------- Sample reference clicks (works in modals too) ----------
  document.addEventListener('click', (e) => {
    const el = e.target.closest('.sample-ref');
    if (!el) return;
    e.preventDefault();
    e.stopPropagation();
    const ref = el.getAttribute('data-ref');
    if (!ref) return;
    if (ref.startsWith('DEL-')) {
      const receiveModal = document.getElementById('modal-receive');
      const alreadyOpen = receiveModal && !receiveModal.classList.contains('hidden');
      if (!alreadyOpen) openReceiveModal();
      const input = document.getElementById('modal-del-ref');
      if (input) input.value = ref;
      // slight delay so modal is painted if just opened
      setTimeout(() => searchAndDisplayDeliveryModal(ref), alreadyOpen ? 0 : 50);
    } else if (ref.startsWith('REL-')) {
      const releaseModal = document.getElementById('modal-release');
      const alreadyOpen = releaseModal && !releaseModal.classList.contains('hidden');
      if (!alreadyOpen) openReleaseModal();
      const input = document.getElementById('modal-rel-ref');
      if (input) input.value = ref;
      setTimeout(() => searchAndDisplayReleaseModal(ref), alreadyOpen ? 0 : 50);
    }
  });

  // ---------- Release Modal ----------
  const btnCloseRelease = document.getElementById('btn-close-release-modal');

  if (btnCloseRelease) btnCloseRelease.addEventListener('click', closeReleaseModal);

  const btnModalSearchRel = document.getElementById('btn-modal-search-rel');

  const modalRelRef = document.getElementById('modal-rel-ref');

  if (btnModalSearchRel && modalRelRef) {
    btnModalSearchRel.addEventListener('click', () => searchAndDisplayReleaseModal(modalRelRef.value));
    modalRelRef.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchAndDisplayReleaseModal(modalRelRef.value);
      }
    });
  }

  const btnModalProcessRel = document.getElementById('btn-modal-process-rel');

  if (btnModalProcessRel) {
    btnModalProcessRel.addEventListener('click', recordManualIssuance);
  const hasYieldCb = document.getElementById('modal-rel-has-yield');
  if (hasYieldCb) {
    hasYieldCb.addEventListener('change', () => {
      const wrap = document.getElementById('modal-rel-yield-wrap');
      if (wrap) wrap.classList.toggle('hidden', !hasYieldCb.checked);
      if (!hasYieldCb.checked) {
        const y = document.getElementById('modal-rel-yield');
        if (y) y.value = '';
      } else {
        document.getElementById('modal-rel-yield')?.focus();
      }
    });
  }


  const relDept = document.getElementById('modal-rel-dept');
  if (relDept) {
    relDept.addEventListener('change', () => {
      fillLocationsForDept(relDept.value, 'modal-rel-location', 'm-rel-location-auto', 'm-rel-location-auto-text');
    });
  }
  const relLoc = document.getElementById('modal-rel-location');
  if (relLoc) relLoc.addEventListener('change', onReleaseLocationChange);
  const btnCloseLoc = document.getElementById('btn-close-locations');
  if (btnCloseLoc) btnCloseLoc.addEventListener('click', closeLocationsModal);
  const btnLocSave = document.getElementById('btn-loc-save');
  if (btnLocSave) btnLocSave.addEventListener('click', saveLocationRow);
  const btnLocClear = document.getElementById('btn-loc-clear');
  if (btnLocClear) btnLocClear.addEventListener('click', () => {
    document.getElementById('loc-edit-id').value = '';
    document.getElementById('loc-edit-dept').value = '';
    document.getElementById('loc-edit-location').value = '';
    document.getElementById('loc-edit-printer').value = '';
  });

  }

  const btnModalCancelRel = document.getElementById('btn-modal-cancel-rel');

  if (btnModalCancelRel) btnModalCancelRel.addEventListener('click', closeReleaseModal);

  const btnReleaseDone = document.getElementById('btn-release-done');

  if (btnReleaseDone) btnReleaseDone.addEventListener('click', () => {
    closeReleaseModal();
    navigateTo('dashboard');
  });

  const btnReleaseErrorBack = document.getElementById('btn-release-error-back');

  if (btnReleaseErrorBack) {
    btnReleaseErrorBack.addEventListener('click', () => {
      resetReleaseModal();
      const input = document.getElementById('modal-rel-ref');
      if (input) { input.value = ''; input.focus(); }
    });
  }

  const btnReleaseTryAnother = document.getElementById('btn-release-try-another');

  if (btnReleaseTryAnother) {
    btnReleaseTryAnother.addEventListener('click', () => {
      resetReleaseModal();
      const input = document.getElementById('modal-rel-ref');
      if (input) { input.value = ''; input.focus(); }
    });
  }

  const modalRelDept = document.getElementById('modal-rel-dept');

  if (modalRelDept) {
    modalRelDept.addEventListener('change', () => {
      fillLocationsForDept(modalRelDept.value, 'modal-rel-location', 'm-rel-location-auto', 'm-rel-location-auto-text');
    });
  }

  const modalRelToner = document.getElementById('modal-rel-toner');

  if (modalRelToner) {
    modalRelToner.addEventListener('change', () => {
      const opt = modalRelToner.options[modalRelToner.selectedIndex];
      const hint = document.getElementById('modal-rel-stock-hint');
      if (hint && opt && opt.value) {
        const q = opt.getAttribute('data-qty') || '0';
        hint.textContent = `${opt.value}: ${q} unit(s) on hand`;
      } else if (hint) hint.textContent = '';
    });
  }

  // Close action modals when clicking the shared backdrop (but not the cards themselves)
  // Backdrop is visual only — not clickable. Modals close only via their buttons.
  const modalBackdrop = document.getElementById('modal-backdrop');

  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', (e) => {
      // Intentionally do nothing when clicking the gray/blurred background
      e.stopPropagation();
    });
  }

  const relDetailBackdrop = document.getElementById('modal-release-detail-backdrop');

  if (relDetailBackdrop) relDetailBackdrop.addEventListener('click', closeReleaseDetailModal);
}

Object.assign(window, {
  openReleaseModal,
  closeReleaseModal,
  resetReleaseModal,
  populateReleaseLocationsForDepartment,
  showReleaseError,
  searchAndDisplayReleaseModal,
  loadReleaseLocations,
  loadAdminUsersForIssuance,
  populateDeptSelect,
  populateIssuedBySelect,
  fillLocationsForDept,
  onReleaseLocationChange,
  initReleaseModalListeners
});
