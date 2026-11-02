// =============================================================================
// try-it.ts — Console de requêtes (accueil et documentation), statut de l'API (accueil)
// =============================================================================

import { $ } from 'core-ts';

const METHODS_WITH_BODY = ['POST', 'PUT', 'PATCH'];

// Chaque identifiant est unique et présent dans _api_console.html.twig
const byId = <T extends HTMLElement>(id: string): T => $<T>(`#${id}`) as T;

const form = byId<HTMLFormElement>('try-it');
const methodControl = byId('try-method');
const urlInput = byId<HTMLInputElement>('try-url');
const bodyField = byId('try-body-field');
const bodyInput = byId<HTMLTextAreaElement>('try-body');
const errorAlert = byId('try-error');
const errorMessage = byId('try-error-message');
const responseBlock = byId('try-response');
const responseRequest = byId('try-request');
const responseDuration = byId('try-duration');
const responseStatus = byId('try-status');
const responseOutput = byId('try-output');
const presets = document.querySelectorAll<HTMLButtonElement>('[data-try-preset]');

const getMethod = (): string =>
  methodControl.querySelector<HTMLElement>('[aria-checked="true"]')?.dataset.value ?? 'GET';

const syncBodyField = (): void => {
  bodyField.hidden = !METHODS_WITH_BODY.includes(getMethod());
};

// Passe par un clic : l'UI kit tient à jour l'état et le focus du segmented control
const selectMethod = (method: string): void => {
  methodControl.querySelector<HTMLElement>(`[data-value="${method}"]`)?.click();
  syncBodyField();
};

const highlightPreset = (selected: HTMLButtonElement | null): void => {
  presets.forEach((preset) => {
    preset.classList.toggle('is-selected', preset === selected);
    preset.setAttribute('aria-pressed', String(preset === selected));
  });
};

const prettify = (content: string): string => {
  try {
    return JSON.stringify(JSON.parse(content), null, 2);
  } catch {
    return content;
  }
};

const send = async (): Promise<void> => {
  const method = getMethod();
  const url = urlInput.value.trim();
  const init: RequestInit = { method, headers: { Accept: 'application/json' } };

  if (METHODS_WITH_BODY.includes(method)) {
    init.body = bodyInput.value;
    init.headers = { ...init.headers, 'Content-Type': 'application/json; charset=UTF-8' };
  }

  const start = performance.now();
  try {
    const response = await fetch(url, init);
    const content = prettify(await response.text());

    responseRequest.textContent = `${method} ${url}`;
    responseDuration.textContent = `${Math.round(performance.now() - start)} ms`;
    responseStatus.textContent = String(response.status);
    responseStatus.className = `badge badge-solid-${response.ok ? 'success' : response.status < 500 ? 'warning' : 'danger'}`;
    responseOutput.textContent = content || 'Aucun contenu';
    responseBlock.hidden = false;
    errorAlert.hidden = true;
  } catch {
    errorMessage.textContent = "La requête n'a pas abouti : vérifiez l'URL et votre connexion.";
    errorAlert.hidden = false;
    responseBlock.hidden = true;
  }
};

const checkHealth = async (): Promise<void> => {
  const badge = document.getElementById('health-status');
  if (!badge) return;

  let up = false;

  try {
    up = (await fetch(badge.dataset.url!)).ok;
  } catch {
    // Serveur injoignable : le badge affiche l'indisponibilité
  }

  badge.textContent = up ? 'API opérationnelle' : 'API indisponible';
  badge.className = `badge badge-solid-${up ? 'success' : 'danger'} mb-4`;
};

presets.forEach((preset) => {
  preset.addEventListener('click', () => {
    selectMethod(preset.dataset.method!);
    urlInput.value = preset.dataset.url!;
    bodyInput.value = preset.dataset.body!;
    highlightPreset(preset);
    void send();
  });
});

methodControl.addEventListener('ds:change', () => {
  syncBodyField();
  highlightPreset(null);
});
urlInput.addEventListener('input', () => highlightPreset(null));
bodyInput.addEventListener('input', () => highlightPreset(null));

form.addEventListener('submit', (event) => {
  event.preventDefault();
  void send();
});

void checkHealth();
void send();
