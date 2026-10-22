import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { normalizeStory, recordSubmission, remainingSubmissions } from './storage';

beforeEach(() => {
  const values = new Map<string, string>();
  vi.stubGlobal('localStorage', {
    getItem: (key: string) => values.get(key) ?? null,
    setItem: (key: string, value: string) => values.set(key, value),
  });
});
afterEach(() => {
  vi.unstubAllGlobals();
  vi.useRealTimers();
});

it('retains the legacy daily limit and resets it on the next local day', () => {
  vi.useFakeTimers();
  vi.setSystemTime(new Date('2026-10-22T12:00:00'));
  expect(remainingSubmissions()).toBe(3);
  recordSubmission();
  recordSubmission();
  recordSubmission();
  expect(remainingSubmissions()).toBe(0);
  expect(localStorage.getItem('nuswhispers.confessionLimit.count')).toBe('0');
  vi.setSystemTime(new Date('2026-10-23T12:00:00'));
  expect(remainingSubmissions()).toBe(3);
});
it('handles unavailable storage', () => {
  vi.stubGlobal('localStorage', {
    getItem: () => {
      throw new Error('blocked');
    },
    setItem: () => {
      throw new Error('blocked');
    },
  });
  expect(remainingSubmissions()).toBe(3);
  expect(() => recordSubmission()).not.toThrow();
});
it('preserves story normalization', () =>
  expect(normalizeStory('  nus whispers and nus mods  ')).toBe('NUSWhispers and NUSMods'));
