function read(key: string): unknown {
  try {
    return JSON.parse(localStorage.getItem(`nuswhispers.${key}`) || 'null');
  } catch {
    return null;
  }
}

export function remember(key: string, value: unknown) {
  if (!key) return;
  try {
    localStorage.setItem(`nuswhispers.${key}`, JSON.stringify(value));
  } catch {
    /* Storage can be unavailable in private browsing. */
  }
}

export function recall(key: string): string | undefined {
  const value = key ? read(key) : null;
  return typeof value === 'string' ? value : undefined;
}

export function remainingSubmissions() {
  const today = new Date().toDateString();
  if (read('confessionLimit.date') !== today) {
    remember('confessionLimit.date', today);
    remember('confessionLimit.count', 3);
    return 3;
  }
  const count = read('confessionLimit.count');
  return typeof count === 'number' && Number.isFinite(count) ? Math.max(0, count) : 3;
}

export function recordSubmission() {
  remember('confessionLimit.count', Math.max(0, remainingSubmissions() - 1));
}

export function normalizeStory(content: string) {
  return content
    .replace(/nus\s*whispers?\b/gi, 'NUSWhispers')
    .replace(/nus\s*mods?\b/gi, 'NUSMods')
    .trim();
}
